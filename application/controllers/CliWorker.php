<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Transfer;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Charge;
use Stripe\Subscription;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Identity\VerificationSession;

class CliWorker extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Restrict access to CLI only
        if (!$this->input->is_cli_request()) {
            show_error('This script can only be accessed via the command line.', 403);
        }

        // Set timezone to Brisbane
        $secretKey = stripe_secret_key();
        if ($secretKey) {
            Stripe::setApiKey($secretKey);
        } else {
            log_message('error', 'Stripe secret key missing; please set STRIPE_SECRET_KEY in .env');
        }
        date_default_timezone_set('Australia/Brisbane');

        $this->load->model('AuctionModel');
    }

    public function checkVaultReminders()
    {
        $this->load->model('VaultModel');
        $this->load->model('EmailQueueModel');

        // Use Database time to avoid PHP/MySQL timezone mismatches
        $sql = "SELECT * FROM tbl_vault_draws 
                WHERE status IN ('active', 'scheduled') 
                AND draw_at BETWEEN DATE_ADD(NOW(), INTERVAL 55 MINUTE) AND DATE_ADD(NOW(), INTERVAL 65 MINUTE)";

        $draws = $this->db->query($sql)->result();

        if (empty($draws)) {
            echo "No upcoming draws found for reminder.\n";
            return;
        }

        foreach ($draws as $draw) {
            echo "Queueing reminders for draw: {$draw->title}\n";
            
            // Get opted-in users
            $sql = "SELECT u.id, u.first_name, c.email 
                    FROM tbl_users u 
                    JOIN tbl_user_credentials c ON u.id = c.user_id
                    WHERE u.notification_prefs IS NULL 
                    OR u.notification_prefs LIKE '%\"vault_reminders\":true%'";
            
            $users = $this->db->query($sql)->result();
            $scheduleTime = date('Y-m-d H:i:s');

            foreach ($users as $user) {
                // Check if we already queued a reminder for this user+draw? 
                // Skipped for simplicity, but good for production.

                $this->EmailQueueModel->add([
                    'to_email'      => $user->email,
                    'subject'       => "⏰ 1 Hour Left: " . $draw->title,
                    'template'      => "vault_drop_reminder",
                    'name'          => $user->first_name,
                    'user_id'       => $user->id,
                    'auction_id'    => $draw->id, 
                    'auction_item'  => $draw->title, // Mapped to $draw_title
                    'auction_amount'=> 0,
                    'auction_total' => 0,
                    'auction_url'   => base_url('vault'),
                    'scheduled_at'  => $scheduleTime,
                    'status'        => 'pending',
                    'error_message' => '',
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                ]);
            }
            echo "Queued " . count($users) . " reminders.\n";
        }
    }

    /**
     * Monitor auction end time and update status to 'ended'.
     * Usage: php index.php CliWorker monitorAuctionEnd <auctionId> <endTimestamp>
     */
    public function monitorAuctionEnd($auctionId = null, $endTimestamp = null)
    {
        log_message('info', "CliWorker: monitorAuctionEnd STARTED for auction ID {$auctionId} at " . date('Y-m-d H:i:s'));

        // Validate inputs
        if (!$auctionId || !$endTimestamp || !is_numeric($endTimestamp)) {
            log_message('error', "CliWorker: Invalid parameters received (auctionId: {$auctionId}, endTimestamp: {$endTimestamp})");
            echo "Invalid parameters.\n";
            return;
        }

        // Fetch auction
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);
        if (!$auctionData) {
            log_message('error', "CliWorker: Auction not found with ID: $auctionId");
            echo "Auction not found.\n";
            return;
        }

        $now = time();
        $wait = $endTimestamp - $now;

        if ($wait > 0) {
            log_message('info', "CliWorker: Auction ID $auctionId will sleep for {$wait} seconds (until " . date('Y-m-d H:i:s', $endTimestamp) . ").");
            sleep($wait);
        } else {
            log_message('debug', "CliWorker: Auction ID $auctionId already expired (timestamp difference = $wait). Proceeding immediately.");
        }

        $this->load->model('AuctionModel');

        // Check for highest bid
        $currentHighestBid = $this->AuctionModel->getCurrentHighestBidWithUser($auctionId);

        if ($currentHighestBid['user_id'] !== null) {
            try {
                $this->load->model('UserModel');
                $userId = $currentHighestBid['user_id'];
                $maxBid = $currentHighestBid['current_highest_bid'];
                $user = $this->UserModel->get_users_information_by_id($userId);

                if ($user) {
                    $this->load->model('EmailQueueModel');
                    $auctionDetails = $this->AuctionModel->getAuctionInformationById($auctionId);

                    if (!isset($auctionDetails[0])) {
                        log_message('error', "CliWorker: No auction details found for auction ID {$auctionId}.");
                    } else {

                        // Update auction to 'expired'
                        $updateData = [
                            'auction_type_id' => 9, // Won
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        $this->AuctionModel->updateAuctionStatusTypeData($auctionId, $updateData);

                        $webhookData = [
                            'auction'       => [
                                'auction_id' => $auctionId,
                                'auction_type_id' => 9
                            ]
                        ];

                        // ✅ Send webhook and get result including payload
                        $webhookResponse = send_webhook_event('auction.win', 'auction', $webhookData);

                        log_message('info', "CliWorker: Auction ID $auctionId marked as won.");

                        // ✅ Reward +150 coins for winning an auction
                        $this->load->model('CoinModel');
                        $this->CoinModel->add_coins($userId, 150, "Won Auction #{$auctionId}", 'auction', $auctionId);

                        log_message('info', "CliWorker: Winner email queued for user ID $userId (Auction ID $auctionId, Bid: $maxBid)");


                        $scheduleTime = date('Y-m-d H:i:s');

                        $auction = $auctionDetails[0];

                        $this->EmailQueueModel->add([
                            'to_email'     => $user->email,
                            'subject'      => "Your Invoice from Storage Punters",
                            'template'     => "auction_invoice_email",
                            'name'         => $user->first_name . " " . $user->last_name,
                            'user_id'  =>  $userId,
                            'auction_id'    => $auctionId,
                            'auction_item'  => trim($auction->auction_storage_facility_name . ' #' . $auction->auction_id . ' - ' . $auction->auction_storage_facility_city . ' , ' . $auction->auction_storage_facility_state),
                            'auction_amount' => $maxBid,
                            'auction_total' => $maxBid,
                            'auction_url'  => base_url('auctions/' . $auctionId),
                            'scheduled_at' => $scheduleTime,
                            'status'       => 'pending',
                            'error_message'        => '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);


                        $this->auctionAutomationPaymentFromUserToBackgroundSchedule($auctionId);
                    }
                } else {
                    log_message('error', "CliWorker: User ID $userId not found. Cannot queue winner email.");
                }
            } catch (Exception $e) {
                log_message('error', "CliWorker: Exception while processing winner for auction ID $auctionId - " . $e->getMessage());
            }
        } else {

            // Update auction to 'expired'
            $updateData = [
                'auction_type_id' => 10, // Expired
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->AuctionModel->updateAuctionStatusTypeData($auctionId, $updateData);

            $webhookData = [
                'auction'       => [
                    'auction_id' => $auctionId,
                    'auction_type_id' => 10
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('auction.expired', 'auction', $webhookData);

            log_message('info', "CliWorker: Auction ID $auctionId marked as expired.");

            log_message('info', "CliWorker: No bids placed for Auction ID $auctionId. No winner email sent.");
        }

        echo "✅ Auction ID $auctionId processed at " . date('Y-m-d H:i:s') . "\n";
    }


    public function monitorInvoicePaymentRelease($invoiceId = null, $scheduleTimestamp = null)
    {
        log_message('info', "CliWorker: monitorInvoiceRelease STARTED for invoice ID {$invoiceId} at " . date('Y-m-d H:i:s'));

        // ✅ Validate inputs
        if (!$invoiceId || !$scheduleTimestamp || !is_numeric($scheduleTimestamp)) {
            log_message('error', "CliWorker: Invalid parameters received (invoiceId: {$invoiceId}, scheduleTimestamp: {$scheduleTimestamp})");
            echo "Invalid parameters.\n";
            return;
        }

        // ✅ Fetch invoice
        $this->load->model('InvoiceModel');
        $invoiceData = $this->InvoiceModel->getInvoiceById($invoiceId);

        if (!$invoiceData) {
            log_message('error', "CliWorker: Invoice not found with ID: {$invoiceId}");
            echo "Invoice not found.\n";
            return;
        }

        // ✅ Wait until scheduled time
        $now = time();
        $wait = $scheduleTimestamp - $now;

        if ($wait > 0) {
            log_message('info', "CliWorker: Invoice ID {$invoiceId} sleeping for {$wait} seconds (until " . date('Y-m-d H:i:s', $endTimestamp) . ").");
            sleep($wait);
        } else {
            log_message('debug', "CliWorker: Invoice ID {$invoiceId} schedule time already passed ({$wait}s ago). Proceeding immediately.");
        }

        // ✅ Load models
        $this->load->model('StorageFacilityModel');
        $this->load->model('LogsModel');
        $this->load->model('BankModel');

        // ✅ Fetch related data
        $auctionData = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoiceId);
        if (!$auctionData) {
            log_message('error', "CliWorker: No auction found for invoice ID {$invoiceId}");
            echo "No related auction found.\n";
            return;
        }

        $auctionId = $auctionData['auction_id'];

        $storageFacility = $this->StorageFacilityModel->getStorageFacilityInformationByAuctionId($auctionId);
        if (!$storageFacility) {
            log_message('error', "CliWorker: No storage facility found for auction ID {$auctionId}");
            echo "No storage facility found.\n";
            return;
        }

        $bank = $this->StorageFacilityModel->getBankIdByStorageFacilityById($storageFacility['storage_facility_id']);
        if (!$bank) {
            log_message('error', "CliWorker: No bank found for storage facility ID {$storageFacility['storage_facility_id']}");
            echo "No bank account found for storage facility.\n";
            return;
        }

        $bankId = $bank['bank_id']; // connected account ID

        $bankDetails = $this->BankModel->getBankAccountByBankId($bankId);
        if (!$bankDetails) {
            log_message('error', "CliWorker: No bank found for bank ID {$bankId}");
            echo "No bank account found.\n";
            return;
        }

        $invoicePaymentStripeLogsData = $this->InvoiceModel->getInvoiceStripePaymentLogsByInvoiceId($invoiceId);
        if (!$invoicePaymentStripeLogsData) {
            log_message('error', "CliWorker: No payment logs found for invoice ID {$invoiceId}");
            echo "No payment logs found.\n";
            return;
        }

        $stripeAccountId = $bankDetails['stripe_bank_account_id']; // connected account ID
        $amountToTransfer = (float)$invoicePaymentStripeLogsData['amount_transfer'];
        $amountInCents = (int) round($amountToTransfer * 100);

        try {
            // 💸 Perform Stripe transfer
            $transfer = \Stripe\Transfer::create([
                'amount'      => $amountInCents,
                'currency'    => 'aud',
                'destination' => $stripeAccountId,
                'description' => "Payout for Invoice #{$invoiceId} (Auction #{$auctionId})",
                'metadata'    => [
                    'invoice_id' => $invoiceId,
                    'auction_id' => $auctionId
                ]
            ]);

            // ✅ Update logs
            $this->LogsModel->updateStripePaymentReleaseStatus($invoiceId, [
                'schedule_status'   => 'paid',
                'stripe_transfer_id' => $transfer->id,
                'released_at'       => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
            ]);

            $webhookData = [
                'transfer'       => [
                    'auction_id' => $auctionId,
                    'invoice_id' => $invoiceId,
                    'amount'      => $amountInCents,
                    'currency'    => 'aud',
                    'destination' => $stripeAccountId,
                    'description' => "Payout for Invoice #{$invoiceId} (Auction #{$auctionId})",
                    'stripe_transfer_id' => $transfer->id
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('payout.sent', 'payout', $webhookData);


            log_message('info', "CliWorker: Invoice ID {$invoiceId} marked as paid and Stripe transfer completed ({$transfer->id}).");
            echo "✅ Invoice ID {$invoiceId} payout processed successfully.\n";
        } catch (\Stripe\Exception\ApiErrorException $e) {
            log_message('error', "CliWorker: Stripe API error for invoice ID {$invoiceId}: " . $e->getMessage());
            echo "Stripe error: " . $e->getMessage() . "\n";
        } catch (Exception $e) {
            log_message('error', "CliWorker: General exception for invoice ID {$invoiceId}: " . $e->getMessage());
            echo "Error: " . $e->getMessage() . "\n";
        }

        log_message('info', "CliWorker: monitorInvoiceRelease COMPLETED for invoice ID {$invoiceId} at " . date('Y-m-d H:i:s'));
    }

    public function auctionAutomationPaymentFromUserToBackgroundSchedule($auctionId)
    {

        // Step 1: Get current time + 5 minutes in Brisbane
        $endDateTime = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
        // $endDateTime->modify('+1 minutes');
        $endTimestamp = $endDateTime->getTimestamp();

        $phpPath = '/usr/bin/php'; // Replace with actual path if different

        // On Windows Apache, PHP_BINARY may point to httpd.exe — fallback
        if (str_contains(strtolower($phpPath), 'httpd.exe')) {
            $phpPath = 'C:\xampp\php\php.exe'; // XAMPP fallback path
        }

        // Step 3: Paths & redirection
        $indexPhpPath = FCPATH . "index.php";
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        // $redirect = $isWindows ? '> NUL 2>&1' : '> /dev/null 2>&1';

        if ($isWindows) {
            log_message('error', 'Scheduling via `at` is not supported on Windows in this function.');
            return;
        }

        if (!$isWindows) {
            // Step 4: Build and run command


            // Format time/date for `at`
            $time = date('H:i', $endTimestamp);         // e.g., 19:00
            $date = date('Y-m-d', $endTimestamp);       // e.g., 2025-08-03

            $cliCommand = "\"{$phpPath}\" \"{$indexPhpPath}\" Email process_auction_invoice_email_by_id {$auctionId} {$endTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";

            // Step 5: Log message
        }

        exec($scheduleCmd, $output, $resultCode);

        if ($resultCode === 0) {
            log_message('info', "Winner Email for Auction ID {$auctionId} will be sent in 1 minutes (Brisbane time: {$endDateTime->format('Y-m-d H:i:s')}, timestamp: {$endTimestamp}).");
            log_message('debug', "CLI command executed: {$cliCommand}");
        } else {
            log_message('error', "Failed to schedule winner for auction ID {$auctionId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }
    /**
     * Periodically check for overdue invoices and apply late fees
     * Usage: php index.php CliWorker checkLateFees
     */
    public function checkLateFees()
    {
        $this->load->model('InvoiceModel');
        $overdueInvoices = $this->InvoiceModel->getOverdueInvoices();

        if (empty($overdueInvoices)) {
            echo "No overdue invoices found.\n";
            return;
        }

        $feeAmount = 15.00; // Fixed late fee
        $count = 0;

        foreach ($overdueInvoices as $invoice) {
            $result = $this->InvoiceModel->applyLateFee($invoice['id'], $feeAmount);
            if ($result) {
                echo "Applied $15.00 late fee to Invoice #{$invoice['invoice_number']} (ID: {$invoice['id']})\n";
                $count++;
                
                // Optional: Queue an email notification about the late fee
                // $this->EmailQueueModel->add_late_fee_notification($invoice['id']);
            }
        }

        echo "✅ Processed {$count} late fees.\n";
    }
}

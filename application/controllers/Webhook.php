<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
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

class Webhook extends CI_Controller
{
    private $stripe_secret;

    public function __construct()
    {
        parent::__construct();

        // Load Auction Model
        $this->load->model('LogsModel');
        $this->load->library('SubscriptionGiveawayWebhookService');

        // Use stripe_helper functions for proper env loading
        $secretKey = stripe_secret_key();
        if ($secretKey) {
            Stripe::setApiKey($secretKey);
        } else {
            log_message('error', 'Stripe secret key missing; please configure STRIPE_SECRET_KEY in .env');
        }

        $this->stripe_secret = stripe_webhook_secret();
        date_default_timezone_set('Australia/Brisbane');
    }

    public function stripe_webhook()
    {
        try {
            $payload = @file_get_contents('php://input');
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

            // In development mode: skip signature verification
            if (ENVIRONMENT === 'development' || empty($this->stripe_secret)) {
                $event = json_decode($payload);
            } else {
                if (!$sig_header) {
                    return $this->respond(false, 'Missing Stripe signature', [], 400);
                }

                try {
                    $event = \Stripe\Webhook::constructEvent(
                        $payload,
                        $sig_header,
                        $this->stripe_secret
                    );
                } catch (\UnexpectedValueException $e) {
                    return $this->respond(false, 'Invalid payload', [], 400);
                } catch (\Stripe\Exception\SignatureVerificationException $e) {
                    return $this->respond(false, 'Invalid signature', [], 400);
                }
            }

        $eventType = $event->type;
        $eventObject = $event->data->object ?? null;

        log_message('info', 'Stripe Webhook received: ' . $eventType);

        // --- Giveaway subscription events routed here via ngrok (Stripe configured for /webhook/stripe) ---
        if ($eventType === 'checkout.session.completed') {
            $isGiveaway = $this->isGiveawayCheckoutSession($eventObject);
            log_message('info', 'checkout.session.completed - isGiveaway: ' . ($isGiveaway ? 'YES' : 'NO'));
            
            if ($isGiveaway) {
                log_message('info', 'Processing giveaway checkout.session.completed');
                $this->subscriptiongiveawaywebhookservice->handleCheckoutCompleted($eventObject);
                return $this->respond(true, 'Giveaway subscription checkout processed', [], 200);
            }
        }

        if (
            $eventType === 'invoice.payment_succeeded'
            && $this->giveawaySubscriptionExists($eventObject->subscription ?? null)
        ) {
            $this->subscriptiongiveawaywebhookservice->handleInvoicePaid($eventObject);
            return $this->respond(true, 'Giveaway subscription renewal processed', [], 200);
        }

        if (
            $eventType === 'customer.subscription.updated'
            && $this->giveawaySubscriptionExists($eventObject->id ?? null)
        ) {
            $this->subscriptiongiveawaywebhookservice->handleSubscriptionUpdated($eventObject);
            return $this->respond(true, 'Giveaway subscription updated', [], 200);
        }

        if (
            $eventType === 'customer.subscription.deleted'
            && $this->giveawaySubscriptionExists($eventObject->id ?? null)
        ) {
            $this->subscriptiongiveawaywebhookservice->handleSubscriptionDeleted($eventObject);
            return $this->respond(true, 'Giveaway subscription cancelled', [], 200);
        }

        // --- 1. Handle other subscription renewals BEFORE invoice validation ---
        if ($eventType == 'invoice.payment_succeeded') {
            $this->process_subscription_renewal($eventObject);
            return $this->respond(true, 'Subscription renewal processed', [], 200);
        }

        // --- 2. Validate Invoice ID (For Auction Payments) ---
        $invoice_id = $event->data->object->metadata->invoice_id ?? null;

        if (empty($invoice_id)) {
            return $this->respond(true, 'Invoice Id is null.', [], 200);
        }

        $this->load->model('InvoiceModel');

        $invoiceData = $this->InvoiceModel->getInvoiceById($invoice_id);
        if (!$invoiceData) {

            return $this->respond(true, 'Invoice Id # ' . $invoice_id . ' not found', [], 200);
        }

        $total_amount = $invoiceData['total_amount'];

        // ✅ Prepare data to insert into database
        $logsData = [
            "stripe_event_id" => $event->id,
            "stripe_payment_intent_id" => $event->data->object->id,
            "stripe_customer_id" =>  $event->data->object->customer,
            "stripe_charge_id" => $event->data->object->latest_charge ?? null,
            "amount_received" => ($event->data->object->amount_received ?? $event->data->object->amount ?? 0) / 100,
            "currency" => $event->data->object->currency,
            "payment_status" =>  $event->data->object->status,
            "event_type" =>  $event->type,
            "payment_at" => date('Y-m-d H:i:s', $event->created),
            "invoice_id" => $event->data->object->metadata->invoice_id,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];

        $this->LogsModel->insert_stripe_invoice_payment_logs_data($logsData);


        if ($eventType == 'payment_intent.succeeded') {
            // ... (one-time payment handle)
            $invoiceData2 = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoice_id);

            if (!$invoiceData2) {
                return $this->respond(true, 'Invoice Id # ' . $invoice_id . ' not found', [], 200);
            }

            $userId = $invoiceData2['user_id'];
            $this->load->model('UserModel');

            // 🧾 Step 1: Update invoice status to paid
            $invoiceResponse = $this->invoice_payment_success($event->data->object);
            $sendInvoiceAfterSoldResponse = $this->send_invoice_after_sold($event->data->object->metadata->invoice_id);

            // 💸 Step 2: Create payment release log for successful payments
            $releaseResponse = $this->schedule_payment_release(
                $event->data->object->metadata->invoice_id ?? null,
                $event->data->object
            );

            return $this->respond(true, 'Invoice updated and payment release log created successfully', [], 200);
        } elseif ($eventType == 'invoice.payment_succeeded') {
            // 🟢 Handle Subscription Renewal
            $invoice = $event->data->object;
            $subscription_id = $invoice->subscription;
            
            if ($subscription_id) {
                $this->load->model('VaultModel');
                $this->load->model('CoinModel');
                $this->load->model('UserModel');
                
                $customer_id = $invoice->customer;
                $userStripeData = $this->UserModel->getUserByStripeCustomerId($customer_id);
                
                if ($userStripeData) {
                    $userId = $userStripeData['user_id'];
                    $tier = $userStripeData['subscription_tier'];
                    
                    // Award Coins (+500)
                    $this->CoinModel->add_coins($userId, 500, 'subscription_renewal', 'subscription', $subscription_id, 'Subscription renewal bonus');
                    
                    // Award Entries based on tier
                    $entry_map = ['player' => 1, 'shark' => 5, 'whale' => 20];
                    $entries = $entry_map[$tier] ?? 0;
                    
                    if ($entries > 0) {
                        $draw = $this->VaultModel->get_active_draw();
                        if ($draw) {
                            $this->VaultModel->add_entries($draw['id'], $userId, $entries, 'subscription_credit', ['subscription_id' => $subscription_id]);
                        }
                    }
                }
            }
            return $this->respond(true, 'Subscription renewal processed');
        } elseif ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;
            if (isset($session->metadata->type) && $session->metadata->type === 'vault_purchase') {
                $user_id = $session->metadata->user_id;
                $package_id = $session->metadata->package_id;
                
                $this->load->model('CoinModel');
                $this->load->model('VaultModel');
                
                $package = $this->CoinModel->get_cash_package_by_id($package_id);
                if ($package) {
                    $draw = $this->VaultModel->get_active_draw();
                    if ($draw) {
                        $entries = (int)$package['entry_count'] + (int)($package['bonus_entries'] ?? 0);
                        $this->VaultModel->add_entries($draw['id'], $user_id, $entries, 'cash_purchase', [
                            'package_id' => $package_id,
                            'stripe_session_id' => $session->id
                        ]);
                    }
                }
            }
            return $this->respond(true, 'Checkout session processed');
        } elseif ($event->type == 'payment_intent.payment_failed') {
            return $this->invoice_payment_failed($invoice_id);

        } else {
            return $this->respond(true, 'Event ignored', [], 200);
        }
        } catch (\Exception $e) {
            log_message('error', 'Webhook Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', 'Webhook Stack Trace: ' . $e->getTraceAsString());
            return $this->respond(false, 'Internal server error: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Process Subscription Renewal Rewards (with Compounding Entry Engine)
     */
    private function process_subscription_renewal($invoice)
    {
        // Only process subscription invoices
        if (empty($invoice->subscription)) return;

        // 1. Identify User
        $stripeCustomerId = $invoice->customer;
        $this->load->model('UserModel');
        $user = $this->db->get_where('tbl_user_stripe_relationship', ['stripe_customer_id' => $stripeCustomerId])->row();

        if (!$user) {
            log_message('error', "Webhook: User not found for Stripe Customer ID $stripeCustomerId");
            return;
        }
        $user_id = $user->user_id;

        // 2. Identify Tier (Product)
        try {
            $subscription = \Stripe\Subscription::retrieve($invoice->subscription);
            $productId = $subscription->items->data[0]->price->product;
            $product = \Stripe\Product::retrieve($productId);
            $planName = strtolower($product->name); // player, shark, whale
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', "Webhook: Failed to retrieve subscription (using fallback): " . $e->getMessage());
                $planName = 'whale';
                $subscription = (object)['id' => $invoice->subscription]; 
            } else {
                log_message('error', "Webhook: Failed to retrieve subscription details: " . $e->getMessage());
                return;
            }
        }

        // 3. Define Base Rewards
        $baseEntries = 0;
        $coins = 500;

        if (strpos($planName, 'player') !== false) {
            $baseEntries = 1;
        } elseif (strpos($planName, 'shark') !== false) {
            $baseEntries = 5;
        } elseif (strpos($planName, 'whale') !== false) {
            $baseEntries = 20;
        }

        // 4. Award Rewards (Legacy Vault System)
        $this->load->model('VaultModel');
        $this->load->model('CoinModel');

        // Coins
        $this->CoinModel->updateBalance(
            $user_id,
            $coins,
            'subscription_renewal',
            'subscription_id',
            $subscription->id,
            "Monthly Renewal Bonus ($planName)"
        );

        // Legacy Entries (if Active Draw exists)
        $activeDraw = $this->VaultModel->getActiveDraw();
        if ($activeDraw && $baseEntries > 0) {
            $this->VaultModel->addEntries(
                $user_id,
                $activeDraw->id,
                $baseEntries,
                'subscription_credit',
                ['subscription_id' => $subscription->id, 'plan' => $planName]
            );
        }

        // 5. NEW: Compounding Entry Engine via SubscriptionGiveawayModel
        $this->load->model('SubscriptionGiveawayModel');
        $punterSub = $this->SubscriptionGiveawayModel->get_subscription_by_stripe_id($invoice->subscription);

        if ($punterSub) {
            // Skip first invoice (handled by checkout.session.completed)
            if ($invoice->billing_reason !== 'subscription_create') {
                $newStreak = (int)$punterSub['streak_months'] + 1;

                // Update streak
                $this->SubscriptionGiveawayModel->update_subscription($punterSub['id'], [
                    'streak_months' => $newStreak,
                    'status' => 'active',
                    'cancel_at_period_end' => 0
                ]);

                $this->db->where('id', $user_id);
                $this->db->update('tbl_users', [
                    'streak_months' => $newStreak,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Grant compounding entries
                $giveawayDraw = $this->SubscriptionGiveawayModel->get_active_draw();
                if ($giveawayDraw) {
                    $compoundedEntries = $this->SubscriptionGiveawayModel->grant_monthly_entries(
                        $user_id,
                        $giveawayDraw['id'],
                        $punterSub['base_entries'],
                        $newStreak
                    );
                    log_message('info', "Webhook: Compounding entries for User $user_id, streak $newStreak, granted $compoundedEntries entries");
                }
            }
        }

        log_message('info', "Webhook: Processed renewal for User $user_id ($planName). Added $baseEntries base entries & $coins coins.");
    }
            private function invoice_payment_success($paymentIntent)
    {
        // Load the Invoice Model
        $this->load->model('InvoiceModel');

        // Extract PaymentIntent ID from Stripe webhook payload
        $payment_intent_id = $paymentIntent->id ?? null;

        if (!$payment_intent_id) {
            return $this->respond(false, 'Missing payment_intent ID', [], 400);
        }

        // Find the invoice by PaymentIntent ID
        $invoice = $this->InvoiceModel->getByPaymentIntentId($payment_intent_id);

        if (!$invoice) {
            return $this->respond(false, 'Invoice not found', [], 404);
        }

        $invoice_id = $invoice['invoice_id'];

        // Prepare data to update invoice
        $updateData = [
            'invoice_payment_status_type_id ' => 2, // or whatever you want to set
            'updated_at' => date('Y-m-d H:i:s') // optional: update the timestamp
        ];

        // Update the invoice
        $updated = $this->InvoiceModel->updateStatusByPaymentIntentId($invoice_id, $updateData);

        if ($updated) {


            $auctionData = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoice_id);
            if (!$auctionData) {

                return $this->respond(false, 'Auction not found', [], 404);
            }

            $auctionId = $auctionData['auction_id'];
            $this->auction_won_by_bidder_payment_success($auctionId);

            // ✅ Reward +150 Coins for Winning an Auction
            $this->load->model('CoinModel');
            $this->CoinModel->add_coins($userId, 150, "Won Auction #{$auctionId}", "auction", $auctionId, "Winning Bid Reward");

            return $this->respond(true, 'Invoice updated to paid successfully', [
                'payment_intent_id' => $payment_intent_id,
                'invoice_id' =>  $invoice_id
            ], 200);
        } else {

            return $this->respond(false, 'Failed to update invoice', [], 500);
        }
    }


    public function process_auction_win_test()
    {
        $nowTime = date('Y-m-d H:i:s');

        /*return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Missing or invalid auction ID.',
                'timestamp' => $nowTime
            ]))
            ->set_status_header(400);*/

        // $this->process_auction_win_email_by_id(26);

        // $this->process_auction_win_email_payment_failed_by_id(26);

        $this->auction_won_by_bidder_payment_success(28);
    }

    private function auction_won_by_bidder_payment_success($auctionId)
    {

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
                    $auctionDetails = $this->AuctionModel->getAuctionWonInformationById($auctionId);

                    if (!isset($auctionDetails[0])) {
                        log_message('error', "Webhook: No auction details found for auction ID {$auctionId}.");
                    } else {

                        $auction = $auctionDetails[0];
                        $scheduleTime = date('Y-m-d H:i:s');

                        // Add email to queue
                        $this->EmailQueueModel->add([
                            'to_email'      => $user->email,
                            'subject'       => "Congratulations, You Won the Auction!",
                            'template'      => "auction_winner_email",
                            'name'          => $user->first_name . " " . $user->last_name,
                            'user_id'  =>  $userId,
                            'auction_id'    => $auctionId,
                            'auction_item'  => trim($auction->auction_storage_facility_name . ' #' . $auction->auction_id . ' - ' . $auction->auction_storage_facility_city . ' , ' . $auction->auction_storage_facility_state),
                            'auction_amount' => $maxBid,
                            'auction_total' => $maxBid,
                            'auction_url'   => base_url('auctions/' . $auctionId),
                            'scheduled_at'  => $scheduleTime,
                            'status'        => 'pending',
                            'error_message'        => '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $this->auctionWonEmailToBackgroundSchedule($auctionId);
                    }
                } else {

                    return $this->respond(false, 'User ID #' . $userId . ' not found', [], 404);
                }
            } catch (Exception $e) {

                return $this->respond(false, 'Exception while processing winner for auction ID $auctionId - ' . $e->getMessage(), [], 404);
            }
        } else {
            return $this->respond(false, "No bids placed for Auction ID $auctionId. No winner email sent.", [], 200);
        }
    }

    private function auction_won_by_bidder_payment_failed($auctionId)
    {

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
                    $auctionDetails = $this->AuctionModel->getAuctionWonInformationById($auctionId);

                    if (!isset($auctionDetails[0])) {
                        log_message('error', "Webhook: No auction details found for auction ID {$auctionId}.");
                    } else {

                        $auction = $auctionDetails[0];
                        $scheduleTime = date('Y-m-d H:i:s');

                        // Add email to queue
                        $this->EmailQueueModel->add([
                            'to_email'      => $user->email,
                            'subject'       => "Action Required: Payment Pending for Your Auction Win",
                            'template'      => "auction_winner_email_payment_failed",
                            'name'          => $user->first_name . " " . $user->last_name,
                            'user_id'  =>  $userId,
                            'auction_id'    => $auctionId,
                            'auction_item'  => trim($auction->auction_storage_facility_name . ' #' . $auction->auction_id . ' - ' . $auction->auction_storage_facility_city . ' , ' . $auction->auction_storage_facility_state),
                            'auction_amount' => $maxBid,
                            'auction_total' => $maxBid,
                            'auction_url'   => base_url('auctions/' . $auctionId),
                            'scheduled_at'  => $scheduleTime,
                            'status'        => 'pending',
                            'error_message'        => '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $this->auctionWonEmailPaymentFailedToBackgroundSchedule($auctionId);
                    }
                } else {

                    return $this->respond(false, 'User ID #' . $userId . ' not found', [], 404);
                }
            } catch (Exception $e) {

                return $this->respond(false, 'Exception while processing winner for auction ID $auctionId - ' . $e->getMessage(), [], 404);
            }
        } else {
            return $this->respond(false, "No bids placed for Auction ID $auctionId. No winner email sent.", [], 200);
        }
    }
    private function send_invoice_after_sold($invoice_id)
    {

        // ✅ Validate inputs
        if (empty($invoice_id)) {

            return [
                "status" => "error",
                "message" => "Missing invoice_id."
            ];
        }

        $this->load->model('InvoiceModel');

        $invoiceData = $this->InvoiceModel->getInvoiceById($invoice_id);
        if (!$invoiceData) {

            return [
                "status" => "error",
                "message" => "Invoice not found."
            ];
        }

        $auctionData = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoice_id);
        if (!$auctionData) {
            return [
                "status" => "error",
                "message" => "Auction not found for the invoice id #" . $invoice_id
            ];
        }


        $auctionId = $auctionData['auction_id'];

        $this->load->model('AuctionModel');

        // Fetch auction details from the database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {

            return [
                "status" => "error",
                "message" => "Auction not found for the auction id #" . $auctionId
            ];
        }

        $invoiceStripePaymentInformation = $this->InvoiceModel->getStripeInvoicePaymentInformationByInvoiceId($invoice_id);
        if (!$invoiceStripePaymentInformation || empty($invoiceStripePaymentInformation['invoice_id'])) {

            return [
                "status" => "error",
                "message" => "No invoice stripe information found for the given invoice."
            ];
        } else {
            try {


                $this->load->model('StorageFacilityModel');

                // 1️⃣ Get Storage Facility by Auction ID
                $storageFacility = $this->StorageFacilityModel->getStorageFacilityInformationByAuctionId($auctionId);
                if (!$storageFacility || empty($storageFacility['storage_facility_id'])) {

                    return [
                        "status" => "error",
                        "message" => "No storage facility found for the given auction id #" . $auctionId
                    ];
                }

                $storageFacilityId = $storageFacility['storage_facility_id'];

                // 2️⃣ Get Company by Storage Facility ID
                $company = $this->StorageFacilityModel->getCompanyIdByStorageFacilityById($storageFacilityId);
                if (!$company || empty($company['company_id'])) {

                    return [
                        "status" => "error",
                        "message" => "No company found for the provided storage facility id #" . $storageFacilityId
                    ];
                }

                $companyId = $company['company_id'];

                $this->load->model('CompanyModel');

                // 3️⃣ Get User by Company ID
                $user = $this->CompanyModel->getUserInformationByCompanyId($companyId);
                if (!$user || empty($user['user_id'])) {

                    return [
                        "status" => "error",
                        "message" => "No user found for the given company id #" . $companyId
                    ];
                }


                $storageFacilityUserId = $user['user_id'];

                $this->load->model('UserModel');
                $maxBid = $invoiceStripePaymentInformation['invoice_amount'];
                $user = $this->UserModel->get_users_information_by_id($storageFacilityUserId);

                if ($user) {
                    $auctionDetails = $this->AuctionModel->getAuctionWonInformationById($auctionId);
                    $auction = $auctionDetails[0];
                    $scheduleTime = date('Y-m-d H:i:s');


                    // Update auction to 'expired'
                    $updateData = [
                        'auction_type_id' => 8, // Sold
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $this->AuctionModel->updateAuctionStatusTypeData($auctionId, $updateData);

                    $webhookData = [
                        'auction'       => [
                            'auction_id' => $auctionId,
                            'auction_type_id' => 8
                        ]
                    ];

                    // ✅ Send webhook and get result including payload
                    $webhookResponse = send_webhook_event('auction.sold', 'auction', $webhookData);

                    $this->load->model('EmailQueueModel');
                    // Add email to queue
                    $this->EmailQueueModel->add([
                        'to_email'      => $user->email,
                        'subject'       => "Your Auction Has Ended!",
                        'template'      => "auction_after_sold_email",
                        'name'          => $user->first_name . " " . $user->last_name,
                        'user_id'  =>  $storageFacilityUserId,
                        'auction_id'    => $auctionId,
                        'auction_item'  => trim($auction->auction_storage_facility_name . ' #' . $auction->auction_id . ' - ' . $auction->auction_storage_facility_city . ' , ' . $auction->auction_storage_facility_state),
                        'auction_amount' => $maxBid,
                        'auction_total' => $maxBid,
                        'auction_url'   => base_url('auctions/' . $auctionId),
                        'scheduled_at'  => $scheduleTime,
                        'status'        => 'pending',
                        'error_message'        => '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);


                    // ✅ Add background invoice release schedule
                    $this->addInvoiceAfterSoldToBackgroundSchedule($auctionId, date("Y-m-d H:i:s", strtotime("+1 minute")));



                    return [
                        "status" => "success",
                        "message" => "Email is queued for the send invoice after sold."
                    ];
                } else {



                    return [
                        "status" => "error",
                        "message" => "User facility ID $storageFacilityUserId not found."
                    ];
                }
            } catch (Exception $e) {


                return [
                    "status" => "error",
                    "message" => "Exception while processing winner for auction ID $auctionId - " . $e->getMessage()
                ];
            }
        }
    }

    public function auctionWonEmailToBackgroundSchedule($auctionId)
    {

        // Step 1: Get current time + 5 minutes in Brisbane
        $endDateTime = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
        $endDateTime->modify('+1 minutes');
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

            $cliCommand = "\"{$phpPath}\" \"{$indexPhpPath}\" Email process_auction_win_email_by_id {$auctionId} {$endTimestamp}";
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


    public function auctionWonEmailPaymentFailedToBackgroundSchedule($auctionId)
    {

        // Step 1: Get current time + 5 minutes in Brisbane
        $endDateTime = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
        $endDateTime->modify('+1 minutes');
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

            $cliCommand = "\"{$phpPath}\" \"{$indexPhpPath}\" Email process_auction_win_email_payment_failed_by_id {$auctionId} {$endTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";

            // Step 5: Log message
        }

        exec($scheduleCmd, $output, $resultCode);

        if ($resultCode === 0) {
            log_message('info', "Winner Email (Payment Failed) for Auction ID {$auctionId} will be sent in 1 minutes (Brisbane time: {$endDateTime->format('Y-m-d H:i:s')}, timestamp: {$endTimestamp}).");
            log_message('debug', "CLI command executed: {$cliCommand}");
        } else {
            log_message('error', "Failed to schedule winner for auction ID {$auctionId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }

    private function schedule_payment_release($invoice_id, $paymentIntent)
    {

        // ✅ Validate inputs
        if (empty($invoice_id)) {
            return [
                "status" => "error",
                "message" => "Missing invoice_id."
            ];
        }

        if (empty($paymentIntent) || empty($paymentIntent->amount_received)) {
            return [
                "status" => "error",
                "message" => "Invalid payment intent data."
            ];
        }

        $this->load->model('InvoiceModel');

        $invoiceData = $this->InvoiceModel->getInvoiceById($invoice_id);
        if (!$invoiceData) {
            return [
                "status" => "error",
                "message" => "Invoice not found."
            ];
        }

        $auctionData = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoice_id);
        if (!$auctionData) {
            return [
                "status" => "error",
                "message" => "Auction not found for the invoice #" . $invoice_id
            ];
        }


        $auctionId = $auctionData['auction_id'];


        $this->load->model('AuctionModel');

        // Fetch auction details from the database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {
            return [
                "status" => "error",
                "message" => "Auction not found for the auction id #" . $auctionId
            ];
        }

        $this->load->model('StorageFacilityModel');

        // 1️⃣ Get Storage Facility by Auction ID
        $storageFacility = $this->StorageFacilityModel->getStorageFacilityInformationByAuctionId($auctionId);
        if (!$storageFacility || empty($storageFacility['storage_facility_id'])) {
            return [
                "status" => "error",
                "message" => "No storage facility found for the given auction."
            ];
        }


        $storageFacilityId = $storageFacility['storage_facility_id'];


        // 2️⃣ Get Company by Storage Facility ID
        $company = $this->StorageFacilityModel->getCompanyIdByStorageFacilityById($storageFacilityId);
        if (!$company || empty($company['company_id'])) {
            return [
                "status" => "error",
                "message" => "No company found for the provided storage facility."
            ];
        }


        // 2️⃣ Get Bank by Storage Facility ID
        $bank = $this->StorageFacilityModel->getBankIdByStorageFacilityById($storageFacilityId);
        if (!$bank || empty($bank['bank_id'])) {
            return [
                "status" => "error",
                "message" => "No bank found for the provided storage facility."
            ];
        }

        $companyId = $company['company_id'];
        $bankId = $bank['bank_id'];

        $this->load->model('CompanyModel');

        // 3️⃣ Get User by Company ID
        $user = $this->CompanyModel->getUserInformationByCompanyId($companyId);
        if (!$user || empty($user['user_id'])) {
            return [
                "status" => "error",
                "message" => "No user found for the given company."
            ];
        }


        $storageFacilityUserId = $user['user_id'];

        $total_amount = $invoiceData['total_amount'];

        $paymentReleaseData = [
            "invoice_id" => $invoice_id,
            "invoice_amount" => $total_amount,
            "bank_id" => $bankId,
            "user_id" => $storageFacilityUserId,
            "schedule_at"      => date("Y-m-d H:i:s", strtotime("+3 minute")), // ✅ 3 minutes from now
            "schedule_status" => "pending",
            'stripe_transfer_id' => "",
            'released_at'       => date('Y-m-d H:i:s'),
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];

        // ✅ Save to database
        $inserted = $this->LogsModel->insert_stripe_payment_release_data($paymentReleaseData);

        if ($inserted) {

            // ✅ Add background invoice release schedule
            $this->addInvoiceToBackgroundSchedule($invoice_id, $paymentReleaseData['schedule_at']);


            return [
                "status" => "success",
                "message" => "Payment release log created successfully.",
                "release_log" => $paymentReleaseData
            ];
        } else {
            return [
                "status" => "error",
                "message" => "Failed to insert payment release log."
            ];
        }
    }

    public function addInvoiceToBackgroundSchedule($invoiceId, $scheduleAt)
    {
        // ✅ Step 1: Parse Brisbane schedule datetime
        $scheduleDateTime = new DateTime($scheduleAt, new DateTimeZone('Australia/Brisbane'));
        $scheduleTimestamp = $scheduleDateTime->getTimestamp();

        // ✅ Step 2: Detect PHP CLI path
        $phpPath = '/usr/bin/php';
        if (str_contains(strtolower($phpPath), 'httpd.exe')) {
            $phpPath = 'C:\xampp\php\php.exe'; // fallback for Windows dev
        }

        // ✅ Step 3: Base paths & OS info
        $indexPhpPath = FCPATH . "index.php";
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // ✅ Step 4: Cleanup any previous at/scheduled jobs for same invoice
        if (!$isWindows) {
            log_message('debug', "Starting job cleanup for invoice ID {$invoiceId}");

            exec("/usr/bin/atq", $atqOutput, $atqStatus);
            if ($atqStatus !== 0) {
                log_message('error', "Failed to list at jobs with atq.");
            }

            foreach ($atqOutput as $line) {
                if (preg_match('/^(\d+)\s/', $line, $matches)) {
                    $jobId = $matches[1];
                    $commandOutput = [];
                    exec("/usr/bin/at -c {$jobId}", $commandOutput, $atcStatus);
                    $jobScript = implode("\n", $commandOutput);

                    if (strpos($jobScript, "CliWorker monitorInvoicePaymentRelease {$invoiceId} ") !== false) {
                        exec("/usr/bin/atrm {$jobId}", $deleteOutput, $deleteResult);
                        if ($deleteResult === 0) {
                            log_message('info', "Successfully removed existing at job ID {$jobId} for invoice {$invoiceId}");
                        } else {
                            log_message('error', "Failed to remove job #{$jobId} for invoice {$invoiceId}. Output: " . implode("\n", $deleteOutput));
                        }
                    }
                }
            }
        }

        // ✅ Step 5: Schedule new background command
        if ($isWindows) {
            // Convert to local system timezone
            $scheduleDateTimeSystem = clone $scheduleDateTime;
            $scheduleDateTimeSystem->setTimezone(new DateTimeZone(date_default_timezone_get()));

            $time = $scheduleDateTimeSystem->format('H:i');
            $date = $scheduleDateTimeSystem->format('m/d/Y');

            $taskName = "invoice_{$invoiceId}_" . uniqid();
            $cliCommand = "{$phpPath} {$indexPhpPath} CliWorker monitorInvoicePaymentRelease {$invoiceId} {$scheduleTimestamp}";
            $scheduleCmd = "schtasks /Create /SC ONCE /TN {$taskName} /TR \"{$cliCommand}\" /ST {$time} /SD {$date} /F";
        } else {
            // Linux: use `at` command
            $time = date('H:i', $scheduleTimestamp);
            $date = date('Y-m-d', $scheduleTimestamp);

            $cliCommand = "{$phpPath} {$indexPhpPath} CliWorker monitorInvoicePaymentRelease {$invoiceId} {$scheduleTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";
        }

        // ✅ Step 6: Execute the schedule command
        exec($scheduleCmd, $output, $resultCode);

        // ✅ Step 7: Log the outcome
        if ($resultCode === 0) {
            log_message('info', "Invoice ID {$invoiceId} scheduled for payout at Brisbane time: {$scheduleDateTime->format('Y-m-d H:i:s')} (timestamp: {$scheduleTimestamp})");
            log_message('debug', "Scheduled command: {$scheduleCmd}");
        } else {
            log_message('error', "Failed to schedule invoice ID {$invoiceId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }

    public function addInvoiceAfterSoldToBackgroundSchedule($auctionId, $scheduleAt)
    {
        // ✅ Step 1: Parse Brisbane schedule datetime
        $scheduleDateTime = new DateTime($scheduleAt, new DateTimeZone('Australia/Brisbane'));
        $scheduleTimestamp = $scheduleDateTime->getTimestamp();

        // ✅ Step 2: Detect PHP CLI path
        $phpPath = '/usr/bin/php';
        if (str_contains(strtolower($phpPath), 'httpd.exe')) {
            $phpPath = 'C:\xampp\php\php.exe'; // fallback for Windows dev
        }

        // ✅ Step 3: Base paths & OS info
        $indexPhpPath = FCPATH . "index.php";
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // ✅ Step 4: Cleanup any previous at/scheduled jobs for same auction
        if (!$isWindows) {
            log_message('debug', "Starting job cleanup for auction ID {$auctionId}");

            exec("/usr/bin/atq", $atqOutput, $atqStatus);
            if ($atqStatus !== 0) {
                log_message('error', "Failed to list at jobs with atq.");
            }

            foreach ($atqOutput as $line) {
                if (preg_match('/^(\d+)\s/', $line, $matches)) {
                    $jobId = $matches[1];
                    $commandOutput = [];
                    exec("/usr/bin/at -c {$jobId}", $commandOutput, $atcStatus);
                    $jobScript = implode("\n", $commandOutput);

                    if (strpos($jobScript, "Email process_auction_after_sold_email_by_id {$auctionId} ") !== false) {
                        exec("/usr/bin/atrm {$jobId}", $deleteOutput, $deleteResult);
                        if ($deleteResult === 0) {
                            log_message('info', "Successfully removed existing at job ID {$jobId} for invoice {$auctionId}");
                        } else {
                            log_message('error', "Failed to remove job #{$jobId} for auction {$auctionId}. Output: " . implode("\n", $deleteOutput));
                        }
                    }
                }
            }
        }

        // ✅ Step 5: Schedule new background command
        if ($isWindows) {
            // Convert to local system timezone
            $scheduleDateTimeSystem = clone $scheduleDateTime;
            $scheduleDateTimeSystem->setTimezone(new DateTimeZone(date_default_timezone_get()));

            $time = $scheduleDateTimeSystem->format('H:i');
            $date = $scheduleDateTimeSystem->format('m/d/Y');

            $taskName = "auction_{$auctionId}_" . uniqid();
            $cliCommand = "{$phpPath} {$indexPhpPath} Email process_auction_after_sold_email_by_id {$auctionId} {$scheduleTimestamp}";
            $scheduleCmd = "schtasks /Create /SC ONCE /TN {$taskName} /TR \"{$cliCommand}\" /ST {$time} /SD {$date} /F";
        } else {
            // Linux: use `at` command
            $time = date('H:i', $scheduleTimestamp);
            $date = date('Y-m-d', $scheduleTimestamp);

            $cliCommand = "{$phpPath} {$indexPhpPath} Email process_auction_after_sold_email_by_id {$auctionId} {$scheduleTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";
        }

        // ✅ Step 6: Execute the schedule command
        exec($scheduleCmd, $output, $resultCode);

        // ✅ Step 7: Log the outcome
        if ($resultCode === 0) {
            log_message('info', "Auction ID {$auctionId} scheduled for payout at Brisbane time: {$scheduleDateTime->format('Y-m-d H:i:s')} (timestamp: {$scheduleTimestamp})");
            log_message('debug', "Scheduled command: {$scheduleCmd}");
        } else {
            log_message('error', "Failed to schedule auction ID {$auctionId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }
    private function invoice_payment_failed($invoice_id)
    {

        // Load the Invoice Model
        $this->load->model('InvoiceModel');
        // Find the invoice by PaymentIntent ID
        $invoice = $this->InvoiceModel->getInvoiceById($invoice_id);

        if (!$invoice) {
            return $this->respond(false, 'Invoice #' . $invoice_id . 'not found', [], 404);
        }

        // Prepare data to update invoice
        $updateData = [
            'invoice_payment_status_type_id' => 4,
            'updated_at' => date('Y-m-d H:i:s')
        ];


        // Update the invoice
        $updated = $this->InvoiceModel->updateStatusByPaymentIntentId($invoice_id, $updateData);

        if ($updated) {


            $auctionData = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoice_id);
            if (!$auctionData) {

                return $this->respond(false, 'Auction not found', [], 404);
            }

            $auctionId = $auctionData['auction_id'];

            $this->auction_won_by_bidder_payment_failed($auctionId);

            return $this->respond(true, 'Invoice marked as failed', [
                'invoice_id' => $invoice_id
            ], 200);
        } else {
            return $this->respond(false, 'Failed to update invoice status', [], 500);
        }
    }




    private function respond($success, $message, $customer = [], $statusCode = 200)
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'customer' => $customer
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($statusCode)
            ->set_output(json_encode($response));
    }

    private function isGiveawayCheckoutSession($eventObject)
    {
        if (!$eventObject) {
            return false;
        }

        $metadata = $eventObject->metadata ?? null;
        if (!$metadata) {
            return false;
        }
        
        // Handle both object and array formats
        if (is_object($metadata)) {
            return isset($metadata->type) && $metadata->type === 'punter_subscription';
        } elseif (is_array($metadata)) {
            return isset($metadata['type']) && $metadata['type'] === 'punter_subscription';
        }
        
        return false;
    }

    private function giveawaySubscriptionExists($stripeSubscriptionId)
    {
        if (empty($stripeSubscriptionId)) {
            return false;
        }

        if (!isset($this->subscriptiongiveawaywebhookservice)) {
            return false;
        }

        return $this->subscriptiongiveawaywebhookservice->subscriptionExists($stripeSubscriptionId);
    }
}

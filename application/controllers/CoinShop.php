<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CoinShop extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->check_auth(); // Integration TODO
        $this->load->model('VaultModel');
        $this->load->model('CoinModel');
        $this->load->model('UserModel');
        $this->load->model('SubscriptionGiveawayModel');
    }

    public function index()
    {
        $userSession = $this->session->userdata('auction_user_session_data');
        $user_id = $userSession['user_id'] ?? 1;

        $data['coin_balance'] = $this->CoinModel->getBalance($user_id);
        $data['coin_packages'] = $this->VaultModel->getCoinPackages();
        $data['cash_packages'] = $this->VaultModel->getCashPackages();
        $data['active_draw'] = $this->VaultModel->getActiveDraw();

        // Get User Info
        $this->load->model('UserModel');
        $user = $this->db->get_where('tbl_users', ['id' => $user_id])->row();
        $data['user_tier'] = strtolower($user->subscription_tier ?? 'none');
        $data['payment_methods'] = $this->UserModel->getBidderPaymentMethodById($user_id);

        $data['scripts'] = [
            base_url('assets/js/script.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/bidder/coin_shop', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    /**
     * Process Coin -> Entry Exchange
     */
    public function buy_with_coins()
    {
        try {
            $userSession = $this->session->userdata('auction_user_session_data');
            $user_id = $userSession['user_id'] ?? 1;

            // Get POST data (Raw JSON)
            $input = json_decode(file_get_contents('php://input'), true);
            $package_id = $input['package_id'] ?? null;

            if (!$package_id) {
                throw new Exception('Package ID required');
            }

            // 1. Get Package Details
            $package = $this->VaultModel->getCoinPackage($package_id);
            if (!$package) {
                throw new Exception('Package not found');
            }

            // 2. Validate Balance
            if (!$this->CoinModel->hasSufficientBalance($user_id, $package->coin_price)) {
                throw new Exception('Insufficient coin balance');
            }

            // 3. Get Active Draw (Vault or Giveaway)
            $vault_draw = $this->VaultModel->getActiveDraw();
            $giveaway_draw = $this->SubscriptionGiveawayModel->get_active_draw();
            if (!$vault_draw && !$giveaway_draw) {
                throw new Exception('No active draw to enter');
            }

            // 4. Process Transaction
            $this->db->trans_start();

            // Deduct Coins
            $target_draw_id = $giveaway_draw['id'] ?? ($vault_draw ? $vault_draw->id : null);
            $this->CoinModel->updateBalance(
                $user_id,
                -$package->coin_price,
                'coin_spent_entries',
                'draw_id',
                $target_draw_id,
                "Purchased {$package->entry_count} Entries"
            );

            // Add Entries
            $total_entries = $package->entry_count + $package->bonus_entries;
            if ($vault_draw) {
                $this->VaultModel->addEntries(
                    $user_id,
                    $vault_draw->id,
                    $total_entries,
                    'coin_purchase',
                    ['package_id' => $package->id, 'package_title' => $package->title]
                );
            }

            if ($giveaway_draw) {
                $this->db->insert('tbl_entries_ledger', [
                    'user_id' => $user_id,
                    'draw_id' => $giveaway_draw['id'],
                    'quantity' => $total_entries,
                    'source' => 'coin_purchase',
                    'meta' => json_encode(['package_id' => $package->id, 'package_title' => $package->title]),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            } else {
                echo json_encode(['status' => true, 'message' => 'Entries purchased successfully!']);
            }
        } catch (Exception $e) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Process Cash -> Entry Purchase using Stripe
     */
    public function buy_with_cash()
    {
        // Set JSON content type at the start
        $this->output->set_content_type('application/json');
        
        $userSession = $this->session->userdata('auction_user_session_data');
        $user_id = $userSession['user_id'] ?? null;

        if (!$user_id) {
            $this->output->set_status_header(401);
            echo json_encode(['status' => false, 'message' => 'Please login to continue']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $package_id = $input['package_id'] ?? null;
        $payment_method_id = $input['payment_method_id'] ?? null;

        if (!$package_id || !$payment_method_id) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => false, 'message' => 'Package ID and Payment Method required']);
            return;
        }

        // 1. Get Package Details
        $package = $this->db->get_where('tbl_entry_shop_cash_packages', ['id' => $package_id, 'is_active' => 1])->row();
        if (!$package) {
            $this->output->set_status_header(404);
            echo json_encode(['status' => false, 'message' => 'Package not found']);
            return;
        }

        // 1.1 Calculate Discounted Price based on Tier
        $this->db->select('u.*, c.email');
        $this->db->from('tbl_users u');
        $this->db->join('tbl_user_credentials c', 'c.user_id = u.id');
        $this->db->where('u.id', $user_id);
        $user = $this->db->get()->row();
        
        $tier = strtolower($user->subscription_tier ?? 'none');
        $final_price_cents = $package->price_cents;

        if ($tier === 'shark') {
            $final_price_cents = round($package->price_cents * 0.50);
        } elseif ($tier === 'whale') {
            $final_price_cents = round($package->price_cents * 0.25);
        }

        // 2. Get User Stripe Data
        $this->load->model('UserModel');
        $userStripe = $this->UserModel->getUserStripeIdById($user_id);
        if (!$userStripe || empty($userStripe['stripe_customer_id'])) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => false, 'message' => 'Stripe customer not found. Please add a payment method first.']);
            return;
        }

        // 3. Get Active Draw
        $vault_draw = $this->VaultModel->getActiveDraw();
        $giveaway_draw = $this->SubscriptionGiveawayModel->get_active_draw();
        if (!$vault_draw && !$giveaway_draw) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => false, 'message' => 'No active draw to enter']);
            return;
        }

        try {
            $secretKey = stripe_secret_key();
            if (!$secretKey) {
                throw new \Exception('Stripe secret key not configured');
            }

            \Stripe\Stripe::setApiKey($secretKey);

            // 4. Verify payment method belongs to customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($payment_method_id);
            
            // If payment method is not attached to any customer or attached to different customer
            if (empty($paymentMethod->customer) || $paymentMethod->customer !== $userStripe['stripe_customer_id']) {
                // Detach from old customer if attached elsewhere
                if (!empty($paymentMethod->customer)) {
                    try {
                        $paymentMethod->detach();
                    } catch (\Exception $e) {
                        // Ignore detach errors
                    }
                }
                // Attach payment method to current customer
                $paymentMethod->attach(['customer' => $userStripe['stripe_customer_id']]);
            }

            // 5. Create Stripe Charge (PaymentIntent)
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $final_price_cents,
                'currency' => 'aud',
                'customer' => $userStripe['stripe_customer_id'],
                'payment_method' => $payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'description' => "Purchase of {$package->entry_count} Vault Entries",
                'metadata' => [
                    'user_id' => $user_id, 
                    'draw_id' => $vault_draw ? $vault_draw->id : ($giveaway_draw['id'] ?? null), 
                    'package_id' => $package->id
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // 5. Success! Add Entries
                $this->db->trans_start();

                $total_entries = $package->entry_count + $package->bonus_entries;
                if ($vault_draw) {
                    $this->VaultModel->addEntries(
                        $user_id,
                        $vault_draw->id,
                        $total_entries,
                        'cash_purchase',
                        [
                            'package_id' => $package->id,
                            'package_title' => $package->title,
                            'stripe_payment_intent_id' => $paymentIntent->id
                        ]
                    );
                }

                if ($giveaway_draw) {
                    $this->db->insert('tbl_entries_ledger', [
                        'user_id' => $user_id,
                        'draw_id' => $giveaway_draw['id'],
                        'quantity' => $total_entries,
                        'source' => 'cash_purchase',
                        'meta' => json_encode([
                            'package_id' => $package->id,
                            'package_title' => $package->title,
                            'stripe_payment_intent_id' => $paymentIntent->id
                        ]),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // --- VAULT: Invoice Generation ---
                $this->load->model('InvoiceModel');
                $invoice_num = 'VLT-' . strtoupper(substr(md5(uniqid()), 0, 8));
                
                $amount_dollars = $final_price_cents / 100;
                $tax_amount = $amount_dollars - ($amount_dollars / 1.1); // Assuming 10% GST included

                $invoiceData = [
                    'invoice_number' => $invoice_num,
                    'amount'         => $amount_dollars,
                    'tax_amount'     => round($tax_amount, 2),
                    'total_amount'   => $amount_dollars,
                    'currency'       => 'AUD',
                    'invoice_date'   => date('Y-m-d'),
                    'due_date'       => date('Y-m-d'),
                    'paid_date'      => date('Y-m-d'),
                    'created_at'     => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s')
                ];

                $inv = $this->InvoiceModel->insert_invoice($invoiceData);
                
                $relData = [
                    'invoice_id' => $inv['invoice_id'],
                    'invoice_type_id' => 3, // Approved
                    'invoice_payment_method_type_id' => 2, // Stripe
                    'invoice_payment_status_type_id' => 2, // Paid
                    'auction_id' => NULL, // Not an auction
                    'user_id' => $user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->InvoiceModel->insert_invoice_auction_relationship($relData);

                // Link Stripe ID
                $this->InvoiceModel->insert_invoice_with_stripe([
                    'invoice_id' => $inv['invoice_id'],
                    'stripe_invoice_id' => $paymentIntent->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // --- Send Receipt Email ---
                $this->load->model('EmailQueueModel');
                $this->EmailQueueModel->add([
                    'to_email'      => $user->email,
                    'subject'       => "Receipt for Your Vault Purchase",
                    'template'      => "vault_purchase_receipt",
                    'name'          => $user->first_name,
                    'user_id'       => $user_id,
                    'auction_id'    => 0, 
                    'auction_item'  => $package->title, // Mapped to package_title
                    'auction_amount'=> $amount_dollars, // Mapped to amount
                    'auction_total' => $total_entries,  // Mapped to entries count (repurposing field)
                    'auction_url'   => $invoice_num,    // Mapped to invoice_number (repurposing field)
                    'scheduled_at'  => date('Y-m-d H:i:s'),
                    'status'        => 'pending',
                    'error_message' => '',
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                ]);
                // ----------------------------------

                $this->db->trans_complete();

                echo json_encode(['status' => true, 'message' => 'Payment successful! Entries added and invoice generated.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Payment failed or requires further action.']);
            }

        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => false, 'message' => 'Stripe Error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            $this->output->set_status_header(500);
            echo json_encode(['status' => false, 'message' => 'System Error: ' . $e->getMessage()]);
        }
    }
}

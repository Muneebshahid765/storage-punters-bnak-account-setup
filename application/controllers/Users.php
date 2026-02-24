<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Product;
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

class Users extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();

        $secretKey = stripe_secret_key();
        if ($secretKey) {
            Stripe::setApiKey($secretKey);
        } else {
            log_message('error', 'Stripe secret key missing; please set STRIPE_SECRET_KEY in .env');
        }

        date_default_timezone_set('Australia/Brisbane');
    }

    private function getStripeBusinessProfileUrl()
    {
        $configuredUrl = rtrim(base_url(), '/');
        if (!$configuredUrl) {
            $configuredUrl = rtrim(site_url(), '/');
        }

        $fallbackUrl = getenv('STRIPE_BUSINESS_PROFILE_URL') ?: 'https://storagepunters.com';

        if (!$configuredUrl) {
            return rtrim($fallbackUrl, '/');
        }

        $host = parse_url($configuredUrl, PHP_URL_HOST);

        if (!$host) {
            return rtrim($fallbackUrl, '/');
        }

        $isLocalhost = in_array(strtolower($host), ['localhost', '127.0.0.1']);
        $isLocalDomain = (bool)preg_match('/\.local(domain)?$/i', $host);

        if ($isLocalhost || $isLocalDomain) {
            return rtrim($fallbackUrl, '/');
        }

        return $configuredUrl;
    }
    public function getBidderProfileById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch bidder profile details from the database
        $userProfileData = $this->UserModel->getBidderProfileById($userId);

        // If user profile not found, return error response
        if (!$userProfileData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No profile is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Your profile retrieved successfully.",
            "data" => $userProfileData
        ], JSON_PRETTY_PRINT);
    }

    public function getDashboardStats()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            return;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            return;
        }

        $session = $this->session->userdata('auction_user_session_data');
        $userId = $session['user_id'];

        $this->load->model('CoinModel');
        $this->load->model('AuctionModel');

        $wallet = $this->CoinModel->get_user_coins($userId);
        $activeBids = $this->AuctionModel->countActiveAuctionsByBidder($userId);
        $wonAuctions = $this->AuctionModel->countWonAuctionsByBidder($userId);

        echo json_encode([
            "status" => "success",
            "data" => [
                "wallet_balance" => $wallet,
                "active_bids" => $activeBids,
                "won_auctions" => $wonAuctions
            ]
        ]);
    }


    public function getStorageFacilityProfileById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch bidder profile details from the database
        $userProfileData = $this->UserModel->getBidderProfileById($userId);

        // If user profile not found, return error response
        if (!$userProfileData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No profile is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Your profile retrieved successfully.",
            "data" => $userProfileData
        ], JSON_PRETTY_PRINT);
    }


    public function getBidderPaymentMethodById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            http_response_code(403); // Forbidden
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access payment methods."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Sanitize input data
        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $search = $this->input->post('search')['value'];
        $orderColumn = $this->input->post('order')[0]['column'];
        $orderDir = $this->input->post('order')[0]['dir'];

        // Validate input data to prevent SQL injection
        if (!is_int($draw) || !is_int($start) || !is_int($length)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid input']))
                ->set_status_header(400);
            return;
        }

        // Load User Model
        $this->load->model('UserModel');

        $totalRecords = $this->UserModel->countAllPaymentMethod();
        $filteredRecords = $this->UserModel->countFilteredPaymentMethod($search, $userId);
        $result = $this->UserModel->getFilteredBidderPaymentMethodById($start, $length, $search, $orderColumn, $orderDir, $userId);

        // Prepare the response data
        $data = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $result,
        ];

        // Return success response with payment method data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Payment method retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }


    private function createStripeCustomer($json_input, $email)
    {
        try {
            // 🔍 First check if customer already exists
            $existing = Customer::all([
                'email' => $email,
                'limit' => 1
            ]);

            if (!empty($existing->data)) {
                return $existing->data[0]; // ✅ return existing customer
            }

            // 🆕 If not found, create a new one
            return Customer::create([
                'email' => $email,
                'name'  => trim(($json_input['first_name'] ?? '') . ' ' . ($json_input['last_name'] ?? '')),
                'phone' => $json_input['cell_phone'] ?? null,
                'address' => [
                    'line1'       => $json_input['address'] ?? null,
                    'line2'       => $json_input['apt_suite'] ?? null,
                    'city'        => $json_input['city'] ?? null,
                    'postal_code' => $json_input['zip'] ?? null,
                    'state'       => $json_input['state'] ?? null,
                    'country'     => $json_input['country'] ?? null,
                ],
            ]);
        } catch (ApiErrorException $e) {
            throw new Exception("Stripe API error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Stripe general error: " . $e->getMessage());
        }
    }

    public function subscribeBidderMembership()
    {

        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in."
            ], JSON_PRETTY_PRINT);
            http_response_code(401); // Unauthorized
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission to place bids on auctions."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $price_id = isset($json_input['price_id']) ? trim($json_input['price_id']) : null;

        if (empty($price_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing price ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch payment methods from model
        $methods = $this->UserModel->getBidderAllPaymentMethodsById($userId);

        // Check if data exists
        if (empty($methods)) {

            echo json_encode([
                "status" => "error",
                "message" => "No active payment methods found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        $stripeData = $this->UserModel->getUserStripeIdById($userId);

        if (empty($stripeData['stripe_customer_id'])) {
            // --- Auto-create Customer Logic ---
            $userProfile = $this->UserModel->getBidderProfileById($userId);
            
            if (!$userProfile) {
                 echo json_encode([
                    "status"  => "error",
                    "message" => "User profile not found. Cannot create Stripe customer."
                ], JSON_PRETTY_PRINT);
                http_response_code(404);
                exit;
            }

            // Map DB fields to helper function expectation
            $customerData = [
                'first_name' => $userProfile['firstname'],
                'last_name'  => $userProfile['lastname'],
                'cell_phone' => $userProfile['cell_phone'],
                'address'    => $userProfile['street_address'],
                'apt_suite'  => $userProfile['apt_suite'],
                'city'       => $userProfile['city'],
                'zip'        => $userProfile['zip'],
                'state'      => $userProfile['state'],
                'country'    => $userProfile['country']
            ];

            try {
                $newCustomer = $this->createStripeCustomer($customerData, $userProfile['email']);
                $customer_id = $newCustomer->id;

                 // Save new Stripe ID to DB
                $this->db->replace('tbl_user_stripe_relationship', [
                    'user_id'            => $userId,
                    'stripe_customer_id' => $customer_id,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s')
                ]);

            } catch (Exception $e) {
                 echo json_encode([
                    "status"  => "error",
                    "message" => "Failed to create Stripe customer: " . $e->getMessage()
                ], JSON_PRETTY_PRINT);
                http_response_code(500);
                exit;
            }
        } else {
             $customer_id = $stripeData['stripe_customer_id'];
        }

        try {

            // ✅ 1. Retrieve attached payment methods for this customer
            $paymentMethods = PaymentMethod::all([
                'customer' => $customer_id,
                'type'    => 'card',
            ]);

            if (empty($paymentMethods->data)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "This customer has no attached payment methods. Please add one first."
                ], JSON_PRETTY_PRINT);
                http_response_code(400);
                exit;
            }

            // ✅ 2. Pick the first payment method (or implement logic to select default from DB)
            $defaultPaymentMethod = $paymentMethods->data[0]->id;

            // ✅ 3. Set it as default for invoice payments
            Customer::update($customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $defaultPaymentMethod
                ]
            ]);


            // ✅ Check if customer already has an active subscription
            $subscriptions = \Stripe\Subscription::all([
                'customer' => $customer_id,
                'status'   => 'active',
                'limit'    => 1
            ]);

            if (!empty($subscriptions->data)) {
                // 🔹 Customer already has subscription → update it
                $subscriptionId = $subscriptions->data[0]->id;
                $subscription   = Subscription::retrieve($subscriptionId);

                // Update subscription with new plan
                $updatedSubscription = Subscription::update($subscriptionId, [
                    'cancel_at_period_end' => false,
                    'proration_behavior'   => 'create_prorations',
                    'items' => [[
                        'id'    => $subscription->items->data[0]->id,
                        'price' => $price_id,
                    ]]
                ]);

                $subscription_id       = $updatedSubscription->id;
                $start_date            = date('Y-m-d H:i:s', $updatedSubscription->start_date);
                $item = $updatedSubscription->items->data[0];
                $current_period_start = date('Y-m-d H:i:s', $item->current_period_start);
                $current_period_end   = date('Y-m-d H:i:s', $item->current_period_end);
                $product_id            = $updatedSubscription->items->data[0]->price->product;
                $price_id              = $updatedSubscription->items->data[0]->price->id;


                $bidderSubscriptionRelationshipData = [
                    'subscription_id'      => $subscription_id,
                    'start_date'           => $start_date,
                    'current_period_start' => $current_period_start,
                    'current_period_end'   => $current_period_end,
                    'product_id'           => $product_id,
                    'price_id'             => $price_id,
                    'user_id'              => $userId,
                    'status_type_id'              => 1,
                    'created_at'           => date('Y-m-d H:i:s'),
                    'updated_at'           => date('Y-m-d H:i:s')
                ];

                if ($price_id == "price_1S6DpJ07pHCLCmTy0n3OX1Xn") {
                    $subscriptionData = $this->UserModel->getUserSubscriptionIdByPriceId($price_id);

                    if (empty($subscriptionData['subscription_id'])) {

                        $product = Product::retrieve($product_id);

                        $limited_offer_value = $product->metadata->limited_offer_value;

                        if ($limited_offer_value > 0) {
                            // Profile
                            $bidderBalanceData = [
                                'transaction_type_id' => 1,
                                'wallet_type_id' => 2,
                                'amount' =>  $limited_offer_value,
                                'user_id' => $userId,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
            $this->UserModel->insertBidderBalanceData($bidderBalanceData);
                        }
                    }
                }

                $this->UserModel->insertBidderSubscriptionRelationshiData($bidderSubscriptionRelationshipData);


                echo json_encode([
                    "status"  => "success",
                    "message" => "Membership updated successfully.",
                    "data"    => $updatedSubscription
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
            } else {
                // 🔹 No subscription → create a new one
                $newSubscription = Subscription::create([
                    'customer' => $customer_id,
                    'items'    => [['price' => $price_id]],
                    'expand'   => ['latest_invoice.payment_intent'],
                ]);

                $subscription_id       = $newSubscription->id;
                $start_date            = date('Y-m-d H:i:s', $newSubscription->start_date);
                $item = $newSubscription->items->data[0];
                $current_period_start = date('Y-m-d H:i:s', $item->current_period_start);
                $current_period_end   = date('Y-m-d H:i:s', $item->current_period_end);
                $product_id            = $newSubscription->items->data[0]->price->product;
                $price_id              = $newSubscription->items->data[0]->price->id;

                $bidderSubscriptionRelationshipData = [
                    'subscription_id'      => $subscription_id,
                    'start_date'           => $start_date,
                    'current_period_start' => $current_period_start,
                    'current_period_end'   => $current_period_end,
                    'product_id'           => $product_id,
                    'price_id'             => $price_id,
                    'user_id'              => $userId,
                    'status_type_id'       => 1,
                    'created_at'           => date('Y-m-d H:i:s'),
                    'updated_at'           => date('Y-m-d H:i:s')
                ];

                if ($price_id == "price_1S6DpJ07pHCLCmTy0n3OX1Xn") {
                    $subscriptionData = $this->UserModel->getUserSubscriptionIdByPriceId($price_id);

                    if (empty($subscriptionData['subscription_id'])) {

                        $product = Product::retrieve($product_id);

                        $limited_offer_value = $product->metadata->limited_offer_value;

                        if ($limited_offer_value > 0) {
                            // Profile
                            $bidderBalanceData = [
                                'transaction_type_id' => 1,
                                'wallet_type_id' => 2,
                                'amount' =>  $limited_offer_value,
                                'user_id' => $userId,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
            $this->UserModel->insertBidderBalanceData($bidderBalanceData);
                        }
                    }
                }


                $this->UserModel->insertBidderSubscriptionRelationshiData($bidderSubscriptionRelationshipData);

                $webhookData = [
                    'subscription'       => [
                        'customer' => $customer_id,
                        'subscription_id'      => $subscription_id,
                        'start_date'           => $start_date,
                        'current_period_start' => $current_period_start,
                        'current_period_end'   => $current_period_end,
                        'product_id'           => $product_id,
                        'price_id'             => $price_id,
                        'user_id'              => $userId,
                    ]
                ];

                // ✅ Send webhook and get result including payload
                $webhookResponse = send_webhook_event('subscription.new_purchased', 'subscription', $webhookData);

                echo json_encode([
                    "status"  => "success",
                    "message" => "Membership purchased successfully.",
                    "data"    => $newSubscription
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
            }
        } catch (ApiErrorException $e) {
            throw new Exception("Stripe API error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Stripe general error: " . $e->getMessage());
        }
    }

    public function claimRetentionBonus()
    {
        header('Content-Type: application/json');

        if (!$this->session->userdata('auction_user_session_data')) {
            echo json_encode(["status" => "error", "message" => "User not logged in."]);
            http_response_code(401);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];

        // 1. Check if active draw exists
        $this->load->model('VaultModel');
        $draw = $this->VaultModel->getActiveDraw();
        if (!$draw) {
            echo json_encode(["status" => "error", "message" => "No active draw available."]);
            return;
        }

        // 2. Check if already claimed for this draw
        // We can check tbl_vault_entry_transactions for 'retention_bonus' and this draw_id
        $this->db->where('user_id', $userId);
        $this->db->where('vault_draw_id', $draw->id);
        $this->db->where('type', 'retention_bonus');
        $claimed = $this->db->count_all_results('tbl_vault_entry_transactions');

        if ($claimed > 0) {
            echo json_encode(["status" => "error", "message" => "You have already claimed this bonus."]);
            return;
        }

        // 3. Add Entries
        $this->VaultModel->addEntries(
            $userId,
            $draw->id,
            5,
            'retention_bonus',
            ['description' => 'User accepted retention offer']
        );

        echo json_encode([
            "status" => "success", 
            "message" => "5 Bonus Entries added! Thanks for staying with us."
        ]);
    }

    public function subscribeCancelBidderMembership()
    {

        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in."
            ], JSON_PRETTY_PRINT);
            http_response_code(401); // Unauthorized
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission to place bids on auctions."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $subscription_id = isset($json_input['subscription_id']) ? trim($json_input['subscription_id']) : null;

        if (empty($subscription_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing subscription ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch payment methods from model
        $methods = $this->UserModel->getBidderAllPaymentMethodsById($userId);

        // Check if data exists
        if (empty($methods)) {

            echo json_encode([
                "status" => "error",
                "message" => "No active payment methods found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        $stripeData = $this->UserModel->getUserStripeIdById($userId);

        if (empty($stripeData['stripe_customer_id'])) {
            echo json_encode([
                "status"  => "error",
                "message" => "No Stripe record found for this user."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        $customer_id = $stripeData['stripe_customer_id'];

        try {

            // ✅ 1. Retrieve attached payment methods for this customer
            $paymentMethods = PaymentMethod::all([
                'customer' => $customer_id,
                'type'    => 'card',
            ]);

            if (empty($paymentMethods->data)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "This customer has no attached payment methods. Please add one first."
                ], JSON_PRETTY_PRINT);
                http_response_code(400);
                exit;
            }

            // ✅ 2. Pick the first payment method (or implement logic to select default from DB)
            $defaultPaymentMethod = $paymentMethods->data[0]->id;

            // ✅ 3. Set it as default for invoice payments
            Customer::update($customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $defaultPaymentMethod
                ]
            ]);


            // Retrieve subscription
            $subscription = \Stripe\Subscription::retrieve($subscription_id);

            if (!$subscription) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Subscription not found."
                ], JSON_PRETTY_PRINT);
                http_response_code(404);
                exit;
            }

            // If already canceled
            if ($subscription->status === "canceled") {
                echo json_encode([
                    "status" => "error",
                    "message" => "Subscription is already cancelled."
                ], JSON_PRETTY_PRINT);
                http_response_code(400);
                exit;
            }

            // Cancel at end of billing period
            $cancel = \Stripe\Subscription::update($subscription_id, [
                'cancel_at_period_end' => true
            ]);


            $updateSubscriptionStatusData = [
                'status_type_id' => 2,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->UserModel->updateUserSubscriptionById($subscription_id, $updateSubscriptionStatusData);

            echo json_encode([
                "status" => "success",
                "message" => "Your membership has been successfully cancelled. It will remain active until the expiration date."
            ], JSON_PRETTY_PRINT);

            http_response_code(200);
        } catch (ApiErrorException $e) {
            throw new Exception("Stripe API error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Stripe general error: " . $e->getMessage());
        }
    }
    public function registerNewBidder()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403);
            exit;
        }

        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $email = isset($json_input['standard_email_address']) ? strtolower(trim($json_input['standard_email_address'])) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "A valid email address is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $existingUser = $this->UserModel->get_user_by_email_address($email);
        if ($existingUser) {
            echo json_encode([
                "status" => "error",
                "message" => "This email is already in use. Please use a different one."
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        }

        $cellPhone = isset($json_input['cell_phone']) ? trim($json_input['cell_phone']) : '';
        if ($cellPhone) {
            $phoneExists = $this->UserModel->isPhoneInUse($cellPhone);
            if ($phoneExists) {
                echo json_encode([
                    "status" => "error",
                    "message" => "This mobile number is already registered. Please use a different one."
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
                exit;
            }
        }

        $password = isset($json_input['current_password']) ? trim($json_input['current_password']) : '';
        $passwordConfirmation = isset($json_input['password_confirmation']) ? trim($json_input['password_confirmation']) : '';

        // 🔒 Validate password length
        if (strlen($password) < 6) {
            echo json_encode([
                "status" => "error",
                "message" => "Password must be at least 6 characters long."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // 🔒 Validate password match
        if ($password !== $passwordConfirmation) {
            echo json_encode([
                "status" => "error",
                "message" => "Password and confirmation do not match."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // ✅ Step 1: Handle Stripe Customer (Create or Use Existing)
        $stripeCustomerId = isset($json_input['stripe_customer_id']) ? trim($json_input['stripe_customer_id']) : null;
        $paymentMethodId = isset($json_input['payment_method_id']) ? trim($json_input['payment_method_id']) : null;

        if (!$stripeCustomerId) {
            try {
                $stripeCustomer = $this->createStripeCustomer($json_input, $email);
                $stripeCustomerId = $stripeCustomer->id;
            } catch (ApiErrorException $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => 'Stripe API error: ' . $e->getMessage()
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
                exit;
            } catch (Exception $e) {

                echo json_encode([
                    "status" => "error",
                    "message" => 'General error: ' . $e->getMessage()
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
                exit;
            }
        }


        // ✅ Begin DB transaction
        $this->db->trans_begin();

        // Identity data
        $bidderIdentityData = [
            'first_name' => isset($json_input['first_name']) ? ucwords(strtolower(trim($json_input['first_name']))) : null,
            'last_name'  => isset($json_input['last_name']) ? ucwords(strtolower(trim($json_input['last_name']))) : null,
            'residential_state' => isset($json_input['residential_state']) ? strtoupper(trim($json_input['residential_state'])) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $bidderIdentityDataInserted = $this->UserModel->insertBidderIdentityData($bidderIdentityData);
        $userId = $bidderIdentityDataInserted['user_id'] ?? 0;

        if ($userId <= 0) {
            $this->db->trans_rollback();
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing user ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Credentials
        $bidderCredentialsData = [
            'email' => $email,
            'password' => $password,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderCredentialsData($bidderCredentialsData);

        // Role
        $bidderRoleData = [
            'role_id' => 2,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderRoleData($bidderRoleData);

        // Status
        $bidderStatusData = [
            'status_type_id' => 1,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderStatusData($bidderStatusData);

        // Profile
        $bidderProfileData = [
            'date_of_birth' => date('Y-m-d'),
            'time_zone' => isset($json_input['timezone']) ? trim($json_input['timezone']) : null,
            'hear_us' => isset($json_input['hear_us']) ? trim($json_input['hear_us']) : null,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderProfileData($bidderProfileData);

        // Alerts
        $alertTypes = [
            [1, 4],
            [2, 4],
            [1, 5],
            [2, 5],
            [1, 3],
            [2, 3]
        ];
        $alertSettings = [];
        foreach ($alertTypes as [$prefId, $notifId]) {
            $alertSettings[] = [
                'preference_type_id' => $prefId,
                'notification_type_id' => $notifId,
                'status_type_id' => 1,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        $this->UserModel->insertBidderAlertSettingsData($alertSettings);

        // Location

        $bidderLocationData = [
            'street_address' => isset($json_input['address']) ? ucwords(strtolower(trim($json_input['address']))) : '',
            'apt_suite'      => isset($json_input['apt_suite']) ? ucwords(strtolower(trim($json_input['apt_suite']))) : '',
            'city'           => isset($json_input['city']) ? ucwords(strtolower(trim($json_input['city']))) : '',
            'postal_code'    => isset($json_input['zip']) ? strtoupper(trim($json_input['zip'])) : '',
            'state'          => isset($json_input['state']) ? ucwords(strtolower(trim($json_input['state']))) : '',
            'country'        => isset($json_input['country']) ? ucwords(strtolower(trim($json_input['country']))) : '',
            'phone'          => isset($json_input['cell_phone']) ? trim($json_input['cell_phone']) : '',
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ];


        $locationInsertResult = $this->UserModel->insertBidderLocationData($bidderLocationData);
        $locationId = $locationInsertResult['location_id'] ?? 0;

        if ($locationId <= 0) {
            $this->db->trans_rollback();
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing location ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Location relationship
        $bidderLocationRelationshipData = [
            'user_id' => $userId,
            'location_id' => $locationId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderLocationRelationshipData($bidderLocationRelationshipData);

        // Generate punter_id (SP-XXXX format)
        $punter_id = 'SP-' . str_pad($userId, 4, '0', STR_PAD_LEFT);
        $this->db->where('id', $userId);
        $this->db->update('tbl_users', ['punter_id' => $punter_id]);

        $this->load->model('AdminSettingModel');
        $signup_bonus_settings = $this->AdminSettingModel->get_signup_bonus_settings();
        $credit_value = 0;

        if ($signup_bonus_settings['enabled'] == 1 && $signup_bonus_settings['amount'] > 0) {
            $credit_value = $signup_bonus_settings['amount'];
            $expiration_days = isset($signup_bonus_settings['expiration_days']) ? (int)$signup_bonus_settings['expiration_days'] : 90;
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$expiration_days} days"));

            $bidderBalanceData = [
                'transaction_type_id' => 1,
                'wallet_type_id' => 1,
                'amount' =>  $signup_bonus_settings['amount'],
                'user_id' => $userId,
                'expires_at' => $expires_at,
                'notes' => 'Sign-up bonus',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->UserModel->insertBidderBalanceData($bidderBalanceData);
        }

        // Award signup coins
        if ($signup_bonus_settings['enabled'] == 1 && isset($signup_bonus_settings['signup_coins']) && $signup_bonus_settings['signup_coins'] > 0) {
            $this->load->model('CoinModel');
            $this->CoinModel->add_coins(
                $userId,
                $signup_bonus_settings['signup_coins'],
                'admin_adjustment',
                null,
                null,
                'Signup bonus'
            );
        }

        // Prepare Stripe Data
        if ($paymentMethodId) {
            try {
                // Attach payment method to customer if not already attached (it should be attached via setup intent, but confirm)
                $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
                if ($paymentMethod->customer !== $stripeCustomerId) {
                    $paymentMethod->attach(['customer' => $stripeCustomerId]);
                }

                // Set as default
                \Stripe\Customer::update($stripeCustomerId, [
                    'invoice_settings' => ['default_payment_method' => $paymentMethodId]
                ]);

                // Save Payment Method to DB
                $bidderPaymentMethodData = [
                    'card_holder_first_name' => $json_input['first_name'] ?? '',
                    'card_holder_last_name' => $json_input['last_name'] ?? '',
                    'card_holder_email' => $email,
                    'card_holder_phone' => $json_input['cell_phone'] ?? '',
                    'card_billing_address' => $json_input['address'] ?? '',
                    'card_billing_zip' => $json_input['zip'] ?? '',
                    'card_type' => $paymentMethod->card->brand,
                    'card_holder_card_number' => $paymentMethod->card->last4,
                    'card_cvv' => 'XXX', 
                    'card_expiration_month' => $paymentMethod->card->exp_month,
                    'card_expiration_year' => $paymentMethod->card->exp_year,
                    'stripe_payment_method_id' => $paymentMethodId,
                    'stripe_customer_id' => $stripeCustomerId,
                    'user_id' => $userId,
                    'status_type_id' => 1, // Active
                    'is_default' => 'true',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->UserModel->insert_bidder_payment_method_data($bidderPaymentMethodData);

            } catch (Exception $e) {
                $this->db->trans_rollback();
                echo json_encode(["status" => "error", "message" => "Payment method error: " . $e->getMessage()], JSON_PRETTY_PRINT);
                exit;
            }
        }

        // Profile
        $bidderStripeData = [
            'stripe_customer_id' => $stripeCustomerId,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderStripeData($bidderStripeData);


        // ✅ Check and commit/rollback
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Registration failed due to a database error. No data was saved."
            ], JSON_PRETTY_PRINT);
        } else {


            $webhookData = [
                'participant'       => [
                    'id'         => $userId,
                    'email'      => $email,
                    'phone'     => isset($json_input['cell_phone']) ? trim($json_input['cell_phone']) : null,
                    'first_name' => isset($json_input['first_name']) ? ucwords(strtolower(trim($json_input['first_name']))) : null,
                    'last_name'  => isset($json_input['last_name']) ? ucwords(strtolower(trim($json_input['last_name']))) : null,
                    'time_zone' => isset($json_input['timezone']) ? trim($json_input['timezone']) : null,
                    'hear_us' => isset($json_input['hear_us']) ? trim($json_input['hear_us']) : null,
                    'credit' =>  $credit_value,
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('storage_punter.signed_up', 'signed_up', $webhookData);


            $this->db->trans_commit();
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Your registration was completed successfully."
            ], JSON_PRETTY_PRINT);
        }

        exit;
    }


    public function registerNewStoragePunter()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403);
            exit;
        }

        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $email = isset($json_input['standard_email_address']) ? strtolower(trim($json_input['standard_email_address'])) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "A valid email address is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $existingUser = $this->UserModel->get_user_by_email_address($email);
        if ($existingUser) {
            echo json_encode([
                "status" => "error",
                "message" => "This email is already in use. Please use a different one."
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        }


        $password = isset($json_input['current_password']) ? trim($json_input['current_password']) : '';
        $passwordConfirmation = isset($json_input['password_confirmation']) ? trim($json_input['password_confirmation']) : '';

        // 🔒 Validate password length
        if (strlen($password) < 6) {
            echo json_encode([
                "status" => "error",
                "message" => "Password must be at least 6 characters long."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // 🔒 Validate password match
        if ($password !== $passwordConfirmation) {
            echo json_encode([
                "status" => "error",
                "message" => "Password and confirmation do not match."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }


        // ✅ Step 1: Handle Stripe Customer (Create or Use Existing)
        $stripeCustomerId = isset($json_input['stripe_customer_id']) ? trim($json_input['stripe_customer_id']) : null;
        $paymentMethodId = isset($json_input['payment_method_id']) ? trim($json_input['payment_method_id']) : null;
        $stripeConnectAccountId = $json_input['stripe_connect_account_id'] ?? null;

        if (!$stripeCustomerId) {
            try {
                $stripeCustomer = $this->createStripeCustomer($json_input, $email);
                $stripeCustomerId = $stripeCustomer->id;
            } catch (ApiErrorException $e) {
                echo json_encode(["status" => "error", "message" => 'Stripe API error: ' . $e->getMessage()], JSON_PRETTY_PRINT);
                http_response_code(200);
                exit;
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => 'General error: ' . $e->getMessage()], JSON_PRETTY_PRINT);
                http_response_code(200);
                exit;
            }
        }

        // ✅ Begin DB transaction
        $this->db->trans_begin();

        // Identity data
        $bidderIdentityData = [
            'first_name' => isset($json_input['first_name']) ? ucwords(strtolower(trim($json_input['first_name']))) : null,
            'last_name'  => isset($json_input['last_name']) ? ucwords(strtolower(trim($json_input['last_name']))) : null,
            // 'stripe_connect_account_id' => $stripeConnectAccountId, // REMOVED: Column does not exist in tbl_users
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $bidderIdentityDataInserted = $this->UserModel->insertBidderIdentityData($bidderIdentityData);
        $userId = $bidderIdentityDataInserted['user_id'] ?? 0;

        if ($userId <= 0) {
            $this->db->trans_rollback();
            echo json_encode(["status" => "error", "message" => "Invalid or missing user ID."], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Credentials
        $bidderCredentialsData = [
            'email' => $email,
            'password' => $password,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderCredentialsData($bidderCredentialsData);

        // Role
        $bidderRoleData = [
            'role_id' => 1,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderRoleData($bidderRoleData);

        // Status
        $bidderStatusData = [
            'status_type_id' => 1,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderStatusData($bidderStatusData);

        // Profile
        $bidderProfileData = [
            'date_of_birth' => date('Y-m-d'),
            'time_zone' => isset($json_input['timezone']) ? trim($json_input['timezone']) : null,
            'hear_us' => isset($json_input['hear_us']) ? trim($json_input['hear_us']) : null,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderProfileData($bidderProfileData);

        // Location
        $bidderLocationData = [
            'street_address' => isset($json_input['address']) ? ucwords(strtolower(trim($json_input['address']))) : '',
            'apt_suite'      => isset($json_input['apt_suite']) && trim($json_input['apt_suite']) !== ''
                ? ucwords(strtolower(trim($json_input['apt_suite'])))
                : '',
            'city'           => isset($json_input['city']) ? ucwords(strtolower(trim($json_input['city']))) : '',
            'postal_code'    => isset($json_input['zip']) ? strtoupper(trim($json_input['zip'])) : '',
            'state'          => isset($json_input['state']) ? ucwords(strtolower(trim($json_input['state']))) : '',
            'country'        => isset($json_input['country']) ? ucwords(strtolower(trim($json_input['country']))) : '',
            'phone'          => isset($json_input['cell_phone']) ? trim($json_input['cell_phone']) : '',
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ];
        $locationInsertResult = $this->UserModel->insertBidderLocationData($bidderLocationData);
        $locationId = $locationInsertResult['location_id'] ?? 0;

        if ($locationId <= 0) {
            $this->db->trans_rollback();
            echo json_encode(["status" => "error", "message" => "Invalid or missing location ID."], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Location relationship
        $bidderLocationRelationshipData = [
            'user_id' => $userId,
            'location_id' => $locationId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderLocationRelationshipData($bidderLocationRelationshipData);

        // Create Stripe Connect Express Account (without bank details - will be collected via Stripe hosted onboarding)
        $stripeOnboardingUrl = null;
        $stripeConnectAccountId = null;
        
        try {
            $firstName = $json_input['first_name'] ?? '';
            $lastName = $json_input['last_name'] ?? '';
            $fullName = trim("$firstName $lastName");

            // Create Express account WITHOUT bank details - Stripe will collect them securely
            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'AU',
                'email' => $email,
                'business_type' => 'individual',
                'business_profile' => [
                    'name' => $fullName,
                    'product_description' => 'Storage facility auction payouts',
                    'url' => $this->getStripeBusinessProfileUrl(),
                ],
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
                'metadata' => [
                    'user_id' => $userId,
                    'platform' => 'StoragePunters',
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ]);
            
            $stripeConnectAccountId = $account->id;

            // Create Stripe hosted onboarding link
            $accountLink = \Stripe\AccountLink::create([
                'account' => $account->id,
                'refresh_url' => base_url('stripe/connect/refresh?account_id=' . $account->id),
                'return_url' => base_url('stripe/connect/return?account_id=' . $account->id),
                'type' => 'account_onboarding',
                'collect' => 'eventually_due',
            ]);
            
            $stripeOnboardingUrl = $accountLink->url;

            // Save Stripe Connect account ID to user record
            $this->UserModel->update_user($userId, ['stripe_connect_account_id' => $stripeConnectAccountId]);

        } catch (Exception $e) {
            // Log error but don't fail registration - user can complete onboarding later
            log_message('error', 'Stripe Connect account creation failed for user ' . $userId . ': ' . $e->getMessage());
        }

        // Profile
        $bidderStripeData = [
            'stripe_customer_id' => $stripeCustomerId,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->UserModel->insertBidderStripeData($bidderStripeData);



        // ✅ Check and commit/rollback
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Registration failed due to a database error. No data was saved."
            ], JSON_PRETTY_PRINT);
        } else {

            $webhookData = [
                'participant'       => [
                    'id'         => $userId,
                    'email'      => $email,
                    'phone'     => isset($json_input['cell_phone']) ? trim($json_input['cell_phone']) : null,
                    'first_name' => isset($json_input['first_name']) ? ucwords(strtolower(trim($json_input['first_name']))) : null,
                    'last_name'  => isset($json_input['last_name']) ? ucwords(strtolower(trim($json_input['last_name']))) : null,
                    'time_zone' => isset($json_input['timezone']) ? trim($json_input['timezone']) : null,
                    'hear_us' => isset($json_input['hear_us']) ? trim($json_input['hear_us']) : null
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('storage_facility.signed_up', 'signed_up', $webhookData);



            $this->db->trans_commit();
            http_response_code(200);
            
            $response = [
                "status" => "success",
                "message" => "Your registration was completed successfully."
            ];
            
            // Include Stripe onboarding URL if available
            if ($stripeOnboardingUrl) {
                $response['stripe_onboarding_url'] = $stripeOnboardingUrl;
                $response['message'] = "Registration complete! Redirecting to secure bank setup...";
            }
            
            echo json_encode($response, JSON_PRETTY_PRINT);
        }

        exit;
    }


    public function getBidderBalanceById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch bidder profile details from the database
        $userBalance = $this->UserModel->getBidderBalanceById($userId);
        $formattedBalance = number_format((float)$userBalance, 2, '.', '');

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status"  => "success",
            "message" => "User balance retrieved successfully.",
            "data"    => [
                "balance" => $formattedBalance ?? 0
            ]
        ], JSON_PRETTY_PRINT);
    }
    public function updateBidderProfileById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Extract only required fields from JSON input
        $bidderProfileData = [
            'date_of_birth' => isset($json_input['dob']) ? trim($json_input['dob']) : null,
            'time_zone' => isset($json_input['timezone']) ? trim($json_input['timezone']) : null,
            'hear_us' => isset($json_input['hear_us']) ? trim($json_input['hear_us']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $bidderLocationData = [
            'street_address' => isset($json_input['address']) ? trim($json_input['address']) : null,
            'apt_suite' => isset($json_input['apt_suite']) ? trim($json_input['apt_suite']) : null,
            'city' => isset($json_input['city']) ? trim($json_input['city']) : null,
            'postal_code' => isset($json_input['zip']) ? trim($json_input['zip']) : null,
            'state' => isset($json_input['state']) ? trim($json_input['state']) : null,
            'country' => isset($json_input['country']) ? trim($json_input['country']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $bidderPasswordData = [
            'password' => isset($json_input['current_password']) ? trim($json_input['current_password']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Load Auction Model
        $this->load->model('UserModel');

        // Update profile, location, and password separately
        // $profileUpdated = $this->UserModel->updateBidderProfile($userId, $bidderProfileData);
        // $locationUpdated = $this->UserModel->updateBidderLocation($userId, $bidderLocationData);
        $passwordUpdated = $this->UserModel->updateBidderPassword($userId, $bidderPasswordData);


        // Check if at least one update was successful
        if (/* $profileUpdated || $locationUpdated || */$passwordUpdated) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Profile has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update profile."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }


    public function updateStorageFacilityProfileById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Extract only required fields from JSON input
        $bidderProfileData = [
            'date_of_birth' => isset($json_input['dob']) ? trim($json_input['dob']) : null,
            'time_zone' => isset($json_input['timezone']) ? trim($json_input['timezone']) : null,
            'hear_us' => isset($json_input['hear_us']) ? trim($json_input['hear_us']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $bidderLocationData = [
            'street_address' => isset($json_input['address']) ? trim($json_input['address']) : null,
            'apt_suite' => isset($json_input['apt_suite']) ? trim($json_input['apt_suite']) : null,
            'city' => isset($json_input['city']) ? trim($json_input['city']) : null,
            'postal_code' => isset($json_input['zip']) ? trim($json_input['zip']) : null,
            'state' => isset($json_input['state']) ? trim($json_input['state']) : null,
            'country' => isset($json_input['country']) ? trim($json_input['country']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $bidderPasswordData = [
            'password' => isset($json_input['current_password']) ? trim($json_input['current_password']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Load Auction Model
        $this->load->model('UserModel');

        // Update profile, location, and password separately
        // $profileUpdated = $this->UserModel->updateBidderProfile($userId, $bidderProfileData);
        // $locationUpdated = $this->UserModel->updateBidderLocation($userId, $bidderLocationData);
        $passwordUpdated = $this->UserModel->updateBidderPassword($userId, $bidderPasswordData);


        // Check if at least one update was successful
        if (/* $profileUpdated || $locationUpdated || */$passwordUpdated) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Profile has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update profile."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }

    public function addBidderPaymentMethodById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Required fields list
        $required_fields = ["firstname", "lastname", "email", "phone", "address", "zip", "card_type", "card_number", "card_security_code", "card_expiration_month", "card_expiration_year"];
        $missing_fields = [];

        // ✅ Validate required fields
        foreach ($required_fields as $field) {
            if (!isset($json_input[$field]) || empty(trim($json_input[$field]))) {
                $missing_fields[] = $field;
            }
        }

        // ✅ Return error if any required field is missing
        if (!empty($missing_fields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missing_fields)
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Additional validation
        if (!filter_var($json_input["email"], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid email format."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        if (!preg_match('/^[0-9]{5,6}$/', $json_input["zip"])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid ZIP code. Only digits allowed (3-10 digits)."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        if (!preg_match('/^[0-9]{20}$/', $json_input["card_number"])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid card number. It must be exactly 20 digits."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        if (!preg_match('/^[0-9]{3}$/', $json_input["card_security_code"])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid CVV. Enter a valid 3 or 4 digit CVV."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // ✅ Validate Expiration Date (Must be in the future)
        $currentYear = date("Y");
        $currentMonth = date("m");

        $expMonth = (int) $json_input["card_expiration_month"];
        $expYear = (int) $json_input["card_expiration_year"];

        if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid expiration date. The card must expire in the future."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }
        // Load Auction Model
        $this->load->model('UserModel');


        // ✅ Prepare data to insert into database
        $paymentMethodData = [
            "card_holder_first_name"   => $json_input["firstname"],
            "card_holder_last_name"    => $json_input["lastname"],
            "card_holder_email"        => $json_input["email"],
            "card_holder_phone"        => $json_input["phone"],
            "card_billing_address"     => $json_input["address"],
            "card_billing_zip"         => $json_input["zip"],
            "card_type"                => $json_input["card_type"],
            "card_holder_card_number"  => $json_input["card_number"],
            "card_cvv"                 => $json_input["card_security_code"],
            "card_expiration_month"    => $json_input["card_expiration_month"],
            "card_expiration_year"     => $json_input["card_expiration_year"],
            "user_id"     => $userId,
            "status_type_id "     => 1,
            "created_at"               => date("Y-m-d H:i:s"),
            "updated_at"               => date("Y-m-d H:i:s")
        ];
        // Fetch auction details from the database
        $paymentMethodResult = $this->UserModel->insert_bidder_payment_method_data($paymentMethodData);

        // Return the response
        if ($paymentMethodResult) {
            echo json_encode([
                "status" => "success",
                "message" => "Your payment method has been successfully added and secured.",
                "data" => [
                    "payment_method_id" => $paymentMethodResult['payment_method_id']
                ]
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Something went wrong. We couldn't add your payment method. Please try again later."
            ], JSON_PRETTY_PRINT);
            http_response_code(500); // Internal Server Error
            exit;
        }
        exit;
    }

    public function updateBidderPaymentMethodById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Check if `payment_method_id` is passed (for update scenarios)
        $payment_method_id = isset($json_input['payment_method_id']) ? (int)$json_input['payment_method_id'] : null;

        // ✅ Handle case when `payment_method_id` is NOT passed
        if ($payment_method_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: payment_method_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Required fields list
        $required_fields = ["firstname", "lastname", "email", "phone", "address", "zip", "card_type", "card_number", "card_security_code", "card_expiration_month", "card_expiration_year"];
        $missing_fields = [];

        // ✅ Validate required fields
        foreach ($required_fields as $field) {
            if (!isset($json_input[$field]) || empty(trim($json_input[$field]))) {
                $missing_fields[] = $field;
            }
        }

        // ✅ Return error if any required field is missing
        if (!empty($missing_fields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missing_fields)
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Additional validation
        if (!filter_var($json_input["email"], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid email format."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        if (!preg_match('/^[0-9]{5,6}$/', $json_input["zip"])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid ZIP code. Only digits allowed (3-10 digits)."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        if (!preg_match('/^[0-9]{20}$/', $json_input["card_number"])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid card number. It must be exactly 20 digits."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        if (!preg_match('/^[0-9]{3}$/', $json_input["card_security_code"])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid CVV. Enter a valid 3 or 4 digit CVV."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // ✅ Validate Expiration Date (Must be in the future)
        $currentYear = date("Y");
        $currentMonth = date("m");

        $expMonth = (int) $json_input["card_expiration_month"];
        $expYear = (int) $json_input["card_expiration_year"];

        if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid expiration date. The card must expire in the future."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }
        // Load Auction Model
        $this->load->model('UserModel');


        // ✅ Prepare data to insert into database
        $paymentMethodData = [
            "card_holder_first_name"   => $json_input["firstname"],
            "card_holder_last_name"    => $json_input["lastname"],
            "card_holder_email"        => $json_input["email"],
            "card_holder_phone"        => $json_input["phone"],
            "card_billing_address"     => $json_input["address"],
            "card_billing_zip"         => $json_input["zip"],
            "card_type"                => $json_input["card_type"],
            "card_holder_card_number"  => $json_input["card_number"],
            "card_cvv"                 => $json_input["card_security_code"],
            "card_expiration_month"    => $json_input["card_expiration_month"],
            "card_expiration_year"     => $json_input["card_expiration_year"],
            "updated_at"               => date("Y-m-d H:i:s")
        ];
        // Fetch auction details from the database
        $paymentMethodResult = $this->UserModel->update_bidder_payment_method_data($payment_method_id, $paymentMethodData);

        // Return the response
        if ($paymentMethodResult) {
            echo json_encode([
                "status" => "success",
                "message" => "Your payment method has been updated successfully.",
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Something went wrong. We couldn't update your payment method. Please try again later."
            ], JSON_PRETTY_PRINT);
            http_response_code(500); // Internal Server Error
            exit;
        }
        exit;
    }


    public function updateBidderPaymentMethodStatusById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Check if `payment_method_id` is passed (for update scenarios)
        $payment_method_id = isset($json_input['payment_method_id']) ? (int)$json_input['payment_method_id'] : null;

        // ✅ Handle case when `payment_method_id` is NOT passed
        if ($payment_method_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: payment_method_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Check if `payment_method_status_name` is passed (for update scenarios)
        $payment_method_status_name = isset($json_input['payment_method_status_name']) ? (string)$json_input['payment_method_status_name'] : null;


        // ✅ Handle case when `payment_method_status_name` is NOT passed
        if ($payment_method_status_name === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: payment_method_status_name is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        if ($payment_method_status_name == "Active") {
            $payment_method_status_id = 1;
        } else if ($payment_method_status_name == "Inactive") {
            $payment_method_status_id = 2;
        } else {
            $payment_method_status_id = 0;
        }

        // ✅ Handle case when `payment_method_status_id` is NOT passed
        if ($payment_method_status_id == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: payment_method_status_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('UserModel');


        // ✅ Prepare data to insert into database
        $paymentMethodStatusData = [
            "status_type_id" => $payment_method_status_id,
            "updated_at" => date("Y-m-d H:i:s")
        ];
        // Fetch auction details from the database
        $paymentMethodResult = $this->UserModel->update_bidder_payment_method_status_data($payment_method_id, $paymentMethodStatusData);

        // Return the response
        if ($paymentMethodResult) {
            echo json_encode([
                "status" => "success",
                "message" => "Your payment method status has been updated successfully.",
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Something went wrong. We couldn't update your payment method status. Please try again later."
            ], JSON_PRETTY_PRINT);
            http_response_code(500); // Internal Server Error
            exit;
        }
        exit;
    }

    public function activate_account()
    {


        $token = $this->input->get('token');

        if (!$token) {
            show_error("Invalid or missing activation token.", 400);
            return;
        }

        $this->load->model('UserModel');

        $record = $this->UserModel->getActivationByToken($token, 1); // 1 for Email Actication Token

        if (!$record) {
            $this->load->view('user/activation/invalid');
            return;
        }

        // Check expiry
        if (strtotime($record['expires_at']) < time()) {
            $this->load->view('user/activation/expired');
            return;
        }

        // Activate the account
        $this->UserModel->update_user_status($record['user_id'], 1);  // 1 for Email Actication Token
        $this->UserModel->deleteActivationToken($token, 1);

        $this->load->view('user/activation/success');
    }

    public function forgot_password_account()
    {


        $token = $this->input->get('token');

        if (!$token) {
            show_error("Invalid or missing activation token.", 400);
            return;
        }

        $this->load->model('UserModel');

        $record = $this->UserModel->getActivationByToken($token, 2); // 2 for Email Forgot Password Token

        if (!$record) {
            $this->load->view('user/activation/invalid');
            return;
        }

        // Check expiry
        if (strtotime($record['expires_at']) < time()) {
            $this->load->view('user/activation/expired');
            return;
        }


        $data['scripts'] = [
            base_url('assets/js/ajax/authentication/password_reset.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('auth/forgot_password');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function staff_invitation_account()
    {


        $token = $this->input->get('token');

        if (!$token) {
            show_error("Invalid or missing activation token.", 400);
            return;
        }

        $this->load->model('UserModel');

        $record = $this->UserModel->getActivationByToken($token, 3); // 3 for Invitation Staff Token

        if (!$record) {
            $this->load->view('user/activation/invalid');
            return;
        }

        // Check expiry
        if (strtotime($record['expires_at']) < time()) {
            $this->load->view('user/activation/expired');
            return;
        }

        $data['scripts'] = [
            base_url('assets/js/hello_bar.js'),
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/staff/accept_invitation.js'),
        ];

        $companyInfo = [

            'user_id' => $record['user_id'],
            'company_id' => $record['company_id'],

        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/storage_facility/staff/accept_invitation', $companyInfo);
        $this->load->view('layout/scripts', $data);
    }

    public function registrationSuccessPage()
    {
        // Securely get the email from query string
        $email = $this->input->get('email', true); // 'true' = XSS filtering

        // Fallback if no email is passed
        if (!$email) {
            redirect('login'); // Or show a custom error page
            return;
        }

        $data['user_email'] = strtolower($email);

        // Load views
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('user/registration/success', $data); // pass email to view
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }


    public function reset_user_account_password()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Get and validate required inputs
        $token = isset($json_input['token']) ? trim($json_input['token']) : '';
        $newPassword = trim($json_input['new_password'] ?? '');
        $passwordConfirmation = trim($json_input['confirm_password'] ?? '');

        if (empty($token)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Missing or invalid password reset token."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // 🔒 Validate password length
        if (strlen($newPassword) < 8) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Password must be at least 8 characters long."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // 🔒 Validate password match
        if ($newPassword !== $passwordConfirmation) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Password and confirmation do not match."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }


        // Load model
        $this->load->model('UserModel');

        $userToken  = $this->UserModel->getActivationByToken($token, 2); // 2 for Email Forgot Password Token
        if (!$userToken) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid or expired token. Please request a new password reset."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        // ✅ If user exists, extract user_id
        $userId = $userToken['user_id'] ?? null;

        if (!$userId) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid token. No associated user."
            ], JSON_PRETTY_PRINT);
            exit;
        }



        $userData = $this->UserModel->get_user_by_id($userId);


        if (!$userData) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "User account not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        // Update password
        $updateData = [
            'password' => $newPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $passwordUpdated = $this->UserModel->updateBidderPassword($userId, $updateData);

        if (!$passwordUpdated) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Failed to update the password. Please try again."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Delete token (1 = general token type for cleanup)
        $this->UserModel->deleteActivationToken($token, 2);


        // Send confirmation email
        $name = $userData['first_name'] . ' ' . $userData['last_name'];
        $email = $userToken['email'];

        // ✅ Final success response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'status' => 'success',
            "message" => "Your password has been successfully reset.",
            "data" => ["name" => $name, "email" => $email]
        ], JSON_PRETTY_PRINT);
    }

    public function acceptInvitationByStaff()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Get and validate required inputs
        $token = isset($json_input['token']) ? trim($json_input['token']) : '';

        if (empty($token)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Missing or invalid password reset token."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $this->load->model('UserModel');

        $userToken  = $this->UserModel->getActivationByToken($token, 3); // 3 for Invitation Token
        if (!$userToken) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid or expired token. Please request a new password reset."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        // Get company ID and bid amount from JSON input
        $companyId = isset($json_input['companyId']) ? (int)$json_input['companyId'] : 0;

        // Validate company ID and bid amount
        if ($companyId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // Get user ID and bid amount from JSON input
        $staffMemberId = isset($json_input['userId']) ? (int)$json_input['userId'] : 0;

        // Validate user ID and bid amount
        if ($staffMemberId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing staff ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $passwordInput = isset($json_input['password']) ? trim($json_input['password']) : '';

        if (empty($passwordInput)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Password is required."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $this->load->model('StaffModel');

        // Update password in credentials table
        $passwordUpdateData = [
            'password' => $passwordInput,
            'updated_at' => date("Y-m-d H:i:s")
        ];
        $this->db->where('user_id', $staffMemberId);
        $this->db->update('tbl_user_credentials', $passwordUpdateData);

        $staffMemberStatusData = [
            "status_type_id"   => 1,
            "updated_at"   => date("Y-m-d H:i:s")
        ];



        $staffMemberStatusDataUpdated = $this->StaffModel->updateStaffMemberStatusData($staffMemberId, $companyId, $staffMemberStatusData);
        $staffMemberAccountStatusDataUpdated = $this->StaffModel->updateStaffMemberAccountStatusData($staffMemberId, $staffMemberStatusData);

        // Check if at least one update was successful
        if ($staffMemberStatusDataUpdated &&  $staffMemberAccountStatusDataUpdated) {

            // Delete token (1 = general token type for cleanup)
            $this->UserModel->deleteActivationToken($token, 3);

            // AUTO LOGIN
            $user_data = $this->UserModel->prepare_user_data($staffMemberId);
            if ($user_data) {
                // If user is staff, check their assigned locations
                if ($user_data['role_id'] == 3) {
                    $assigned_locations = $this->StaffModel->getStaffAssignedLocations($user_data['user_id']);
                    
                    if (!$user_data['manage_all_locations']) {
                        if (count($assigned_locations) === 1) {
                            $user_data['active_storage_facility_id'] = $assigned_locations[0];
                        }
                    }
                }
                $this->session->set_userdata('auction_user_session_data', $user_data);
            }

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "You have successfully accepted the invitation and your account is now ready. Welcome aboard!"
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update invitation status."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }


    public function cancelInvitationByStaff()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Get and validate required inputs
        $token = isset($json_input['token']) ? trim($json_input['token']) : '';

        if (empty($token)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Missing or invalid password reset token."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $this->load->model('UserModel');

        $userToken  = $this->UserModel->getActivationByToken($token, 3); // 3 for Invitation Token
        if (!$userToken) {
            echo json_encode([
                'success' => false,
                "status" => "error",
                "message" => "Invalid or expired token. Please request a new password reset."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        // Get company ID and bid amount from JSON input
        $companyId = isset($json_input['companyId']) ? (int)$json_input['companyId'] : 0;

        // Validate company ID and bid amount
        if ($companyId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // Get user ID and bid amount from JSON input
        $staffMemberId = isset($json_input['userId']) ? (int)$json_input['userId'] : 0;

        // Validate user ID and bid amount
        if ($staffMemberId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing staff ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $this->load->model('StaffModel');

        $staffMemberStatusData = [
            "status_type_id"   => 2,
            "updated_at"   => date("Y-m-d H:i:s")
        ];


        $staffMemberStatusDataUpdated = $this->StaffModel->updateStaffMemberStatusData($staffMemberId, $companyId, $staffMemberStatusData);

        // Check if at least one update was successful
        if ($staffMemberStatusDataUpdated) {



            // Delete token (1 = general token type for cleanup)
            $this->UserModel->deleteActivationToken($token, 3);

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "You’ve cancelled the invitation. No changes have been made."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update invitation status."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }


    public function getAdminProfileById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 4) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch admin profile details from the database
        $userProfileData = $this->UserModel->getAdminProfileById($userId);

        // If user profile not found, return error response
        if (!$userProfileData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No profile is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Your profile retrieved successfully.",
            "data" => $userProfileData
        ], JSON_PRETTY_PRINT);
    }


    public function updateAdminProfileById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 4) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Extract only required fields from JSON input
        $adminProfileData = [
            'date_of_birth' => isset($json_input['dob']) ? trim($json_input['dob']) : null,
            'time_zone' => isset($json_input['timezone']) ? trim($json_input['timezone']) : null,
            'hear_us' => isset($json_input['hear_us']) ? trim($json_input['hear_us']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $adminLocationData = [
            'street_address' => isset($json_input['address']) ? trim($json_input['address']) : null,
            'apt_suite' => isset($json_input['apt_suite']) ? trim($json_input['apt_suite']) : null,
            'city' => isset($json_input['city']) ? trim($json_input['city']) : null,
            'postal_code' => isset($json_input['zip']) ? trim($json_input['zip']) : null,
            'state' => isset($json_input['state']) ? trim($json_input['state']) : null,
            'country' => isset($json_input['country']) ? trim($json_input['country']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $adminPasswordData = [
            'password' => isset($json_input['current_password']) ? trim($json_input['current_password']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Load Auction Model
        $this->load->model('UserModel');

        // Update profile, location, and password separately
        $profileUpdated = $this->UserModel->updateAdminProfile($userId, $adminProfileData);
        $locationUpdated = $this->UserModel->updateAdminLocation($userId, $adminLocationData);
        $passwordUpdated = $this->UserModel->updateAdminPassword($userId, $adminPasswordData);


        // Check if at least one update was successful
        if ($profileUpdated || $locationUpdated || $passwordUpdated) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Profile has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update profile."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }

    /**
     * Update user phone verification status
     * POST: { phone: "+61412345678" }
     */
    public function updatePhoneVerification()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            exit;
        }

        $session = $this->session->userdata('auction_user_session_data');
        if (!$session) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
            exit;
        }

        $userId = $session['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $input['phone'] ?? null;

        if (!$phone) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Phone number required']);
            exit;
        }

        // Check if phone was verified in session
        $verifiedPhone = $this->session->userdata('phone_verified');
        if ($verifiedPhone !== $phone) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Phone not verified in session']);
            exit;
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '+61' . substr($phone, 1);
        }
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        if (!preg_match('/^\+614\d{8}$/', $phone)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid Australian mobile number (+614XXXXXXXX).']);
            exit;
        }

        // Update user record
        $this->db->where('id', $userId);
        $updated = $this->db->update('tbl_users', [
            'phone_verified' => 1,
            'phone_verified_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $locationRel = $this->db
            ->select('location_id')
            ->from('tbl_user_location_relationship')
            ->where('user_id', $userId)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($locationRel) || empty($locationRel['location_id'])) {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => 'No location is linked to this user.'
            ]);
            exit;
        }

        $primaryLocationId = (int)$locationRel['location_id'];
        $oldLocationPhone = null;
        $locationRow = $this->db->select('phone')->get_where('tbl_locations', ['id' => $primaryLocationId], 1)->row_array();
        if (is_array($locationRow) && array_key_exists('phone', $locationRow)) {
            $oldLocationPhone = $locationRow['phone'];
        }

        $this->db->where('id', $primaryLocationId);
        $this->db->set('phone', $phone);
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->update('tbl_locations');

        $locationAffected = $this->db->affected_rows();
        $dbChange = ($locationAffected > 0);
        $updatedLocations = $dbChange ? 1 : 0;
        $attemptedLocations = 1;
        $locationIds = [$primaryLocationId];

        if ($updated) {
            $this->db->simple_query("UPDATE tbl_users SET phone_number = " . $this->db->escape($phone) . ", updated_at = " . $this->db->escape(date('Y-m-d H:i:s')) . " WHERE id = " . (int)$userId);

            // Update session
            $session['phone_verified'] = 1;
            $session['phone'] = $phone;
            $this->session->set_userdata('auction_user_session_data', $session);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Phone verified successfully',
                'phone' => $phone,
                'locations_updated' => $updatedLocations,
                'location_ids' => $locationIds,
                'locations_attempted' => $attemptedLocations,
                'db_changed' => $dbChange,
                'primary_location_id' => $primaryLocationId,
                'old_location_phone' => $oldLocationPhone,
                'location_affected_rows' => $locationAffected,
                'db_name' => $this->db->database
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update verification status']);
        }
        exit;
    }
}

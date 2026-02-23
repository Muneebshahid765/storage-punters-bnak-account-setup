<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Charge;
use Stripe\Subscription;
use Stripe\Product;
use Stripe\Price;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Identity\VerificationSession;

class StripeMembershipPlans extends CI_Controller
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

    private function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function getInput(array $requiredKeys = [])
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid JSON: ' . json_last_error_msg()
            ], 400);
        }

        foreach ($requiredKeys as $key) {
            if (!isset($input[$key])) {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => "Missing required field: $key"
                ], 400);
            }
        }

        return $input;
    }

    function getSubscriptionStatusName($statusTypeId)
    {
        if (empty($statusTypeId)) {
            return [
                'code' => 'unknown',
                'message' => "The membership doesn't exist in our database"
            ];
        }

        switch ($statusTypeId) {
            case 1:
                return [
                    'code' => 'active',
                    'message' => "The current membership is active for renewable"
                ];

            case 2:
                return [
                    'code' => 'inactive',
                    'message' => "The cancellation request has been sent"
                ];

            default:
                return [
                    'code' => 'unknown',
                    'message' => "The membership doesn't exist in our database"
                ];
        }
    }


    public function getStripeMembershipProduct()
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
        $email = $userSessionData['email'];
        $first_name = $userSessionData['first_name'];
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

        // ✅ Check if `interval` is passed (for update scenarios)
        $interval = isset($json_input['interval']) ? strtolower($json_input['interval']) : null;

        // ✅ Handle case when `payment_method_id` is NOT passed
        if (!$interval || !in_array($interval, ['yearly', 'monthly'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing or invalid parameter: interval must be 'year' or 'month'."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }



        try {

            // --- REPLACED: Fetch from Stripe with Local DB ---
            $this->load->model('UserModel');
            $this->load->model('AdminSettingModel'); // Ensure this is loaded
            
            // Get Base Fee (Platform Fee)
             // Default to 17.5 if not found, logic similar to getBidderPlansList in AdminDashboard
            $defaultCommissionRate = $this->AdminSettingModel->get_commission_value(1, "percentage");
            
            $localPlans = $this->UserModel->getAllBidderSubscriptionPlans();
            
            $membership = [];

            foreach ($localPlans as $plan) {
                // Calculate Fee % (Base - Reduction)
                $commissionRate = max(0, $defaultCommissionRate - $plan['fee_reduction']); 
                
                // --- STRIPE SYNC LOGIC ---
                $stripeProductId = $plan['stripe_product_id'];
                $stripePriceMonthlyId = $plan['stripe_price_id_monthly'];
                $stripePriceYearlyId = $plan['stripe_price_id_yearly'];
                $dbUpdateNeeded = false;

                // 1. Create Product if missing
                if (empty($stripeProductId)) {
                    try {
                        $stripeProduct = \Stripe\Product::create([
                            'name' => $plan['plan_name'],
                            'description' => "Bidder Subscription Plan: " . $plan['plan_name'],
                            'metadata' => [
                                'commission_rate' => $commissionRate,
                                'plan_id' => $plan['id']
                            ]
                        ]);
                        $stripeProductId = $stripeProduct->id;
                        $dbUpdateNeeded = true;
                    } catch (\Exception $e) {
                        // Log error or fallback? For now, continue but this plan will likely fail to subscribe
                        log_message('error', 'Stripe Product Create Failed: ' . $e->getMessage());
                    }
                }

                // 2. Create Monthly Price if missing
                if (!empty($stripeProductId) && empty($stripePriceMonthlyId)) {
                    try {
                        $stripePriceMonthly = \Stripe\Price::create([
                            'product' => $stripeProductId,
                            'unit_amount' => (int)($plan['price_28_days'] * 100), // In cents
                            'currency' => 'aud',
                            'recurring' => ['interval' => 'month'],
                            'nickname' => 'Monthly'
                        ]);
                        $stripePriceMonthlyId = $stripePriceMonthly->id;
                        $dbUpdateNeeded = true;
                    } catch (\Exception $e) {
                         log_message('error', 'Stripe Monthly Price Create Failed: ' . $e->getMessage());
                    }
                }

                // 3. Create Yearly Price if missing
                if (!empty($stripeProductId) && empty($stripePriceYearlyId)) {
                    try {
                        $stripePriceYearly = \Stripe\Price::create([
                            'product' => $stripeProductId,
                            'unit_amount' => (int)($plan['price_year'] * 100), // In cents
                            'currency' => 'aud',
                            'recurring' => ['interval' => 'year'],
                            'nickname' => 'Yearly'
                        ]);
                        $stripePriceYearlyId = $stripePriceYearly->id;
                        $dbUpdateNeeded = true;
                    } catch (\Exception $e) {
                         log_message('error', 'Stripe Yearly Price Create Failed: ' . $e->getMessage());
                    }
                }

                // 4. Update Database if new IDs created
                if ($dbUpdateNeeded) {
                    $this->db->where('id', $plan['id']);
                    $this->db->update('tbl_bidder_subscription_plans', [
                        'stripe_product_id' => $stripeProductId,
                        'stripe_price_id_monthly' => $stripePriceMonthlyId,
                        'stripe_price_id_yearly' => $stripePriceYearlyId,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                // --- END STRIPE SYNC ---

                // Construct Plans Array (Monthly & Yearly) using REAL Stripe IDs
                $plansData = [];
                
                // Monthly Plan
                if ($stripePriceMonthlyId) {
                    $plansData[] = [
                        'price_id' => $stripePriceMonthlyId, // Real Stripe Price ID
                        'price'    => $plan['price_28_days'],
                        'currency' => 'AUD', 
                        'interval' => 'monthly',
                    ];
                }

                // Yearly Plan
                if ($stripePriceYearlyId) {
                    $plansData[] = [
                        'price_id' => $stripePriceYearlyId, // Real Stripe Price ID
                        'price'    => $plan['price_year'],
                        'currency' => 'AUD',
                        'interval' => 'yearly',
                    ];
                }

                $membership[] = [
                    'membership' => [
                        'product_id'       => $stripeProductId ?? 'local_prod_' . $plan['id'], // Real or fallback
                        'name'             => $plan['plan_name'],
                        'description'      => "Enjoy reduced fees of just {$commissionRate}% on all auction wins.", // Auto-generate description
                        'commission_rate'  => $commissionRate,
                        'product_order'    => $plan['fee_reduction'], // Use fee reduction as sort order or id
                        'limited_offer_text'  => null,
                        'limited_offer_value'  => null
                    ],
                    'plans' => $plansData
                ];
            }
            // --- END REPLACEMENT ---

            $this->load->model('UserModel');
            // ✅ Get Stripe customer ID from DB
            $stripeData = $this->UserModel->getUserStripeIdById($userId);
            $user = null;
            $subscriptions = null;

            if ($stripeData && !empty($stripeData['stripe_customer_id'])) {

                try {
                    $customerId = $stripeData['stripe_customer_id'];
                    $customer   = Customer::retrieve($customerId);

                    if (!isset($customer->deleted) || !$customer->deleted) {
                        $user = [
                            'id'    => $customer->id,
                            'email' => $customer->email,
                            'name'  => $customer->name,
                        ];

                        $subs = Subscription::all(['customer' => $customerId, 'status' => 'active', 'limit' => 1]);

                        foreach ($subs->data as $sub) {
                            $item = $sub->items->data[0]; // first subscription item

                            $product = \Stripe\Product::retrieve($item->price->product);

                            $subscriptionData = $this->UserModel->getUserSubscriptionIdById($sub->id);
                            $statusInfo = $this->getSubscriptionStatusName($subscriptionData['status_type_id'] ?? null);

                            $subscriptions[] = [
                                'subscription_id'      => $sub->id,
                                'status'               => $sub->status,
                                // ✔ NEW FIELD NAMES
                                'membership_status'          => $statusInfo['code'],
                                'membership_status_message'  => $statusInfo['message'],
                                'start_date'           => date('Y-m-d H:i:s', $sub->start_date),
                                'current_period_start' => date('Y-m-d H:i:s', $item->current_period_start),
                                'current_period_end'   => date('Y-m-d H:i:s', $item->current_period_end),
                                'plan' => [
                                    'product_id' => $item->price->product,
                                    'name'             => $product->name,
                                    'price_id'   => $item->price->id,
                                    'amount'     => $item->price->unit_amount / 100,
                                    'currency'   => strtoupper($item->price->currency),
                                    'interval'   => $item->price->recurring->interval . "ly",
                                ]
                            ];
                        }
                    }
                } catch (ApiErrorException $e) {
                    // Don’t break output, just leave user/subscriptions as null
                }
            }

            $this->load->model('CoinModel');
            $coin_balance = $this->CoinModel->getBalance($userId);
            $punter_id = "SP-" . str_pad($userId, 4, '0', STR_PAD_LEFT);

            $output = [
                'membership' => $membership,
                'user' => $user,
                'profile' => ["first_name" => $first_name],
                'subscriptions' => $subscriptions,
                'coin_balance' => $coin_balance,
                'punter_id' => $punter_id
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode(['status' => "success", 'message' => "Membership plans successfully retreived", 'data' => $output], JSON_PRETTY_PRINT));
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => "error", 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => "error", 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }

    public function getBidderMembershipByUserId()
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
        $email = $userSessionData['email'];
        $first_name = $userSessionData['first_name'];
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


        $this->load->model('UserModel');
        // ✅ Get Stripe customer ID from DB
        $stripeData = $this->UserModel->getUserStripeIdById($userId);
        $user = null;
        $subscriptions = [];

        // Load Auction Model
        $this->load->model('AdminSettingModel');

        // Fetch bidder profile details from the database
        $defaultCommissionRate = $this->AdminSettingModel->get_commission_value(1, "percentage");
        $commissionRate = $defaultCommissionRate; // ✅ Default commission rate

        // --- 1. Fetch Local Subscription (Admin Created) ---
        $localSubscription = $this->UserModel->getUserSubscriptionByUserId($userId);

        if ($localSubscription) {
             // Map local status ID to string code
             $statusInfo = $this->getSubscriptionStatusName($localSubscription['status_type_id'] ?? null);
             $localStatus = $statusInfo['code']; // 'active', 'inactive', 'unknown'
             
             // ✅ Calculate dynamic commission rate for the local subscription
             // We need to fetch the reduction for this plan
             $planId = $localSubscription['plan_id'] ?? 0;
             $feeReduction = 0;
             if ($planId) {
                // Fetch fee reduction from tbl_bidder_subscription_plans
                $this->db->select('fee_reduction');
                $this->db->from('tbl_bidder_subscription_plans');
                $this->db->where('id', $planId);
                $planInfo = $this->db->get()->row_array();
                $feeReduction = isset($planInfo['fee_reduction']) ? floatval($planInfo['fee_reduction']) : 0;
             }
             $localCommissionRate = max(0, $defaultCommissionRate - $feeReduction);
             
             $subscriptions[] = [
                'subscription_id'           => $localSubscription['subscription_id'] ?? 'local_' . $localSubscription['id'], 
                'status'                    => $localStatus === 'active' ? 'active' : 'canceled', // Map to Stripe-like status
                'membership_status'         => $localStatus,
                'membership_status_message' => $statusInfo['message'],
                'start_date'                => $localSubscription['created_at'],
                'current_period_start'      => $localSubscription['created_at'], // Or calculated field
                'current_period_end'        => $localSubscription['next_due_date'],
                'is_local'                  => true, // Flag to identify local subs
                'plan' => [
                    'product_id'      => 'local_plan_' . $localSubscription['id'], // Placeholder or real product ID if stored
                    'name'            => $localSubscription['plan_name'],
                    'price_id'        => 'local_price_' . $localSubscription['id'],
                    'amount'          => $localSubscription['amount'],
                    'currency'        => 'AUD', // Default or fetch from DB
                    'interval'        => $localSubscription['billing_cycle'] . "ly",
                    'commission_rate' => $localCommissionRate // ✅ Dynamically calculated
                ]
            ];

            // If this is the active subscription, update the overall commissionRate
            if ($localStatus === 'active') {
                $commissionRate = $localCommissionRate;
            }
        }

        // --- 2. Fetch Stripe Subscription (if exists) ---
        if ($stripeData && !empty($stripeData['stripe_customer_id'])) {
            try {
                $customerId = $stripeData['stripe_customer_id'];
                $customer   = Customer::retrieve($customerId);

                if (!isset($customer->deleted) || !$customer->deleted) {
                    $user = [
                        'id'    => $customer->id,
                        'email' => $customer->email,
                        'name'  => $customer->name,
                    ];

                    // --- Get active subscription(s) ---
                    $subs = Subscription::all(['customer' => $customerId, 'status' => 'active', 'limit' => 1]);

                    foreach ($subs->data as $sub) {
                        $item = $sub->items->data[0];
                        $product = \Stripe\Product::retrieve($item->price->product);

                        // ✅ Fetch fee reduction for this plan from DB to make it dynamic
                        $feeReduction = 0;
                        // Attempt to find the plan by stripe_product_id
                        $this->db->select('fee_reduction');
                        $this->db->from('tbl_bidder_subscription_plans');
                        $this->db->where('stripe_product_id', $item->price->product);
                        $planInfo = $this->db->get()->row_array();
                        
                        if ($planInfo) {
                            $feeReduction = floatval($planInfo['fee_reduction']);
                        } else {
                            // Fallback to metadata if not in DB
                            $feeReduction = isset($product->metadata->fee_reduction) 
                                ? floatval($product->metadata->fee_reduction) 
                                : 0;
                        }

                        $commissionRate = max(0, $defaultCommissionRate - $feeReduction);

                        $subscriptions[] = [
                            'subscription_id'      => $sub->id,
                            'status'               => $sub->status,
                            'start_date'           => date('Y-m-d H:i:s', $sub->start_date),
                            'current_period_start' => date('Y-m-d H:i:s', $item->current_period_start),
                            'current_period_end'   => date('Y-m-d H:i:s', $item->current_period_end),
                            'plan' => [
                                'product_id' => $item->price->product,
                                'name'       => $product->name,
                                'price_id'   => $item->price->id,
                                'amount'     => $item->price->unit_amount / 100,
                                'currency'   => strtoupper($item->price->currency),
                                'interval'   => $item->price->recurring->interval . "ly",
                                'commission_rate' => $commissionRate
                            ]
                        ];
                    }
                }
            } catch (ApiErrorException $e) {
                // Stripe API issue - continue with local only
            } catch (Exception $e) {
                // Generic issue
            }
        } 
        
        // If no user found from Stripe, try to use local user data if needed, 
        // or just return what we have since frontend handles null user but checks subscriptions.

        // --- Final Output ---
        $output = [
            'user' => $user,
            'profile' => ["first_name" => $first_name],
            'subscriptions' => $subscriptions,
            'commission_rate' => $commissionRate
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'  => "success",
                'message' => "Membership details successfully retrieved.",
                'data'    => $output
            ], JSON_PRETTY_PRINT));
    }
}

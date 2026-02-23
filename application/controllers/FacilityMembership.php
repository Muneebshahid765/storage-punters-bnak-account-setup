<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Product;
use Stripe\Price;
use Stripe\Exception\ApiErrorException;

class FacilityMembership extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        try {
            // Check if the user session exists
            if (!$this->session->userdata('auction_user_session_data')) {
                 header('Content-Type: application/json');
                 echo json_encode(['status' => 'error', 'message' => 'Unauthorized'], JSON_PRETTY_PRINT);
                 exit;
            }

            $secretKey = stripe_secret_key();
            if ($secretKey) {
                Stripe::setApiKey($secretKey);
            } else {
                log_message('error', 'Stripe secret key missing; please set STRIPE_SECRET_KEY in .env');
            }

            date_default_timezone_set('Australia/Brisbane');
            $this->load->model('UserModel');
            $this->load->model('CompanyModel');
            $this->load->model('StorageFacilityModel');
        } catch (\Throwable $e) {
            log_message('error', 'FacilityMembership Constructor Error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'System initialization failed: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }

    private function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    public function getFacilityMembershipProduct()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];

        // Get Company ID for this user
        $company = $this->CompanyModel->getCompanyByUserId($userId);
        if (!$company) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Please complete your Company Setup before managing facility subscriptions.'
            ], 200);
        }
        $companyId = $company['company_id'];

        $json_input = json_decode(file_get_contents('php://input'), true);
        $interval = isset($json_input['interval']) ? strtolower($json_input['interval']) : 'monthly';

        try {
            // Fetch Facility Plans
            $this->db->select('fp.*, GROUP_CONCAT(ff.feature_name SEPARATOR "||") as features');
            $this->db->from('tbl_facility_subscription_plans fp');
            $this->db->join('tbl_facility_plan_features ff', 'ff.plan_id = fp.id', 'left');
            $this->db->group_by('fp.id');
            $this->db->order_by('fp.monthly_price', 'ASC');
            $localPlans = $this->db->get()->result_array();

            $membership = [];

            foreach ($localPlans as $plan) {
                $stripeProductId = $plan['stripe_product_id'];
                $stripePriceMonthlyId = $plan['stripe_price_id_monthly'];
                $stripePriceYearlyId = $plan['stripe_price_id_yearly'];
                $dbUpdateNeeded = false;

                // 1. Create Product if missing
                if (empty($stripeProductId)) {
                    try {
                        $stripeProduct = Product::create([
                            'name' => $plan['plan_name'],
                            'description' => "Facility Subscription Plan: " . $plan['plan_name'],
                            'metadata' => [
                                'type' => 'facility',
                                'plan_id' => $plan['id']
                            ]
                        ]);
                        $stripeProductId = $stripeProduct->id;
                        $dbUpdateNeeded = true;
                    } catch (\Exception $e) {
                        log_message('error', 'Stripe Facility Product Create Failed: ' . $e->getMessage());
                    }
                }

                // 2. Create Monthly Price if missing
                if (!empty($stripeProductId) && empty($stripePriceMonthlyId)) {
                    try {
                        $stripePriceMonthly = Price::create([
                            'product' => $stripeProductId,
                            'unit_amount' => (int)($plan['monthly_price'] * 100),
                            'currency' => 'aud',
                            'recurring' => ['interval' => 'month'],
                            'nickname' => 'Monthly'
                        ]);
                        $stripePriceMonthlyId = $stripePriceMonthly->id;
                        $dbUpdateNeeded = true;
                    } catch (\Exception $e) {
                         log_message('error', 'Stripe Facility Monthly Price Create Failed: ' . $e->getMessage());
                    }
                }

                // 3. Create Yearly Price if missing
                if (!empty($stripeProductId) && empty($stripePriceYearlyId)) {
                    try {
                        $stripePriceYearly = Price::create([
                            'product' => $stripeProductId,
                            'unit_amount' => (int)($plan['annual_price'] * 100),
                            'currency' => 'aud',
                            'recurring' => ['interval' => 'year'],
                            'nickname' => 'Yearly'
                        ]);
                        $stripePriceYearlyId = $stripePriceYearly->id;
                        $dbUpdateNeeded = true;
                    } catch (\Exception $e) {
                         log_message('error', 'Stripe Facility Yearly Price Create Failed: ' . $e->getMessage());
                    }
                }

                if ($dbUpdateNeeded) {
                    $this->db->where('id', $plan['id']);
                    $this->db->update('tbl_facility_subscription_plans', [
                        'stripe_product_id' => $stripeProductId,
                        'stripe_price_id_monthly' => $stripePriceMonthlyId,
                        'stripe_price_id_yearly' => $stripePriceYearlyId,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                $plansData = [];
                if ($stripePriceMonthlyId) {
                    $plansData[] = [
                        'price_id' => $stripePriceMonthlyId,
                        'price'    => $plan['monthly_price'],
                        'currency' => 'AUD',
                        'interval' => 'monthly',
                    ];
                }
                if ($stripePriceYearlyId) {
                    $plansData[] = [
                        'price_id' => $stripePriceYearlyId,
                        'price'    => $plan['annual_price'],
                        'currency' => 'AUD',
                        'interval' => 'yearly',
                    ];
                }

                $membership[] = [
                    'membership' => [
                        'product_id'       => $stripeProductId,
                        'name'             => $plan['plan_name'],
                        'description'      => $plan['features'] ? str_replace('||', '. ', $plan['features']) : 'Facility Management Plan',
                        'product_order'    => $plan['id'],
                        'limited_offer_text'  => null
                    ],
                    'plans' => $plansData
                ];
            }

            // Fetch Current Subscription
            $subscriptions = [];
            $this->db->where('company_id', $companyId);
            $this->db->where('status', 'paid');
            $localSub = $this->db->get('tbl_facility_subscriptions')->row_array();

            if ($localSub) {
                // Get plan details
                $this->db->where('id', $localSub['plan_id']);
                $localPlan = $this->db->get('tbl_facility_subscription_plans')->row_array();

                // Fix next billing date - fetch from Stripe if available
                $nextBillingDate = null;
                if (!empty($localSub['stripe_subscription_id'])) {
                    try {
                        $stripeSub = Subscription::retrieve($localSub['stripe_subscription_id']);
                        if ($stripeSub && $stripeSub->current_period_end) {
                            $nextBillingDate = date('Y-m-d H:i:s', $stripeSub->current_period_end);
                            
                            // Update database with correct date
                            $this->db->where('id', $localSub['id']);
                            $this->db->update('tbl_facility_subscriptions', [
                                'current_period_end' => $nextBillingDate,
                                'next_billing_date' => date('Y-m-d', $stripeSub->current_period_end)
                            ]);
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to fetch Stripe subscription for next billing date: ' . $e->getMessage());
                    }
                }
                
                // Fallback to database values
                if (!$nextBillingDate) {
                    $nextBillingDate = $localSub['current_period_end'] ?? $localSub['next_billing_date'];
                }

                $subscriptions[] = [
                    'subscription_id'      => $localSub['stripe_subscription_id'] ?? 'local_' . $localSub['id'],
                    'status'               => $localSub['status'] === 'paid' ? 'active' : 'canceled',
                    'membership_status'    => $localSub['status'] === 'paid' ? 'active' : 'inactive',
                    'start_date'           => $localSub['start_date'],
                    'current_period_end'   => $nextBillingDate,
                    'plan' => [
                        'product_id' => $localPlan['stripe_product_id'] ?? '',
                        'name'       => $localPlan['plan_name'] ?? 'Custom Plan',
                        'price_id'   => $localSub['cycle'] === 'monthly' ? ($localPlan['stripe_price_id_monthly'] ?? '') : ($localPlan['stripe_price_id_yearly'] ?? ''),
                        'amount'     => $localSub['amount'],
                        'currency'   => $localSub['currency'],
                        'interval'   => $localSub['cycle'] . "ly",
                    ]
                ];
            }

            return $this->jsonResponse([
                'status' => 'success',
                'data' => [
                    'membership' => $membership,
                    'subscriptions' => $subscriptions,
                    'user' => ['id' => $userId, 'email' => $userSessionData['email'], 'name' => $userSessionData['first_name']],
                    'profile' => ['first_name' => $userSessionData['first_name']]
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function subscribeFacilityMembership()
    {
        $userSessionData = $this->session->userdata('auction_user_session_data');
        
        if (!$userSessionData) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'User session not found'], 401);
        }
        
        $userId = $userSessionData['user_id'] ?? null;
        $email = $userSessionData['email'] ?? null;
        $firstName = $userSessionData['first_name'] ?? '';
        $lastName = $userSessionData['last_name'] ?? '';

        if (!$userId || !$email) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid user session data'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $priceId = $input['price_id'] ?? null;
        $interval = $input['interval'] ?? 'monthly';

        if (!$priceId) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Price ID is required'], 400);
        }

        try {
            // Get Company ID
            if (!isset($this->CompanyModel)) {
                throw new \Exception('CompanyModel not loaded');
            }
            
            $company = $this->CompanyModel->getCompanyByUserId($userId);
            if (!$company) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Company not found. Please complete your company setup first.'], 404);
            }
            
            if (!isset($company['company_id'])) {
                throw new \Exception('Company ID not found in company data');
            }
            
            $companyId = $company['company_id'];

            // Get or Create Stripe Customer (For the company owner)
            if (!isset($this->UserModel)) {
                throw new \Exception('UserModel not loaded');
            }
            
            $stripeData = $this->UserModel->getUserStripeIdById($userId);
            $customerId = $stripeData['stripe_customer_id'] ?? null;

            if (!$customerId) {
                if (!class_exists('Stripe\Customer')) {
                    throw new \Exception('Stripe SDK not loaded properly');
                }
                
                $customer = Customer::create([
                    'email' => $email,
                    'name' => $firstName . ' ' . $lastName,
                    'metadata' => ['user_id' => $userId, 'company_id' => $companyId]
                ]);
                $customerId = $customer->id;
                
                if (!$this->db->insert('tbl_user_stripe_relationship', [
                    'user_id' => $userId,
                    'stripe_customer_id' => $customerId,
                    'created_at' => date('Y-m-d H:i:s')
                ])) {
                    throw new \Exception('Failed to save Stripe customer ID: ' . $this->db->error()['message']);
                }
            }

            // Check for payment methods
            $customer_obj = Customer::retrieve($customerId);
            $payment_methods = \Stripe\PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card',
            ]);

            if (empty($payment_methods->data)) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'No active payment methods found. Please add a payment method first.'], 200);
            }

            $paymentMethodId = $payment_methods->data[0]->id;

            // Create Subscription
            $subscription = Subscription::create([
                'customer' => $customerId,
                'items' => [['price' => $priceId]],
                'default_payment_method' => $paymentMethodId,
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // Save to DB
            // Find local plan ID by stripe price ID
            if (empty($priceId)) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid price ID provided.'
                ], 400);
            }
            
            $this->db->where("(stripe_price_id_monthly = " . $this->db->escape($priceId) . " OR stripe_price_id_yearly = " . $this->db->escape($priceId) . ")", NULL, FALSE);
            $plan = $this->db->get('tbl_facility_subscription_plans')->row_array();

            if (!$plan) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Selected plan could not be found. Please refresh and try again.'
                ], 404);
            }

            $subData = [
                'company_id' => $companyId,
                'stripe_customer_id' => $customerId,
                'stripe_subscription_id' => $subscription->id,
                'plan_id' => $plan['id'] ?? 0,
                'cycle' => $interval === 'yearly' ? 'annual' : 'monthly',
                'status' => 'paid',
                'next_billing_date' => date('Y-m-d', $subscription->current_period_end),
                'start_date' => date('Y-m-d H:i:s', $subscription->start_date),
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end),
                'amount' => $subscription->items->data[0]->price->unit_amount / 100,
                'currency' => strtoupper($subscription->items->data[0]->price->currency),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('company_id', $companyId);
            $exists = $this->db->get('tbl_facility_subscriptions')->num_rows() > 0;

            if ($exists) {
                $this->db->where('company_id', $companyId);
                $this->db->update('tbl_facility_subscriptions', $subData);
            } else {
                $subData['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('tbl_facility_subscriptions', $subData);
            }

            return $this->jsonResponse(['status' => 'success', 'message' => 'Subscription successful', 'subscription_id' => $subscription->id]);

        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe API Error in subscribeFacilityMembership: ' . $e->getMessage());
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 200);
        } catch (\Throwable $e) {
            log_message('error', 'Fatal Error in subscribeFacilityMembership: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
            return $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }

    public function cancelFacilityMembership()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $subscriptionId = $input['subscription_id'] ?? null;

        if (!$subscriptionId) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Subscription ID is required'], 400);
        }

        try {
            $periodEnd = null;

            if (strpos($subscriptionId, 'local_') === 0) {
                $id = str_replace('local_', '', $subscriptionId);
                $localSub = $this->db->get_where('tbl_facility_subscriptions', ['id' => $id])->row_array();
                $periodEnd = $localSub['current_period_end'] ?? $localSub['next_billing_date'];
            } else {
                $subscription = Subscription::update($subscriptionId, [
                    'cancel_at_period_end' => true,
                ]);
                $periodEnd = date('Y-m-d H:i:s', $subscription->current_period_end);
            }

            if (!$periodEnd) {
                $periodEnd = date('Y-m-d H:i:s', strtotime('+30 days'));
            }

            $this->db->where(strpos($subscriptionId, 'local_') === 0 ? 'id' : 'stripe_subscription_id', strpos($subscriptionId, 'local_') === 0 ? str_replace('local_', '', $subscriptionId) : $subscriptionId);
            $this->db->update('tbl_facility_subscriptions', [
                'status' => 'pending',
                'cancel_at_period_end' => 1,
                'current_period_end' => $periodEnd,
                'next_billing_date' => date('Y-m-d', strtotime($periodEnd)),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return $this->jsonResponse(['status' => 'success', 'message' => 'Subscription will remain active until ' . date('M j, Y', strtotime($periodEnd)) . ' and will not renew.']);

        } catch (ApiErrorException $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function pauseFacilityMembership()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $subscriptionId = $input['subscription_id'] ?? null;

        if (!$subscriptionId) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Subscription ID is required'], 400);
        }

        $resumeTimestamp = strtotime('+30 days');
        $resumeDate = date('Y-m-d H:i:s', $resumeTimestamp);

        try {
            if (strpos($subscriptionId, 'local_') === 0) {
                $id = str_replace('local_', '', $subscriptionId);
                $this->db->where('id', $id);
            } else {
                Subscription::update($subscriptionId, [
                    'pause_collection' => [
                        'behavior' => 'mark_uncollectible',
                        'resumes_at' => $resumeTimestamp
                    ]
                ]);

                $this->db->where('stripe_subscription_id', $subscriptionId);
            }

            $this->db->update('tbl_facility_subscriptions', [
                'status' => 'pending',
                'next_billing_date' => date('Y-m-d', $resumeTimestamp),
                'current_period_end' => $resumeDate,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return $this->jsonResponse(['status' => 'success', 'message' => 'Subscription paused for one month.']);
        } catch (ApiErrorException $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}

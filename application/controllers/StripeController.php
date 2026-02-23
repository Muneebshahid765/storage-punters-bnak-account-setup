<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\BankAccount;
use Stripe\PaymentMethod;
use Stripe\Charge;
use Stripe\Subscription;
use Stripe\SetupIntent;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Balance;
use Stripe\Token;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Identity\VerificationSession;

class StripeController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!class_exists('Stripe\\Stripe') && file_exists(FCPATH . 'vendor/autoload.php')) {
            require_once FCPATH . 'vendor/autoload.php';
        }

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

    private function getStripeErrorMeta(ApiErrorException $e)
    {
        $requestId = method_exists($e, 'getRequestId') ? $e->getRequestId() : null;
        $stripeBody = method_exists($e, 'getJsonBody') ? $e->getJsonBody() : null;
        $stripeError = is_array($stripeBody) && isset($stripeBody['error']) ? $stripeBody['error'] : [];

        return [
            'stripe_request_id' => $requestId,
            'stripe_error_type' => $stripeError['type'] ?? null,
            'stripe_error_code' => $stripeError['code'] ?? null,
            'stripe_error_param' => $stripeError['param'] ?? null,
        ];
    }

    private function getUserConnectAccountId($userId)
    {
        if (empty($userId)) {
            return null;
        }

        if ($this->db->field_exists('stripe_connect_account_id', 'tbl_users')) {
            $row = $this->db->get_where('tbl_users', ['id' => (int)$userId], 1)->row_array();
            return !empty($row['stripe_connect_account_id']) ? $row['stripe_connect_account_id'] : null;
        }

        return null;
    }

    private function setUserConnectAccountId($userId, $accountId)
    {
        if (empty($userId) || empty($accountId)) {
            return;
        }

        if ($this->db->field_exists('stripe_connect_account_id', 'tbl_users')) {
            $data = ['stripe_connect_account_id' => $accountId];
            if ($this->db->field_exists('updated_at', 'tbl_users')) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            $this->db->where('id', (int)$userId);
            $this->db->update('tbl_users', $data);
        }
    }

    /**
     * Retrieves platform available balance for a currency in cents
     */
    private function getPlatformAvailableBalance($currency = 'aud')
    {
        try {
            $balance = Balance::retrieve();
            $available = 0;

            if (!empty($balance->available)) {
                foreach ($balance->available as $entry) {
                    if (strtolower($entry->currency) === strtolower($currency)) {
                        $available += (int)$entry->amount;
                    }
                }
            }

            return $available;
        } catch (ApiErrorException $e) {
            log_message('error', 'Unable to fetch Stripe balance: ' . $e->getMessage());
            return null;
        }
    }


    private function clearUserConnectAccountId($userId)
    {
        if (empty($userId)) {
            return;
        }

        if ($this->db->field_exists('stripe_connect_account_id', 'tbl_users')) {
            $data = ['stripe_connect_account_id' => null];
            if ($this->db->field_exists('updated_at', 'tbl_users')) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            $this->db->where('id', (int)$userId);
            $this->db->update('tbl_users', $data);
        }
    }

    public function createConnectOnboardingLink()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Only POST requests are allowed.']);
            exit;
        }

        // Some clients may not set CI's AJAX header consistently; do not hard-block if user is authenticated.

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'You do not have the required permissions to access this request.']);
            exit;
        }

        // Get location_ids from request body
        $input = json_decode(file_get_contents('php://input'), true);
        $locationIds = isset($input['location_ids']) && is_array($input['location_ids']) ? $input['location_ids'] : [];
        
        // Store location_ids in session for use after Stripe onboarding
        if (!empty($locationIds)) {
            $this->session->set_userdata('pending_bank_location_ids', $locationIds);
            log_message('info', "Stored location_ids in session for user {$userId}: " . json_encode($locationIds));
        }

        $returnBase = site_url();
        if (empty($returnBase) || !preg_match('#^https?://#i', $returnBase)) {
            $returnBase = base_url();
        }

        try {
            // Always create a fresh Stripe Connect account for each new bank account
            // This allows users to add multiple bank accounts without detaching existing ones
            $account = Account::create([
                'type' => 'express',
                'country' => 'AU',
                'email' => $userSessionData['email'],
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
                'tos_acceptance' => [
                    'service_agreement' => 'recipient',
                ],
                'metadata' => [
                    'platform_user_id' => (string)$userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ]);
            $accountId = $account->id;
            
            log_message('info', "Created new Stripe Connect account {$accountId} for user {$userId}");

            $returnUrl = rtrim($returnBase, '/') . '/stripe/connect/return?account_id=' . urlencode($accountId);
            $refreshUrl = rtrim($returnBase, '/') . '/stripe/connect/refresh?account_id=' . urlencode($accountId);

            $link = AccountLink::create([
                'account' => $accountId,
                'refresh_url' => $refreshUrl,
                'return_url' => $returnUrl,
                'type' => 'account_onboarding',
            ]);

            echo json_encode([
                'status' => 'success',
                'onboarding_url' => $link->url,
                'account_id' => $accountId,
                'stripe_mode' => (string)(getenv('STRIPE_MODE') ?: ''),
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        } catch (ApiErrorException $e) {
            $meta = $this->getStripeErrorMeta($e);
            log_message('error', 'Stripe AccountLink onboarding error: ' . json_encode(['message' => $e->getMessage()] + $meta));
            echo json_encode([
                'status' => 'error',
                'message' => 'Stripe error: ' . $e->getMessage(),
            ] + $meta, JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        } catch (Exception $e) {
            log_message('error', 'Connect onboarding general error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'General error: ' . $e->getMessage()], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        }
    }

    public function connectReturn()
    {
        $accountId = $this->input->get('account_id');
        
        if ($accountId) {
            try {
                // Retrieve account to check status
                $account = \Stripe\Account::retrieve($accountId);
                
                // Check if onboarding is complete
                if ($account->details_submitted) {
                    // Get user from session or find by stripe account
                    $userSessionData = $this->session->userdata('auction_user_session_data');
                    
                    if ($userSessionData && isset($userSessionData['user_id'])) {
                        $userId = $userSessionData['user_id'];
                        
                        // Save bank account info to database if external accounts exist
                        if (!empty($account->external_accounts->data)) {
                            $this->load->model('BankModel');
                            $bankAccount = $account->external_accounts->data[0];
                            
                            // Extract bank details from Stripe
                            $accountHolderName = $bankAccount->account_holder_name ?? $account->business_profile->name ?? 'Bank Account';
                            $routingNumber = $bankAccount->routing_number ?? ''; // BSB for AU
                            $last4 = $bankAccount->last4 ?? '';
                            $bankName = $bankAccount->bank_name ?? '';
                            
                            // Check if bank already exists
                            $existingBank = $this->BankModel->getBankAccountByAccountConenctedId($accountId);
                            
                            if (!$existingBank) {
                                $bankAccountData = [
                                    'user_id' => $userId,
                                    'stripe_bank_account_id' => $accountId,
                                    'stripe_customer_id' => $bankAccount->id ?? '',
                                    'account_name' => $accountHolderName,
                                    'bsb' => $routingNumber,
                                    'account_number' => $last4 ? '****' . $last4 : '',
                                    'bank_institution' => $bankName,
                                    'is_default' => 'true',
                                    'status_type_id' => 1,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ];
                                $bankResult = $this->BankModel->insertStorageFacilityBankData($bankAccountData);
                                
                                if ($bankResult && isset($bankResult['bank_account_id'])) {
                                    $bankAccountId = $bankResult['bank_account_id'];
                                    
                                    $this->BankModel->insertStorageFacilityBankUserRelationshipData([
                                        'user_id' => $userId,
                                        'bank_id' => $bankAccountId,
                                        'status_type_id' => 1,
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                                    
                                    // Link locations to bank account if they were selected
                                    $pendingLocationIds = $this->session->userdata('pending_bank_location_ids');
                                    if (!empty($pendingLocationIds) && is_array($pendingLocationIds)) {
                                        foreach ($pendingLocationIds as $facilityId) {
                                            if (!empty($facilityId)) {
                                                $this->BankModel->linkBankToFacility($bankAccountId, (int)$facilityId);
                                                log_message('info', "Linked bank {$bankAccountId} to facility {$facilityId}");
                                            }
                                        }
                                        
                                        // Update Stripe metadata with facility info
                                        try {
                                            $this->load->model('UserModel');
                                            $activeFacilities = $this->UserModel->getBankAccountAllStorageFacilityById($bankAccountId);
                                            
                                            if (!empty($activeFacilities)) {
                                                $facilityIds = array_column($activeFacilities, 'storage_facility_id');
                                                $facilityNames = array_column($activeFacilities, 'storage_facility_name');
                                                
                                                $metadata = [
                                                    'facility_status'   => 'assigned',
                                                    'facility_ids'      => implode(',', $facilityIds),
                                                    'facility_names'    => implode(', ', $facilityNames),
                                                    'facility_count'    => count($facilityIds),
                                                    'facility_updated'  => date('Y-m-d H:i:s'),
                                                ];
                                                
                                                \Stripe\Account::update($accountId, ['metadata' => $metadata]);
                                                log_message('info', "Stripe metadata updated for bank_id {$bankAccountId}: " . json_encode($metadata));
                                            }
                                        } catch (Exception $stripeEx) {
                                            log_message('error', 'Stripe metadata update failed during connectReturn: ' . $stripeEx->getMessage());
                                        }
                                        
                                        // Clear the session data
                                        $this->session->unset_userdata('pending_bank_location_ids');
                                        log_message('info', "Cleared pending_bank_location_ids from session for user {$userId}");
                                    }
                                }
                            } else {
                                // Update existing bank record with Stripe data
                                $this->BankModel->updateBankAccount($existingBank['id'], [
                                    'account_name' => $accountHolderName,
                                    'bsb' => $routingNumber,
                                    'account_number' => $last4 ? '****' . $last4 : '',
                                    'bank_institution' => $bankName,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }
                        
                        // Update user's stripe connect account ID
                        $this->load->model('UserModel');
                        $this->UserModel->update_user($userId, ['stripe_connect_account_id' => $accountId]);
                    }
                    
                    // Set success flash message
                    $this->session->set_flashdata('success', 'Bank account setup completed successfully! Your account is now ready to receive payouts.');
                } else {
                    $this->session->set_flashdata('warning', 'Bank account setup is incomplete. Please complete the verification process.');
                }
                
            } catch (Exception $e) {
                log_message('error', 'Stripe Connect Return Error: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'There was an issue verifying your bank account. Please try again.');
            }
        }
        
        $redirect = base_url('company/setup/new');
        redirect($redirect);
    }

    public function connectRefresh()
    {
        $accountId = $this->input->get('account_id');
        
        if ($accountId) {
            try {
                // Create a new onboarding link
                $accountLink = \Stripe\AccountLink::create([
                    'account' => $accountId,
                    'refresh_url' => base_url('stripe/connect/refresh?account_id=' . $accountId),
                    'return_url' => base_url('stripe/connect/return?account_id=' . $accountId),
                    'type' => 'account_onboarding',
                ]);
                
                redirect($accountLink->url);
                return;
                
            } catch (Exception $e) {
                log_message('error', 'Stripe Connect Refresh Error: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'Unable to resume bank setup. Please try again later.');
            }
        }
        
        $redirect = base_url('company/setup/new');
        redirect($redirect);
    }


    public function test_stripe()
    {

        $test = 3;

        $email = strtolower("specialemailid2050@gmail.com");
        $name = "Ali";

        try {
            if ($test == 1) {


                $customers = Customer::all([
                    'email' => $email,
                    'limit' => 1
                ]);

                if (!empty($customers->data)) {
                    $customer = $customers->data[0];


                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['success' => true, 'message' => "Customer found", 'customer' => [
                            'id'    => $customer->id,
                            'email' => $customer->email,
                            'name'  => $customer->name,
                        ]]))
                        ->set_status_header(200);
                } else {

                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['success' => false, 'message' => "Customer not found"]))
                        ->set_status_header(200);
                }
            } else if ($test == 2) {

                // cus_S9Rp4QnqmWqP2b
                // Create the customer in Stripe
                $customer = \Stripe\Customer::create([
                    'email' => $email,
                    'name'  => $name
                ]);

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => true, 'message' => "Customer found", 'customer' => $customer]))
                    ->set_status_header(200);
            } else {

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => "Test mode is off"]))
                    ->set_status_header(200);
            }
        } catch (ApiErrorException $e) {

            $meta = $this->getStripeErrorMeta($e);
            log_message('error', 'Stripe connected account create failed: ' . json_encode(['message' => $e->getMessage()] + $meta));
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Stripe API error: ' . $e->getMessage(),
                ] + $meta, JSON_PRETTY_PRINT))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'General error: ' . $e->getMessage()], JSON_PRETTY_PRINT))
                ->set_status_header(200);
        }
    }


    public function addStripeCustomer()
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
        $name = $userSessionData['first_name'] . " " . $userSessionData['last_name'];
        $email = $userSessionData['email'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 2, 3])) {
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


        try {

            // ✅ Create Stripe Customer
            $customer = \Stripe\Customer::create([
                'email' => $email,
                'name'  => $name,
                'metadata' => [
                    'app_user_id' => $userId
                ]
            ]);


            // ✅ Respond with success
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => "Customer created successfully",
                    'customer' => [
                        'id'    => $customer->id,
                        'email' => $customer->email,
                        'name'  => $customer->name,
                    ]
                ]))
                ->set_status_header(200);
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }

    public function searchStripeCustomer()
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
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 2, 3])) {
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


        try {
            $customers = Customer::all([
                'email' => $email,
                'limit' => 1
            ]);

            if (!empty($customers->data)) {
                $customer = $customers->data[0];


                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => true, 'message' => "Customer found", 'customer' => [
                        'id'    => $customer->id,
                        'email' => $customer->email,
                        'name'  => $customer->name,
                    ]]))
                    ->set_status_header(200);
            } else {

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => "Customer not found"]))
                    ->set_status_header(200);
            }
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }


    public function prepareRegistration()
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

        $rawBody = file_get_contents('php://input');
        $json_input = json_decode($rawBody, true);
        if (!is_array($json_input)) {
            $json_input = $this->input->post(NULL, true);
        }
        if (!is_array($json_input)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request payload"
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $email = isset($json_input['email']) ? trim($json_input['email']) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "Valid email is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $first_name = $json_input['first_name'] ?? '';
        $last_name = $json_input['last_name'] ?? '';

        try {
            $secretKey = stripe_secret_key();
            if (!$secretKey) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Stripe is not configured. Please set STRIPE_SECRET_KEY."
                ], JSON_PRETTY_PRINT);
                http_response_code(503);
                return;
            }

            // 1. Search for existing customer
            $existing = \Stripe\Customer::all([
                'email' => $email,
                'limit' => 1
            ]);

            if (!empty($existing->data)) {
                $customer = $existing->data[0];
            } else {
                // 2. Create new customer
                $customer = \Stripe\Customer::create([
                    'email' => $email,
                    'name' => trim("$first_name $last_name"),
                    'metadata' => [
                        'registration_pending' => 'true'
                    ]
                ]);
            }

            // 3. Create SetupIntent
            $setupIntent = SetupIntent::create([
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
            ]);

            echo json_encode([
                "status" => "success",
                "client_secret" => $setupIntent->client_secret,
                "stripe_customer_id" => $customer->id
            ], JSON_PRETTY_PRINT);
            http_response_code(200);

        } catch (ApiErrorException $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Stripe Error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Stripe Error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
            http_response_code(500);
        }
    }


    public function prepareFacilityRegistration()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            http_response_code(405);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['standard_email_address'] ?? null;
        $first_name = $input['first_name'] ?? '';
        $last_name = $input['last_name'] ?? '';

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "message" => "Valid email required"]);
            http_response_code(400);
            exit;
        }

        try {
            // Create Express Account
            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'AU',
                'email' => $email,
                'business_profile' => [
                    'name' => "$first_name $last_name",
                    'product_description' => "Auction Payouts",
                ],
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
                'tos_acceptance' => [
                    'service_agreement' => 'recipient',
                ],
            ]);

            // Create Account Session
            $accountSession = \Stripe\AccountSession::create([
                'account' => $account->id,
                'components' => [
                    'account_onboarding' => [
                        'enabled' => true,
                        'features' => ['external_account_collection' => true],
                    ],
                ],
            ]);

            echo json_encode([
                "status" => "success",
                "client_secret" => $accountSession->client_secret,
                "account_id" => $account->id
            ]);

        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Stripe Error: " . $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function createStripeSetupIntent()
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
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 2, 3])) {
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

        $customer_id = isset($json_input['customer_id']) ? trim($json_input['customer_id']) : null;

        if (empty($customer_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing customer ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }



        try {

            $setupIntent = \Stripe\SetupIntent::create([
                'customer' => $customer_id
            ]);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'client_secret' => $setupIntent->client_secret]))
                ->set_status_header(200);
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }

    public function attachStripePaymentMethod()
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
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 2, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission."
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

        $customer_id = isset($json_input['customer_id']) ? trim($json_input['customer_id']) : null;

        if (empty($customer_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing customer ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $payment_method_id = isset($json_input['payment_method_id']) ? trim($json_input['payment_method_id']) : null;

        if (empty($customer_id) || empty($payment_method_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing payment method ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        try {

            // Attach payment method to customer
            $paymentMethod = PaymentMethod::retrieve($payment_method_id);
            $paymentMethod->attach(['customer' => $customer_id]);

            // Retrieve details for DB
            $card = $paymentMethod->card;
            $billing = $paymentMethod->billing_details;

            // ✅ Prepare data to insert into database
            $paymentMethodData = [
                "user_id"                  => $userId,
                "stripe_customer_id"       => $customer_id,
                "stripe_payment_method_id" => $payment_method_id,
                "is_default"               => "false",
                "status_type_id"           => 1, // Note: Extra space in original key removed
                "card_holder_first_name"   => $billing->name ? explode(' ', $billing->name)[0] : 'Unknown',
                "card_holder_last_name"    => $billing->name ? (explode(' ', $billing->name)[1] ?? '') : '',
                "card_holder_email"        => $billing->email ?? $email, // Fallback to user email
                "card_holder_phone"        => $billing->phone ?? '',
                "card_billing_address"     => $billing->address->line1 ?? '',
                "card_billing_zip"         => $billing->address->postal_code ?? '',
                "card_type"                => ucfirst($card->brand),
                "card_holder_card_number"  => $card->last4,
                "card_expiration_month"    => $card->exp_month,
                "card_expiration_year"     => $card->exp_year,
                "card_cvv"                 => "XXX", // We don't store CVV
                "created_at"               => date("Y-m-d H:i:s"),
                "updated_at"               => date("Y-m-d H:i:s")
            ];

            // Load Auction Model
            $this->load->model('UserModel');

            // Fetch auction details from the database
            $paymentMethodResult = $this->UserModel->insert_bidder_payment_method_data($paymentMethodData);

            // Return the response
            if ($paymentMethodResult) {


                // ✅ Set it as the default payment method for the customer
                Customer::update($customer_id, [
                    'invoice_settings' => [
                        'default_payment_method' => $payment_method_id,
                    ],
                ]);

                $paymentMethodCardStatusData = [
                    "is_default" => "true",
                    "updated_at" => date("Y-m-d H:i:s")
                ];

                // ✅ Prepare data to insert into database
                $paymentMethodOtherCardStatusData = [
                    "is_default" => "false",
                    "updated_at" => date("Y-m-d H:i:s")
                ];

                // Fetch auction details from the database
                $this->UserModel->update_bidder_payment_method_card_status_data($userId, $paymentMethodResult['payment_method_id'], $paymentMethodCardStatusData, $paymentMethodOtherCardStatusData);


                $webhookData = [
                    'payment_method'       => [
                        "id"   => $paymentMethodResult['payment_method_id'],
                        "stripe_customer_id"   => $customer_id,
                        "stripe_payment_method_id"    => $payment_method_id,
                        "is_default"        => "true",
                        "user_id"     => $userId,
                    ]
                ];

                // ✅ Send webhook and get result including payload
                $webhookResponse = send_webhook_event('payment_method.card_added', 'payment_method', $webhookData);

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
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }


    public function getStorageFacilityBankAccountFromStripeById()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access payment methods."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $search = $this->input->post('search')['value'];
        $orderColumnIndex = (int)$this->input->post('order')[0]['column'];
        $orderDir = $this->input->post('order')[0]['dir'];

        $columns = [
            'account_holder_name',
            'account_routing_number',
            'account_number',
            'account_status_type_name',
            'account_status_type_id',
            'account_updated_at'
        ];

        $orderByColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'card_updated_at';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'desc';

        try {
            $this->load->model('UserModel');
            $bankAccountRecords = $this->UserModel->getStorageFacilityBankAccountById($userId);

            $result = [];
            foreach ($bankAccountRecords as $record) {

                // Skip if Stripe bank account ID is missing or invalid
                if (empty($record['stripe_bank_account_id'])) {
                    continue;
                }

                try {
                    $bkacc = \Stripe\PaymentMethod::retrieve($record['stripe_bank_account_id']);
                    $bnk = $bkacc->au_becs_debit;
                    $meta = $bkacc->billing_details;

                    $result[] = [
                        "bank_id" => $record['bank_id'],
                        "bank_account_id" => $record['stripe_bank_account_id'],
                        "account_holder_name" => $meta->name ?? '',
                        "account_routing_number" => $bnk->bsb_number ?? '',
                        "account_number" => $bnk->last4 ?? '',
                        "account_is_default" => $record['account_is_default'],
                        "account_status_type_name" => $record['account_status_type_name'],
                        "account_status_type_id" => $record['account_status_type_id'],
                        "account_updated_at" => $record['account_updated_at'],
                    ];
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    // Optional: log Stripe-specific error for this record
                    continue; // skip invalid/broken Stripe records
                }
            }

            // Filter
            if (!empty($search)) {
                $result = array_filter($result, function ($item) use ($search) {
                    return stripos(json_encode($item), $search) !== false;
                });
            }

            // Sort
            usort($result, function ($a, $b) use ($orderByColumn, $orderDir) {
                return $orderDir === 'asc'
                    ? strcmp($a[$orderByColumn], $b[$orderByColumn])
                    : strcmp($b[$orderByColumn], $a[$orderByColumn]);
            });

            // Paginate
            $total = count($result);
            $paginated = array_slice($result, $start, $length);

            echo json_encode([
                "status" => "success",
                "message" => "Bank account retrieved successfully.",
                "result" => [
                    "draw" => $draw,
                    "recordsTotal" => $total,
                    "recordsFiltered" => $total,
                    "data" => array_values($paginated)
                ]
            ], JSON_PRETTY_PRINT);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Stripe API error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }

    public function getStorageFacilityAccountConnectLinkedFromStripeById()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access payment methods."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $search = $this->input->post('search')['value'];
        $orderColumnIndex = (int)$this->input->post('order')[0]['column'];
        $orderDir = $this->input->post('order')[0]['dir'];

        $columns = [
            'account_id',
            'account_name',
            'account_email',
            'account_type',
            'charges_enabled',
            'payouts_enabled',
            'details_submitted',
            'created_at'
        ];

        $orderByColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'card_updated_at';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'desc';

        try {
            $this->load->model('UserModel');
            $connectedAccounts  = $this->UserModel->getStorageFacilityBankAccountById($userId);

            $result = [];
            foreach ($connectedAccounts  as $record) {

                $stripeAccountId = $record['stripe_bank_account_id'] ?? null;

                // Skip if Stripe bank account ID is missing or invalid
                if (empty($record['stripe_bank_account_id'])) {
                    continue;
                }

                try {
                    $account = \Stripe\Account::retrieve($stripeAccountId);

                    $onboardingUrl = null;

                    // ✅ If onboarding is still pending, create an Account Link
                    if (!empty($account->requirements->currently_due)) {
                        //⃣ Create onboarding link
                        $accountLink = \Stripe\AccountLink::create([
                            'account'     => $account->id,
                            'refresh_url' => base_url("stripe/connected-account/reauth/{$account->id}"),
                            'return_url'  => base_url("stripe/connected-account/success/{$account->id}"),
                            'type'        => 'account_onboarding',
                        ]);

                        $onboardingUrl = $accountLink->url;
                    }

                    $result[] = [
                        "bank_id" => $record['bank_id'],
                        "account_id" => $account->id,
                        "business_profile_name" => $account->business_profile['name'],
                        "account_name" => $account->settings->payments->statement_descriptor,
                        "account_email" => $account->email,
                        "account_type" => ucfirst($account->type),
                        "charges_enabled" => $account->charges_enabled ? "Yes" : "No",
                        "payouts_enabled" => $account->payouts_enabled ? "Yes" : "No",
                        "details_submitted" => $account->details_submitted ? "Yes" : "No",
                        "account_status" => $account->requirements->currently_due ? "Pending" : "Complete",
                        "account_status_type_id" => $record['account_status_type_id'],
                        "onboarding_url"       => $onboardingUrl, // ✅ Added here
                        "created_at" => $record['account_created_at']
                            ?? (!empty($account->created) ? date('Y-m-d H:i:s', $account->created) : ''),
                        "updated_at" => $record['account_updated_at'] ?? '',
                    ];
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    // Optional: log Stripe-specific error for this record
                    continue; // skip invalid/broken Stripe records
                }
            }

            // Filter
            if (!empty($search)) {
                $result = array_filter($result, function ($item) use ($search) {
                    return stripos(json_encode($item), $search) !== false;
                });
            }

            // Sort
            usort($result, function ($a, $b) use ($orderByColumn, $orderDir) {
                return $orderDir === 'asc'
                    ? strcmp($a[$orderByColumn], $b[$orderByColumn])
                    : strcmp($b[$orderByColumn], $a[$orderByColumn]);
            });

            // Paginate
            $total = count($result);
            $paginated = array_slice($result, $start, $length);

            echo json_encode([
                "status" => "success",
                "message" => "Connected accounts retrieved successfully.",
                "result" => [
                    "draw" => $draw,
                    "recordsTotal" => $total,
                    "recordsFiltered" => $total,
                    "data" => array_values($paginated)
                ]
            ], JSON_PRETTY_PRINT);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Stripe API error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }

    public function getBidderPaymentMethodFromStripeById()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access payment methods."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 2, 3])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $search = $this->input->post('search')['value'];
        $orderColumnIndex = (int)$this->input->post('order')[0]['column'];
        $orderDir = $this->input->post('order')[0]['dir'];

        $columns = [
            'card_holder_first_name',
            'card_holder_email',
            'card_holder_phone',
            'card_billing_address',
            'card_billing_zip',
            'card_type',
            'card_holder_card_number',
            'card_expiration_month',
            'card_expiration_year',
            'card_status_type_name',
            'card_status_type_id',
            'card_updated_at'
        ];

        $orderByColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'card_updated_at';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'desc';

        try {
            $this->load->model('UserModel');
            $paymentMethodRecords = $this->UserModel->getBidderPaymentMethodById($userId);

            $result = [];
            foreach ($paymentMethodRecords as $record) {
                $pm = \Stripe\PaymentMethod::retrieve($record['stripe_payment_method_id']);
                $card = $pm->card;
                $meta = $pm->billing_details;

                $result[] = [
                    "payment_method_id" => $record['payment_method_id'],
                    "card_holder_first_name" => $meta->name ?? '',
                    "card_holder_email" => $meta->email ?? '',
                    "card_holder_phone" => $meta->phone ?? '',
                    "card_billing_address" => $meta->address->line1 ?? '',
                    "card_billing_zip" => $meta->address->postal_code ?? '',
                    "card_type" => ucfirst($card->brand),
                    "card_holder_card_number" => "**** **** **** " . $card->last4,
                    "card_expiration_month" => str_pad($card->exp_month, 2, "0", STR_PAD_LEFT),
                    "card_expiration_year" => $card->exp_year,
                    "card_is_default" => $record['card_is_default'],
                    "card_status_type_name" => $record['card_status_type_name'],
                    "card_status_type_id" => $record['card_status_type_id'],
                    "card_updated_at" => $record['card_updated_at'],
                ];
            }

            // Filter
            if (!empty($search)) {
                $result = array_filter($result, function ($item) use ($search) {
                    return stripos(json_encode($item), $search) !== false;
                });
            }

            // Sort
            usort($result, function ($a, $b) use ($orderByColumn, $orderDir) {
                return $orderDir === 'asc'
                    ? strcmp($a[$orderByColumn], $b[$orderByColumn])
                    : strcmp($b[$orderByColumn], $a[$orderByColumn]);
            });

            // Paginate
            $total = count($result);
            $paginated = array_slice($result, $start, $length);

            echo json_encode([
                "status" => "success",
                "message" => "Payment method retrieved successfully.",
                "result" => [
                    "draw" => $draw,
                    "recordsTotal" => $total,
                    "recordsFiltered" => $total,
                    "data" => array_values($paginated)
                ]
            ], JSON_PRETTY_PRINT);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Stripe API error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }


    public function updateBidderPaymentMethodCardStatusFromStripeById()
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
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 2, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission."
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
        $payment_method_id = isset($json_input['payment_method_id']) ? (string)$json_input['payment_method_id'] : null;

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

        if ($payment_method_status_name == "Yes") {
            $is_default = "true";
        } else if ($payment_method_status_name == "No") {
            $is_default = "false";
        } else {
            $is_default = 0;
        }

        // ✅ Handle case when `is_default` is NOT passed
        if ($is_default == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: is_default is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('UserModel');


        // Fetch auction details from the database
        $paymentMethodData = $this->UserModel->getPaymentMethodById($payment_method_id);

        // If auction not found, return error response
        if (!$paymentMethodData) {
            echo json_encode([
                "status" => "error",
                "message" => "Payment method not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }


        try {

            $stripePaymentMethodId = $paymentMethodData['stripe_payment_method_id'];
            $stripeCustomerId = $paymentMethodData['stripe_customer_id'];
            $cardStatusId = $paymentMethodData['status_type_id'];


            if ($cardStatusId == 2) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Something went wrong. We couldn't update your payment method status. Please try again later."
                ], JSON_PRETTY_PRINT);
                http_response_code(500); // Internal Server Error
                exit;
            }

            if ($is_default) {
                // Set as default card for the customer
                \Stripe\Customer::update(
                    $stripeCustomerId,
                    ['invoice_settings' => ['default_payment_method' => $stripePaymentMethodId]]
                );
            } else {
                // Set as default card for the customer
                \Stripe\Customer::update(
                    $stripeCustomerId,
                    ['invoice_settings' => ['default_payment_method' => null]]
                );
            }

            // ✅ Prepare data to insert into database
            $paymentMethodCardStatusData = [
                "is_default" => $is_default,
                "updated_at" => date("Y-m-d H:i:s")
            ];

            // ✅ Prepare data to insert into database
            $paymentMethodOtherCardStatusData = [
                "is_default" => "false",
                "updated_at" => date("Y-m-d H:i:s")
            ];

            // Fetch auction details from the database
            $paymentMethodResult = $this->UserModel->update_bidder_payment_method_card_status_data($userId, $payment_method_id, $paymentMethodCardStatusData, $paymentMethodOtherCardStatusData);

            // Return the response
            if ($paymentMethodResult) {

                $webhookData = [
                    'payment_method'       => [
                        "id"   => $payment_method_id,
                        "stripe_customer_id"   => $stripeCustomerId,
                        "stripe_payment_method_id"    => $stripePaymentMethodId,
                        "is_default"        => $is_default,
                        "user_id"     => $userId,
                    ]
                ];

                // ✅ Send webhook and get result including payload
                $webhookResponse = send_webhook_event('payment_method.card_updated', 'payment_method', $webhookData);

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
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }


    public function updateStorageFacilityBankAccountStatusFromStripeById()
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
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission."
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

        // ✅ Check if `bank_account_id` is passed (for update scenarios)
        $bank_account_id = isset($json_input['bank_account_id']) ? (string)$json_input['bank_account_id'] : null;

        // ✅ Handle case when `bank_account_id` is NOT passed
        if ($bank_account_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: bank_account_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Check if `bank_account_status_name` is passed (for update scenarios)
        $bank_account_status_name = isset($json_input['bank_account_status_name']) ? (string)$json_input['bank_account_status_name'] : null;


        // ✅ Handle case when `bank_account_status_name` is NOT passed
        if ($bank_account_status_name === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: bank_account_status_name is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        if ($bank_account_status_name == "Yes") {
            $is_default = "true";
        } else if ($bank_account_status_name == "No") {
            $is_default = "false";
        } else {
            $is_default = 0;
        }

        // ✅ Handle case when `is_default` is NOT passed
        if ($is_default == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: is_default is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch auction details from the database
        $bankAccountData = $this->UserModel->getBankAccountById($bank_account_id);

        // If auction not found, return error response
        if (!$bankAccountData) {
            echo json_encode([
                "status" => "error",
                "message" => "Bank account not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }


        try {

            $stripeBankAccountId = $bankAccountData['stripe_bank_account_id'];
            $stripeCustomerId = $bankAccountData['stripe_customer_id'];
            $bankStatusId = $bankAccountData['status_type_id'];


            if ($bankStatusId == 2) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Something went wrong. We couldn't update your bank account status. Please try again later."
                ], JSON_PRETTY_PRINT);
                http_response_code(500); // Internal Server Error
                exit;
            }



            if ($is_default) {
                // Set as default bank account for the customer
                \Stripe\Customer::update(
                    $stripeCustomerId,
                    ['invoice_settings' => ['default_payment_method' => $stripeBankAccountId]]
                );
            } else {
                // Set as default bank account for the customer
                \Stripe\Customer::update(
                    $stripeCustomerId,
                    ['invoice_settings' => ['default_payment_method' => null]]
                );
            }

            // ✅ Prepare data to insert into database
            $bankAccountAccountStatusData = [
                "is_default" => $is_default,
                "updated_at" => date("Y-m-d H:i:s")
            ];

            // ✅ Prepare data to insert into database
            $bankAccountOtherAccountStatusData = [
                "is_default" => "false",
                "updated_at" => date("Y-m-d H:i:s")
            ];

            // Fetch auction details from the database
            $bankAccountResult = $this->UserModel->update_storage_facility_bank_account_account_status_data($userId, $bank_account_id, $bankAccountAccountStatusData, $bankAccountOtherAccountStatusData);

            // Return the response
            if ($bankAccountResult) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Your bank account status has been updated successfully.",
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
                exit;
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Something went wrong. We couldn't update your bank account status. Please try again later."
                ], JSON_PRETTY_PRINT);
                http_response_code(500); // Internal Server Error
                exit;
            }
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }

    public function attachDetachStorageFacilityBankAccountStatusFromStripeById()
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
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission."
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

        // ✅ Check if `bank_account_id` is passed (for update scenarios)
        $bank_account_id = isset($json_input['bank_account_id']) ? (string)$json_input['bank_account_id'] : null;

        // ✅ Handle case when `bank_account_id` is NOT passed
        if ($bank_account_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: bank_account_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Check if `bank_account_status_name` is passed (for update scenarios)
        $bank_account_status_name = isset($json_input['bank_account_status_name']) ? (string)$json_input['bank_account_status_name'] : null;


        // ✅ Handle case when `bank_account_status_name` is NOT passed
        if ($bank_account_status_name === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: bank_account_status_name is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        if ($bank_account_status_name == "Active") {
            $bank_account_status_id = 1;
        } else if ($bank_account_status_name == "Inactive") {
            $bank_account_status_id = 2;
        } else {
            $bank_account_status_id = 0;
        }

        // ✅ Handle case when `bank_account_status_id` is NOT passed
        if ($bank_account_status_id == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: bank_account_status_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('UserModel');


        // Fetch auction details from the database
        $bankAccountData = $this->UserModel->getBankAccountById($bank_account_id);

        // If auction not found, return error response
        if (!$bankAccountData) {
            echo json_encode([
                "status" => "error",
                "message" => "Bank account not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }


        try {

            $stripeBankAccountId = $bankAccountData['stripe_bank_account_id'];
            $stripeCustomerId = $bankAccountData['stripe_customer_id'];

            $stripeBankAccount = \Stripe\PaymentMethod::retrieve($stripeBankAccountId);

            if ($bank_account_status_id == 1) {
                echo json_encode([
                    "status" => "error",
                    "message" => "This bank account was previously removed for security reasons and cannot be reused. Please add your bank details again to continue."
                ], JSON_PRETTY_PRINT);
                http_response_code(500);
                exit;
            } else if ($bank_account_status_id == 2) {
                $stripeBankAccount->detach();

                // ✅ Prepare data to insert into database
                $bankAccountDefaultStatusInfo = [
                    "is_default" => "false",
                    "updated_at" => date("Y-m-d H:i:s")
                ];

                // ✅ Prepare data to insert into database
                $bankAccountAttachDetachStatusData = [
                    "status_type_id" => $bank_account_status_id,
                    "updated_at" => date("Y-m-d H:i:s")
                ];

                // Fetch auction details from the database
                $bankAccountResult = $this->UserModel->update_storage_facility_bank_account_status_data($bank_account_id, $bankAccountDefaultStatusInfo, $bankAccountAttachDetachStatusData);


                // Return the response
                if ($bankAccountResult) {

                    echo json_encode([
                        "status" => "success",
                        "message" => "Your bank account has been detached successfully.",
                    ], JSON_PRETTY_PRINT);
                    http_response_code(200);

                    exit;
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Something went wrong. We couldn't update your bank account status. Please try again later."
                    ], JSON_PRETTY_PRINT);
                    http_response_code(500); // Internal Server Error
                    exit;
                }
            }
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }
    public function attachDetachBidderPaymentMethodCardStatusFromStripeById()
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
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 2, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission."
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
        $payment_method_id = isset($json_input['payment_method_id']) ? (string)$json_input['payment_method_id'] : null;

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


        // Fetch auction details from the database
        $paymentMethodData = $this->UserModel->getPaymentMethodById($payment_method_id);

        // If auction not found, return error response
        if (!$paymentMethodData) {
            echo json_encode([
                "status" => "error",
                "message" => "Payment method not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }


        try {

            $stripePaymentMethodId = $paymentMethodData['stripe_payment_method_id'];
            $stripeCustomerId = $paymentMethodData['stripe_customer_id'];

            $stripePaymentMethod = \Stripe\PaymentMethod::retrieve($stripePaymentMethodId);

            if ($payment_method_status_id == 1) {
                echo json_encode([
                    "status" => "error",
                    "message" => "This card was previously removed for security reasons and cannot be reused. Please add your card details again to continue."
                ], JSON_PRETTY_PRINT);
                http_response_code(500);
                exit;
            } else if ($payment_method_status_id == 2) {
                $stripePaymentMethod->detach();

                // ✅ Prepare data to insert into database
                $paymentMethodDefaultStatusInfo = [
                    "is_default" => "false",
                    "updated_at" => date("Y-m-d H:i:s")
                ];

                // ✅ Prepare data to insert into database
                $paymentMethodAttachDetachStatusData = [
                    "status_type_id" => $payment_method_status_id,
                    "updated_at" => date("Y-m-d H:i:s")
                ];

                // Fetch auction details from the database
                $paymentMethodResult = $this->UserModel->update_bidder_payment_method_status_data($payment_method_id, $paymentMethodDefaultStatusInfo, $paymentMethodAttachDetachStatusData);


                // Return the response
                if ($paymentMethodResult) {

                    echo json_encode([
                        "status" => "success",
                        "message" => "Your payment method has been detached successfully.",
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
            }
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }



    public function invoicePaymentBidderFromStripeById()
    {
        header('Content-Type: application/json');

        // --- Validate request type ---
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403);
            exit;
        }

        // --- Validate session ---
        if (!$this->session->userdata('auction_user_session_data')) {
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in."
            ], JSON_PRETTY_PRINT);
            http_response_code(401);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId     = $userSessionData['user_id'];
        $email      = $userSessionData['email'];
        $userRoleId = $userSessionData['role_id'];

        if ($userRoleId != 2) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // --- Parse and validate input ---
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        if (!isset($json_input['invoice_id']) || !isset($json_input['membership'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required parameters: 'invoice_id' and 'membership' are required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $invoice_id = (int)$json_input['invoice_id'];
        if ($invoice_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid invoice_id. It must be a positive integer."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // --- Validate membership structure ---
        $membership = $json_input['membership'];
        $expectedMembershipKeys = ['hasMembership', 'commissionRate', 'invoiceAmount', 'totalPayable', 'taxRate'];
        foreach ($expectedMembershipKeys as $key) {
            if (!array_key_exists($key, $membership)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Missing key in 'membership' object: '$key' is required."
                ], JSON_PRETTY_PRINT);
                http_response_code(400);
                exit;
            }
        }

        // Normalize
        $hasMembership  = (bool)$membership['hasMembership'];
        $commissionRate = (float)$membership['commissionRate'];
        $invoiceAmount  = (float)$membership['invoiceAmount'];
        $totalPayable   = (float)$membership['totalPayable'];
        $taxRate        = (float)$membership['taxRate'];

        if ($invoiceAmount <= 0 || $totalPayable <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid numeric values: invoiceAmount and totalPayable must be greater than 0."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // --- Load models ---
        $this->load->model('UserModel');
        $this->load->model('InvoiceModel');

        $userStripeData = $this->UserModel->getUserStripeIdById($userId);
        if (!$userStripeData || empty($userStripeData['stripe_customer_id'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status'  => "error",
                    'message' => "No Stripe customer record found for this user. The user may not have any membership yet."
                ], JSON_PRETTY_PRINT));
            return;
        }

        $invoiceData = $this->InvoiceModel->getInvoiceById($invoice_id);
        if (!$invoiceData) {
            echo json_encode([
                "status" => "error",
                "message" => "Invoice not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        $auctionData = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoice_id);
        if (!$auctionData) {
            echo json_encode([
                "status" => "error",
                "message" => "Auction not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }


        $auctionId = $auctionData['auction_id'];


        /*$this->load->model('AuctionModel');

        $auctionConfirmByBidderResult = $this->AuctionModel->hasAuctionByBidderConfirm($auctionId, $userId);


        if (!$auctionConfirmByBidderResult) {
            http_response_code(409);
            echo json_encode([
                "status"  => "error",
                "message" => "You must confirm the auction before proceeding with payment."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $this->load->model('StorageFacilityModel');

        // 1️⃣ Get Storage Facility by Auction ID
        $storageFacility = $this->StorageFacilityModel->getStorageFacilityInformationByAuctionId($auctionId);
        if (!$storageFacility || empty($storageFacility['storage_facility_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "No storage facility found for the given auction."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            return;
        }



        $storageFacilityId = $storageFacility['storage_facility_id'];



        // 2️⃣ Get Company by Storage Facility ID
        $company = $this->StorageFacilityModel->getCompanyIdByStorageFacilityById($storageFacilityId);
        if (!$company || empty($company['company_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "No company found for the provided storage facility."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            return;
        }

        $companyId = $company['company_id'];

        $this->load->model('CompanyModel');

        // 3️⃣ Get User by Company ID
        $user = $this->CompanyModel->getUserInformationByCompanyId($companyId);
        if (!$user || empty($user['user_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "No user found for the given company."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            return;
        }


        $storageFacilityUserId = $user['user_id'];


        $auctionConfirmByStorageFacilityResult = $this->AuctionModel->hasAuctionByStorageFacilityConfirm($auctionId, $storageFacilityUserId);


        if (!$auctionConfirmByStorageFacilityResult) {
            http_response_code(409);
            echo json_encode([
                "status"  => "error",
                "message" => "The storage facility (Owner of the auction unit) must confirm the auction before proceeding with payment."
            ], JSON_PRETTY_PRINT);
            exit;
        }*/

        try {
            $stripeCustomerId = $userStripeData['stripe_customer_id'];
            $customer = \Stripe\Customer::retrieve($stripeCustomerId);

            if (isset($customer->deleted) && $customer->deleted) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(410)
                    ->set_output(json_encode([
                        'status'  => "error",
                        'message' => "The Stripe customer record for this user has been deleted. Please re-subscribe or contact support."
                    ], JSON_PRETTY_PRINT));
                return;
            }

            if (empty($customer->invoice_settings->default_payment_method)) {
                throw new Exception("Customer has no default payment method.");
            }

            // 🟢 Check for active subscription
            $subscriptions = \Stripe\Subscription::all([
                'customer' => $stripeCustomerId,
                'status'   => 'active',
                'limit'    => 1
            ]);

            $hasActiveSubscription = !empty($subscriptions->data);

            // ✅ Default commission rate from admin settings
            $this->load->model('AdminSettingModel');
            $defaultCommissionRate = $this->AdminSettingModel->get_commission_value(1, "percentage");
            $effectiveCommissionRate = $defaultCommissionRate; // fallback default

            // 🧩 If user has an active membership subscription
            if ($hasActiveSubscription && $hasMembership) {
                try {
                    // Get the first active subscription
                    $activeSubscription = $subscriptions->data[0];
                    $item = $activeSubscription->items->data[0];

                    // Retrieve the related Stripe product
                    $product = \Stripe\Product::retrieve($item->price->product);

                    // ✅ Pull commission rate from product metadata
                    if (isset($product->metadata->commission_rate) && is_numeric($product->metadata->commission_rate)) {
                        $effectiveCommissionRate = floatval($product->metadata->commission_rate);
                    } else {
                        $effectiveCommissionRate = $defaultCommissionRate;
                    }
                } catch (Exception $e) {
                    // Fallback gracefully if Stripe metadata retrieval fails
                    log_message('error', 'Stripe Product metadata fetch failed: ' . $e->getMessage());
                    $effectiveCommissionRate = $defaultCommissionRate;
                }
            } else {
                // No active membership — use default admin setting
                $effectiveCommissionRate = $defaultCommissionRate;
            }

            $invoiceNumber = $invoiceData['id'];
            $invoiceAmount = $invoiceData['total_amount']; // Convert to cents;
            $invoiceCurrency = strtolower($invoiceData['currency']); // Always lowercase like 'usd'

            // $taxRate = 10; // 10% is a percentage

            // 🧾 Calculate total amount
            $commissionAmount = ($invoiceAmount  * $effectiveCommissionRate) / 100;
            $taxAmount = $commissionAmount / 10;
            $totalAmount      = $invoiceAmount  + $commissionAmount;

            // Convert to cents for Stripe
            $stripeAmount = round($totalAmount * 100);

            $defaultPaymentMethodId = $customer->invoice_settings->default_payment_method;
            $invoiceCurrency = strtolower($invoiceData['currency'] ?? 'aud');

            // 1️⃣ Create Stripe PaymentIntent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $stripeAmount,
                'currency' => $invoiceCurrency,
                'customer' => $stripeCustomerId,
                'payment_method' => $defaultPaymentMethodId,
                'confirm' => true,
                'off_session' => true,
                'description' => 'Payment of the Invoice #' . $invoice_id,
                'metadata' => [
                    'auction_id' => $auctionId,
                    'invoice_id' => $invoiceNumber,
                    'user_id' => $userId,
                    'commission_rate' => $effectiveCommissionRate,
                    'has_membership'   => $hasMembership ? 'true' : 'false',
                    'subscription_active' => $hasActiveSubscription ? 'true' : 'false'
                ]
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                throw new Exception("Payment failed. Status: " . $paymentIntent->status);
            }

            // ✅ Record payment in DB
            $invoicePaymentByStripeData = [
                "invoice_id"         => $invoiceNumber,
                "stripe_invoice_id"  => $paymentIntent->id,
                "invoice_amount"         => $invoiceAmount,
                "commission_rate"    => $effectiveCommissionRate,
                "commission_amount"  => $commissionAmount,
                "tax_rate"           => $taxRate,
                "tax_amount"         => $taxAmount,
                "total_paid"         => $totalAmount,
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s")
            ];

            $invoicePaymentResult = $this->InvoiceModel->insert_invoice_with_stripe($invoicePaymentByStripeData);

            if ($invoicePaymentResult) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Invoice has been paid successfully.",
                    "data" => [
                        "payment_intent_id" => $paymentIntent->id,
                        "commission_rate"   => $effectiveCommissionRate,
                        "commission_amount" => round($commissionAmount, 2),
                        "tax_amount"        => round($taxAmount, 2),
                        "total_paid"        => round($totalAmount, 2),
                        "currency"          => strtoupper($invoiceCurrency),
                        "status"            => $paymentIntent->status
                    ]
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
                exit;
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Something went wrong. We couldn't process the invoice payment."
                ], JSON_PRETTY_PRINT);
                http_response_code(500);
                exit;
            }
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'General error: ' . $e->getMessage()
                ]))
                ->set_status_header(500);
        }
    }


    public function addStripeBankAccount()
    {
        $input = $this->getInput(['stripe_customer_id', 'bank_token']);
        try {
            $bankAccount = Customer::createSource($input['stripe_customer_id'], [
                'source' => $input['bank_token']
            ]);
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Bank account added successfully.',
                'result' => $bankAccount
            ]);
        } catch (ApiErrorException $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function updateStripeBankAccount()
    {
        $input = $this->getInput(['stripe_customer_id', 'bank_account_id']);
        try {
            $updated = Customer::updateSource($input['stripe_customer_id'], $input['bank_account_id'], [
                'metadata' => ['updated_by' => $this->session->userdata('auction_user_session_data')['user_id']]
            ]);
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Bank account updated successfully.',
                'result' => $updated
            ]);
        } catch (ApiErrorException $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function deactivateStripeBankAccount()
    {
        $input = $this->getInput(['stripe_customer_id', 'bank_account_id']);
        try {
            $deleted = Customer::deleteSource($input['stripe_customer_id'], $input['bank_account_id']);
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Bank account removed successfully.',
                'result' => $deleted
            ]);
        } catch (ApiErrorException $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function getBankAccountByCustomer()
    {
        $input = $this->getInput(['stripe_customer_id']);
        try {
            $customer = Customer::retrieve($input['stripe_customer_id']);
            $bankAccounts = array_filter($customer->sources->data, function ($source) {
                return $source->object === 'bank_account';
            });
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Bank accounts retrieved successfully.',
                'result' => array_values($bankAccounts)
            ]);
        } catch (ApiErrorException $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function defaultPaymentMethod()
    {
        $input = $this->getInput(['stripe_customer_id', 'payment_method_id']);
        try {
            $customer = Customer::update($input['stripe_customer_id'], [
                'invoice_settings' => [
                    'default_payment_method' => $input['payment_method_id']
                ]
            ]);
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Default payment method set successfully.',
                'result' => $customer
            ]);
        } catch (ApiErrorException $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function defaultBankAccount()
    {
        $input = $this->getInput(['stripe_customer_id', 'bank_account_id']);
        try {
            $bankAccount = Customer::updateSource($input['stripe_customer_id'], $input['bank_account_id'], [
                'default_for_currency' => true
            ]);
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Default bank account set successfully.',
                'result' => $bankAccount
            ]);
        } catch (ApiErrorException $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function addBankByStorageFacilityUserFromStripeById()
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

        $customer_id = isset($json_input['customer_id']) ? trim($json_input['customer_id']) : null;

        if (empty($customer_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing customer ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $payment_method_id = isset($json_input['payment_method_id']) ? trim($json_input['payment_method_id']) : null;

        if (empty($customer_id) || empty($payment_method_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing bank payment_method_id."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        try {


            // Attach bank account to customer
            /*$bankAccount = \Stripe\Customer::createSource($customer_id, [
                'source' => $token,
            ]);*/

            // 1. Retrieve the PaymentMethod
            $paymentMethod = \Stripe\PaymentMethod::retrieve($payment_method_id);

            // 2. Attach to customer
            $paymentMethod->attach([
                'customer' => $customer_id,
            ]);

            // 3. Optionally set as default for invoices/subscriptions
            \Stripe\Customer::update($customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $payment_method_id,
                ],
            ]);

            // ✅ Prepare data to insert into database
            $bankAccountData = [
                "user_id"     => $userId,
                "stripe_customer_id"   => $customer_id,
                "stripe_bank_account_id"    => $payment_method_id,
                "is_default"        => "false",
                "status_type_id "     => 1,
                "created_at"               => date("Y-m-d H:i:s"),
                "updated_at"               => date("Y-m-d H:i:s")
            ];

            // Load Auction Model
            $this->load->model('BankModel');

            // Fetch auction details from the database
            $bankAccountResult = $this->BankModel->insertStorageFacilityBankData($bankAccountData);

            // Return the response
            if ($bankAccountResult) {

                $bankAccountId = $bankAccountResult['bank_account_id'];
                // ✅ Prepare data to insert into database
                $bankAccountUserRelationshipData = [
                    "user_id"     => $userId,
                    "bank_id"   => $bankAccountId,
                    "status_type_id"     => 1,
                    "created_at"               => date("Y-m-d H:i:s"),
                    "updated_at"               => date("Y-m-d H:i:s")
                ];

                $bankUserRelationshipDataInserted = $this->BankModel->insertStorageFacilityBankUserRelationshipData($bankAccountUserRelationshipData);

                echo json_encode([
                    "status" => "success",
                    "message" => "Your bank account has been successfully added and secured.",
                    "data" => [
                        "bank_account_id" => $bankAccountResult['bank_account_id']
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
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }


    public function addConnectedAccountByStorageFacilityUserFromStripeById()
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
        $firstName = $userSessionData['first_name'];
        $lastName = $userSessionData['last_name'];
        $fullName =  $firstName . " " . $lastName;
        $email = $userSessionData['email'];
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

        $customer_id = isset($json_input['customer_id']) ? trim($json_input['customer_id']) : null;

        if (empty($customer_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing customer ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load BankModel early to clean up old incomplete accounts
        $this->load->model('BankModel');

        // Delete old incomplete/pending bank accounts for this user (older than 1 hour)
        // This prevents confusion when user tries to add new account
        $this->db->where('user_id', $userId);
        $this->db->where('status_type_id', 2); // Pending status
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-1 hour')));
        $deletedCount = $this->db->delete('tbl_bank');
        
        if ($deletedCount > 0) {
            log_message('info', "Deleted {$deletedCount} old incomplete bank account(s) for user {$userId}");
        }

        try {

            // 1️⃣ Create a connected account (Express type)
            $now = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
            $uniqueLabel = "User{$userId}_" . strtoupper(substr(md5(uniqid()), 0, 6));

            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'AU',
                'email' => $email,
                'business_profile' => [
                    'name' => "{$fullName}",
                    'product_description' => "Payouts for auction sales via the Auction Platform.",
                    'support_email' => $email, // optional, helps Stripe support users
                    'support_url' => base_url(),
                    'url' => base_url(),
                    'mcc' => '7399', // optional (Business Services, Not Elsewhere Classified)
                ],
                'settings' => [
                    'payouts' => [
                        'schedule' => [
                            'interval' => 'manual', // You control payouts via transfers
                        ],
                    ],
                ],
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
                'tos_acceptance' => [
                    'service_agreement' => 'recipient',
                ],
                'metadata' => [
                    'user_id'       => $userId,
                    'account_label' => $uniqueLabel,
                    'created_at'    => $now->format('Y-m-d H:i:s'),
                    'timezone'      => $now->getTimezone()->getName(),
                    'source'        => base_url(),
                    'facility_status'  => 'unassigned',
                    'facility_ids'     => '',
                    'facility_names'   => '',
                    'facility_count'   => 0,
                    'facility_updated' => $now->format('Y-m-d H:i:s'),
                ],
            ]);


            // 2️⃣ Create Account Session for Embedded Onboarding
            try {
                $accountSession = \Stripe\AccountSession::create([
                    'account' => $account->id,
                    'components' => [
                        'account_onboarding' => [
                            'enabled' => true,
                            'features' => [
                                'external_account_collection' => true,
                            ],
                        ],
                    ],
                ]);

                $clientSecret = $accountSession->client_secret;
            } catch (\Exception $e) {
                // Fallback or specific error handling
                log_message('error', 'Failed to create Account Session: ' . $e->getMessage());
                throw $e;
            }

            // ✅ Prepare data to insert into database
            $bankAccountData = [
                "user_id"     => $userId,
                "stripe_customer_id"   => $customer_id,
                "stripe_bank_account_id"    => $account->id,
                "is_default"        => "false",
                "status_type_id"     => 2, // Pending/Inactive initially
                "created_at"               => date("Y-m-d H:i:s"),
                "updated_at"               => date("Y-m-d H:i:s")
            ];

            // Load Auction Model
            $this->load->model('BankModel');

            // Fetch auction details from the database
            $bankAccountResult = $this->BankModel->insertStorageFacilityBankData($bankAccountData);

            // Return the response
            if ($bankAccountResult) {

                $bankAccountId = $bankAccountResult['bank_account_id'];
                // ✅ Prepare data to insert into database
                $bankAccountUserRelationshipData = [
                    "user_id"     => $userId,
                    "bank_id"   => $bankAccountId,
                    "status_type_id"     => 2,
                    "created_at"               => date("Y-m-d H:i:s"),
                    "updated_at"               => date("Y-m-d H:i:s")
                ];

                $bankUserRelationshipDataInserted = $this->BankModel->insertStorageFacilityBankUserRelationshipData($bankAccountUserRelationshipData);


                if ($bankUserRelationshipDataInserted) {


                    $this->load->model('UserModel');
                    $user = $this->UserModel->get_users_information_by_id($userId);

                    if ($user) {
                        /*$this->load->model('EmailQueueModel');

                        $scheduleTime = date('Y-m-d H:i:s');
                        
                        // NOTE: Email sending logic might need adjustment as we don't have a static URL anymore. 
                        // For now, we can skip queuing the email with the link, or send a generic "Finish setup" email that leads to the dashboard.
                        
                        // Add email to queue
                        $this->EmailQueueModel->add([
                            'to_email'      => $user->email,
                            'subject'       => "Your Payment Account Is Pending - Please Complete Verification",
                            'template'      => "connected_account_inactive_verification_email",
                            'name'          => $user->first_name . " " . $user->last_name,
                            'user_id'  =>  $userId,
                            'bank_id'    => $bankAccountId,
                            'onboarding_url'    => base_url('bank/setup'), // Start point
                            'scheduled_at'  => $scheduleTime,
                            'status'        => 'pending',
                            'error_message'        => '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $this->generateConnectedAccountInActiveEmailToBackgroundSchedule($bankAccountId);*/
                        
                        $webhookData = [
                            'payout_method'       => [
                                "id"   => $bankAccountId,
                                "stripe_customer_id"   => $customer_id,
                                "stripe_payout_account_id"    => $account->id,
                                "is_default"        => "false",
                                "user_id"     => $userId,
                            ]
                        ];

                        // ✅ Send webhook and get result including payload
                        $webhookResponse = send_webhook_event('payout_method.bank_account_added', 'payout_method', $webhookData);

                        echo json_encode([
                            "status" => "success",
                            "message" => "Connected account created. Ready for embedded onboarding.",
                            "data" => [
                                "bank_account_id" => $bankAccountResult['bank_account_id'],
                                "account_id"     => $account->id,
                                "client_secret"  => $clientSecret
                            ]
                        ], JSON_PRETTY_PRINT);
                        http_response_code(200);
                        exit;
                    } else {
                        http_response_code(500);
                        echo json_encode([
                            "status" => "error",
                            "message" => "Connected account was created, but we could not find the user account information needed to complete the setup. Please try again later."
                        ], JSON_PRETTY_PRINT);
                        exit;
                    }
                } else {
                    http_response_code(500);
                    echo json_encode([
                        "status" => "error",
                        "message" => "Connected account was created, but we couldn't complete the setup. Please try again later."
                    ], JSON_PRETTY_PRINT);
                    exit;
                }
            } else {

                http_response_code(500);
                echo json_encode([
                    "status" => "error",
                    "message" => "Connected account was created, but we couldn't save it in the system. Please contact support."
                ], JSON_PRETTY_PRINT);
                exit;
            }
        } catch (ApiErrorException $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage()]))
                ->set_status_header(200);
        } catch (Exception $e) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'General error: ' . $e->getMessage()]))
                ->set_status_header(200);
        }
    }

    public function createConnectedAccountSession()
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

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in."
            ], JSON_PRETTY_PRINT);
            http_response_code(401);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        $account_id = $json_input['account_id'] ?? null;
        $create_new = $json_input['create_new'] ?? false;

        try {
            $this->load->model('UserModel');
            
            // 1. If ID not provided, try to find existing one for user
            if (!$account_id) {
                // Assuming 'stripe_connect_account_id' is the column in users table
                $user = $this->UserModel->get_user_by_id($userId);
                if (!empty($user['stripe_connect_account_id'])) {
                    $account_id = $user['stripe_connect_account_id'];
                }
            }

            // 2. If still no ID and we are allowed to create one
            if (!$account_id && $create_new) {
                $stripeAccount = \Stripe\Account::create([
                    'type' => 'express',
                    'country' => 'AU',
                    'email' => $userSessionData['email'],
                    'capabilities' => [
                        'transfers' => ['requested' => true],
                    ],
                    'tos_acceptance' => [
                        'service_agreement' => 'recipient',
                    ],
                    'business_type' => 'company', // or individual, maybe let them pick later or default
                ]);
                
                $account_id = $stripeAccount->id;

                // Save to DB
                $this->UserModel->update_user($userId, ['stripe_connect_account_id' => $account_id]);
            }

            if (!$account_id) {
                 echo json_encode(["status" => "error", "message" => "No account ID found or created."]);
                 http_response_code(400);
                 exit;
            }

            $accountSession = \Stripe\AccountSession::create([
                'account' => $account_id,
                'components' => [
                    'account_onboarding' => [
                        'enabled' => true,
                        'features' => [
                            'external_account_collection' => true,
                        ],
                    ],
                ],
            ]);

            echo json_encode([
                "status" => "success",
                "client_secret" => $accountSession->client_secret,
                "account_id" => $account_id // Return this so frontend can store it if needed
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to create account session: " . $e->getMessage()
            ]);
            http_response_code(500);
        }
    }

    // ✅ SUCCESS CALLBACK
    public function successConnectedAccountByStorageFacility($accountId)
    {
        try {
            // 🔒 Validate input
            if (empty($accountId) || !preg_match('/^acct_[A-Za-z0-9]+$/', $accountId)) {
                show_error('Invalid or missing Stripe Account ID.', 400);
                return;
            }
            // 🧾 Retrieve account
            $account = \Stripe\Account::retrieve($accountId);
            if (!$account) {
                show_error('Stripe account not found.', 404);
                return;
            }

            // Prepare data for view
            $data = [
                'business_name' => $account->business_profile->name ?? 'Business Account',
                'account_email' => $account->email ?? 'N/A',
                'account_type'  => ucfirst($account->type ?? 'Express'),
                'details_submitted' => $account->details_submitted,
                'charges_enabled'   => $account->charges_enabled,
                'payouts_enabled'   => $account->payouts_enabled,
                'account_id' => $accountId,
            ];

            // Load the bank model
            $this->load->model('BankModel');

            // Fetch bank details from database
            $accountConenctedData = $this->BankModel->getBankAccountByAccountConenctedId($accountId);

            if ($accountConenctedData) {

                $bankId = $accountConenctedData['id'];
                $userId = $accountConenctedData['user_id'];

                $accountConnectedStatusTypeData = [
                    'status_type_id' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $accountConnectedDataUpdated = $this->BankModel->updateAccountConenctedStatusTypeData($bankId, $accountConnectedStatusTypeData);

                if ($accountConnectedDataUpdated) {


                    $this->load->model('UserModel');
                    $user = $this->UserModel->get_users_information_by_id($userId);

                    if ($user) {
                        $this->load->model('EmailQueueModel');

                        $scheduleTime = date('Y-m-d H:i:s');
                        // Add email to queue
                        $this->EmailQueueModel->add([
                            'to_email'      => $user->email,
                            'subject'       => "Your Payment Account Is Verified - You are Ready to Receive Payouts",
                            'template'      => "connected_account_active_verification_email",
                            'name'          => $user->first_name . " " . $user->last_name,
                            'user_id'  =>  $userId,
                            'bank_id'    => $bankId,
                            'scheduled_at'  => $scheduleTime,
                            'status'        => 'pending',
                            'error_message'        => '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $this->generateConnectedAccountActiveEmailToBackgroundSchedule($bankId);

                        // ✅ Load view

                        $scripts = [
                            base_url('assets/js/hello_bar.js'),
                        ];

                        $dataScript['scripts'] = $scripts;

                        $this->load->view('layout/head');
                        $this->load->view('dashboard/storage_facility/stripe/onboarding_success', $data);
                        $this->load->view('layout/scripts', $dataScript);
                    } else {

                        $scripts = [
                            base_url('assets/js/hello_bar.js'),
                        ];

                        $dataScript['scripts'] = $scripts;

                        $this->load->view('layout/head');

                        $this->load->view('dashboard/storage_facility/stripe/onboarding_error', [
                            'title'   => 'Information Not Found',
                            'message' => 'We could not find the user account information needed to complete the update. Please reconnect your Stripe account or contact support for assistance.',
                        ]);

                        $this->load->view('layout/scripts', $dataScript);
                    }
                } else {

                    $scripts = [
                        base_url('assets/js/hello_bar.js'),
                    ];

                    $dataScript['scripts'] = $scripts;

                    $this->load->view('layout/head');

                    $this->load->view('dashboard/storage_facility/stripe/onboarding_error', [
                        'title' => 'Update Account Error',
                        'message' => 'We encountered an issue while updating your connected account. Please try again in a few minutes or contact support if the problem persists.',
                    ]);

                    $this->load->view('layout/scripts', $dataScript);
                }
            } else {

                $scripts = [
                    base_url('assets/js/hello_bar.js'),
                ];

                $dataScript['scripts'] = $scripts;

                $this->load->view('layout/head');

                $this->load->view('dashboard/storage_facility/stripe/onboarding_error', [
                    'title' => 'Onboarding Account Error',
                    'message' => 'We couldn’t find your Stripe account record. Please contact support or try reconnecting your Stripe account.'
                ]);

                $this->load->view('layout/scripts', $dataScript);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            log_message('error', 'Stripe API error (success): ' . $e->getMessage());

            $scripts = [
                base_url('assets/js/hello_bar.js'),
            ];

            $dataScript['scripts'] = $scripts;

            $this->load->view('layout/head');


            $this->load->view('dashboard/storage_facility/stripe/onboarding_error', [
                'title' => 'Stripe API Error',
                'message' => htmlspecialchars($e->getMessage()),
            ]);

            $this->load->view('layout/scripts', $dataScript);
        } catch (Exception $e) {
            log_message('error', 'Unexpected error (success): ' . $e->getMessage());

            $scripts = [
                base_url('assets/js/hello_bar.js'),
            ];

            $dataScript['scripts'] = $scripts;

            $this->load->view('layout/head');


            $this->load->view('dashboard/storage_facility/stripe/onboarding_error', [
                'title' => 'Unexpected Error',
                'message' => 'An unexpected error occurred. Please try again later.',
            ]);

            $this->load->view('layout/scripts', $dataScript);
        }
    }

    public function generateConnectedAccountInActiveEmailToBackgroundSchedule($bankId)
    {

        // Step 1: Get current time + 5 minutes in Brisbane
        $endDateTime = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
        $endDateTime->modify('+1 minutes');
        $endTimestamp = $endDateTime->getTimestamp();

        // Step 2: Determine proper PHP path
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

            // Format time/date for `at`
            $time = date('H:i', $endTimestamp);         // e.g., 19:00
            $date = date('Y-m-d', $endTimestamp);       // e.g., 2025-08-03

            // Step 4: Build and run command
            $cliCommand = "\"{$phpPath}\" \"{$indexPhpPath}\" Email process_connected_account_inactive_email_by_id {$bankId} {$endTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";
        }


        exec($scheduleCmd, $output, $resultCode);

        if ($resultCode === 0) {
            // Step 5: Log message
            log_message('info', "Account Connected (Inactive) for Bank ID {$bankId} will be generated in 3 minutes (Brisbane time: {$endDateTime->format('Y-m-d H:i:s')}, timestamp: {$endTimestamp}).");
            log_message('debug', "CLI command executed: {$cliCommand}");
        } else {
            log_message('error', "Failed to schedule account connected (Inactive) for bank ID {$bankId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }

    public function generateConnectedAccountActiveEmailToBackgroundSchedule($bankId)
    {

        // Step 1: Get current time + 5 minutes in Brisbane
        $endDateTime = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
        $endDateTime->modify('+3 minutes');
        $endTimestamp = $endDateTime->getTimestamp();

        // Step 2: Determine proper PHP path
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

            // Format time/date for `at`
            $time = date('H:i', $endTimestamp);         // e.g., 19:00
            $date = date('Y-m-d', $endTimestamp);       // e.g., 2025-08-03

            // Step 4: Build and run command
            $cliCommand = "\"{$phpPath}\" \"{$indexPhpPath}\" Email process_connected_account_active_email_by_id {$bankId} {$endTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";
        }


        exec($scheduleCmd, $output, $resultCode);

        if ($resultCode === 0) {
            // Step 5: Log message
            log_message('info', "Account Connected (active) for Bank ID {$bankId} will be generated in 3 minutes (Brisbane time: {$endDateTime->format('Y-m-d H:i:s')}, timestamp: {$endTimestamp}).");
            log_message('debug', "CLI command executed: {$cliCommand}");
        } else {
            log_message('error', "Failed to schedule account connected (active) for bank ID {$bankId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }

    // ⚠️ REAUTH CALLBACK
    public function reauthConnectedAccountByStorageFacility($accountId)
    {
        try {
            // 🔒 Validate ID
            if (empty($accountId) || !preg_match('/^acct_[A-Za-z0-9]+$/', $accountId)) {
                show_error('Invalid Stripe Account ID.', 400);
                return;
            }

            // 🧾 Verify account
            $account = \Stripe\Account::retrieve($accountId);
            if (!$account) {
                show_error('Stripe account not found.', 404);
                return;
            }

            // 🔗 Create new onboarding link
            $accountLink = \Stripe\AccountLink::create([
                'account'     => $accountId,
                'refresh_url' => base_url("stripe/connected-account/reauth/{$accountId}"),
                'return_url'  => base_url("stripe/connected-account/success/{$accountId}"),
                'type'        => 'account_onboarding',
            ]);

            $scripts = [
                base_url('assets/js/hello_bar.js'),
            ];

            $dataScript['scripts'] = $scripts;

            $this->load->view('layout/head');


            // ✅ Load redirect view
            $this->load->view('dashboard/storage_facility/stripe/onboarding_redirect', [
                'onboarding_url' => $accountLink->url
            ]);

            $this->load->view('layout/scripts', $dataScript);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            log_message('error', 'Stripe API error (reauth): ' . $e->getMessage());

            $scripts = [
                base_url('assets/js/hello_bar.js'),
            ];

            $dataScript['scripts'] = $scripts;

            $this->load->view('layout/head');


            $this->load->view('dashboard/storage_facility/stripe/onboarding_error', [
                'title' => 'Stripe API Error',
                'message' => htmlspecialchars($e->getMessage()),
            ]);

            $this->load->view('layout/scripts', $dataScript);
        } catch (Exception $e) {
            log_message('error', 'Unexpected error (reauth): ' . $e->getMessage());

            $scripts = [
                base_url('assets/js/hello_bar.js'),
            ];

            $dataScript['scripts'] = $scripts;

            $this->load->view('layout/head');


            $this->load->view('dashboard/storage_facility/stripe/onboarding_error', [
                'title' => 'Unexpected Error',
                'message' => 'An unexpected error occurred while resuming onboarding.',
            ]);

            $this->load->view('layout/scripts', $dataScript);
        }
    }

    public function checkout_entries($package_id)
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data) {
            redirect('login');
        }

        $user_id = $session_data['user_id'];
        $this->load->model('CoinModel');
        $this->load->model('UserModel');
        $this->load->model('VaultModel');

        $package = $this->CoinModel->get_cash_package_by_id($package_id);
        if (!$package) {
            show_error('Package not found');
        }

        $user = $this->UserModel->get_user_by_id($user_id);
        $user_tier = strtolower($user['subscription_tier'] ?? 'none');

        // Apply dynamic tier-based discounts (Shark: 50%, Whale: 75% as per client request)
        $price_cents = (int)$package['price_cents'];
        if ($user_tier === 'shark') {
            $price_cents = (int)($price_cents * 0.50);
        } elseif ($user_tier === 'whale') {
            $price_cents = (int)($price_cents * 0.25);
        }

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'aud',
                        'product_data' => [
                            'name' => $package['title'] . " (Vault Entries)",
                            'description' => ($package['entry_count'] + ($package['bonus_entries'] ?? 0)) . " Entries for the Vault",
                        ],
                        'unit_amount' => $price_cents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => site_url('vault?success=true'),
                'cancel_url' => site_url('vault?cancel=true'),
                'metadata' => [
                    'user_id' => $user_id,
                    'package_id' => $package_id,
                    'type' => 'vault_purchase'
                ]
            ]);

            redirect($session->url);
        } catch (Exception $e) {
            show_error('Stripe error: ' . $e->getMessage());
        }
    }
}

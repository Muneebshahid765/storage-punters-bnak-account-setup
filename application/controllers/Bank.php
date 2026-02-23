<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\BankAccount;
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

class Bank extends CI_Controller
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
    public function index()
    {

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            redirect(base_url());
            return;
        }

        // Get logged-in user ID and role
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url());
            return;
        }

        // Load the bank model
        $this->load->model('BankModel');

        // Check if bank exists for this user
        $bankData = $this->BankModel->getBankByUserId($userId); // you must implement this method

        if ($bankData) {
            // If bank exists, redirect to view page
            //redirect('bank/edit/' . $bankData['bank_id']);
            redirect('bank/setup/new');
        } else {
            // If bank does not exist, redirect to setup page
            redirect('bank/setup/new');
        }
    }


    public function bankDetailsViewByStorageFacilityUser($bankId)
    {

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        // Validate bank ID
        if (!$bankId || !is_numeric($bankId)) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        // Load the bank model
        $this->load->model('BankModel');

        // Fetch bank details from database
        $bankData = $this->BankModel->getBankById($bankId);

        if (!$bankData) {
            redirect(base_url()); // Redirect if bank is not found
            return;
        }

        // Fetch bank details from database
        $bankByIdWithUserId = $this->BankModel->getBankByIdWithUserId($bankId, $userId);

        if (!$bankByIdWithUserId) {
            redirect(base_url()); // Redirect if bank is not found
            return;
        }


        $data['scripts'] = [
            base_url('assets/js/ajax/dashboard/storage_facility/bank/view_bank_details_by_id.js'),

        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/bank/details');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }


    public function bankDetailsSetupAddByStorageFacilityUser()
    {
        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        /*
            // Load the bank model
            $this->load->model('BankModel');


            // Check if bank exists for this user
            $bankData = $this->BankModel->getBankByUserId($userId); // you must implement this method

            if ($bankData) {
                // If bank exists, redirect to view page
                redirect('bank/edit/' . $bankData['id']);

            }
        */

        $data['scripts'] = [
            base_url('assets/js/ajax/dashboard/storage_facility/bank/bank_account.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/bank/add_bank');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function getAccountsForFacility()
    {
        header('Content-Type: application/json');

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied"]);
            exit;
        }

        $this->load->model('BankModel');
        $accounts = $this->BankModel->getBankAccountsByUserId($userId);

        // Sync bank data from Stripe if BSB is missing
        foreach ($accounts as &$account) {
            if (empty($account['bsb']) && !empty($account['stripe_bank_account_id'])) {
                $stripeAccountId = $account['stripe_bank_account_id'];
                try {
                    $stripeAccount = \Stripe\Account::retrieve($stripeAccountId);
                    if (!empty($stripeAccount->external_accounts->data)) {
                        $bankAccount = $stripeAccount->external_accounts->data[0];
                        
                        $updateData = [
                            'bsb' => $bankAccount->routing_number ?? '',
                            'account_number' => $bankAccount->last4 ? '****' . $bankAccount->last4 : '',
                            'bank_institution' => $bankAccount->bank_name ?? '',
                            'account_name' => $bankAccount->account_holder_name ?? $account['account_name'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        
                        $this->BankModel->updateBankAccount($account['id'], $updateData);
                        
                        // Update local array for response
                        $account['bsb'] = $updateData['bsb'];
                        $account['account_number'] = $updateData['account_number'];
                        $account['bank_institution'] = $updateData['bank_institution'];
                        $account['account_name'] = $updateData['account_name'];
                    }
                } catch (Exception $e) {
                    log_message('error', 'Failed to sync bank data from Stripe: ' . $e->getMessage());
                }
            }
        }

        echo json_encode([
            "status" => "success",
            "data" => $accounts ?: []
        ]);
    }

    public function addAccountForFacility()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Invalid request method"]);
            exit;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];
        $email = $userSessionData['email'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied"]);
            exit;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        $accountName = $json_input['account_name'] ?? '';
        $bsb = $json_input['bsb'] ?? '';
        $accountNumber = $json_input['account_number'] ?? '';
        $bankInstitution = $json_input['bank_institution'] ?? '';
        $locationIds = $json_input['location_ids'] ?? [];

        if (!$accountName || !$bsb || !$accountNumber) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit;
        }

        try {
            $cleanBsb = preg_replace('/[^0-9]/', '', $bsb);
            if (strlen($cleanBsb) !== 6) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "BSB must be 6 digits." ]);
                exit;
            }

            // Check for duplicate bank account
            if ($this->BankModel->isDuplicateBankAccount($userId, $cleanBsb, $accountNumber)) {
                http_response_code(409);
                echo json_encode([
                    "status" => "error", 
                    "message" => "This bank account (BSB: {$cleanBsb}, Account: {$accountNumber}) is already linked to your profile. Please use a different account."
                ]);
                exit;
            }

            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'AU',
                'email' => $email,
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
                'tos_acceptance' => [
                    'service_agreement' => 'recipient',
                ],
                'external_account' => [
                    'object' => 'bank_account',
                    'country' => 'AU',
                    'currency' => 'aud',
                    'account_holder_name' => $accountName,
                    'account_holder_type' => 'individual',
                    'routing_number' => $cleanBsb,
                    'account_number' => $accountNumber,
                ],
                'metadata' => [
                    'user_id' => $userId,
                ],
            ]);

            $this->load->model('BankModel');
            $bankData = [
                'user_id' => $userId,
                'stripe_customer_id' => '',
                'stripe_bank_account_id' => $account->id,
                'account_name' => $accountName,
                'bsb' => $cleanBsb,
                'account_number' => $accountNumber,
                'bank_institution' => $bankInstitution,
                'is_default' => 'false',
                'status_type_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $result = $this->BankModel->insertStorageFacilityBankData($bankData);

            if ($result && isset($result['bank_account_id'])) {
                $bankAccountId = $result['bank_account_id'];
                
                $relationData = [
                    'user_id' => $userId,
                    'bank_id' => $bankAccountId,
                    'status_type_id' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->BankModel->insertStorageFacilityBankUserRelationshipData($relationData);

                // Link locations to bank account if provided
                if (!empty($locationIds) && is_array($locationIds)) {
                    foreach ($locationIds as $facilityId) {
                        if (!empty($facilityId)) {
                            $this->BankModel->linkBankToFacility($bankAccountId, (int)$facilityId);
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

                            \Stripe\Account::update($account->id, ['metadata' => $metadata]);
                            log_message('info', "Stripe metadata updated for bank_id {$bankAccountId}: " . json_encode($metadata));
                        }
                    } catch (Exception $stripeEx) {
                        log_message('error', 'Stripe metadata update failed during account creation: ' . $stripeEx->getMessage());
                    }
                }
            }

            echo json_encode([
                "status" => "success",
                "message" => "Bank account added successfully!"
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Stripe API error: " . $e->getMessage()
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Bank account error: " . $e->getMessage()
            ]);
        }
    }

    public function setDefaultAccountForFacility()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Invalid request method"]);
            return;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            return;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied"]);
            return;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        $bankId = isset($json_input['bank_id']) ? intval($json_input['bank_id']) : 0;

        if ($bankId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid bank account selected."]);
            return;
        }

        $this->load->model('BankModel');
        $bank = $this->BankModel->getBankAccountByBankId($bankId);

        if (!$bank || (int)$bank['user_id'] !== (int)$userId) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Bank account not found."]);
            return;
        }

        if ($this->BankModel->setDefaultBankForUser($userId, $bankId)) {
            echo json_encode(["status" => "success", "message" => "Default bank account updated."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Unable to update default bank account."]);
        }
    }

    public function detachAccountForFacility()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Invalid request method"]);
            return;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            return;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied"]);
            return;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        $bankId = isset($json_input['bank_id']) ? intval($json_input['bank_id']) : 0;

        if ($bankId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid bank account selected."]);
            return;
        }

        $this->load->model('BankModel');
        $bank = $this->BankModel->getBankAccountByBankId($bankId);

        if (!$bank || (int)$bank['user_id'] !== (int)$userId) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Bank account not found."]);
            return;
        }

        try {
            // Update Stripe metadata if account exists
            if (!empty($bank['stripe_bank_account_id'])) {
                try {
                    \Stripe\Account::update($bank['stripe_bank_account_id'], [
                        'metadata' => [
                            'facility_status'  => 'detached',
                            'facility_updated' => date('Y-m-d H:i:s'),
                        ]
                    ]);
                    log_message('info', "Stripe account {$bank['stripe_bank_account_id']} marked as detached");
                } catch (\Stripe\Exception\ApiErrorException $stripeErr) {
                    // Log but don't fail - local DB update is more important
                    log_message('error', "Stripe update failed during detach: " . $stripeErr->getMessage());
                }
            }

            // Deactivate in local database
            $deactivated = $this->BankModel->deactivateBankAccount($bankId);
            
            if (!$deactivated) {
                throw new Exception("Failed to deactivate bank account in database");
            }
            
            log_message('info', "Bank account {$bankId} deactivated for user {$userId}");
            
            // Check if user has any other active bank accounts
            $remainingAccounts = $this->BankModel->getBankAccountsByUserId($userId);
            
            // If no active accounts remain, clear the user's stripe_connect_account_id
            // so that next time they add a bank, a fresh Stripe Connect account is created
            if (empty($remainingAccounts)) {
                $this->load->model('UserModel');
                $this->UserModel->update_user($userId, ['stripe_connect_account_id' => null]);
                log_message('info', "Cleared stripe_connect_account_id for user {$userId} - no active banks remain");
            }

            echo json_encode([
                "status" => "success", 
                "message" => "Bank account detached successfully.",
                "debug" => [
                    "bank_id" => $bankId,
                    "remaining_active_accounts" => count($remainingAccounts)
                ]
            ]);
        } catch (Exception $e) {
            log_message('error', "Detach failed for bank {$bankId}: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function getBankAccountStorageFacilityById()
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


        // Get bank ID and bid amount from JSON input
        $bankId = isset($json_input['bankId']) ? (int)$json_input['bankId'] : 0;

        // Validate bank ID and bid amount
        if ($bankId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing bank ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // Load Auction Model
        $this->load->model('BankModel');

        // Fetch bidder profile details from the database
        $bankData = $this->BankModel->getBankAccountByBankId($bankId);

        // If user profile not found, return error response
        if (!$bankData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No bank data is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }


        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch bidder profile details from the database
        $facilityBankData = $this->UserModel->getBankAccountAllStorageFacilityById($bankId);

        // If user profile not found, return error response
        if (!$facilityBankData) {
            http_response_code(200); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No facility data is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Facility data has been retrieved successfully.",
            "data" => $facilityBankData
        ], JSON_PRETTY_PRINT);
    }

    public function updateBankAccountFacilitiesById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to continue."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user session
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userRoleId = $userSessionData['role_id'];

        // Check permissions (example: admin or manager roles)
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "You do not have permission to update facilities."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Decode JSON input
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $bankId   = isset($json_input['bank_id']) ? (int)$json_input['bank_id'] : 0;
        $added    = isset($json_input['added']) && is_array($json_input['added']) ? $json_input['added'] : [];
        $deleted  = isset($json_input['deleted']) && is_array($json_input['deleted']) ? $json_input['deleted'] : [];

        if ($bankId <= 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing bank ID."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Load models
        $this->load->model('BankModel');

        // Fetch auction details from the database
        $bankData = $this->BankModel->getBankAccountByBankId($bankId);

        // If auction not found, return error response
        if (!$bankData) {
            echo json_encode([
                "status" => "error",
                "message" => "Bank account not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        try {

            $stripe_bank_account_id = $bankData['stripe_bank_account_id'];

            // Use transaction to keep DB consistent
            $this->db->trans_begin();

            $addedFacilityIds = [];
            $deletedFacilityIds = [];

            // Insert new facilities
            if (!empty($added)) {
                foreach ($added as $facility) {
                    if (!empty($facility['facility_id'])) {
                        $this->BankModel->linkBankToFacility($bankId, (int)$facility['facility_id']);
                    }
                }
            }

            // Delete removed facilities
            if (!empty($deleted)) {
                foreach ($deleted as $facility) {
                    if (!empty($facility['facility_id'])) {
                        $this->BankModel->unlinkBankFromFacility($bankId, (int)$facility['facility_id']);
                    }
                }
            }

            // Commit or rollback
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                http_response_code(500);
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to update facilities. Please try again."
                ], JSON_PRETTY_PRINT);
                exit;
            } else {
                $this->db->trans_commit();

                // ✅ Update Stripe metadata with facility info
                if (!empty($stripe_bank_account_id)) {

                    $this->load->model('UserModel');

                    try {
                        // 🏦 Get all active linked facilities for this bank
                        $activeFacilities = $this->UserModel->getBankAccountAllStorageFacilityById($bankId);

                        // If none found, set unassigned metadata
                        if (empty($activeFacilities)) {
                            $metadata = [
                                'facility_status'   => 'unassigned',
                                'facility_ids'      => '',
                                'facility_names'    => '',
                                'facility_count'    => 0,
                                'facility_updated'  => date('Y-m-d H:i:s'),
                            ];

                            \Stripe\Account::update($stripe_bank_account_id, ['metadata' => $metadata]);
                            log_message('info', "Stripe metadata reset to unassigned for bank_id {$bankId}");
                        } else {
                            // Extract fields safely
                            $facilityIds   = array_column($activeFacilities, 'storage_facility_id');
                            $facilityNames = array_column($activeFacilities, 'storage_facility_name');

                            // 🧩 Prepare metadata
                            $metadata = [
                                'facility_status'   => 'assigned',
                                'facility_ids'      => implode(',', $facilityIds),
                                'facility_names'    => implode(', ', $facilityNames),
                                'facility_count'    => count($facilityIds),
                                'facility_updated'  => date('Y-m-d H:i:s'),
                            ];

                            // 🔄 Update Stripe metadata
                            \Stripe\Account::update($stripe_bank_account_id, ['metadata' => $metadata]);
                            log_message('info', "Stripe metadata updated for bank_id {$bankId}: " . json_encode($metadata));
                        }
                    } catch (Exception $stripeEx) {
                        log_message('error', 'Stripe metadata update failed: ' . $stripeEx->getMessage());
                        // continue – do not fail the main request
                    }
                }

                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Facilities updated successfully and Stripe metadata synced.",
                ], JSON_PRETTY_PRINT);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Error: " . $e->getMessage()
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }
}

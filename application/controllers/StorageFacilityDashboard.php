<?php
defined('BASEPATH') or exit('No direct script access allowed');

class StorageFacilityDashboard extends CI_Controller
{
    private $phoneVerified = true;

    public function __construct()
    {
        parent::__construct();

        // Check if the user session exists
        if (!$this->session->userdata('auction_user_session_data')) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }

        // Enforce phone verification only for facility owners (role_id 1)
        if ((int) $userRoleId === 1) {
            $this->phoneVerified = $this->checkPhoneVerification($userSessionData['user_id']);

            if (!$this->phoneVerified && !$this->input->is_ajax_request()) {
                $currentMethod = $this->router->fetch_method();
                if ($currentMethod !== 'storageFacilityDashboard') {
                    redirect(base_url('storage-facility/dashboard'));
                    exit;
                }
            }
        }
    }

    private function checkPhoneVerification($userId)
    {
        $user = $this->db->select('phone_verified')->where('id', $userId)->get('tbl_users')->row();

        if (!$user || !$user->phone_verified) {
            $this->session->set_userdata('phone_verify_redirect', current_url());
            $this->session->set_userdata('show_phone_verification', true);
            return false;
        }

        $this->session->unset_userdata('show_phone_verification');
        $this->session->unset_userdata('phone_verify_redirect');
        return true;
    }

    private function getUserPhone($userId)
    {
        $row = $this->db->select('l.phone')
            ->from('tbl_user_location_relationship ulr')
            ->join('tbl_locations l', 'l.id = ulr.location_id', 'left')
            ->where('ulr.user_id', $userId)
            ->limit(1)
            ->get()
            ->row_array();

        return $row['phone'] ?? '';
    }


    public function storageFacilityDashboard()
    {
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];

        // If phone not verified, show verification gate and stop here
        if (!$this->phoneVerified) {
            $data['user_phone'] = $this->getUserPhone($userId);
            $data['scripts'] = [base_url('assets/js/script.js')];

            $this->load->view('layout/head');
            $this->load->view('layout/header');
            $this->load->view('auth/phone_verification_gate', $data);
            $this->load->view('layout/footer');
            $this->load->view('layout/scripts', $data);
            return;
        }

        $this->load->model('AuctionModel');
        $this->load->model('UserModel');
        
        $data['dashboardStats'] = $this->AuctionModel->getDashboardStats($userId);
        $data['recoveryStats'] = $this->AuctionModel->getFinancialRecoveryStats($userId);
        $data['soldByLocation'] = $this->AuctionModel->getAuctionsSoldByLocation($userId);
        $data['user_phone'] = $this->getUserPhone($userId);

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            // base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_status.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_types.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_storage_facilities.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/auction.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/dashboard.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/index', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function storageFacilityAccount()
    {

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
         if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }


        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/account.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/account');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function storageFacilityStaffManage()
    {

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }

        // Load the Company model
        $this->load->model('CompanyModel');

        // Check if company exists for this user
        $companyData = $this->CompanyModel->getCompanyByUserId($userId); // you must implement this method

        if ($companyData == null) {
            // If company does not exist, redirect to setup page
            redirect('company/setup/new');
        }

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/staff/manage.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/staff/manage');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }


    public function storageFacilityManage()
    {

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }

        // Load the Company model
        $this->load->model('CompanyModel');

        // Check if company exists for this user
        $companyData = $this->CompanyModel->getCompanyByUserId($userId); // you must implement this method

        if ($companyData == null) {
            // If company does not exist, redirect to setup page
            redirect('company/setup/new');
        }

        // Pass company ID to view for location discovery
        $data['company_id'] = $companyData['company_id'];

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/storage_facility_location/view_storage_facility_all.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_companies.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/storage_facility_location/add_storage_facility.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/storage_facility_location/import_storage_facility.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/location_discovery.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/storage_facility_location/manage', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }



    public function storageFacilityAdd()
    {

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }

        // Load the Company model
        $this->load->model('CompanyModel');

        // Check if company exists for this user
        $companyData = $this->CompanyModel->getCompanyByUserId($userId); // you must implement this method

        if ($companyData == null) {
            // If company does not exist, redirect to setup page
            redirect('company/setup/new');
        }

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_companies.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/storage_facility_location/add_storage_facility.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/storage_facility_location/add_storage_facility');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function storageFacilityDetailsEditByStorageFacilityUser($storageFacilityId = null)
    {

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }


        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];


        // Validate auction ID
        if (!$storageFacilityId || !is_numeric($storageFacilityId)) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        // Load the Auction model
        $this->load->model('StorageFacilityModel');

        // Fetch auction details from database
        $storageFacilityData = $this->StorageFacilityModel->getStorageFacilityById($storageFacilityId);

        if (!$storageFacilityData) {
            redirect(base_url()); // Redirect if auction is not found
            return;
        }


        // Fetch auction details from database
        $companyDataByStorageFacilityData = $this->StorageFacilityModel->getCompanyIdByStorageFacilityById($storageFacilityId);

        if (!$companyDataByStorageFacilityData) {
            redirect(base_url()); // Redirect if auction is not found
            return;
        }

        $companyId =  $companyDataByStorageFacilityData['company_id'];

        // Load the Company model
        $this->load->model('CompanyModel');


        // Fetch company details from database
        $CompanyByIdWithUserId = $this->CompanyModel->getCompanyByIdWithUserId($companyId, $userId);

        if (!$CompanyByIdWithUserId) {
            redirect(base_url()); // Redirect if company is not found
            return;
        }


        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_companies.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/storage_facility_location/view_storage_facility_by_id.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/storage_facility_location/details');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function auctionManage()
    {
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }

        // Load the Company model
        $this->load->model('CompanyModel');

        // Check if company exists for this user
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);

        if ($companyData == null) {
            // If company does not exist, redirect to setup page
            redirect('company/setup/new');
        }

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/manage_auctions.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/auctions/manage');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function newListingAI()
    {
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url());
            return;
        }
        // Fetch Global Rules
        $this->load->model('CompanyModel');
        $companyData = $this->CompanyModel->getCompanyByUserId($userSessionData['user_id']);
        $globalRules = null;
        if ($companyData) {
            $companyId = $companyData['company_id'] ?? $companyData['id'];
            $globalRules = $this->CompanyModel->getGlobalAuctionRules($companyId);
        }
        $data['global_rules'] = $globalRules;

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/new_listing_ai.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/new_listing_ai', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function subscription()
    {
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in (Super Admin or Facility Owner)
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url());
            return;
        }

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/subscription.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/subscription');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function globalAuctionRules()
    {
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userRoleId = $userSessionData['role_id'];
        $userId = $userSessionData['user_id'];

        // Check if user is logged in (Super Admin or Facility Owner)
        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url());
            return;
        }

        // Load the Company model
        $this->load->model('CompanyModel');

        // Check if company exists for this user
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);

        if ($companyData == null) {
            // If company does not exist, redirect to setup page
            redirect('company/setup/new');
        }

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            // Assuming this JS file exists or needs to be created. 
            // Based on other methods, it follows a pattern.
            base_url('assets/js/ajax/dashboard/storage_facility/global_auction_rules.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/global_auction_rules');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function getGlobalAuctionRules()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only GET requests are allowed."
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

        // Check login
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in."
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
                "message" => "Unauthorized access."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $this->load->model('CompanyModel');
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);

        if (!$companyData) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Company not found."]);
            exit;
        }

        $rules = $this->CompanyModel->getGlobalAuctionRules($companyData['id']);

        echo json_encode([
            "status" => "success",
            "data" => $rules
        ]);
    }

    public function saveGlobalAuctionRules()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             echo json_encode(["status" => "error", "message" => "Invalid request method."]);
             http_response_code(405);
             exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
             echo json_encode(["status" => "error", "message" => "Unauthorized request."]);
             http_response_code(403);
             exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        
        $this->load->model('CompanyModel');
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);

        if (!$companyData) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Company not found."]);
            exit;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        
        // Prepare data for saving
        // Note: You should validate fields here based on your requirements
        $data = [
            'estimate_best_photo' => isset($json_input['estimate_best_photo']) ? 1 : 0,
            'starting_price_percentage' => (int)($json_input['starting_price_percentage'] ?? 0),
            'display_debt_value' => isset($json_input['display_debt_value']) ? 1 : 0,
            'default_auction_duration' => (int)($json_input['default_auction_duration'] ?? 7),
            'start_immediately' => isset($json_input['start_immediately']) ? 1 : 0,
            'cleaning_deposit_amount' => (float)($json_input['cleaning_deposit_amount'] ?? 0),
            'has_no_deposit' => isset($json_input['has_no_deposit']) ? 1 : 0,
            'default_terms_conditions' => trim($json_input['default_terms_conditions'] ?? ''),
            'accept_cash' => isset($json_input['accept_cash']) ? 1 : 0,
            'accept_card' => isset($json_input['accept_card']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if record exists logic is handled inside model usually or here
        // The model method saveGlobalAuctionRules seemed to handle update/insert
        $result = $this->CompanyModel->saveGlobalAuctionRules($companyData['id'], $data);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Global auction rules saved successfully."]);
        } else {
             http_response_code(500);
             echo json_encode(["status" => "error", "message" => "Failed to save rules."]);
        }
    }

    public function notifications()
    {
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            redirect(base_url());
            return;
        }

        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/notifications.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/notifications');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function getNotificationPreferences()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
             http_response_code(405);
             echo json_encode(["status" => "error", "message" => "Method not allowed"]);
             exit;
        }

        if (!$this->input->is_ajax_request()) {
             http_response_code(403);
             echo json_encode(["status" => "error", "message" => "Unauthorized"]);
             exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        if (!$userSessionData) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Not logged in"]);
            exit;
        }

        $userId = $userSessionData['user_id'];
        
        $user = $this->db->get_where('tbl_users', ['id' => $userId])->row();
        
        if (!$user) {
             http_response_code(404);
             echo json_encode(["status" => "error", "message" => "User not found"]);
             exit;
        }

        $prefs = json_decode($user->notification_prefs, true);
        if (!$prefs) {
            $prefs = [];
        }

        // Defaults
        $defaults = [
            'audio_alert_enabled' => 1,
            'winning_auction_email' => 1,
            'winning_auction_sms' => 1,
            'outbid_auction_email' => 0,
            'outbid_auction_sms' => 0,
            'auction_closed_email' => 1,
            'auction_closed_sms' => 0
        ];

        $data = array_merge($defaults, $prefs);

        echo json_encode([
            "status" => "success",
            "data" => $data
        ]);
    }

    public function saveNotificationPreferences()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             http_response_code(405);
             echo json_encode(["status" => "error", "message" => "Method not allowed"]);
             exit;
        }

        if (!$this->input->is_ajax_request()) {
             http_response_code(403);
             echo json_encode(["status" => "error", "message" => "Unauthorized"]);
             exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        if (!$userSessionData) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Not logged in"]);
            exit;
        }

        $userId = $userSessionData['user_id'];
        
        $json_input = json_decode(file_get_contents('php://input'), true);

        // Fetch current prefs to merge
        $user = $this->db->get_where('tbl_users', ['id' => $userId])->row();
        $current_prefs = json_decode($user->notification_prefs, true);
        if (!$current_prefs) {
            $current_prefs = [];
        }

        // Update with new values
        $keys = [
            'audio_alert_enabled',
            'winning_auction_email',
            'winning_auction_sms',
            'outbid_auction_email',
            'outbid_auction_sms',
            'auction_closed_email',
            'auction_closed_sms'
        ];

        foreach ($keys as $key) {
            if (isset($json_input[$key])) {
                // Store as 1 or 0
                $current_prefs[$key] = $json_input[$key] ? 1 : 0;
            }
        }

        $this->db->where('id', $userId);
        $update = $this->db->update('tbl_users', ['notification_prefs' => json_encode($current_prefs)]);

        if ($update) {
            echo json_encode(["status" => "success", "message" => "Preferences saved"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Database error"]);
        }
    }
}

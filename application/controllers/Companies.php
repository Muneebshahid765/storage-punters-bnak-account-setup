<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Companies extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
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

        // Load the Company model
        $this->load->model('CompanyModel');

        // Check if company exists for this user
        $companyData = $this->CompanyModel->getCompanyByUserId($userId); // you must implement this method

        if ($companyData) {
            // If company exists, redirect to view page
            redirect('company/edit/' . $companyData['company_id']);
        } else {
            // If company does not exist, redirect to setup page
            redirect('company/setup/new');
        }
    }


    public function getAllCompaniesByUser()
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
        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getAllCompaniesByUser($userId);

        // If user profile not found, return error response
        if (!$companyData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company data is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Company data has been retrieved successfully.",
            "data" => $companyData
        ], JSON_PRETTY_PRINT);
    }

    public function companyDetailsViewByStorageFacilityUser($companyId)
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

        // Validate company ID
        if (!$companyId || !is_numeric($companyId)) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        // Load the Company model
        $this->load->model('CompanyModel');

        // Fetch company details from database
        $companyData = $this->CompanyModel->getCompanyById($companyId);

        if (!$companyData) {
            redirect(base_url()); // Redirect if company is not found
            return;
        }

        // Fetch company details from database
        $CompanyByIdWithUserId = $this->CompanyModel->getCompanyByIdWithUserId($companyId, $userId);

        if (!$CompanyByIdWithUserId) {
            redirect(base_url()); // Redirect if company is not found
            return;
        }


        $data['scripts'] = [
            base_url('assets/js/ajax/dashboard/storage_facility/company/view_company_details_by_id.js'),
        ];



        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/company/details');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }
    public function companyDetailsSetupAddByStorageFacilityUser()
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
        // Load the Company model
        $this->load->model('CompanyModel');

        // Check if company exists for this user
        $companyData = $this->CompanyModel->getCompanyByUserId($userId); // you must implement this method

        if ($companyData) {
            // If company exists, redirect to view page
            redirect('company/edit/' . $companyData['id']);
        }

        $data['scripts'] = [
            base_url('assets/js/ajax/dashboard/storage_facility/company/add_company_details.js'),

        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/company/add_company');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function addCompanyByStorageFacilityUser()
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

        // ✅ Required fields list
        $required_fields = [
            'company_name',
            'abn',
            'company_website',
            'company_email',
            'company_street_address',
            'company_city',
            'company_zip',
            'company_state',
            'company_country',
            'company_cell_phone'

        ];

        $missing_fields = [];

        // ✅ Validate required fields
        foreach ($required_fields as $field) {
            if (!isset($json_input[$field]) || trim((string)$json_input[$field]) === '') {
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


        // Extract only required fields from JSON input
        $companyData = [
            'name' => isset($json_input['company_name']) ? trim($json_input['company_name']) : null,
            'abn' => isset($json_input['abn']) ? trim($json_input['abn']) : null,
            'website' => isset($json_input['company_website']) ? trim($json_input['company_website']) : null,
            'email' => isset($json_input['company_email']) ? trim($json_input['company_email']) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];


        // Load Company Model
        $this->load->model('CompanyModel');


        $companyDataInserted = $this->CompanyModel->insertCompanyData($companyData);

        // Get company ID from JSON input
        $companyId = $companyDataInserted['company_id'];

        // Validate company ID
        if ($companyId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $companyLocationData = [
            'street_address' => isset($json_input['company_street_address']) ? trim($json_input['company_street_address']) : null,
            'apt_suite' => isset($json_input['company_apt_suite']) ? trim($json_input['company_apt_suite']) : null,
            'city' => isset($json_input['company_city']) ? trim($json_input['company_city']) : null,
            'postal_code' => isset($json_input['company_zip']) ? trim($json_input['company_zip']) : null,
            'state' => isset($json_input['company_state']) ? trim($json_input['company_state']) : null,
            'country' => isset($json_input['company_country']) ? trim($json_input['company_country']) : null,
            'phone' => isset($json_input['company_cell_phone']) ? trim($json_input['company_cell_phone']) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];


        $companyLocationDataInserted = $this->CompanyModel->insertCompanyLocationData($companyLocationData);

        // Get company location ID from JSON input
        $companyLocationId = $companyLocationDataInserted['company_location_id'];

        // Validate company ID
        if ($companyLocationId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company location ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $companyLocationRelationshipData = [
            'company_id' => $companyId,
            'location_id' => $companyLocationId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $companyStatusData = [
            'company_id' => $companyId,
            'status_type_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $companyUserRelationshipData = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'status_type_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];


        $companyLocationRelationshipDataInserted = $this->CompanyModel->insertCompanyLocationRelationshipData($companyLocationRelationshipData);
        $companyStatusDataInserted = $this->CompanyModel->insertCompanyStatusData($companyStatusData);
        $companyUserRelationshipDataInserted = $this->CompanyModel->insertCompanyUserRelationshipData($companyUserRelationshipData);


        // Check if at least one update was successful
        if ($companyDataInserted || $companyLocationData || $companyLocationRelationshipDataInserted || $companyStatusDataInserted ||  $companyUserRelationshipDataInserted) {

            $webhookData = [
                'company'       => [
                    'id' => $companyId,
                    'user_id' => $userId,
                    'name' => isset($json_input['company_name']) ? trim($json_input['company_name']) : null,
                    'abn' => isset($json_input['abn']) ? trim($json_input['abn']) : null,
                    'website' => isset($json_input['company_website']) ? trim($json_input['company_website']) : null,
                    'email' => isset($json_input['company_email']) ? trim($json_input['company_email']) : null,
                    'street_address' => isset($json_input['company_street_address']) ? trim($json_input['company_street_address']) : null,
                    'apt_suite' => isset($json_input['company_apt_suite']) ? trim($json_input['company_apt_suite']) : null,
                    'city' => isset($json_input['company_city']) ? trim($json_input['company_city']) : null,
                    'postal_code' => isset($json_input['company_zip']) ? trim($json_input['company_zip']) : null,
                    'state' => isset($json_input['company_state']) ? trim($json_input['company_state']) : null,
                    'country' => isset($json_input['company_country']) ? trim($json_input['company_country']) : null,
                    'phone' => isset($json_input['company_cell_phone']) ? trim($json_input['company_cell_phone']) : null
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('company.created', 'company', $webhookData);

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Company informations has been added successfully.",
                "data" => [
                    "company_id" => $companyId,
                    "user_id" => $userId,
                    "user_role_id" => $userId
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to add company details."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }


    public function updateCompanyByStorageFacilityUser()
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

        // Get company ID from JSON input
        $companyId = isset($json_input['companyId']) ? (int)$json_input['companyId'] : 0;

        // Validate company ID
        if ($companyId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Company Model
        $this->load->model('CompanyModel');

        // Check if company exists for this user
        // Check if company exists for this user
        $companyLocationData = $this->CompanyModel->getCompanyLocationIdByCompanyId($companyId); // you must implement this method

        // Get company location ID from JSON input
        $companyLocationId = $companyLocationData['location_id'] ?? 0;

        // Validate company ID
        if ($companyLocationId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company location ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // ✅ Required fields list
        $required_fields = [
            'company_name',
            'abn',
            'company_website',
            'company_email',
            'company_street_address',
            'company_city',
            'company_zip',
            'company_state',
            'company_country',
            'company_cell_phone'

        ];
        $missing_fields = [];

        // ✅ Validate required fields
        foreach ($required_fields as $field) {
            if (!isset($json_input[$field]) || trim((string)$json_input[$field]) === '') {
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


        // Extract only required fields from JSON input
        $companyData = [
            'name' => isset($json_input['company_name']) ? trim($json_input['company_name']) : null,
            'abn' => isset($json_input['abn']) ? trim($json_input['abn']) : null,
            'website' => isset($json_input['company_website']) ? trim($json_input['company_website']) : null,
            'email' => isset($json_input['company_email']) ? trim($json_input['company_email']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];





        $companyLocationData = [
            'street_address' => isset($json_input['company_street_address']) ? trim($json_input['company_street_address']) : null,
            'apt_suite' => isset($json_input['company_apt_suite']) ? trim($json_input['company_apt_suite']) : null,
            'city' => isset($json_input['company_city']) ? trim($json_input['company_city']) : null,
            'postal_code' => isset($json_input['company_zip']) ? trim($json_input['company_zip']) : null,
            'state' => isset($json_input['company_state']) ? trim($json_input['company_state']) : null,
            'country' => isset($json_input['company_country']) ? trim($json_input['company_country']) : null,
            'phone' => isset($json_input['company_cell_phone']) ? trim($json_input['company_cell_phone']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $companyDataUpdated = $this->CompanyModel->updateCompanyData($companyId, $companyData);
        $companyLocationDataUpdated = $this->CompanyModel->updateCompanyLocationData($companyLocationId, $companyLocationData);


        // Check if at least one update was successful
        if ($companyDataUpdated && $companyLocationDataUpdated) {

            $webhookData = [
                'company'       => [
                    'id' => $companyId,
                    'user_id' => $userId,
                    'name' => isset($json_input['company_name']) ? trim($json_input['company_name']) : null,
                    'abn' => isset($json_input['abn']) ? trim($json_input['abn']) : null,
                    'website' => isset($json_input['company_website']) ? trim($json_input['company_website']) : null,
                    'email' => isset($json_input['company_email']) ? trim($json_input['company_email']) : null,
                    'street_address' => isset($json_input['company_street_address']) ? trim($json_input['company_street_address']) : null,
                    'apt_suite' => isset($json_input['company_apt_suite']) ? trim($json_input['company_apt_suite']) : null,
                    'city' => isset($json_input['company_city']) ? trim($json_input['company_city']) : null,
                    'postal_code' => isset($json_input['company_zip']) ? trim($json_input['company_zip']) : null,
                    'state' => isset($json_input['company_state']) ? trim($json_input['company_state']) : null,
                    'country' => isset($json_input['company_country']) ? trim($json_input['company_country']) : null,
                    'phone' => isset($json_input['company_cell_phone']) ? trim($json_input['company_cell_phone']) : null,
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('company.updated', 'company', $webhookData);


            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Company information has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update company details."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }

    public function getCompanyInformationById()
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

        // Get company ID from JSON input
        $companyId = isset($json_input['companyId']) ? (int)$json_input['companyId'] : 0;

        // Validate auction ID
        if ($companyId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getCompanyInformationById($companyId);

        // If user profile not found, return error response
        if (!$companyData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Your company data is retrieved successfully.",
            "data" => $companyData
        ], JSON_PRETTY_PRINT);
    }


    public function getCompanyDetailsById()
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

        // Get company ID from JSON input
        $companyId = isset($json_input['companyId']) ? (int)$json_input['companyId'] : 0;

        // Validate auction ID
        if ($companyId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getCompanyDetailsById($companyId);

        // If user profile not found, return error response
        if (!$companyData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Your company data is retrieved successfully.",
            "data" => $companyData
        ], JSON_PRETTY_PRINT);
    }

    /**
     * AI Location Discovery - Uses Gemini API to discover storage facility locations
     */
    public function discoverLocations()
    {
        header('Content-Type: application/json');

        // Validate request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized request']);
            exit;
        }

        // Check authentication
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions']);
            exit;
        }

        // Get company ID, manual list, and raw text from request
        $json_input = json_decode(file_get_contents('php://input'), true);
        $companyId = $json_input['company_id'] ?? 0;
        $manualList = $json_input['manual_list'] ?? null;
        $rawText = $json_input['raw_text'] ?? '';

        if ($companyId <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid company ID']);
            exit;
        }

        // Load company data
        $this->load->model('CompanyModel');
        $companyData = $this->CompanyModel->getCompanyById($companyId);

        if (!$companyData) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Company not found']);
            exit;
        }

        // Verify user owns this company
        $companyUserData = $this->CompanyModel->getCompanyByIdWithUserId($companyId, $userId);
        if (!$companyUserData) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }

        $companyName = $companyData['name'];

        // Prepare Gemini API call
        $api_key = 'AIzaSyBwvFjOw6fnI2evvJt3tKrQLdKqMNccUEM';
        // Use gemini-2.0-flash on v1beta which supports tools and response_mime_type
        $api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$api_key}";

        if ($manualList && is_array($manualList)) {
            $locationsContext = json_encode($manualList, JSON_PRETTY_PRINT);
            $prompt = "You are a data extraction assistant for a storage facility management system.
            
            Company Context:
            Name: {$companyData['name']}
            Website: " . ($companyData['website'] ?? 'Not provided') . "

            The user has provided a list of potential locations extracted from Google Business Profile:
            {$locationsContext}

            RAW TEXT SOURCE (for extra context):
            ---
            {$rawText}
            ---

            Your task is to:
            1. Verify each location exists and belongs to this company.
            2. Fetch the following missing details from the RAW TEXT or Google Search:
               - name: Exact business name
               - address: Full street address
               - phone: Phone number
               - website: Website URL (specific to this facility)
               - hours: Opening hours as a JSON object (keys: mon, tue, wed, thu, fri, sat, sun)
               - place_id: The specific Google Maps Place ID

            CRITICAL: Return results ONLY as a valid JSON array. Do not include any explanation or code blocks.";
        } else {
            $prompt = "Find ALL storage facility locations (warehouses, self-storage centers) owned or operated by '{$companyData['name']}' in Australia.
            
            For each location, you MUST extract:
            - name: Exact business name
            - address: Full street address
            - phone: Phone number
            - website: Website URL
            - hours: Opening hours as a JSON object (keys: mon, tue, wed, thu, fri, sat, sun)
            - place_id: The specific Google Maps Place ID

            CRITICAL: Return results ONLY as a valid JSON array. Do not include any explanation or code blocks.
            If NO locations are found, return an empty array: []";
        }

        log_message('debug', 'Gemini Request Prompt: ' . $prompt);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'tools' => [
                ['google_search' => (object)[]]
            ]
        ];

        // Call Gemini API
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            log_message('error', 'Gemini API Curl Error: ' . curl_error($ch));
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'AI Service Unavailable: ' . curl_error($ch)]);
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        // Parse response
        $result = json_decode($response, true);
        if (isset($result['error'])) {
            log_message('error', 'Gemini API Error: ' . print_r($result['error'], true));
            
            // Check for specific error codes
            $errorCode = $result['error']['code'] ?? 0;
            $errorMessage = $result['error']['message'] ?? 'Unknown error';
            
            // Handle quota exhaustion (429 error)
            if ($errorCode == 429 || stripos($errorMessage, 'resource exhausted') !== false || stripos($errorMessage, 'quota') !== false) {
                http_response_code(503); // Service Unavailable
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'AI Location Discovery is temporarily unavailable due to high demand. Please try again in a few minutes or add locations manually.'
                ]);
                exit;
            }
            
            // Handle other API errors
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'AI service error. Please try again later or add locations manually.']);
            exit;
        }

        $raw_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        log_message('debug', 'AI Location Discovery Raw Response: ' . $raw_text);
        
        // Remove markdown code block markers if present
        $clean_text = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw_text));
        
        // Find the first [ and last ] to extract the JSON array
        $first_bracket = strpos($clean_text, '[');
        $last_bracket = strrpos($clean_text, ']');
        
        if ($first_bracket !== false && $last_bracket !== false) {
            $json_text = substr($clean_text, $first_bracket, $last_bracket - $first_bracket + 1);
        } else {
            $json_text = $clean_text;
        }

        $locations = json_decode($json_text, true);

        // Fallback if parsing fails
        if ($locations === null) {
            log_message('error', 'AI Location Parsing Failed. Raw: ' . $raw_text . ' | JSON Error: ' . json_last_error_msg());
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Failed to parse AI response. Please ensure your pasted content contains valid location information.', 
                'raw' => $raw_text,
                'json_error' => json_last_error_msg()
            ]);
            exit;
        }

        // Ensure it's an array
        if (!is_array($locations)) {
            $locations = [];
        }

        // Return discovered locations
        echo json_encode([
            'status' => 'success',
            'message' => 'Locations discovered successfully',
            'data' => $locations
        ]);
    }

    /**
     * Batch Onboard Locations - Creates storage facilities from verified location data
     */
    public function onboardLocations()
    {
        header('Content-Type: application/json');

        // Validate request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized request']);
            exit;
        }

        // Check authentication
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions']);
            exit;
        }

        // Get input data
        $json_input = json_decode(file_get_contents('php://input'), true);
        $companyId = $json_input['company_id'] ?? 0;
        $locations = $json_input['locations'] ?? [];

        if ($companyId <= 0 || empty($locations)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid company ID or locations data']);
            exit;
        }

        // Load models
        $this->load->model('CompanyModel');
        $this->load->model('StorageFacilityModel');

        // Verify company ownership
        $companyUserData = $this->CompanyModel->getCompanyByIdWithUserId($companyId, $userId);
        if (!$companyUserData) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        // Process each location
        foreach ($locations as $location) {
            try {
                // Parse address components
                $addressParts = $this->parseAddress($location['address'] ?? '');
                
                // Create storage facility
                $storageFacilityData = [
                    'name' => $location['name'] ?? 'Unknown Location',
                    'abn' => '',
                    'website' => $location['website'] ?? '',
                    'description' => '',
                    'timezone' => 'Australia/Brisbane',
                    'contact_email' => '',
                    'additional_email' => '',
                    'office_hours' => json_encode($location['hours'] ?? []),
                    'payout_details' => '',
                    'youtube_video_url' => '',
                    'branding_logo' => '',
                    'notice_to_buyers' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $facilityResult = $this->StorageFacilityModel->insertStorageFacilityData($storageFacilityData);
                $facilityId = $facilityResult['storage_facility_id'];

                // Create location record
                $locationData = [
                    'street_address' => $addressParts['street'] ?? $location['address'],
                    'apt_suite' => '',
                    'city' => $addressParts['city'] ?? '',
                    'postal_code' => $addressParts['postal_code'] ?? '',
                    'state' => $addressParts['state'] ?? '',
                    'country' => $addressParts['country'] ?? 'Australia',
                    'phone' => $location['phone'] ?? '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $locationResult = $this->StorageFacilityModel->insertStorageFacilityLocationData($locationData);
                $locationId = $locationResult['storage_facility_location_id'];

                // Link facility to location
                $this->StorageFacilityModel->insertStorageFacilityLocationRelationshipData([
                    'storage_facility_id' => $facilityId,
                    'location_id' => $locationId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Link facility to company
                $this->StorageFacilityModel->insertStorageFacilityCompanyRelationshipData([
                    'storage_facility_id' => $facilityId,
                    'company_id' => $companyId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Set status to active
                $this->StorageFacilityModel->insertStorageFacilityStatusData([
                    'storage_facility_id' => $facilityId,
                    'status_type_id' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $successCount++;
            } catch (Exception $e) {
                $failedCount++;
                $errors[] = $location['name'] . ': ' . $e->getMessage();
                log_message('error', 'Location onboarding failed: ' . $e->getMessage());
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => "Onboarded {$successCount} location(s), {$failedCount} failed",
            'data' => [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors
            ]
        ]);
    }

    /**
     * Helper function to parse address string into components
     */
    private function parseAddress($address)
    {
        // Simple address parsing - can be enhanced
        $parts = [
            'street' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => 'Australia'
        ];

        // Extract postal code (Australian format)
        if (preg_match('/\b(\d{4})\b/', $address, $matches)) {
            $parts['postal_code'] = $matches[1];
        }

        // Extract state (Australian states)
        $states = ['NSW', 'VIC', 'QLD', 'SA', 'WA', 'TAS', 'NT', 'ACT'];
        foreach ($states as $state) {
            if (stripos($address, $state) !== false) {
                $parts['state'] = $state;
                break;
            }
        }

        // Simple city/street extraction
        $addressParts = explode(',', $address);
        if (count($addressParts) >= 2) {
            $parts['street'] = trim($addressParts[0]);
            $parts['city'] = trim($addressParts[count($addressParts) - 2]);
        } else {
            $parts['street'] = $address;
        }

        return $parts;
    }
}

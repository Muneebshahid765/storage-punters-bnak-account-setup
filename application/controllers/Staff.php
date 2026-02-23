<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }


    public function getAllStaffMember()
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
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);

        // If user profile not found, return error response
        if (!$companyData) {

            // Prepare the response data
            $data = [
                "draw" => (int) $this->input->post('draw'),
                "recordsTotal" => "0",
                "recordsFiltered" => "0",
                "data" => [],
            ];

            http_response_code(200); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company data is found.",
                "result" => $data
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $companyId = $companyData['company_id'];
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
        $this->load->model('StaffModel');

        $totalRecords = $this->StaffModel->countAllStaff();
        $filteredRecords = $this->StaffModel->countFilteredStaff($search, $companyId);
        $result = $this->StaffModel->getFilteredStaffById($start, $length, $search, $orderColumn, $orderDir, $companyId);

        // Prepare the response data
        $data = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $result,
        ];

        // Return success response with storage facilities data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Staff are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }


    public function addStaffMemberByStorageFacilityUser()
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
        $required_fields = ["firstname", "lastname", "email"];
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

        $email = strtolower($json_input["email"]);

        // ✅ Additional validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid email format."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $existingStaff = $this->UserModel->get_user_by_email_address($email);
        if ($existingStaff) {
            echo json_encode([
                "status" => "error",
                "message" => "This email is already in use. Please use a different one."
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        }

        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);

        // If user profile not found, return error response
        if (!$companyData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company data found. Please set up your company profile first."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $companyId = $companyData['company_id'];

        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch auction details from the database
        $existingUser = $this->UserModel->get_user_by_email_address($email);


        // If email found, return conflict error response
        if ($existingUser) {

            // ✅ If user exists, extract user_id
            $staffMemberId = $existingUser['user_id'] ?? null;

            if (!$staffMemberId) {
                echo json_encode([
                    "status" => "error",
                    "message" => "User record is missing an ID."
                ], JSON_PRETTY_PRINT);
                http_response_code(500);
                exit;
            }

            $existingUserRole = $this->UserModel->get_user_role_by_id($staffMemberId);


            if ($existingUserRole) {

                $staffMemberRoleId = $existingUserRole['role_id'] ?? null;

                if (!$staffMemberRoleId) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "User role is missing an ID."
                    ], JSON_PRETTY_PRINT);
                    http_response_code(500);
                    exit;
                }

                if ($staffMemberRoleId == 3) {

                    // Fetch auction details from the database
                    $hasAssociatedWithCompany = $this->UserModel->get_user_by_company_id($staffMemberId, $companyId);

                    if ($hasAssociatedWithCompany) {

                        echo json_encode([
                            "status" => "error",
                            "message" => "This email is already associated with a member of your team. Try inviting someone who isn’t already part of your organization."
                        ], JSON_PRETTY_PRINT);
                        http_response_code(409); // Conflict
                        exit;
                    } else {


                        // ✅ Prepare data to insert into database
                        $staffMemberCompanyData = [
                            "company_id"  => $companyId,
                            "user_id"   => $staffMemberId,
                            "status_type_id"   => 2,
                            "created_at"   => date("Y-m-d H:i:s"),
                            "updated_at"   => date("Y-m-d H:i:s")
                        ];

                        // Load Staff Model
                        $this->load->model('StaffModel');

                        $staffCompanyAddResult = $this->StaffModel->insert_staff_company_data($staffMemberCompanyData);

                        // Handle location assignments and manage_all flag
                        if (isset($json_input['location_ids']) && is_array($json_input['location_ids'])) {
                            $this->StaffModel->assignStaffLocations($staffMemberId, $json_input['location_ids']);
                        }
                        if (isset($json_input['manage_all'])) {
                            $this->StaffModel->updateStaffManageAllLocations($staffMemberId, $companyId, (int)$json_input['manage_all']);
                        }

                        // Handle granular permissions
                        $permissions = [
                            'can_manage_auctions' => isset($json_input['can_manage_auctions']) ? (int)$json_input['can_manage_auctions'] : 0,
                            'can_view_reports' => isset($json_input['can_view_reports']) ? (int)$json_input['can_view_reports'] : 0,
                            'can_handle_disputes' => isset($json_input['can_handle_disputes']) ? (int)$json_input['can_handle_disputes'] : 0,
                            'can_manage_users' => isset($json_input['can_manage_users']) ? (int)$json_input['can_manage_users'] : 0,
                            'updated_at' => date("Y-m-d H:i:s")
                        ];
                        $this->StaffModel->updateStaffPermissions($staffMemberId, $companyId, $permissions);

                        echo json_encode([
                            "status" => "success",
                            "message" => "Staff member has been successfully created.",
                            "data" => [
                                "staff_member_id" => $staffMemberId,
                                "company_id" => $companyId,
                                "email" => $email
                            ]
                        ], JSON_PRETTY_PRINT);
                        http_response_code(200);
                        exit;
                    }
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "This user already exists in the system with a different role. Please use a different email address or contact support if this is unexpected."
                    ], JSON_PRETTY_PRINT);
                    http_response_code(409); // Conflict
                    exit;
                }
            } else {

                echo json_encode([
                    "status" => "error",
                    "message" => "This user exists in the system but has no role assigned. Please contact support to resolve this issue before proceeding."
                ], JSON_PRETTY_PRINT);
                http_response_code(409); // Conflict
                exit;
            }
        } else {
            // Load Auction Model
            $this->load->model('StaffModel');


            // ✅ Prepare data to insert into database
            $staffMemberData = [
                "first_name" => ucwords(strtolower($json_input["firstname"])),
                "last_name" => ucwords(strtolower($json_input["lastname"])),
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];


            // Fetch auction details from the database
            $staffAddResult = $this->StaffModel->insert_staff_data($staffMemberData);


            // Return the response
            if ($staffAddResult) {

                // ✅ Prepare data to insert into database
                $staffMemberCredentialsData = [
                    "email"        => $email,
                    "password"        => "12345",
                    "user_id"   => $staffAddResult['staff_member_id'],
                    "created_at"   => date("Y-m-d H:i:s"),
                    "updated_at"   => date("Y-m-d H:i:s")
                ];

                $staffMemberRoleData = [
                    "user_id"   => $staffAddResult['staff_member_id'],
                    "role_id"    => 3,
                    "created_at"   => date("Y-m-d H:i:s"),
                    "updated_at"   => date("Y-m-d H:i:s")
                ];

                $staffMemberStatusData = [
                    "user_id"   => $staffAddResult['staff_member_id'],
                    "status_type_id"    => 2,
                    "created_at"   => date("Y-m-d H:i:s"),
                    "updated_at"   => date("Y-m-d H:i:s")
                ];

                // ✅ Prepare data to insert into database
                $staffMemberCompanyData = [
                    "company_id"        => $companyId,
                    "user_id"   => $staffAddResult['staff_member_id'],
                    "status_type_id"   => 2,
                    "created_at"   => date("Y-m-d H:i:s"),
                    "updated_at"   => date("Y-m-d H:i:s")
                ];

                $staffCredentialsAddResult = $this->StaffModel->insert_staff_credentials_data($staffMemberCredentialsData);
                $staffRoleAddResult = $this->StaffModel->insert_staff_role_data($staffMemberRoleData);
                $staffStatusAddResult = $this->StaffModel->insert_staff_status_data($staffMemberStatusData);
                $staffCompanyAddResult = $this->StaffModel->insert_staff_company_data($staffMemberCompanyData);

                // Handle location assignments and manage_all flag
                if (isset($json_input['location_ids']) && is_array($json_input['location_ids'])) {
                    $this->StaffModel->assignStaffLocations($staffAddResult['staff_member_id'], $json_input['location_ids']);
                }
                if (isset($json_input['manage_all'])) {
                    $this->StaffModel->updateStaffManageAllLocations($staffAddResult['staff_member_id'], $companyId, (int)$json_input['manage_all']);
                }

                // Handle granular permissions
                $permissions = [
                    'can_manage_auctions' => isset($json_input['can_manage_auctions']) ? (int)$json_input['can_manage_auctions'] : 0,
                    'can_view_reports' => isset($json_input['can_view_reports']) ? (int)$json_input['can_view_reports'] : 0,
                    'can_handle_disputes' => isset($json_input['can_handle_disputes']) ? (int)$json_input['can_handle_disputes'] : 0,
                    'can_manage_users' => isset($json_input['can_manage_users']) ? (int)$json_input['can_manage_users'] : 0,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ];
                $this->StaffModel->updateStaffPermissions($staffAddResult['staff_member_id'], $companyId, $permissions);

                echo json_encode([
                    "status" => "success",
                    "message" => "Staff member has been successfully created.",
                    "data" => [
                        "staff_member_id" => $staffAddResult['staff_member_id'],
                        "company_id" => $companyId,
                        "email" => $email
                    ]
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
                exit;
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Something went wrong. We encountered an issue while creating the staff member."
                ], JSON_PRETTY_PRINT);
                http_response_code(500); // Internal Server Error
                exit;
            }
        }
    }


    public function updateStaffMemberStatusByIdByStorageFacilityUser()
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


        // Get  staff member ID from JSON input
        $staffMemberId = isset($json_input['staff_member_id']) ? (int)$json_input['staff_member_id'] : 0;

        // Validate staff member ID
        if ($staffMemberId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing staff member ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // ✅ Check if `staff_member_status_name	` is passed (for update scenarios)
        $staff_member_status_name = isset($json_input['staff_member_status_name']) ? (string)$json_input['staff_member_status_name'] : null;


        // ✅ Handle case when `staff_member_status_name	` is NOT passed
        if ($staff_member_status_name === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: staff_member_status_name is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);

        // If user profile not found, return error response
        if (!$companyData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company data found. Please set up your company profile first."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $companyId = $companyData['company_id'];

        if ($staff_member_status_name == "Active") {
            $staff_member_status_id = 1;
        } else if ($staff_member_status_name == "Inactive") {
            $staff_member_status_id = 2;
        } else {
            $staff_member_status_id = 0;
        }

        // ✅ Handle case when `staff_member_status_id` is NOT passed
        if ($staff_member_status_id == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: staff_member_status_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $this->load->model('StaffModel');

        if ($staff_member_status_id == 1) {

            // ✅ Prepare data to insert into database
            $staffMemberCompanyData = [
                "company_id"  => $companyId,
                "user_id"   => $staffMemberId,
                "status_type_id"   => 1,
                "created_at"   => date("Y-m-d H:i:s"),
                "updated_at"   => date("Y-m-d H:i:s")
            ];

            $this->load->model('UserModel');
            // Fetch auction details from the database
            $hasAssociatedWithCompany = $this->UserModel->get_user_by_company_id($staffMemberId, $companyId);

            if ($hasAssociatedWithCompany) {
                $staffMemberStatusData = [
                    "status_type_id"   => 1,
                    "updated_at"   => date("Y-m-d H:i:s")
                ];


                $staffMemberStatusDataUpdated = $this->StaffModel->updateStaffMemberStatusData($staffMemberId, $companyId, $staffMemberStatusData);
            } else {
                $staffMemberStatusDataUpdated = $this->StaffModel->insert_staff_company_data($staffMemberCompanyData);
            }
        } else if ($staff_member_status_id == 2) {

            $staffMemberStatusData = [
                "status_type_id"   => 2,
                "updated_at"   => date("Y-m-d H:i:s")
            ];

            $staffMemberStatusDataUpdated = $this->StaffModel->updateStaffMemberStatusData($staffMemberId, $companyId, $staffMemberStatusData);
        }
        /*
        // Load User Model
        $this->load->model('StaffModel');

        $staffMemberStatusData = [
            'status_type_id' => $staff_member_status_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];*/



        // Check if at least one update was successful
        if ($staffMemberStatusDataUpdated) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Staff member status has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update staff member status."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }


    public function updateStaffMemberByStorageFacilityUser()
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
        $required_fields = ["firstname", "lastname"];
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

        // Get  staff member ID from JSON input
        $staffMemberId = isset($json_input['staff_member_id']) ? (int)$json_input['staff_member_id'] : 0;

        // Validate staff member ID
        if ($staffMemberId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing staff member ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        $staffMemberStatusDataUpdated = false;

        try {
            // Load User Model
            $this->load->model('StaffModel');
            $staffMemberData = [
                "first_name" => ucwords(strtolower($json_input["firstname"])),
                "last_name" => ucwords(strtolower($json_input["lastname"])),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $staffMemberStatusDataUpdated = $this->StaffModel->updateStaffMemberData($staffMemberId, $staffMemberData);

            // Fetch company details
            $this->load->model('CompanyModel');
            $companyData = $this->CompanyModel->getCompanyByUserId($userId);
            $companyId = 0;
            if (is_array($companyData)) {
                $companyId = (int)($companyData['company_id'] ?? ($companyData['id'] ?? 0));
            }

            if ($companyId > 0) {
                // Handle location assignments and manage_all flag
                if (isset($json_input['location_ids']) && is_array($json_input['location_ids'])) {
                    $this->StaffModel->assignStaffLocations($staffMemberId, $json_input['location_ids']);
                    $staffMemberStatusDataUpdated = true; // Mark as updated if locations changed
                }
                if (isset($json_input['manage_all'])) {
                    $this->StaffModel->updateStaffManageAllLocations($staffMemberId, $companyId, (int)$json_input['manage_all']);
                    $staffMemberStatusDataUpdated = true;
                }

                // Handle granular permissions
                $permissions = [
                    'can_manage_auctions' => isset($json_input['can_manage_auctions']) ? (int)$json_input['can_manage_auctions'] : 0,
                    'can_view_reports' => isset($json_input['can_view_reports']) ? (int)$json_input['can_view_reports'] : 0,
                    'can_handle_disputes' => isset($json_input['can_handle_disputes']) ? (int)$json_input['can_handle_disputes'] : 0,
                    'can_manage_users' => isset($json_input['can_manage_users']) ? (int)$json_input['can_manage_users'] : 0,
                    'updated_at' => date("Y-m-d H:i:s")
                ];
                $permOk = $this->StaffModel->updateStaffPermissions($staffMemberId, $companyId, $permissions);
                if (!$permOk) {
                    $dbError = $this->db->error();
                    throw new Exception('Permissions update failed: ' . ($dbError['message'] ?? 'Unknown DB error'));
                }
                $staffMemberStatusDataUpdated = true;
            }
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update staff member.",
                "error" => $e->getMessage()
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Check if at least one update was successful
        if ($staffMemberStatusDataUpdated) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Staff member has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update staff member."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }

    public function deleteStaffMemberByStorageFacilityUser()
    {
        header('Content-Type: application/json');

        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Forbidden"]);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        if (!$userSessionData || !in_array($userSessionData['role_id'], [1, 3])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        $staffMemberId = isset($json_input['staff_member_id']) ? (int)$json_input['staff_member_id'] : 0;

        if ($staffMemberId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid staff member ID."]);
            exit;
        }

        $this->load->model('StaffModel');
        $this->load->model('CompanyModel');
        $companyData = $this->CompanyModel->getCompanyByUserId($userSessionData['user_id']);
        
        if (!$companyData) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Company not found."]);
            exit;
        }

        if ($this->StaffModel->deleteStaffMember($staffMemberId, $companyData['company_id'])) {
            echo json_encode(["status" => "success", "message" => "Staff member deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete staff member."]);
        }
        exit;
    }

    public function getStaffAssignedLocationsByStaffId()
    {
        header('Content-Type: application/json');

        $userSessionData = $this->session->userdata('auction_user_session_data');
        if (!$userSessionData || !in_array($userSessionData['role_id'], [1, 3])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit;
        }

        $staffMemberId = $this->input->get('staff_member_id');
        if (!$staffMemberId) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing staff member ID."]);
            exit;
        }

        $this->load->model('StaffModel');
        $locations = $this->StaffModel->getStaffAssignedLocations($staffMemberId);

        echo json_encode([
            "status" => "success",
            "data" => $locations
        ]);
        exit;
    }
    public function updateStaffPermissions()
    {
        header('Content-Type: application/json');

        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Forbidden"]);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        if (!$userSessionData || !in_array($userSessionData['role_id'], [1, 3])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        $staffMemberId = isset($json_input['staff_member_id']) ? (int)$json_input['staff_member_id'] : 0;
        
        if ($staffMemberId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid staff member ID."]);
            exit;
        }

        $this->load->model('CompanyModel');
        $companyData = $this->CompanyModel->getCompanyByUserId($userSessionData['user_id']);
        if (!$companyData) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Company not found."]);
            exit;
        }
        $companyId = (int)($companyData['company_id'] ?? ($companyData['id'] ?? 0));
        if ($companyId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid company id."]);
            exit;
        }

        $permissions = [
            'can_manage_auctions' => isset($json_input['can_manage_auctions']) ? (int)$json_input['can_manage_auctions'] : 0,
            'can_view_reports'    => isset($json_input['can_view_reports'])    ? (int)$json_input['can_view_reports']    : 0,
            'can_handle_disputes' => isset($json_input['can_handle_disputes']) ? (int)$json_input['can_handle_disputes'] : 0,
            'can_manage_users'    => isset($json_input['can_manage_users'])    ? (int)$json_input['can_manage_users']    : 0,
            'updated_at'          => date('Y-m-d H:i:s')
        ];

        $this->load->model('StaffModel');
        $result = $this->StaffModel->updateStaffPermissions($staffMemberId, $companyId, $permissions);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Permissions updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update permissions."]);
        }
    }
    public function getStaffPermissionsByStaffId()
    {
        header('Content-Type: application/json');

        $userSessionData = $this->session->userdata('auction_user_session_data');
        if (!$userSessionData || !in_array($userSessionData['role_id'], [1, 3])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit;
        }

        $staffMemberId = $this->input->get('staff_member_id');
        if (!$staffMemberId) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing staff member ID."]);
            exit;
        }

        $this->load->model('CompanyModel');
        $companyData = $this->CompanyModel->getCompanyByUserId($userSessionData['user_id']);
        if (!$companyData) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Company profile not found."]);
            exit;
        }

        $companyId = (int)($companyData['company_id'] ?? ($companyData['id'] ?? 0));
        if ($companyId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid company id."]);
            exit;
        }

        $this->load->model('StaffModel');
        $permissions = $this->StaffModel->getStaffPermissions($staffMemberId, $companyId);

        if (empty($permissions) || !is_array($permissions)) {
            $permissions = [
                'can_manage_auctions' => 0,
                'can_view_reports' => 0,
                'can_handle_disputes' => 0,
                'can_manage_users' => 0
            ];
        } else {
            // Ensure keys exist for frontend
            $permissions = array_merge([
                'can_manage_auctions' => 0,
                'can_view_reports' => 0,
                'can_handle_disputes' => 0,
                'can_manage_users' => 0
            ], $permissions);
        }

        echo json_encode([
            "status" => "success",
            "data" => $permissions
        ]);
        exit;
    }
    public function run_migration_fix()
    {
        // Only allow admin or storage facility users
        $userSessionData = $this->session->userdata('auction_user_session_data');
        if (!$userSessionData || !in_array($userSessionData['role_id'], [1, 3])) {
            die("Unauthorized access.");
        }

        $sql = "ALTER TABLE `tbl_staff_permissions` MODIFY `storage_facility_id` INT(11) NULL;";
        
        if ($this->db->query($sql)) {
            echo "<h1>Success!</h1><p>The database table has been updated. You can now go back and save your staff member permissions.</p>";
        } else {
            echo "<h1>Error</h1><p>Failed to update the database. Error: " . $this->db->error()['message'] . "</p>";
        }
    }

    public function run_permissions_migration_fix()
    {
        // Only allow admin or storage facility users
        $userSessionData = $this->session->userdata('auction_user_session_data');
        if (!$userSessionData || !in_array($userSessionData['role_id'], [1, 3])) {
            die("Unauthorized access.");
        }

        $added = [];
        $skipped = [];
        $errors = [];

        $cols = [
            'can_manage_auctions' => "TINYINT(1) NOT NULL DEFAULT 0",
            'can_view_reports' => "TINYINT(1) NOT NULL DEFAULT 0",
            'can_handle_disputes' => "TINYINT(1) NOT NULL DEFAULT 0",
            'can_manage_users' => "TINYINT(1) NOT NULL DEFAULT 0"
        ];

        foreach ($cols as $col => $definition) {
            $exists = $this->db->query("SHOW COLUMNS FROM `tbl_staff_permissions` LIKE " . $this->db->escape($col))->num_rows() > 0;
            if ($exists) {
                $skipped[] = $col;
                continue;
            }

            $sql = "ALTER TABLE `tbl_staff_permissions` ADD COLUMN `{$col}` {$definition}";
            if ($this->db->query($sql)) {
                $added[] = $col;
            } else {
                $errors[] = $col . ': ' . ($this->db->error()['message'] ?? 'Unknown DB error');
            }
        }

        echo "<h1>Granular Permissions Migration</h1>";
        echo "<p><strong>Added:</strong> " . (empty($added) ? 'None' : implode(', ', $added)) . "</p>";
        echo "<p><strong>Already existed:</strong> " . (empty($skipped) ? 'None' : implode(', ', $skipped)) . "</p>";
        if (!empty($errors)) {
            echo "<h2>Errors</h2><pre>" . htmlspecialchars(implode("\n", $errors), ENT_QUOTES, 'UTF-8') . "</pre>";
        } else {
            echo "<p><strong>Status:</strong> Done. Ab aap staff permissions save kar sakte hain.</p>";
        }
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminSettings extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }
    public function getAdminCreditSettingsById()
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
        // Load AdminSettingModel
        $this->load->model('AdminSettingModel');

        // Fetch bidder profile details from the database
        // This will now use 'signup_bonus' as the type (harmonized)
        $adminCreditSettingsData = $this->AdminSettingModel->get_credit_value(1, "signup_bonus");

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Credit data is retrieved successfully.",
            "data" => array("credit_value" => $adminCreditSettingsData)
        ], JSON_PRETTY_PRINT);
    }


    public function getAdminCommissionSettingsById()
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
        // Load AdminSettingModel
        $this->load->model('AdminSettingModel');

        // Fetch bidder profile details from the database
        $adminCommissionSettingsData = $this->AdminSettingModel->get_commission_value(1, "percentage");

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Commission data is retrieved successfully.",
            "data" => array("commission_value" => $adminCommissionSettingsData)
        ], JSON_PRETTY_PRINT);
    }


    public function updateAdminCreditSettingsById()
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
        $creditData = [
            'credit_value' => isset($json_input['credit_value']) ? trim($json_input['credit_value']) : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];


        // Load Auction Model
        $this->load->model('AdminSettingModel');

        // Update profile, location, and password separately
        $creditUpdated = $this->AdminSettingModel->updateCreditSetting(1, $creditData);


        // Check if at least one update was successful
        if ($creditUpdated) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Credit value has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update credit value."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }


     public function updateAdminCommissionSettingsById()
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
        $commissionData = [
            'commission_value' => isset($json_input['commission_value']) ? trim($json_input['commission_value']) : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];


        // Load Auction Model
        $this->load->model('AdminSettingModel');

        // Update profile, location, and password separately
        $commissionUpdated = $this->AdminSettingModel->updateCommissionSetting(1, $commissionData);


        // Check if at least one update was successful
        if ($commissionUpdated) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Commission value has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update commission value."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }
}

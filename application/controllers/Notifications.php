<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notifications extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }

    public function getBidderNotificationById()
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

        // Load Notification Model
        $this->load->model('NotificationsModel');

        // Fetch Notifications for the logged-in user
        $userNotifications = $this->NotificationsModel->get_user_alert_details($userId);

        // Check if notifications exist
        if (!empty($userNotifications)) {
            echo json_encode([
                "status" => "success",
                "message" => "Notifications retrieved successfully.",
                "data" => $userNotifications
            ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No notifications found for this user."
            ], JSON_PRETTY_PRINT);
        }
    }

    public function updateBidderNotificationStatusById()
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

        // ✅ Check if `status_type_id` is passed (for update scenarios)
        $status_type_id = isset($json_input['status_type_id']) ? (int)$json_input['status_type_id'] : null;

        // ✅ Handle case when `payment_method_id` is NOT passed
        if ($status_type_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: status_type_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Check if `alert_setting_id` is passed (for update scenarios)
        $alert_setting_id = isset($json_input['alert_setting_id']) ? (int)$json_input['alert_setting_id'] : null;

        // ✅ Handle case when `payment_method_id` is NOT passed
        if ($alert_setting_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: alert_setting_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Notification Model
        $this->load->model('NotificationsModel');

        $updated_at = date('Y-m-d H:i:s');

        // Fetch auction details from the database
        $notificationsStatusResult = $this->NotificationsModel->update_notification_status($alert_setting_id, $status_type_id, $updated_at);

        // Return the response
        if ($notificationsStatusResult) {
            echo json_encode([
                "status" => "success",
                "message" => "Notification status updated.",
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
            exit;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Something went wrong. We couldn't update your notification status. Please try again later."
            ], JSON_PRETTY_PRINT);
            http_response_code(500); // Internal Server Error
            exit;
        }
    }
}

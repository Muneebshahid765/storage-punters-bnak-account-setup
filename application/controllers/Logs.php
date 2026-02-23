<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logs extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }

    public function invoicePaymentHistoryBidderFromStripeById()
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
        if (!in_array($userRoleId, [1, 2, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }


        $invoiceId = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
        // Validate auction ID and bid amount
        if ($invoiceId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing invoice ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
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
        $this->load->model('LogsModel');

        $totalRecords = $this->LogsModel->countAllInvoiceHistoryByBidderUser($invoiceId);
        $filteredRecords = $this->LogsModel->countFilteredInvoiceHistoryByBidderUser($search, $invoiceId);
        $result = $this->LogsModel->getFilteredInvoiceHistoryByBidderUser($start, $length, $search, $orderColumn, $orderDir, $invoiceId);

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
            "message" => "Invoice #$invoiceId logs are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }



    public function auctionConfirmWinnerLogsById()
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
        if (!in_array($userRoleId, [1, 2, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }


        $winnerId = isset($_POST['winner_id']) ? (int)$_POST['winner_id'] : 0;
        // Validate auction ID and bid amount
        if ($winnerId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing winner ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
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
        $this->load->model('LogsModel');

        $totalRecords = $this->LogsModel->countAllAuctionWinnerConfirmLogsById($winnerId);
        $filteredRecords = $this->LogsModel->countFilteredAuctionWinnerConfirmLogsById($search, $winnerId);
        $result = $this->LogsModel->getFilteredAuctionWinnerConfirmLogsById($start, $length, $search, $orderColumn, $orderDir, $winnerId);

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
            "message" => "Winner #$winnerId logs are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }
}

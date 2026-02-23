<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }

    public function signIn()
    {

        // Check if user is logged in
        if ($this->session->userdata('auction_user_session_data')) {
            $userSessionData = $this->session->userdata('auction_user_session_data');
            redirect($this->_get_role_redirect_url($userSessionData['role_id']));
            return;
        }

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('auth/sign_in');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    public function registerBidder()
    {

        // Check if user is logged in
        if ($this->session->userdata('auction_user_session_data')) {
            $userSessionData = $this->session->userdata('auction_user_session_data');
            redirect($this->_get_role_redirect_url($userSessionData['role_id']));
            return;
        }

        $data['scripts'] = [
            base_url('assets/js/ajax/registrations/bidder.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('auth/register_bidder');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function registerStorageFacility()
    {
        // Check if user is logged in
        if ($this->session->userdata('auction_user_session_data')) {
            $userSessionData = $this->session->userdata('auction_user_session_data');
            redirect($this->_get_role_redirect_url($userSessionData['role_id']));
            return;
        }

        $data['scripts'] = [
            base_url('assets/js/ajax/registrations/storage_facility.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('auth/register_storage_facility');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function checkUserLogin()
    {
        header('Content-Type: application/json');


        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            echo json_encode([
                "status" => false,
                "message" => "User is not logged in."
            ], JSON_PRETTY_PRINT);
            http_response_code(401); // Unauthorized
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');

        $userId = $userSessionData['user_id'];
        $userName = $userSessionData['first_name'];
        $roleId = $userSessionData['role_id'];
        $roleName = $userSessionData['role_name'];
        $isLoggedIn = $userSessionData['user_id'] ? true : false;

        $userData = array(
            "userId" => $userId,
            "userName" => $userName,
            "roleId" => $roleId,
            "roleName" => $roleName,
            "isLoggedIn" => $isLoggedIn
        );



        // Return the response with pretty JSON formatting
        echo json_encode([
            "status" => true,
            "message" => "User is successfully logged in.",
            "user_data" => $userData
        ], JSON_PRETTY_PRINT);
        http_response_code(200); // Success
        exit;
    }

    private function authenticate_user($email, $password)
    {
        $this->load->model('UserModel');
        $result = $this->UserModel->authenticate($email, $password);

        // Handle authentication cases
        switch ($result['message']) {
            case 'email_not_found':
                return ["status" => false, 'message' => 'The email you entered does not exist.', 'code' => 404];
            case 'password_incorrect':
                return ["status" => false, 'message' => 'Incorrect password. Please try again.', 'code' => 401];
            case 'user_inactive':
                return ["status" => false, 'message' => 'Please check your emails to verify your account.', 'code' => 403];
            case 'role_unauthorized':
                return ["status" => false, 'message' => 'You do not have the required permissions to access this system.', 'code' => 403];
            case 'failed_to_retrieve_user_data':
                return ["status" => false, 'message' => 'Failed to retrieve user data. Please contact support.', 'code' => 500];
        }

        // Authentication successful
        return ['status' => true, 'user' => $result['data']];
    }

    function get_user_authentication_details()
    {

        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => false,
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => false,
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            echo json_encode([
                "status" => false,
                "message" => "User is not logged in."
            ], JSON_PRETTY_PRINT);
            http_response_code(401); // Unauthorized
            exit;
        }


        // Get session data
        $session_data = $this->session->userdata('auction_user_session_data');

        // Format it back like your original array
        $user_data = [
            'user_id'     => $session_data['user_id'],
            'first_name'  => $session_data['first_name'],
            'last_name'   => $session_data['last_name'],
            'email'       => $session_data['email'],
            'role_id'     => $session_data['role_id'],
            'role_name'   => $session_data['role_name'],
            'status'      => $session_data['status'],
        ];

        // Example: echo JSON response
        echo json_encode([
            "status"  => true,
            "message" => "Session data retrieved successfully.",
            "user"    => $user_data
        ], JSON_PRETTY_PRINT);
    }
	
    function process_login()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => false,
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => false,
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
        }

        // Check if user is already logged in
        if ($this->session->userdata('auction_user_session_data')) {
            echo json_encode([
                "status" => false,
                "message" => "User is already logged in."
            ], JSON_PRETTY_PRINT);
            http_response_code(200); // OK
            exit;
        }

        // Read JSON input safely (php://input can only be read once)
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => false,
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $data = $json_input ?? [];

        // Validate input data (email and password)
        $email = isset($data['email']) ? trim($data['email']) : null;
        $password = isset($data['password']) ? trim($data['password']) : null;

        if (empty($email) || empty($password)) {
            echo json_encode([
                "status" => false,
                "message" => "Username and password are required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            echo json_encode([
                "status" => false,
                "message" => "Please provide a valid email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load user model
        $this->load->model('UserModel');

        // DEBUG: Log the authentication attempt
        log_message('debug', 'Login attempt for email: ' . $email);

        // Fetch user from database
        $auth_result = $this->authenticate_user($email, $password);

        // DEBUG: Log authentication result
        log_message('debug', 'Auth result: ' . json_encode($auth_result));


        if ($auth_result['status'] === false) {

            // DEBUG: Log the failure reason
            log_message('debug', 'Authentication failed: ' . $auth_result['message']);

            echo json_encode([
                "status" => false,
                "message" => $auth_result['message']
            ], JSON_PRETTY_PRINT);
            http_response_code($auth_result['code']); // Not Found
            exit;
        }

//		// 👉 ADD BLOCK HERE
//		if ((int)$auth_result['user']['role_id'] === 4) {
//			log_message('debug', 'Login blocked for role_id 4: ' . $email);
//
//			echo json_encode([
//				"status" => false,
//				"message" => "something went wrong."
//			], JSON_PRETTY_PRINT);
//
//			http_response_code(403);
//			exit;
//		}


        // Prepare session data
        $session_data = [
            'user_id'      => $auth_result['user']['user_id'],
            'first_name'    => $auth_result['user']['first_name'],
            'last_name'    => $auth_result['user']['last_name'],
            'email'        => $auth_result['user']['email'],
            'role_id'    => $auth_result['user']['role_id'],
            'role_name'    => $auth_result['user']['role_name'],
            'status'       => $auth_result['user']['status'],
            'manage_all_locations' => $auth_result['user']['manage_all_locations'],
        ];

        // If user is staff, check their assigned locations
        if ($session_data['role_id'] == 3) {
            $this->load->model('StaffModel');
            $assigned_locations = $this->StaffModel->getStaffAssignedLocations($session_data['user_id']);
            
            // If they don't manage all and have 0 or 1 location, we can auto-select or block
            if (!$session_data['manage_all_locations']) {
                if (count($assigned_locations) === 0) {
                     echo json_encode([
                        "status" => false,
                        "message" => "Account error: No locations assigned. Please contact your administrator."
                    ], JSON_PRETTY_PRINT);
                    http_response_code(403);
                    exit;
                }
                
                if (count($assigned_locations) === 1) {
                    $session_data['active_storage_facility_id'] = $assigned_locations[0];
                }
            }
        }

        $this->session->set_userdata('auction_user_session_data', $session_data);

        $redirect_url = $this->_get_role_redirect_url($session_data['role_id']);

        echo json_encode([
            "status" => true,
            "message" => "Login successful.",
            "redirect_url" => $redirect_url,
            'user' => $auth_result['user']
        ], JSON_PRETTY_PRINT);
        http_response_code(200); // OK
        exit;
    }

    public function process_logout()
    {

        // Check if user is logged in
        if ($this->session->userdata('auction_user_session_data')) {
            // Get logged-in user ID
            $userSessionData = $this->session->userdata('auction_user_session_data');
            $userId = $userSessionData['user_id'];
            $userRoleId = $userSessionData['role_id'];

            // Check if user is logged in
            if ($userRoleId == 2) {

                // Load the Auction model
                $this->load->model('AuctionModel');

                $this->AuctionModel->deleteAuctionViewDataByUserId($userId);
            }
        }


        // Destroy the session securely
        $this->session->sess_destroy();

        echo json_encode([
            "status" => true,
            "message" => "Logout successful."
        ], JSON_PRETTY_PRINT);
        http_response_code(200); // OK
        exit;
    }


    public function process_forgot_password()
    {
        try {
            // Check if request is an AJAX request
            if (!$this->input->is_ajax_request()) {
                $response = [
                    'status' => false,
                    'message' => 'Invalid request type',
                    'http_code' => 400
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_PRETTY_PRINT));
                return;
            }

            // Check if request method is POST
            if ($this->input->server('REQUEST_METHOD') !== 'POST') {
                $response = [
                    'status' => false,
                    'message' => 'Only POST requests are allowed',
                    'http_code' => 405
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_PRETTY_PRINT));
                return;
            }

            // Get the input email
            $json_input = file_get_contents('php://input');
            $data = json_decode($json_input);

            $email = isset($data->email) ? trim($data->email) : null;

            if (empty($email)) {



                $response = [
                    'status' => false,
                    'message' => 'Email is required.',
                    'http_code' => 400
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_PRETTY_PRINT));
                return;
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {



                $response = [
                    'status' => false,
                    'message' => 'Please provide a valid email address.',
                    'http_code' => 400
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_PRETTY_PRINT));
                return;
            }

            // Check if the email exists in the database
            $user = $this->UserModel->get_user_by_email_address($email);

            if (!$user) {



                $response = [
                    'status' => false,
                    'message' => 'No account found with that email.',
                    'http_code' => 400
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_PRETTY_PRINT));
                return;
            }

            // Generate a secure reset token
            $reset_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Save the reset token and expiry to the database
            $this->UserModel->store_password_reset_token($user['id'], $reset_token, $expires_at);

            // Send reset link via email
            $reset_link = base_url("account/reset_password?token={$reset_token}");
            $subject = 'Leadforce Password Reset';
            $body = "
            <p>Hi {$user['name']},</p>
            <p>We received a request to reset your password. Click the link below to reset it:</p>
            <p><a href='{$reset_link}'>Reset Password</a></p>
            <p>This link is valid for 5 minutes. If you didn’t request this, please ignore this email.</p>
        ";

            $this->send_email($email, $subject, $body);


            $response = [
                'status' => true,
                'message' => 'Password reset link has been sent to your email.',
                'http_code' => 200
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_PRETTY_PRINT));
            return;
        } catch (Exception $e) {
            log_message('error', $e->getMessage());



            $response = [
                'status' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'http_code' => 500
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_PRETTY_PRINT));
            return;
        }
    }

    public function process_reset_password()
    {
        try {
            // Ensure that the request is made via AJAX
            if (!$this->input->is_ajax_request()) {

                $response = [
                    'status' => false,
                    'message' => 'Only AJAX requests are allowed.',
                    'http_code' => 403
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_PRETTY_PRINT));
                return;
            }

            $json_input = file_get_contents('php://input');
            $data = json_decode($json_input);

            $token = isset($data->token) ? $data->token : null;
            $new_password = isset($data->password) ? $data->password : null;

            if (empty($token) || empty($new_password)) {


                $response = [
                    'status' => false,
                    'message' => 'All fields are required.',
                    'http_code' => 400
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_PRETTY_PRINT));
                return;
            }

            $this->load->model('UserModel');
            $user = $this->UserModel->get_user_by_reset_token($token);

            if (!$user) {



                $response = [
                    'status' => false,
                    'message' => 'Invalid or expired token.',
                    'http_code' => 400
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_PRETTY_PRINT));
                return;
            }

            // Update the user's password
            $hashed_password = $new_password;
            $updated_at = date('Y-m-d H:i:s');
            $this->UserModel->update_password($user['id'], $hashed_password, $updated_at);

            // Invalidate the token
            $this->UserModel->invalidate_reset_token($user['id']);


            $response = [
                'status' => true,
                'message' => 'Your password has been successfully reset.',
                'http_code' => 200
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_PRETTY_PRINT));
            return;
        } catch (Exception $e) {
            log_message('error', $e->getMessage());


            $response = [
                'status' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'http_code' => 500
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_PRETTY_PRINT));
            return;
        }
    }

    /**
     * Helper function to get the correct dashboard URL based on user role
     */
    private function _get_role_redirect_url($role_id)
    {
        switch ($role_id) {
            case 1:
            case 3:
                return base_url('storage-facility/dashboard');
            case 2:
                return base_url('show-dashboard/dashboard');
            case 4:
                return base_url('my-dashboard/dashboard');
            default:
                return base_url();
        }
    }
}

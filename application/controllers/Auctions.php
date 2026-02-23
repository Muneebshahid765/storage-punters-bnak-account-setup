<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auctions extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }

    public function index()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/auctions/get_auctions.js'),
            base_url('assets/js/ajax/auctions/distance_by_filter.js'),
            base_url('assets/js/ajax/auctions/get_all_auctions.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('auctions/index');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function actionDetails($auctionId = null)
    {
        // Validate auction ID
        if (!$auctionId || !is_numeric($auctionId)) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        // Load the Auction model
        $this->load->model('AuctionModel');

        // Fetch auction details from database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        if (!$auctionData) {
            redirect(base_url()); // Redirect if auction is not found
            return;
        }


        // Check if user is logged in
        if ($this->session->userdata('auction_user_session_data')) {
            // Get logged-in user ID
            $userSessionData = $this->session->userdata('auction_user_session_data');
            $userId = $userSessionData['user_id'];
            $userRoleId = $userSessionData['role_id'];

            // Check if user is logged in
            if ($userRoleId == 2) {

                $auctionViewData = [
                    'auction_id' => $auctionId,
                    'user_id' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->AuctionModel->insertAuctionViewData($auctionViewData);
            }
        }



        $data['scripts'] = [
            base_url('assets/js/ajax/auctions/variables.js'),
            base_url('assets/js/ajax/auctions/add_to_watch_list.js'),
            base_url('assets/js/ajax/auctions/place_bid.js'),
            base_url('assets/js/ajax/auctions/get_auction_by_id.js')

        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('auctions/details');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function auctionRemoveViewDataByBidderUser()
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

        // Allow for non-AJAX beacon requests
        if (
            !$this->input->is_ajax_request() &&
            (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') &&
            (!isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false)
        ) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only valid JSON requests."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Check login session
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

        if (in_array($userRoleId, [1, 3])) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Permission denied."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Parse JSON input
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($json_input['auctionId'])) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload or missing auctionId."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $auctionId = (int) $json_input['auctionId'];
        if ($auctionId <= 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid auction ID."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $this->load->model('AuctionModel');
        $success = $this->AuctionModel->deleteAuctionViewDataByUserAuctionId($userId, $auctionId);

        if ($success) {
            echo json_encode([
                "status" => "success",
                "message" => "Auction view removed for user."
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to remove auction view."
            ], JSON_PRETTY_PRINT);
        }

        exit;
    }


    public function auctionDetailsEditByStorageFacilityUser($auctionId = null)
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

        if ($companyData == null) {
            // If company does not exist, redirect to setup page
            redirect('company/setup/new');
        }

        // Validate auction ID
        if (!$auctionId || !is_numeric($auctionId)) {
            redirect(base_url()); // Redirect to the default controller route
            return;
        }

        // Load the Auction model
        $this->load->model('AuctionModel');

        // Fetch auction details from database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        if (!$auctionData) {
            redirect(base_url()); // Redirect if auction is not found
            return;
        }



        $data['scripts'] = [
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_category.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_types.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_storage_facilities.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/auction_functions.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_auction_details_by_id.js')

        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/auctions/details');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }


    public function auctionDetailsAddByStorageFacilityUser()
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

        if ($companyData == null) {
            // If company does not exist, redirect to setup page
            redirect('company/setup/new');
        }

        $companyId = $companyData['company_id'] ?? $companyData['id'];
        $data['global_rules'] = $this->CompanyModel->getGlobalAuctionRules($companyId);

        $data['scripts'] = [
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_category.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_types.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/get_all_auction_storage_facilities.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/auction_functions.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/add_auction_details.js')

        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/auctions/add_auction');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function getServerTime()
    {
        header('Content-Type: application/json');

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

        $serverTime = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
        
        echo json_encode([
            "status" => "success",
            "server_time" => $serverTime->format('Y-m-d H:i:s'),
            "timezone" => 'Australia/Brisbane',
            "timestamp" => $serverTime->getTimestamp()
        ], JSON_PRETTY_PRINT);
        http_response_code(200);
        exit;
    }

    public function getTopThreeAuctions()
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

        $this->load->model('AuctionModel');

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

        // Validate the limit parameter
        $limit = isset($json_input['limit']) ? $json_input['limit'] : 3;
        if ($limit === 'all') {
            $limit = null; // Fetch all auctions
        } else {
            $limit = is_numeric($limit) ? intval($limit) : 3;
        }

        // Fetch auctions from the model
        $auctions = $this->AuctionModel->getAuctions($limit, null, "details");

        // Return the response with pretty JSON formatting
        echo json_encode([
            "status" => "success",
            "data" => $auctions
        ], JSON_PRETTY_PRINT);
        http_response_code(200); // Success
        exit;
    }


    public function getAllAuctions()
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

        $this->load->model('AuctionModel');

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

        // Validate start and end parameters
        $start = isset($json_input['start']) && is_numeric($json_input['start']) ? intval($json_input['start']) : 0;
        $end   = isset($json_input['end']) && is_numeric($json_input['end']) && $json_input['end'] > $start
            ? intval($json_input['end'])
            : $start + 16; // Default range: 16 items

        // Optional fallback limit if 'limit' is used instead of start/end
        $limit = isset($json_input['limit']) ? $json_input['limit'] : null;
        if ($limit === 'all') {
            $start = 0;
            $end = null;
        }

        // 🔁 Get total count BEFORE applying limit
        $totalCount = $this->AuctionModel->getAuctionCount();

        // Fetch auctions from the model
        $auctions = $this->AuctionModel->getAllAuctions($start, $end);

        // Return the response with pretty JSON formatting
        echo json_encode([
            "status" => "success",
            "data" => $auctions,
            "totalCount" => $totalCount
        ], JSON_PRETTY_PRINT);
        http_response_code(200); // Success
        exit;
    }

    public function addAuctionToWatchlist()
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
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission to place bids on auctions."
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

        // Get auction ID from JSON input
        $auctionId = isset($json_input['auctionId']) ? (int)$json_input['auctionId'] : 0;

        // Validate auction ID
        if ($auctionId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('AuctionModel');

        // Fetch auction details from the database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {
            echo json_encode([
                "status" => "error",
                "message" => "Auction not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }


        $currentAuctionStatus = $this->AuctionModel->getAuctionStatusById($auctionId);

        if ($currentAuctionStatus->auction_type_id != 1) {
            echo json_encode([
                "status" => "error",
                "message" => "This auction is no longer live. Bidding is closed."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Check if the user has already favorited this auction
        $isFavorite = $this->AuctionModel->isAuctionFavorite($userId, $auctionId);

        if ($isFavorite) {
            // Remove from favorites
            $this->AuctionModel->removeFromFavorites($userId, $auctionId);
            echo json_encode([
                "status" => "success",
                "message" => "Removed from watch list."
            ], JSON_PRETTY_PRINT);
            http_response_code(200); // Success
        } else {
            // Add to favorites
            $this->AuctionModel->addToFavorites($userId, $auctionId);
            echo json_encode([
                "status" => "success",
                "message" => "Added to watch list."
            ], JSON_PRETTY_PRINT);
            http_response_code(200); // Success
        }
        exit;
    }


    public function addBidToAuction()
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
        $userRoleId = $userSessionData['role_id'];
        $userEmail = $userSessionData['email'];
        $userFirstName = $userSessionData['first_name'];
        $userLastName = $userSessionData['last_name'];

        // Check if user is logged in
        if ($userRoleId != 2) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "Access Denied: Your account does not have permission to place bids on auctions."
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


        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch payment methods from model
        $methods = $this->UserModel->getBidderAllPaymentMethodsById($userId);

        // Check if data exists
        if (empty($methods)) {

            echo json_encode([
                "status" => "error",
                "message" => "No active payment methods found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }


        // Get auction ID and bid amount from JSON input
        $auctionId = isset($json_input['auctionId']) ? (int)$json_input['auctionId'] : 0;
        $bidAmount = isset($json_input['bidAmount']) ? (float)$json_input['bidAmount'] : 0.00;

        // Validate auction ID and bid amount
        if ($auctionId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        if ($bidAmount <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Bid amount must be greater than 0."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('AuctionModel');

        // Fetch auction details from the database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {
            echo json_encode([
                "status" => "error",
                "message" => "Auction not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        // Combine start date + time into DateTime
        try {
            $startDateTime = new DateTime($auctionData['start_date'] . ' ' . $auctionData['start_time']);
        } catch (Exception $e) {
            $startDateTime = false;
        }

        // Combine end date + time into DateTime
        try {
            $endDateTime = new DateTime($auctionData['end_date'] . ' ' . $auctionData['end_time']);
        } catch (Exception $e) {
            $endDateTime = false;
        }

        // Get current server time
        $now = new DateTime();

        // --- Condition 1: Auction is in the future ---
        if ($startDateTime && $startDateTime > $now) {
            echo json_encode([
                "status" => "info",
                "message" => "Auction has not started yet. It will start on " . $startDateTime->format('Y-m-d h:i A')
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // --- Condition 2: Auction is completely in the past ---
        if ($endDateTime && $endDateTime < $now) {
            echo json_encode([
                "status" => "info",
                "message" => "Auction has already ended on " . $endDateTime->format('Y-m-d h:i A')
            ], JSON_PRETTY_PRINT);
            exit;
        }

        if (!$startDateTime || !$endDateTime) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid auction date/time format."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }


        $currentAuctionStatus = $this->AuctionModel->getAuctionStatusById($auctionId);

        if ($currentAuctionStatus->auction_type_id != 1) {
            echo json_encode([
                "status" => "error",
                "message" => "This auction is no longer live. Bidding is closed."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Fetch the current highest bid separately
        $currentHighestBid = $this->AuctionModel->getCurrentHighestBid($auctionId);

        // Check if the bid amount is lower than the current price
        if ($bidAmount <= $currentHighestBid) {
            echo json_encode([
                "status" => "error",
                "message" => "Your bid must be higher than the current highest bid of $" . number_format($currentHighestBid, 2)
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // --- Bidding Limits Check ---
        $activeBidsCount = $this->AuctionModel->countActiveAuctionsByBidder($userId);
        $winsCount = $this->AuctionModel->countWonAuctionsByBidder($userId);
        $hasAlreadyBid = $this->AuctionModel->hasUserBidOnAuction($userId, $auctionId);

        // Limit: 5 active auctions max
        if (!$hasAlreadyBid && $activeBidsCount >= 5) {
            echo json_encode([
                "status" => "error",
                "message" => "You have reached your limit of 5 active auction bids. Please wait for an auction to end before bidding on a new one."
            ], JSON_PRETTY_PRINT);
            http_response_code(403);
            exit;
        }

        // Limit: 2 wins max
        if ($winsCount >= 2) {
            echo json_encode([
                "status" => "error",
                "message" => "You have reached your limit of 2 wins. Please complete your current purchases before winning more auctions."
            ], JSON_PRETTY_PRINT);
            http_response_code(403);
            exit;
        }

        // --- Anti-Sniping Logic ---
        $isExtended = false;
        $diff = $endDateTime->getTimestamp() - $now->getTimestamp();
        if ($diff > 0 && $diff <= 300) { // Within 5 minutes
            $this->AuctionModel->extendAuctionTime($auctionId, 5);
            $isExtended = true;
            log_message('info', "Auction ID {$auctionId} extended by 5 minutes due to late bid from User ID {$userId}");
        }

        // Add bid to the auction
        $bidResult = $this->AuctionModel->addBidToAuction($userId, $auctionId, $bidAmount);

        // Return the response
        if ($bidResult) {

            // --- THE VAULT: Coin Reward Integration (Tier-Based Multiplier) ---
            $this->load->model('CoinModel');
            $this->load->model('SubscriptionGiveawayModel');
            $coinRules = $this->CoinModel->getRules();
            $baseCoins = $coinRules ? $coinRules->coins_per_bid : 10;

            // Apply tier-based coin multiplier (Players: 0.5x, Sharks: 1x, Whales: 2x)
            $coinMultiplier = 1.0;
            $userSub = $this->SubscriptionGiveawayModel->get_user_subscription($userId);
            if ($userSub && $userSub['status'] === 'active') {
                $coinMultiplier = (float)($userSub['coin_multiplier'] ?? 1.0);
            }
            $coinsEarned = (int)round($baseCoins * $coinMultiplier);

            $this->CoinModel->updateBalance(
                $userId,
                $coinsEarned,
                'bid_placed',
                'auction_id',
                $auctionId,
                "Bid on Auction #{$auctionId} ({$coinMultiplier}x multiplier)"
            );
            // ------------------------------------------

            // Fetch auction details from the database
            $auctionDetails = $this->AuctionModel->getAuctionInformationById($auctionId);

            $auction = $auctionDetails[0]; // use the first (and only) result

            $users_ids = $this->AuctionModel->getUsersWithLowerMaxBid($userId, $auctionId, $bidAmount);

            $this->load->model('EmailQueueModel');

            $this->EmailQueueModel->delete_all_bidder_outbid_queue($auction->auction_id, 'auction_outbid_email');

            foreach ($users_ids as $user) {

                $user_data = $this->AuctionModel->get_interested_users_by_id($user->user_id, $auctionId);

                if (!$user_data) {
                    log_message('error', "User with ID {$user->user_id} not found or did not favorite auction ID {$auctionId}");
                    continue;
                }

                $nowTime = date('Y-m-d H:i:s');

                $this->EmailQueueModel->add([
                    'to_email'     => $user_data->email,
                    'subject'      => "You have Been Outbid!",
                    'template'     => "auction_outbid_email",
                    'name'         => trim($user_data->first_name . ' ' . $user_data->last_name),
                    'user_id'  =>  $user->user_id,
                    'auction_id'  =>  $auction->auction_id,
                    'auction_item' => trim($auction->auction_storage_facility_name . ' #' . $auction->auction_id . ' - ' . $auction->auction_storage_facility_city . ' , ' . $auction->auction_storage_facility_state),
                    'auction_amount' => $bidAmount,
                    'auction_total' => $bidAmount,
                    'auction_url'  => base_url('auctions/' . $auction->auction_id),
                    'scheduled_at' => $nowTime,
                    'status'       => 'pending'
                ]);
            }

            $webhookData = [
                'punter'       => [
                    'id'         => $userId,
                    'email'      => $userEmail,
                    'first_name' => $userFirstName,
                    'last_name'  => $userLastName
                ],
                'bid' => [
                    'amount'     => (float)$bidAmount,
                    'currency'   => 'AUD',
                ],
                // 🏦 Auction Information — split into meaningful keys
                'auction' => [
                    'id'              => (int)$auction->auction_id,
                    'facility_name'   => trim($auction->auction_storage_facility_name),
                    'city'            => trim($auction->auction_storage_facility_city),
                    'state'           => trim($auction->auction_storage_facility_state),
                    'country'         => isset($auction->auction_storage_facility_country)
                        ? trim($auction->auction_storage_facility_country)
                        : 'Australia'
                ]
            ];

            // NOTE: Coin reward already granted above with tier multiplier (lines 775-796)

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('auction.bid_placed', 'bid', $webhookData);

            echo json_encode([
                "status" => "success",
                "message" => $isExtended ? "Bid placed successfully! Auction has been extended by 5 minutes." : "Bid placed successfully.",
                "is_extended" => $isExtended
            ], JSON_PRETTY_PRINT);
            http_response_code(200); // Success
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to place bid. Please try again."
            ], JSON_PRETTY_PRINT);
            http_response_code(500); // Internal Server Error
        }
        exit;
    }


    public function addInvoiceToBackgroundSchedule($invoiceId, $scheduleAt)
    {
        // ✅ Step 1: Parse Brisbane schedule datetime
        $scheduleDateTime = new DateTime($scheduleAt, new DateTimeZone('Australia/Brisbane'));
        $scheduleTimestamp = $scheduleDateTime->getTimestamp();

        // ✅ Step 2: Detect PHP CLI path
        $phpPath = '/usr/bin/php';
        if (str_contains(strtolower($phpPath), 'httpd.exe')) {
            $phpPath = 'C:\xampp\php\php.exe'; // fallback for Windows dev
        }

        // ✅ Step 3: Base paths & OS info
        $indexPhpPath = FCPATH . "index.php";
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // ✅ Step 4: Cleanup any previous at/scheduled jobs for same invoice
        if (!$isWindows) {
            log_message('debug', "Starting job cleanup for invoice ID {$invoiceId}");

            exec("/usr/bin/atq", $atqOutput, $atqStatus);
            if ($atqStatus !== 0) {
                log_message('error', "Failed to list at jobs with atq.");
            }

            foreach ($atqOutput as $line) {
                if (preg_match('/^(\d+)\s/', $line, $matches)) {
                    $jobId = $matches[1];
                    $commandOutput = [];
                    exec("/usr/bin/at -c {$jobId}", $commandOutput, $atcStatus);
                    $jobScript = implode("\n", $commandOutput);

                    if (strpos($jobScript, "CliWorker monitorInvoicePaymentRelease {$invoiceId} ") !== false) {
                        exec("/usr/bin/atrm {$jobId}", $deleteOutput, $deleteResult);
                        if ($deleteResult === 0) {
                            log_message('info', "Successfully removed existing at job ID {$jobId} for invoice {$invoiceId}");
                        } else {
                            log_message('error', "Failed to remove job #{$jobId} for invoice {$invoiceId}. Output: " . implode("\n", $deleteOutput));
                        }
                    }
                }
            }
        }

        // ✅ Step 5: Schedule new background command
        if ($isWindows) {
            // Convert to local system timezone
            $scheduleDateTimeSystem = clone $scheduleDateTime;
            $scheduleDateTimeSystem->setTimezone(new DateTimeZone(date_default_timezone_get()));

            $time = $scheduleDateTimeSystem->format('H:i');
            $date = $scheduleDateTimeSystem->format('m/d/Y');

            $taskName = "invoice_{$invoiceId}_" . uniqid();
            $cliCommand = "{$phpPath} {$indexPhpPath} CliWorker monitorInvoicePaymentRelease {$invoiceId} {$scheduleTimestamp}";
            $scheduleCmd = "schtasks /Create /SC ONCE /TN {$taskName} /TR \"{$cliCommand}\" /ST {$time} /SD {$date} /F";
        } else {
            // Linux: use `at` command
            $time = date('H:i', $scheduleTimestamp);
            $date = date('Y-m-d', $scheduleTimestamp);

            $cliCommand = "{$phpPath} {$indexPhpPath} CliWorker monitorInvoicePaymentRelease {$invoiceId} {$scheduleTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";
        }

        // ✅ Step 6: Execute the schedule command
        exec($scheduleCmd, $output, $resultCode);

        // ✅ Step 7: Log the outcome
        if ($resultCode === 0) {
            log_message('info', "Invoice ID {$invoiceId} scheduled for payout at Brisbane time: {$scheduleDateTime->format('Y-m-d H:i:s')} (timestamp: {$scheduleTimestamp})");
            log_message('debug', "Scheduled command: {$scheduleCmd}");
        } else {
            log_message('error', "Failed to schedule invoice ID {$invoiceId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }

    public function addAuctionToConfirm()
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
        $userRoleId = $userSessionData['role_id'];

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
        $auctionId = isset($json_input['auctionId']) ? (int)$json_input['auctionId'] : 0;

        // Validate auction ID and bid amount
        if ($auctionId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        $auctionWinnerId = isset($json_input['auctionWinnerId']) ? (int)$json_input['auctionWinnerId'] : 0;


        // Validate auction ID and bid amount
        if ($auctionWinnerId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction winner ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        $confirmationTypeId = isset($json_input['confirmationTypeId']) ? (int)$json_input['confirmationTypeId'] : 0;

        // Validate auction ID and bid amount
        if ($confirmationTypeId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction confirmation type ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('AuctionModel');


        // Fetch auction details from the database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {
            echo json_encode([
                "status" => "error",
                "message" => "Auction not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }


        // Fetch auction details from the database
        $auctionWinnerData = $this->AuctionModel->getAuctionWinnerById($auctionWinnerId);

        // If auction not found, return error response
        if (!$auctionWinnerData) {
            echo json_encode([
                "status" => "error",
                "message" => "Auction winners are not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        $auctionConfirmBeforeResult = $this->AuctionModel->hasAuctionConfirm($auctionId, $auctionWinnerId, $userId, $confirmationTypeId);


        if ($auctionConfirmBeforeResult) {
            http_response_code(409); // 409 Conflict - better status for duplicate submissions
            echo json_encode([
                "status"  => "error",
                "message" => "You have already submitted your confirmation for this auction. Duplicate confirmations are not allowed."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $auctionConfirmationData = [
            'auction_id' => $auctionId,
            'winner_id' => $auctionWinnerId,
            'user_id' => $userId,
            'confirmation_type_id' => $confirmationTypeId,
            'confirmed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $auctionConfirmResult = $this->AuctionModel->addAuctionToConfirm($auctionConfirmationData);

        if ($auctionConfirmResult) {

            echo json_encode([
                "status" => "success",
                "message" => "Your confirmation has been successfully submitted."
            ], JSON_PRETTY_PRINT);
            http_response_code(200);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Unable to confirm the auction at this time. Please try again later."
            ], JSON_PRETTY_PRINT);
            http_response_code(500); // Internal Server Error
        }

        /*if ($auctionConfirmResult) {


            $paymentReleaseData = [
                "schedule_at"      => date("Y-m-d H:i:s", strtotime("+3 days")),
            ];

            $this->load->model('InvoiceModel');

            $invoiceData = $this->InvoiceModel->getInvoiceDetailsByAuctionId($auctionId);

            if (!$invoiceData) {

                echo json_encode([
                    "status" => "error",
                    "message" => "Invoice not found."
                ], JSON_PRETTY_PRINT);
                http_response_code(404); // Not Found
                exit;
            }

            $invoice_id = $invoiceData['invoice_id'];
            $userId = $invoiceData['user_id'];

            $auctionConfirmByBidderResult = $this->AuctionModel->hasAuctionByBidderConfirm($auctionId, $userId);


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

            if ($auctionConfirmByBidderResult && $auctionConfirmByStorageFacilityResult) {
                $this->addInvoiceToBackgroundSchedule($invoice_id, $paymentReleaseData['schedule_at']);

                // Role-based message
                if (in_array($userRoleId, [1, 3])) {
                    $message = "All confirmations are complete. Your payment will be released within 3 days.";
                } elseif ($userRoleId == 2) {
                    $message = "All confirmations are complete. You have successfully approved the payment release to the owner.";
                } else {
                    $message = "All confirmations have been successfully completed.";
                }

                echo json_encode([
                    "status" => "success",
                    "message" => $message
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
            } else {
                echo json_encode([
                    "status" => "success",
                    "message" => "Your confirmation has been successfully submitted."
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Unable to confirm the auction at this time. Please try again later."
            ], JSON_PRETTY_PRINT);
            http_response_code(500); // Internal Server Error
        }*/

        /*

         $this->load->model('InvoiceModel');


        // Add bid to the auction
        $auctionPaymentResult = $this->InvoiceModel->hasAuctionPaymentPaid($auctionId);

        if ($auctionPaymentResult) {

            $auctionConfirmationData = [
                'winner_id' => $auctionWinnerId,
                'user_id' => $userId,
                'confirmation_type_id' => $confirmationTypeId,
                'confirmed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $auctionConfirmResult = $this->AuctionModel->addAuctionToConfirm($auctionConfirmationData);


            if ($auctionConfirmResult) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Your confirmation has been successfully submitted."
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Unable to confirm the auction at this time. Please try again later."
                ], JSON_PRETTY_PRINT);
                http_response_code(500); // Internal Server Error
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Payment has not been confirmed. Please complete the payment before confirming the auction.",
                "payment_confirmed" => false
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad request or custom handling
        }
        */


        exit;
    }


    public function getAuctionById()
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

        // Get auction ID from JSON input
        $auctionId = isset($json_input['auctionId']) ? (int)$json_input['auctionId'] : 0;
        $limit = isset($json_input['limit']) ? (int)$json_input['limit'] : 1;

        // Validate auction ID
        if ($auctionId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // Load Auction Model
        $this->load->model('AuctionModel');

        // Fetch auction details from database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {
            echo json_encode([
                "status" => "error",
                "message" => "Auction not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }
        $mode = isset($json_input['mode']) ? (string)$json_input['mode'] : "view";


        // Fetch additional auction data if needed
        $auctions = $this->AuctionModel->getAuctions($limit, $auctionId, $mode);

        $userData = array();

        // Check if user is logged in
        if ($this->session->userdata('auction_user_session_data')) {
            // Get logged-in user ID
            $userSessionData = $this->session->userdata('auction_user_session_data');
            $userId = $userSessionData['user_id'];

            // Check if the user has already favorited this auction
            $isFavorite = $this->AuctionModel->isAuctionFavorite($userId, $auctionId);

            if ($isFavorite) {
                $userData['is_favorite'] = true;
            } else {
                $userData['is_favorite'] = false;
            }
        } else {
            $userData['is_favorite'] = false;
        }

        // Load AdminSettingModel to get current platform commission
        $this->load->model('AdminSettingModel');
        $platformCommission = $this->AdminSettingModel->get_commission_value(1, "percentage");

        // Apply tier-based fee rate override (Sharks/Whales get 0% fees)
        $userTierFeeRate = null;
        if (isset($userId)) {
            $this->load->model('SubscriptionGiveawayModel');
            $userSub = $this->SubscriptionGiveawayModel->get_user_subscription($userId);
            if ($userSub && $userSub['status'] === 'active') {
                $userTierFeeRate = (float)$userSub['fee_rate'];
                $platformCommission = $userTierFeeRate;
            }
        }

        // Return the response with pretty JSON formatting
        echo json_encode([
            "status" => "success",
            "data" => $auctions,
            "message" => "Auction details retrieved successfully.",
            "user_data" => $userData,
            "platform_commission" => $platformCommission,
            "tier_fee_rate" => $userTierFeeRate
        ], JSON_PRETTY_PRINT);
        http_response_code(200); // Success
        exit;
    }

    public function getCurrentActiveAuctionsByBidderUser()
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

        $auction_type_ids = [1]; // Example array of auction type IDs
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
        $this->load->model('AuctionModel');

        $totalRecords = $this->AuctionModel->countAllAuctionByBidderUser($userId);
        $filteredRecords = $this->AuctionModel->countFilteredAuctionByBidderUser($search, $userId);
        $result = $this->AuctionModel->getFilteredAuctionByBidderUser($start, $length, $search, $orderColumn, $orderDir, $userId, $auction_type_ids, false, false);

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
            "message" => "Current/Active Auctions are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }


    public function getAllAuctionsByBidderUser()
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


        $auction_type_ids = [1, 2, 3, 4, 5, 6, 7]; // Example array of auction type IDs
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
        $this->load->model('AuctionModel');

        $totalRecords = $this->AuctionModel->countAllAuctionByBidderUser($userId);
        $filteredRecords = $this->AuctionModel->countFilteredAuctionByBidderUser($search, $userId);
        $result = $this->AuctionModel->getFilteredAuctionByBidderUser($start, $length, $search, $orderColumn, $orderDir, $userId,  $auction_type_ids, false, false);

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
            "message" => "All Auctions are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }

    public function getWatchedAuctionsByBidderUser()
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


        $auction_type_ids = [1, 2, 3, 4, 5, 6, 7]; // Example array of auction type IDs
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
        $this->load->model('AuctionModel');

        $totalRecords = $this->AuctionModel->countAllAuctionByBidderUser($userId);
        $filteredRecords = $this->AuctionModel->countFilteredAuctionByBidderUser($search, $userId);
        $result = $this->AuctionModel->getFilteredAuctionByBidderUser($start, $length, $search, $orderColumn, $orderDir, $userId,  $auction_type_ids, true, false);

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
            "message" => "Watched Auctions are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }


    public function getWonAuctionsByBidderUser()
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


        $auction_type_ids = [1, 2, 3, 4, 5, 6, 7]; // Example array of auction type IDs
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
        $this->load->model('AuctionModel');

        $totalRecords = $this->AuctionModel->countAllAuctionByBidderUser($userId);
        $filteredRecords = $this->AuctionModel->countFilteredAuctionByBidderUser($search, $userId);
        $result = $this->AuctionModel->getFilteredAuctionByBidderUser($start, $length, $search, $orderColumn, $orderDir, $userId,  $auction_type_ids, false, true);

        foreach ($result as &$auction) {
            $auction['confirmations'] = [];

            if (!empty($auction['auction_winner_id'])) {
                $winnerId = $auction['auction_winner_id'];
                $confirmations = $this->AuctionModel->getConfirmationsForWinnerId($winnerId);
                $auction['confirmations'] = $confirmations;

                $userConfirmed = false;
                $otherUserConfirmed = false;

                foreach ($confirmations as $confirm) {
                    if ($confirm['user_id'] == $userId) {
                        $userConfirmed = true;
                    } else {
                        $otherUserConfirmed = true;
                    }
                }

                // ✅ Determine message
                if (!$userConfirmed) {
                    $auction['confirmation_message'] = "Please confirm";
                } elseif ($userConfirmed && !$otherUserConfirmed && $userRoleId == 2) {
                    $auction['confirmation_message'] = "Waiting for painter to confirm";
                } elseif ($userConfirmed && !$otherUserConfirmed && in_array($userRoleId, [1, 3])) {
                    $auction['confirmation_message'] = "Waiting for client to confirm";
                } elseif ($userConfirmed && $otherUserConfirmed) {
                    $auction['confirmation_message'] = "Both parties have confirmed";
                } else {
                    $auction['confirmation_message'] = "Confirmation pending";
                }
            } else {
                $auction['confirmation_message'] = "Please confirm";
            }
        }
        unset($auction);



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
            "message" => "Won Auctions are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }


    public function getAllStatuses()
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
        $this->load->model('AuctionModel');

        // Fetch bidder profile details from the database
        $statusData = $this->AuctionModel->getAllStatuses();

        // If user profile not found, return error response
        if (!$statusData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No status data is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Status data has been retrieved successfully.",
            "data" => $statusData
        ], JSON_PRETTY_PRINT);
    }


    public function getAllCategories()
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
        $this->load->model('AuctionModel');

        // Fetch bidder profile details from the database
        $categoryData = $this->AuctionModel->getAllCategories();

        // If user profile not found, return error response
        if (!$categoryData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No category data is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Category data has been retrieved successfully.",
            "data" => $categoryData
        ], JSON_PRETTY_PRINT);
    }

    public function getAllTypes()
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
        $filter_by = isset($json_input['filter_by']) ? (string)$json_input['filter_by'] : "specific";


        // Load Auction Model
        $this->load->model('AuctionModel');


        // Fetch bidder profile details from the database
        $typeData = $this->AuctionModel->getAllTypes($filter_by);

        // If user profile not found, return error response
        if (!$typeData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No type data is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Type data has been retrieved successfully.",
            "data" => $typeData
        ], JSON_PRETTY_PRINT);
    }

    public function getAllAuctionsByStorageFacilityUser()
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

        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getAllCompaniesByUser($userId);

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

        // Extract just the company IDs
        $companyIds = array_column($companyData, 'company_id');

        $this->load->model('StorageFacilityModel');

        $storageFacilityIds = $this->StorageFacilityModel->getStorageFacilityIdsByCompanyId($companyIds);

        if (empty($storageFacilityIds)) {

            // Prepare the response data
            $data = [
                "draw" => (int) $this->input->post('draw'),
                "recordsTotal" => "0",
                "recordsFiltered" => "0",
                "data" => [],
            ];

            http_response_code(200);
            echo json_encode([
                "status" => "error",
                "message" => "No storage facilities found for the user's companies.",
                "result" => $data
            ], JSON_PRETTY_PRINT);
            exit;
        }


        $this->load->model('AuctionModel');

        $auctionIds = $this->AuctionModel->getAuctionIdsByStorageFacilityIds($storageFacilityIds);

        if (empty($auctionIds)) {

            // Prepare the response data
            $data = [
                "draw" => (int) $this->input->post('draw'),
                "recordsTotal" => "0",
                "recordsFiltered" => "0",
                "data" => [],
            ];


            http_response_code(200);
            echo json_encode([
                "status" => "error",
                "message" => "No auctions are found for the user's storage facilities.",
                "result" => $data
            ], JSON_PRETTY_PRINT);
            exit;
        }


        $auction_type_ids = [1, 2, 3, 4, 5, 6, 7]; // Example array of auction type IDs
        // Sanitize input data
        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $search = $this->input->post('search')['value'];
        $orderColumn = $this->input->post('order')[0]['column'];
        $orderDir = $this->input->post('order')[0]['dir'];
        $auctionTypeId = $this->input->post('auction_type_id');
        $storageFacilityId = $this->input->post('storage_facility_id');

        // Validate input data to prevent SQL injection
        if (!is_int($draw) || !is_int($start) || !is_int($length)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid input']))
                ->set_status_header(400);
            return;
        }

        // Load User Model
        $this->load->model('AuctionModel');

        $totalRecords = $this->AuctionModel->countAllAuctionByStorageFacilityUser($auctionIds);
        $filteredRecords = $this->AuctionModel->countFilteredAuctionByStorageFacilityUser($search, $auctionIds, false, $auctionTypeId, $storageFacilityId);
        $result = $this->AuctionModel->getFilteredAuctionByStorageFacilityUser($start, $length, $search, $orderColumn, $orderDir, $auctionIds, false, $auctionTypeId, $storageFacilityId);

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
            "message" => "All Auctions are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }


    public function getWonAuctionsByStorageFacilityUser()
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

        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getAllCompaniesByUser($userId);

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

        // Extract just the company IDs
        $companyIds = array_column($companyData, 'company_id');

        $this->load->model('StorageFacilityModel');

        $storageFacilityIds = $this->StorageFacilityModel->getStorageFacilityIdsByCompanyId($companyIds);

        if (empty($storageFacilityIds)) {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "No storage facilities found for the user's companies."
            ], JSON_PRETTY_PRINT);
            exit;
        }


        $this->load->model('AuctionModel');

        $auctionIds = $this->AuctionModel->getAuctionIdsByStorageFacilityIds($storageFacilityIds);

        if (empty($auctionIds)) {

            // Prepare the response data
            $data = [
                "draw" => (int) $this->input->post('draw'),
                "recordsTotal" => "0",
                "recordsFiltered" => "0",
                "data" => [],
            ];


            http_response_code(200);
            echo json_encode([
                "status" => "error",
                "message" => "No auctions are found for the user's storage facilities.",
                "result" => $data
            ], JSON_PRETTY_PRINT);
            exit;
        }



        $auction_type_ids = [1, 2, 3, 4, 5, 6, 7]; // Example array of auction type IDs
        // Sanitize input data
        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $search = $this->input->post('search')['value'];
        $orderColumn = $this->input->post('order')[0]['column'];
        $orderDir = $this->input->post('order')[0]['dir'];
        $auctionTypeId = $this->input->post('auction_type_id');
        $storageFacilityId = $this->input->post('storage_facility_id');

        // Validate input data to prevent SQL injection
        if (!is_int($draw) || !is_int($start) || !is_int($length)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid input']))
                ->set_status_header(400);
            return;
        }

        // Load User Model
        $this->load->model('AuctionModel');

        $totalRecords = $this->AuctionModel->countAllAuctionByStorageFacilityUser($auctionIds);
        $filteredRecords = $this->AuctionModel->countFilteredAuctionByStorageFacilityUser($search, $auctionIds, true, $auctionTypeId, $storageFacilityId);
        $result = $this->AuctionModel->getFilteredAuctionByStorageFacilityUser($start, $length, $search, $orderColumn, $orderDir, $auctionIds, true,  $auctionTypeId, $storageFacilityId);



        foreach ($result as &$auction) {
            $auction['confirmations'] = [];

            if (!empty($auction['auction_winner_id'])) {
                $winnerId = $auction['auction_winner_id'];
                $confirmations = $this->AuctionModel->getConfirmationsForWinnerId($winnerId);
                $auction['confirmations'] = $confirmations;

                $userConfirmed = false;
                $otherUserConfirmed = false;

                foreach ($confirmations as $confirm) {
                    if ($confirm['user_id'] == $userId) {
                        $userConfirmed = true;
                    } else {
                        $otherUserConfirmed = true;
                    }
                }
                if (!$userConfirmed) {
                    $auction['confirmation_message'] = "Please confirm";
                } elseif ($userConfirmed && !$otherUserConfirmed && $userRoleId == 2) {
                    $auction['confirmation_message'] = "Waiting for painter to confirm";
                } elseif ($userConfirmed && !$otherUserConfirmed && in_array($userRoleId, [1, 3])) {
                    $auction['confirmation_message'] = "Waiting for client to confirm";
                } elseif ($userConfirmed && $otherUserConfirmed) {
                    $auction['confirmation_message'] = "Both parties have confirmed";
                } else {
                    $auction['confirmation_message'] = "Confirmation pending";
                }
            } else {
                $auction['confirmation_message'] = "Please confirm";
            }
        }

        unset($auction);


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
            "message" => "Won Auctions are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }

    public function updateAuctionByAuctionIdForStorageFacilityUser()
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
            'description',
            'unit_number',
            'disclose_unit_number_on_website',
            'outstanding_dept',
            'starting_price',
            'start_date',
            'start_time',
            'end_date',
            'end_time',
            'cleaning_deposit_price',
            'cleaning_deposit_payment_type',
            'unit_size',
            'tags',
            'additional_features'
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


        // Get auction ID from JSON input
        $auctionId = isset($json_input['auctionId']) ? (int)$json_input['auctionId'] : 0;

        // Validate auction ID
        if ($auctionId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $storage_facility_id = !empty($json_input['storage_facility_id']) ? (int)$json_input['storage_facility_id'] : 0;

        // Validate auction ID
        if ($storage_facility_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing storage facility ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $auction_category_id = !empty($json_input['auction_category_id']) ? (int)$json_input['auction_category_id'] : 0;

        // Validate auction ID
        if ($auction_category_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction category ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $auction_type_id = !empty($json_input['auction_type_id']) ? (int)$json_input['auction_type_id'] : 0;


        // Validate auction ID
        if ($auction_type_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction type ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $startDateTime = new DateTime($json_input['start_date'] . ' ' . $json_input['start_time'], new DateTimeZone('Australia/Brisbane'));
        $brisbaneNow = new DateTime('now', new DateTimeZone('Australia/Brisbane'));

        if ($startDateTime < $brisbaneNow) {
            echo json_encode([
                "status" => "error",
                "message" => "Start date and time must be in the future based on Brisbane time."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Extract only required fields from JSON input
        $auctionData = [
            'description' => isset($json_input['description']) ? trim($json_input['description']) : null,
            'unit_number' => isset($json_input['unit_number']) ? trim($json_input['unit_number']) : null,
            'disclose_unit_number_on_website' => isset($json_input['disclose_unit_number_on_website']) ? trim($json_input['disclose_unit_number_on_website']) : null,
            'outstanding_dept' => isset($json_input['outstanding_dept']) ? trim($json_input['outstanding_dept']) : null,
            'starting_price' => isset($json_input['starting_price']) ? trim($json_input['starting_price']) : null,
            'start_date' => isset($json_input['start_date']) ? trim($json_input['start_date']) : null,
            'start_time' => isset($json_input['start_time']) ? date('H:i:s', strtotime(trim($json_input['start_time']))) : null,
            'end_date' => isset($json_input['end_date']) ? trim($json_input['end_date']) : null,
            'end_time' => isset($json_input['end_time']) ? date('H:i:s', strtotime(trim($json_input['end_time']))) : null,
            'youtube_video_url' => isset($json_input['youtube_video_url']) ? strtolower(trim($json_input['youtube_video_url'])) : null,
            'unit_size' => isset($json_input['unit_size']) ? trim($json_input['unit_size']) : null,
            'tags' => isset($json_input['tags']) ? strtolower(trim($json_input['tags'])) : null,
            'additional_features' => isset($json_input['additional_features']) ? strtolower(trim($json_input['additional_features'])) : null,
            'cleaning_deposit_price' => isset($json_input['cleaning_deposit_price']) ? trim($json_input['cleaning_deposit_price']) : null,
            'cleaning_deposit_payment_type' => isset($json_input['cleaning_deposit_payment_type']) ? trim($json_input['cleaning_deposit_payment_type']) : null,
            'accept_cash' => isset($json_input['accept_cash']) ? (int)$json_input['accept_cash'] : 0,
            'accept_card' => isset($json_input['accept_card']) ? (int)$json_input['accept_card'] : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $auctionStorageFacilityData = [
            'storage_facility_id' => isset($json_input['storage_facility_id']) ? trim($json_input['storage_facility_id']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $auctionCategoryData = [
            'auction_category_id' => isset($json_input['auction_category_id']) ? trim($json_input['auction_category_id']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $auctionStatusTypeData = [
            'auction_type_id' => isset($json_input['auction_type_id']) ? trim($json_input['auction_type_id']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Load Auction Model
        $this->load->model('AuctionModel');


        $auctionDataUpdated = $this->AuctionModel->updateAuctionData($auctionId, $auctionData);
        $auctionStorageFacilityDataUpdated = $this->AuctionModel->updateAuctionStorageFacilityData($auctionId, $auctionStorageFacilityData);
        $auctionCategoryDataUpdated = $this->AuctionModel->updateAuctionCategoryData($auctionId, $auctionCategoryData);
        $auctionStatusTypeDataUpdated = $this->AuctionModel->updateAuctionStatusTypeData($auctionId, $auctionStatusTypeData);

        // Check if at least one update was successful
        if ($auctionDataUpdated || $auctionStorageFacilityDataUpdated || $auctionCategoryDataUpdated || $auctionStatusTypeDataUpdated) {


            // Insert upload_id and auction_id into tbl_auction_upload_relationship
            if (!empty($json_input['upload_ids']) && is_array($json_input['upload_ids'])) {
                $uploadRelationshipData = [];
                foreach ($json_input['upload_ids'] as $uploadId) {
                    $uploadRelationshipData[] = [
                        'auction_id' => $auctionId,
                        'upload_id' => $uploadId,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                }

                // Insert batch upload relationships
                $this->AuctionModel->insertAuctionUploadData($uploadRelationshipData);
            }

            // ✅ Remove upload_id and auction_id from tbl_auction_upload_relationship
            if (!empty($json_input['remove_upload_ids']) && is_array($json_input['remove_upload_ids'])) {
                foreach ($json_input['remove_upload_ids'] as $uploadIdToRemove) {
                    $this->AuctionModel->deleteAuctionUploadData($auctionId, $uploadIdToRemove);
                }
            }

            $this->addAuctionToBackgroundSchedule($auctionId, $auctionData);

            $webhookData = [
                'auction'       => [
                    'id' => $auctionId,
                    'storage_facility_id' => isset($json_input['storage_facility_id']) ? trim($json_input['storage_facility_id']) : null,
                    'auction_type_id' => isset($json_input['auction_type_id']) ? trim($json_input['auction_type_id']) : null,
                    'description' => isset($json_input['description']) ? trim($json_input['description']) : null,
                    'unit_number' => isset($json_input['unit_number']) ? trim($json_input['unit_number']) : null,
                    'disclose_unit_number_on_website' => isset($json_input['disclose_unit_number_on_website']) ? trim($json_input['disclose_unit_number_on_website']) : null,
                    'outstanding_dept' => isset($json_input['outstanding_dept']) ? trim($json_input['outstanding_dept']) : null,
                    'starting_price' => isset($json_input['starting_price']) ? trim($json_input['starting_price']) : null,
                    'start_date' => isset($json_input['start_date']) ? trim($json_input['start_date']) : null,
                    'start_time' => isset($json_input['start_time']) ? trim($json_input['start_time']) : null,
                    'end_date' => isset($json_input['end_date']) ? trim($json_input['end_date']) : null,
                    'end_time' => isset($json_input['end_time']) ? trim($json_input['end_time']) : null,
                    'youtube_video_url' => isset($json_input['youtube_video_url']) ? strtolower(trim($json_input['youtube_video_url'])) : null,
                    'unit_size' => isset($json_input['unit_size']) ? trim($json_input['unit_size']) : null,
                    'tags' => isset($json_input['tags']) ? strtolower(trim($json_input['tags'])) : null,
                    'additional_features' => isset($json_input['additional_features']) ? strtolower(trim($json_input['additional_features'])) : null,
                    'cleaning_deposit_price' => isset($json_input['cleaning_deposit_price']) ? trim($json_input['cleaning_deposit_price']) : null,
                    'cleaning_deposit_payment_type' => isset($json_input['cleaning_deposit_payment_type']) ? trim($json_input['cleaning_deposit_payment_type']) : null
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('auction.updated', 'auction', $webhookData);

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Auction informations has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update auction details."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }


    public function addAuctionByStorageFacilityUser()
    {
        $json_input = json_decode(file_get_contents('php://input'), true);
        log_message('error', 'STANDARD_ADD_AUCTION FULL INPUT: ' . json_encode($json_input));
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


        $this->load->model('CompanyModel');

        // 3️⃣ Get User by Company ID
        $user = $this->CompanyModel->getCompanyByUserId($userId);
        if (!$user || empty($user['user_id'])) {

            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "The current user has no company information setup yet."
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
            'description',
            'unit_number',
            'disclose_unit_number_on_website',
            'outstanding_dept',
            'starting_price',
            'start_date',
            'start_time',
            'end_date',
            'end_time',
            'cleaning_deposit_price',
            'cleaning_deposit_payment_type',
            'unit_size',
            'tags',
            'additional_features',
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

        $storage_facility_id = !empty($json_input['storage_facility_id']) ? (int)$json_input['storage_facility_id'] : 0;

        // Validate auction ID
        if ($storage_facility_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing storage facility ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $this->load->model('StorageFacilityModel');

        // 1️⃣ Get Storage Facility by Auction ID
        $storageFacilityInformation = $this->StorageFacilityModel->getStorageFacilityById($storage_facility_id);
        if (!$storageFacilityInformation || empty($storageFacilityInformation['id'])) {

            echo json_encode([
                "status" => "error",
                "message" => "No storage facility information is found for the given facility."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $facility_name = $storageFacilityInformation['name'];

        // 1️⃣ Get Storage Facility by Auction ID
        $storageFacilityBankAttachedInformation = $this->StorageFacilityModel->getStorageFacilityBankAttachedById($storage_facility_id);
        if (!$storageFacilityBankAttachedInformation || empty($storageFacilityBankAttachedInformation['id'])) {

            echo json_encode([
                "status" => "error",
                "message" => "{$facility_name} isn’t linked to any bank account. Please connect a bank account first to continue."

            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $auction_category_id = !empty($json_input['auction_category_id']) ? (int)$json_input['auction_category_id'] : 0;

        // Validate auction ID
        if ($auction_category_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction category ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $auction_type_id = !empty($json_input['auction_type_id']) ? (int)$json_input['auction_type_id'] : 0;


        // Validate auction ID
        if ($auction_type_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction type ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $startDateTime = new DateTime($json_input['start_date'] . ' ' . $json_input['start_time'], new DateTimeZone('Australia/Brisbane'));
        $brisbaneNow = new DateTime('now', new DateTimeZone('Australia/Brisbane'));

        if ($startDateTime < $brisbaneNow) {
            echo json_encode([
                "status" => "error",
                "message" => "Start date and time must be in the future based on Brisbane time."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Extract only required fields from JSON input
        $auctionData = [
            'description' => isset($json_input['description']) ? trim($json_input['description']) : null,
            'unit_number' => isset($json_input['unit_number']) ? trim($json_input['unit_number']) : null,
            'disclose_unit_number_on_website' => isset($json_input['disclose_unit_number_on_website']) ? trim($json_input['disclose_unit_number_on_website']) : null,
            'outstanding_dept' => isset($json_input['outstanding_dept']) ? trim($json_input['outstanding_dept']) : null,
            'starting_price' => isset($json_input['starting_price']) ? trim($json_input['starting_price']) : null,
            'start_date' => isset($json_input['start_date']) ? trim($json_input['start_date']) : null,
            'start_time' => isset($json_input['start_time']) ? date('H:i:s', strtotime(trim($json_input['start_time']))) : null,
            'end_date' => isset($json_input['end_date']) ? trim($json_input['end_date']) : null,
            'end_time' => isset($json_input['end_time']) ? date('H:i:s', strtotime(trim($json_input['end_time']))) : null,
            'youtube_video_url' => isset($json_input['youtube_video_url']) ? strtolower(trim($json_input['youtube_video_url'])) : null,
            'unit_size' => isset($json_input['unit_size']) && !empty($json_input['unit_size']) ? trim($json_input['unit_size']) : 'Unknown',
            'tags' => isset($json_input['tags']) ? strtolower(trim($json_input['tags'])) : null,
            'additional_features' => isset($json_input['additional_features']) ? strtolower(trim($json_input['additional_features'])) : null,
            'cleaning_deposit_price' => isset($json_input['cleaning_deposit_price']) ? trim($json_input['cleaning_deposit_price']) : null,
            'cleaning_deposit_payment_type' => isset($json_input['cleaning_deposit_payment_type']) ? trim($json_input['cleaning_deposit_payment_type']) : null,
            'accept_cash' => isset($json_input['accept_cash']) ? (int)$json_input['accept_cash'] : 0,
            'accept_card' => isset($json_input['accept_card']) ? (int)$json_input['accept_card'] : 0,
            'updated_at' => date('Y-m-d H:i:s'),
            "created_at" => date("Y-m-d H:i:s")
        ];



        // Load Auction Model
        $this->load->model('AuctionModel');

        $location_ids = isset($json_input['location_ids']) && is_array($json_input['location_ids']) ? $json_input['location_ids'] : [$storage_facility_id];
        $created_auction_ids = [];

        foreach ($location_ids as $current_loc_id) {
            $auctionDataInserted = $this->AuctionModel->insertAuctionData($auctionData);
            $auctionId = $auctionDataInserted['auction_id'];
            $created_auction_ids[] = $auctionId;

            $auctionStorageFacilityData = [
                'storage_facility_id' => $current_loc_id,
                'auction_id' => $auctionId,
                'updated_at' => date('Y-m-d H:i:s'),
                "created_at" => date("Y-m-d H:i:s")
            ];

            $auctionCategoryData = [
                'auction_category_id' => isset($json_input['auction_category_id']) ? trim($json_input['auction_category_id']) : null,
                'auction_id' => $auctionId,
                'updated_at' => date('Y-m-d H:i:s'),
                "created_at" => date("Y-m-d H:i:s")
            ];

            $auctionStatusTypeData = [
                'auction_type_id' => isset($json_input['auction_type_id']) ? trim($json_input['auction_type_id']) : null,
                'auction_id' => $auctionId,
                'updated_at' => date('Y-m-d H:i:s'),
                "created_at" => date("Y-m-d H:i:s")
            ];

            $this->AuctionModel->insertAuctionStorageFacilityData($auctionStorageFacilityData);
            $this->AuctionModel->insertAuctionCategoryData($auctionCategoryData);
            $this->AuctionModel->insertAuctionStatusTypeData($auctionStatusTypeData);

            // Images
            if (!empty($json_input['upload_ids']) && is_array($json_input['upload_ids'])) {
                $uploadRelationshipData = [];
                foreach ($json_input['upload_ids'] as $uploadId) {
                    $uploadRelationshipData[] = [
                        'auction_id' => $auctionId,
                        'upload_id' => $uploadId,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                }
                $this->AuctionModel->insertAuctionUploadData($uploadRelationshipData);
            }

            $this->addAuctionToBackgroundSchedule($auctionId, $auctionData);
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => count($created_auction_ids) . " auction(s) created successfully.",
            "data" => [
                "auction_ids" => $created_auction_ids
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }


    public function updateAuctionStatusByAuctionIdForStorageFacilityUser()
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


        // Get auction ID from JSON input
        $auctionId = isset($json_input['auctionId']) ? (int)$json_input['auctionId'] : 0;

        // Validate auction ID
        if ($auctionId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $auction_type_id = !empty($json_input['auction_type_id']) ? (int)$json_input['auction_type_id'] : 0;


        // Validate auction ID
        if ($auction_type_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing auction type ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getAllCompaniesByUser($userId);

        // If user profile not found, return error response
        if (!$companyData) {

            http_response_code(200); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company data is found.",
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Extract just the company IDs
        $companyIds = array_column($companyData, 'company_id');

        $this->load->model('StorageFacilityModel');

        $storageFacilityIds = $this->StorageFacilityModel->getStorageFacilityIdsByCompanyId($companyIds);

        if (empty($storageFacilityIds)) {

            http_response_code(200);
            echo json_encode([
                "status" => "error",
                "message" => "No storage facilities found for the user's companies.",
            ], JSON_PRETTY_PRINT);
            exit;
        }


        $this->load->model('AuctionModel');

        $auctionIds = $this->AuctionModel->getAuctionIdsByStorageFacilityIds($storageFacilityIds);

        if (empty($auctionIds)) {

            http_response_code(200);
            echo json_encode([
                "status" => "error",
                "message" => "No auctions are found for the user's storage facilities.",
            ], JSON_PRETTY_PRINT);
            exit;
        }


        // Load Auction Model
        $this->load->model('AuctionModel');

        // Fetch auction details from database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {
            echo json_encode([
                "status" => "error",
                "message" => "Auction not found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }


        $auctionStatusTypeData = [
            'auction_type_id' =>  $auction_type_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Load Auction Model
        $this->load->model('AuctionModel');

        $auctionStatusTypeDataUpdated = $this->AuctionModel->updateAuctionStatusTypeData($auctionId, $auctionStatusTypeData);

        if ($auctionStatusTypeDataUpdated) {

            if ($auction_type_id  == 11) {

                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Auction informations has been deleted successfully."
                ], JSON_PRETTY_PRINT);

                exit;
            } else {
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Auction informations has been updated successfully."
                ], JSON_PRETTY_PRINT);

                exit;
            }
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update auction details."
            ], JSON_PRETTY_PRINT);

            exit;
        }

       }

    public function deleteAuctionByAuctionIdForStorageFacilityUser()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Invalid request method."]);
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Unauthorized request."]);
            exit;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        $auctionId = isset($json_input['auctionId']) ? (int)$json_input['auctionId'] : 0;

        if ($auctionId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid auction ID."]);
            exit;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "User not logged in."]);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Insufficient permissions."]);
            exit;
        }

        $this->load->model('CompanyModel');
        $companyData = $this->CompanyModel->getAllCompaniesByUser($userId);
        if (!$companyData) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Company data not found."]);
            exit;
        }

        $companyIds = array_column($companyData, 'company_id');
        $this->load->model('StorageFacilityModel');
        $storageFacilityIds = $this->StorageFacilityModel->getStorageFacilityIdsByCompanyId($companyIds);

        $this->load->model('AuctionModel');
        $validAuctionIds = $this->AuctionModel->getAuctionIdsByStorageFacilityIds($storageFacilityIds);

        if (!in_array($auctionId, $validAuctionIds)) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied."]);
            exit;
        }

        // Use the existing "Cancelled" status (ID 5) instead of a non-existent value to
        // avoid foreign key violations when marking the record as deleted.
        $success = $this->AuctionModel->updateAuctionStatusTypeData($auctionId, [
            'auction_type_id' => 5,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($success) {
            echo json_encode(["status" => "success", "message" => "Auction deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete auction."]);
        }
    }

    public function addAuctionToBackgroundSchedule2($auctionId, $auctionData)
    {
        // Step 1: Parse Brisbane date/time
        $endDateTimeBrisbane = $auctionData['end_date'] . ' ' . $auctionData['end_time'];
        $endDateTime = new DateTime($endDateTimeBrisbane, new DateTimeZone('Australia/Brisbane'));
        $endTimestamp = $endDateTime->getTimestamp();

        // Step 2: Determine proper PHP path
        $phpPath = PHP_BINARY;

        // On Windows Apache, PHP_BINARY may point to httpd.exe — fallback
        if (str_contains(strtolower($phpPath), 'httpd.exe')) {
            $phpPath = 'C:\xampp\php\php.exe'; // XAMPP fallback path
        }

        // Step 3: Paths & redirection
        $indexPhpPath = FCPATH . "index.php";
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $redirect = $isWindows ? '> NUL 2>&1' : '> /dev/null 2>&1';

        // Step 4: Build and run command
        $cliCommand = "\"{$phpPath}\" \"{$indexPhpPath}\" CliWorker monitorAuctionEnd {$auctionId} {$endTimestamp} {$redirect}";
        exec($cliCommand);

        // Step 5: Log it
        log_message('info', "Auction ID {$auctionId} scheduled to end at {$endDateTime->format('Y-m-d H:i:s')} (timestamp: {$endTimestamp})");
        log_message('debug', "CLI command: {$cliCommand}");
    }

    public function addAuctionToBackgroundSchedule($auctionId, $auctionData)
    {
        // Step 1: Parse Brisbane end datetime
        $endDateTimeBrisbane = $auctionData['end_date'] . ' ' . $auctionData['end_time'];
        $endDateTime = new DateTime($endDateTimeBrisbane, new DateTimeZone('Australia/Brisbane'));
        $endTimestamp = $endDateTime->getTimestamp();

        // Step 2: Detect correct PHP CLI path
        $phpPath = '/usr/bin/php'; // Replace with actual path if different
        if (str_contains(strtolower($phpPath), 'httpd.exe')) {
            $phpPath = 'C:\xampp\php\php.exe'; // Fallback for XAMPP
        }

        // Step 3: Paths and platform info
        $indexPhpPath = FCPATH . "index.php";
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if (!$isWindows) {
            log_message('debug', "Starting job cleanup for auction ID {$auctionId}");

            exec("/usr/bin/atq", $atqOutput, $atqStatus);
            log_message('debug', "atq output: " . print_r($atqOutput, true));

            if ($atqStatus !== 0) {
                log_message('error', "Failed to list at jobs with atq.");
            }

            foreach ($atqOutput as $line) {
                if (preg_match('/^(\d+)\s/', $line, $matches)) {
                    $jobId = $matches[1];
                    $commandOutput = [];
                    exec("/usr/bin/at -c {$jobId}", $commandOutput, $atcStatus);
                    $jobScript = implode("\n", $commandOutput);

                    log_message('debug', "Checking job ID {$jobId} contents: " . $jobScript);

                    if (strpos($jobScript, "CliWorker monitorAuctionEnd {$auctionId} ") !== false) {
                        exec("/usr/bin/atrm {$jobId}", $deleteOutput, $deleteResult);

                        if ($deleteResult === 0) {
                            log_message('info', "Successfully removed at job ID {$jobId} for auction {$auctionId}");
                        } else {
                            log_message('error', "Failed to remove job #{$jobId} for auction ID {$auctionId}. Output: " . implode("\n", $deleteOutput));
                        }
                    }
                } else {
                    log_message('debug', "Could not extract job ID from line: {$line}");
                }
            }
        }



        // Step 4: Schedule the command
        if ($isWindows) {


            // Convert Brisbane time to local system time (e.g. Asia/Riyadh)
            $endDateTimeSystem = clone $endDateTime;
            $endDateTimeSystem->setTimezone(new DateTimeZone(date_default_timezone_get()));

            $time = $endDateTimeSystem->format('H:i');
            $date = $endDateTimeSystem->format('m/d/Y');

            $taskName = "auction_{$auctionId}_" . uniqid();
            $cliCommand = "{$phpPath} {$indexPhpPath} CliWorker monitorAuctionEnd {$auctionId} {$endTimestamp}";
            $scheduleCmd = "schtasks /Create /SC ONCE /TN {$taskName} /TR \"{$cliCommand}\" /ST {$time} /SD {$date} /F";
        } else {


            // Linux: schedule using 'at' command
            /*$scheduleTime = date('Y-m-d H:i', $endTimestamp);
            $cliCommand = "\"{$phpPath}\" \"{$indexPhpPath}\" CliWorker monitorAuctionEnd {$auctionId} {$endTimestamp}";
            $scheduleCmd = "echo {$cliCommand} | at {$scheduleTime}";*/

            $time = date('H:i', $endTimestamp);         // e.g., 19:00
            $date = date('Y-m-d', $endTimestamp);       // e.g., 2025-08-03

            $cliCommand = "{$phpPath} {$indexPhpPath} CliWorker monitorAuctionEnd {$auctionId} {$endTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";
        }

        // Step 5: Execute the command
        exec($scheduleCmd, $output, $resultCode);

        // Step 6: Log the outcome
        if ($resultCode === 0) {
            log_message('info', "Auction ID {$auctionId} scheduled to run at Brisbane time: {$endDateTime->format('Y-m-d H:i:s')} (timestamp: {$endTimestamp})");
            log_message('debug', "Scheduled command: {$scheduleCmd}");
        } else {
            log_message('error', "Failed to schedule auction ID {$auctionId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }

    public function testAuctionToBackgroundSchedule()
    {
        $auctionId = 13;

        $auctionData = [
            'end_date' => '2025-05-01',
            'end_time' => '07:51 PM'
        ];

        $this->addAuctionToBackgroundSchedule($auctionId, $auctionData);

        echo "✅ Test background job scheduled for Auction ID {$auctionId}. Check logs to confirm.\n";
    }
}

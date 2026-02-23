<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BidderDashboard extends CI_Controller
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
        if ($userRoleId != 2) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }

        // Check phone verification for bidders
        $this->phoneVerified = $this->checkPhoneVerification($userSessionData['user_id']);

        if (!$this->phoneVerified && !$this->input->is_ajax_request()) {
            $currentMethod = $this->router->fetch_method();
            if ($currentMethod !== 'bidderDashboard') {
                redirect(base_url('show-dashboard/dashboard'));
                exit;
            }
        }
    }

    private function checkPhoneVerification($userId)
    {
        // Get user's phone verification status
        $user = $this->db->select('phone_verified')->where('id', $userId)->get('tbl_users')->row();
        
        if (!$user || !$user->phone_verified) {
            // Store intended destination
            $this->session->set_userdata('phone_verify_redirect', current_url());
            
            // Set flag to show modal
            $this->session->set_userdata('show_phone_verification', true);

            return false;
        }

        // Clean up flags if already verified
        $this->session->unset_userdata('show_phone_verification');
        $this->session->unset_userdata('phone_verify_redirect');

        return true;
    }

    public function bidderDashboard()
    {
        $baseScripts = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/bidder/auction.js'),
            base_url('assets/js/ajax/dashboard/bidder/dashboard.js'),
        ];

        $this->load->model('UserModel');
        $userSession = $this->session->userdata('auction_user_session_data');
        $user_id = $userSession['user_id'];
        $userProfile = $this->UserModel->getBidderProfileById($user_id);
        $userPhone = $userProfile['cell_phone'] ?? '';

        if (!$this->phoneVerified) {
            $data = [
                'scripts' => $baseScripts,
                'user_phone' => $userPhone,
            ];

            $this->load->view('layout/head');
            $this->load->view('layout/header');
            $this->load->view('dashboard/bidder/locked', $data);
            $this->load->view('layout/footer');
            $this->load->view('layout/scripts', $data);
            return;
        }

        // --- Vault Stats ---
        $this->load->model('CoinModel');
        $this->load->model('VaultModel');
        $this->load->model('SubscriptionGiveawayModel');

        $data = [
            'scripts' => $baseScripts,
            'user_phone' => $userPhone,
        ];

        $data['user_coins'] = $this->CoinModel->getBalance($user_id);

        // Get entries from both vault and giveaway draws
        $vault_entries = 0;
        $giveaway_entries = 0;

        $vaultDraw = $this->VaultModel->getActiveDraw();
        if ($vaultDraw) {
            $vault_entries = $this->VaultModel->getUserEntries($user_id, $vaultDraw->id);
        }

        $giveawayDraw = $this->SubscriptionGiveawayModel->get_active_draw();
        if ($giveawayDraw) {
            $giveaway_entries = $this->SubscriptionGiveawayModel->get_user_total_entries($user_id, $giveawayDraw['id']);
        }

        $data['user_entries'] = $vault_entries + $giveaway_entries;
        
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/bidder/index', $data); // Pass $data to view
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }


    public function bidderCoinShop()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/bidder/script.js'), // Ensuring navigation script is loaded
        ];

        $userSession = $this->session->userdata('auction_user_session_data');
        $user_id = $userSession['user_id'];

        $this->load->model('VaultModel');
        $this->load->model('CoinModel');
        $this->load->model('UserModel');
        $this->load->model('SubscriptionGiveawayModel');

        $data['coin_balance'] = $this->CoinModel->getBalance($user_id);
        $data['coin_history'] = $this->CoinModel->getHistory($user_id, 50); // Fetch recent 50 transactions
        $data['coin_packages'] = $this->VaultModel->getCoinPackages();
        $data['cash_packages'] = $this->VaultModel->getCashPackages();
        $vaultDraw = $this->VaultModel->getActiveDraw();
        $giveawayDraw = $this->SubscriptionGiveawayModel->get_active_draw();

        $activeDraw = $vaultDraw ? (object)$vaultDraw : null;

        if ((!$activeDraw || empty($activeDraw->prize_value_cents)) && !empty($giveawayDraw)) {
            $activeDraw = (object)[
                'prize_value_cents' => $giveawayDraw['prize_amount_cents'] ?? ($giveawayDraw['prize_value_cents'] ?? null),
                'title' => $giveawayDraw['title'] ?? null,
            ];
        }

        $data['active_draw'] = $activeDraw;

        // Get User Info
        $user = $this->db->get_where('tbl_users', ['id' => $user_id])->row();
        $data['user_tier'] = strtolower($user->subscription_tier ?? 'none');
        $data['payment_methods'] = $this->UserModel->getBidderPaymentMethodById($user_id);

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/bidder/coin_shop', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function showOwnerContactAfterPayment()
    {
        header('Content-Type: application/json');

        /** ------------------------
         * 1️⃣ Request Method Check
         * ------------------------ */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                "status"  => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            return;
        }

        /** ------------------------
         * 2️⃣ AJAX Request Check
         * ------------------------ */
        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode([
                "status"  => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            return;
        }

        /** ------------------------
         * 3️⃣ Session Check
         * ------------------------ */
        $session = $this->session->userdata('auction_user_session_data');
        if (!$session) {
            http_response_code(401);
            echo json_encode([
                "status"  => "error",
                "message" => "User is not logged in. Please log in to access this endpoint."
            ], JSON_PRETTY_PRINT);
            return;
        }

        $userId    = $session['user_id'];
        $userRole  = $session['role_id'];

        if ($userRole != 2) {
            http_response_code(403);
            echo json_encode([
                "status"  => "error",
                "message" => "Access denied. Only bidder users can perform this action."
            ], JSON_PRETTY_PRINT);
            return;
        }

        /** ------------------------
         * 4️⃣ Read JSON Body
         * ------------------------ */
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                "status"  => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            return;
        }

        $auctionId = isset($input['auctionId']) ? (int)$input['auctionId'] : 0;
        $invoiceId = isset($input['invoiceId']) ? (int)$input['invoiceId'] : 0;

        if ($auctionId <= 0 || $invoiceId <= 0) {
            http_response_code(400);
            echo json_encode([
                "status"  => "error",
                "message" => "Invalid or missing auctionId or invoiceId."
            ], JSON_PRETTY_PRINT);
            return;
        }

        /** ------------------------
         * 5️⃣ Load Models
         * ------------------------ */
        $this->load->model('AuctionModel');
        $this->load->model('InvoiceModel');
        $this->load->model('StorageFacilityModel');
        $this->load->model('CompanyModel');
        $this->load->model('UserModel');

        /** ------------------------
         * 6️⃣ Business Logic
         * ------------------------ */
        $auction = $this->AuctionModel->getAuctionById($auctionId);
        if (!$auction) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => "Auction not found."
            ], JSON_PRETTY_PRINT);
            return;
        }

        $payment = $this->InvoiceModel->getInvoiceStripePaymentLogsByInvoiceId($invoiceId);
        if (!$payment) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => "Payment not completed or invoice not found."
            ], JSON_PRETTY_PRINT);
            return;
        }

        $facility = $this->StorageFacilityModel->getStorageFacilityInformationByAuctionId($auctionId);
        if (!$facility || empty($facility['storage_facility_id'])) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => "No storage facility linked to this auction."
            ], JSON_PRETTY_PRINT);
            return;
        }

        $facilityId = $facility['storage_facility_id'];
        $facilityInfo = $this->StorageFacilityModel->getStorageFacilityById($facilityId);
        if (!$facilityInfo) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => "Storage facility details not found."
            ], JSON_PRETTY_PRINT);
            return;
        }

        $company = $this->StorageFacilityModel->getCompanyIdByStorageFacilityById($facilityId);
        if (!$company || empty($company['company_id'])) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => "No company associated with this facility."
            ], JSON_PRETTY_PRINT);
            return;
        }

        $companyId = $company['company_id'];
        $companyInfo = $this->CompanyModel->getCompanyById($companyId);
        if (!$companyInfo) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => "Company details not found."
            ], JSON_PRETTY_PRINT);
            return;
        }

        $user = $this->CompanyModel->getUserInformationByCompanyId($companyId);
        if (!$user || empty($user['user_id'])) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => "Owner user information not found."
            ], JSON_PRETTY_PRINT);
            return;
        }

        $owner = $this->UserModel->getFacilityOwnerInformationById($user['user_id']);
        if (!$owner) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => "Owner details not found."
            ], JSON_PRETTY_PRINT);
            return;
        }

        /** ------------------------
         * 7️⃣ Success Response
         * ------------------------ */
        $ownerEmail = $owner['email'];
        if (empty($ownerEmail)) {
            $ownerEmail = $companyInfo['email'] ?? $facilityInfo['contact_email'] ?? $facilityInfo['additional_email'];
        }

        $responseData = [
            "company_name"             => $companyInfo['name'],
            "facility_name"            => $facilityInfo['name'],
            "facility_website"         => $facilityInfo['website'],
            "facility_contact_email"   => $facilityInfo['contact_email'],
            "facility_additional_email" => $facilityInfo['additional_email'],
            "facility_office_hours"    => $facilityInfo['office_hours'],
            "owner_name"               => $owner['firstname'] . ' ' . $owner['lastname'],
            "owner_email"              => $ownerEmail,
            "owner_phone"              => $owner['phone']
        ];

        http_response_code(200);
        echo json_encode([
            "status"  => "success",
            "message" => "Owner contact details fetched successfully.",
            "data"    => $responseData
        ], JSON_PRETTY_PRINT);
    }


    public function bidderAccount()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/bidder/account.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/bidder/account');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }


    public function bidderPaymentMethod()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/bidder/card_validation.js'),
            base_url('assets/js/ajax/dashboard/bidder/stripe.js'),
            base_url('assets/js/ajax/dashboard/bidder/payment.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/bidder/payment');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function bidderMembership()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('subscription/membership');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function bidderInvoices()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/bidder/invoices.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/bidder/invoices');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }
    public function bidderNotification()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/bidder/notifications.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/bidder/notifications');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }





    public function storageFacilityDashboard()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/auction.js'),
            base_url('assets/js/ajax/dashboard/storage_facility/dashboard.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/storage_facility/index');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function storageFacilityAccount()
    {
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

    public function getCoinHistory()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $session = $this->session->userdata('auction_user_session_data');
        if (!$session) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
            exit;
        }

        $userId = $session['user_id'];
        $this->load->model('CoinModel');

        // DataTables parameters
        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $search = $this->input->post('search')['value'] ?? '';
        $orderColumn = $this->input->post('order')[0]['column'] ?? 0;
        $orderDir = $this->input->post('order')[0]['dir'] ?? 'desc';

        $totalRecords = $this->CoinModel->countAllHistory($userId);
        $filteredRecords = $this->CoinModel->countFilteredHistory($search, $userId);
        $data = $this->CoinModel->getFilteredHistory($start, $length, $search, $orderColumn, $orderDir, $userId);

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
        exit;
    }
}

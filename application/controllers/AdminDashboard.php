<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminDashboard extends CI_Controller
{

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
        if ($userRoleId != 4) {
            redirect(base_url()); // Redirect to the default controller
            return;
        }
    }

    public function adminDashboard()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/dashboard.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/index');
        $this->load->view('layout/scripts', $data);
    }


    public function adminAccount()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/account.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/account');
        $this->load->view('layout/scripts', $data);
    }

    public function platformSettings()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/platform_settings.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/platform_settings');
        $this->load->view('layout/scripts', $data);
    }

    public function manageBidders()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/manage_bidders.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/manage_bidders');
        $this->load->view('layout/scripts', $data);
    }

    public function manageFacilities()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/manage_facilities.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/manage_facilities');
        $this->load->view('layout/scripts', $data);
    }

    public function manageAuctions()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/manage_auctions.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/manage_auctions');
        $this->load->view('layout/scripts', $data);
    }

    public function manageStaff()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/manage_staff.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/manage_staff');
        $this->load->view('layout/scripts', $data);
    }

    public function prizeDrawer()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/prize_drawer.js'),
        ];
        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/prize_drawer');
        $this->load->view('layout/scripts', $data);
    }

    public function manageVideos()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/videos.js'),
        ];
        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/videos');
        $this->load->view('layout/scripts', $data);
    }

    public function manageEconomy()
    {
        $this->load->model('CoinModel');
        $data['rules'] = $this->CoinModel->get_rules();
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/economy.js'),
        ];
        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/economy', $data);
        $this->load->view('layout/scripts', $data);
    }

    public function manageGiveawayDraws()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
        ];
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/admin/giveaway_draws', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    public function manageSubscriptions()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
        ];
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('dashboard/admin/manage_subscriptions', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }

    // API Methods for Bidder Management
    public function getBiddersList()
    {
        $this->load->model('UserModel');
        
        $search = $this->input->post('search');
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');

        // Get bidders with role_id = 2
        $this->db->select('u.id, u.first_name, u.last_name, uc.email, l.phone,
            COALESCE(COUNT(DISTINCT b.auction_id), 0) as total_bids,
            COALESCE(COUNT(DISTINCT aw.id), 0) as total_wins,
            COALESCE(AVG(wb.bid_amount), 0) as avg_win_price,
            COALESCE(w.balance, 0) as wallet_balance,
            s.name as status_name,
            s.id as status_id');
        $this->db->from('tbl_users u');
        $this->db->join('tbl_user_credentials uc', 'uc.user_id = u.id', 'left');
        $this->db->join('tbl_user_role_relationship urr', 'urr.user_id = u.id', 'left');
        $this->db->join('tbl_user_location_relationship ulr', 'ulr.user_id = u.id', 'left');
        $this->db->join('tbl_locations l', 'l.id = ulr.location_id', 'left');
        $this->db->join('tbl_bids b', 'b.user_id = u.id', 'left');
        $this->db->join('tbl_auction_won aw', 'aw.user_id = u.id', 'left');
        $this->db->join('tbl_bids wb', 'wb.auction_id = aw.auction_id AND wb.user_id = aw.user_id', 'left');
        $this->db->join('tbl_user_wallet_relationship w', 'w.user_id = u.id', 'left');
        $this->db->join('tbl_user_status_relationship usr', 'usr.user_id = u.id', 'left');
        $this->db->join('tbl_status_types s', 's.id = usr.status_type_id', 'left');
        $this->db->where('urr.role_id', 2); // Bidders only

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('u.first_name', $search);
            $this->db->or_like('u.last_name', $search);
            $this->db->or_like('uc.email', $search);
            $this->db->group_end();
        }

        $this->db->group_by(['u.id', 'u.first_name', 'u.last_name', 'uc.email', 'l.phone', 'w.balance', 's.name', 's.id']);
        
        // Get total count (more reliable way for GROUP BY in CI3)
        $totalRecordsQuery = clone $this->db;
        $totalRecords = $this->db->query("SELECT COUNT(*) as count FROM (" . $totalRecordsQuery->get_compiled_select() . ") as temp_table")->row()->count;

        // Apply pagination
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $bidders = $query->result_array();

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $bidders
        ]);
    }

    public function updateBidder()
    {
        $userId = $this->input->post('user_id');
        $firstName = $this->input->post('first_name');
        $lastName = $this->input->post('last_name');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');

        $this->load->model('UserModel');

        // Update user details
        $this->db->where('id', $userId);
        $this->db->update('tbl_users', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update email
        $this->db->where('user_id', $userId);
        $this->db->update('tbl_user_credentials', [
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update phone
        $this->db->select('location_id');
        $this->db->from('tbl_user_location_relationship');
        $this->db->where('user_id', $userId);
        $locationRel = $this->db->get()->row_array();

        if ($locationRel) {
            $this->db->where('id', $locationRel['location_id']);
            $this->db->update('tbl_locations', [
                'phone' => $phone,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Bidder updated successfully']);
    }

    public function addBidderCredit()
    {
        $userId = $this->input->post('user_id');
        $creditAmount = $this->input->post('credit_amount');
        $note = $this->input->post('note');

        // Create a transaction record
        $this->db->insert('tbl_user_wallet_relationship', [
            'user_id' => $userId,
            'amount' => $creditAmount,
            'transaction_type_id' => 1, // Credit
            'wallet_type_id' => 1, // General
            'notes' => $note,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode(['success' => true, 'message' => 'Credit added successfully']);
    }

    public function toggleBidderStatus()
    {
        $userId = $this->input->post('user_id');
        $statusId = $this->input->post('status_id'); // 1 = Active, 2 = Blocked

        $this->db->where('user_id', $userId);
        $this->db->update('tbl_user_status_relationship', [
            'status_type_id' => $statusId,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    }

    // API Methods for Facility Management
    public function getFacilitiesList()
    {
        $search = $this->input->post('search');
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');

        // Get facilities with company info
        $this->db->select('c.id as company_id, c.name as company_name, 
            u.first_name, u.last_name, uc.email,
            COUNT(DISTINCT sf.id) as total_locations,
            COALESCE(SUM(a.outstanding_dept), 0) as total_debt,
            s.name as status_name,
            s.id as status_id');
        $this->db->from('tbl_companies c');
        $this->db->join('tbl_user_company_relationship ucr', 'ucr.company_id = c.id', 'left');
        $this->db->join('tbl_users u', 'u.id = ucr.user_id', 'left');
        $this->db->join('tbl_user_credentials uc', 'uc.user_id = u.id', 'left');
        $this->db->join('tbl_storage_facility_company_relationship sfcr', 'sfcr.company_id = c.id', 'left');
        $this->db->join('tbl_storage_facilities sf', 'sf.id = sfcr.storage_facility_id', 'left');
        $this->db->join('tbl_auction_storage_facility_relationship asfr', 'asfr.storage_facility_id = sf.id', 'left');
        $this->db->join('tbl_auctions a', 'a.id = asfr.auction_id', 'left');
        $this->db->join('tbl_user_status_relationship usr', 'usr.user_id = u.id', 'left');
        $this->db->join('tbl_status_types s', 's.id = usr.status_type_id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('c.name', $search);
            $this->db->or_like('uc.email', $search);
            $this->db->group_end();
        }

        $this->db->group_by(['c.id', 'c.name', 'u.first_name', 'u.last_name', 'uc.email', 's.name', 's.id']);
        
        // Get total count (more reliable way for GROUP BY in CI3)
        $totalRecordsQuery = clone $this->db;
        $totalRecords = $this->db->query("SELECT COUNT(*) as count FROM (" . $totalRecordsQuery->get_compiled_select() . ") as temp_table")->row()->count;

        // Apply pagination
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $facilities = $query->result_array();

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $facilities
        ]);
    }

    public function getFacilityLocations()
    {
        $companyId = $this->input->post('company_id');

        $this->db->select('sf.id, sf.name, l.city, l.state, l.street_address');
        $this->db->from('tbl_storage_facilities sf');
        $this->db->join('tbl_storage_facility_company_relationship sfcr', 'sfcr.storage_facility_id = sf.id', 'left');
        $this->db->join('tbl_storage_facility_location_relationship sflr', 'sflr.storage_facility_id = sf.id', 'left');
        $this->db->join('tbl_locations l', 'l.id = sflr.location_id', 'left');
        $this->db->where('sfcr.company_id', $companyId);

        $query = $this->db->get();
        $locations = $query->result_array();

        echo json_encode(['success' => true, 'locations' => $locations]);
    }

    public function updateFacility()
    {
        $companyId = $this->input->post('company_id');
        $companyName = $this->input->post('company_name');
        $email = $this->input->post('email');

        // Update company name
        $this->db->where('id', $companyId);
        $this->db->update('tbl_companies', [
            'name' => $companyName,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update email
        $this->db->select('user_id');
        $this->db->from('tbl_user_company_relationship');
        $this->db->where('company_id', $companyId);
        $userRel = $this->db->get()->row_array();

        if ($userRel) {
            $this->db->where('user_id', $userRel['user_id']);
            $this->db->update('tbl_user_credentials', [
                'email' => $email,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Facility updated successfully']);
    }

    public function toggleFacilityStatus()
    {
        $companyId = $this->input->post('company_id');
        $statusId = $this->input->post('status_id'); // 1 = Active, 2 = Blocked

        // Get user ID from company
        $this->db->select('user_id');
        $this->db->from('tbl_user_company_relationship');
        $this->db->where('company_id', $companyId);
        $userRel = $this->db->get()->row_array();

        if ($userRel) {
            $this->db->where('user_id', $userRel['user_id']);
            $this->db->update('tbl_user_status_relationship', [
                'status_type_id' => $statusId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    }

    // API Methods for Auctions Management
    public function getAuctionsList()
    {
        ob_start();
        try {
            $search = $this->input->post('search');
            $statusFilter = $this->input->post('status_filter');
            $draw = intval($this->input->post('draw'));
            $start = intval($this->input->post('start'));
            $length = intval($this->input->post('length'));

            $this->db->distinct();
            $this->db->select('a.id as auction_id, a.unit_number, a.outstanding_dept,
                a.starting_price, a.start_date, a.end_date,
                sf.name as facility_name,
                l.city, l.state,
                ast.name as status_name,
                ast.id as status_id,
                (SELECT COALESCE(MAX(bid_amount), a.starting_price) FROM tbl_bids WHERE auction_id = a.id) as current_price,
                a.is_staged');
            $this->db->from('tbl_auctions a');
            $this->db->join('tbl_auction_storage_facility_relationship asfr', 'asfr.auction_id = a.id', 'left');
            $this->db->join('tbl_storage_facilities sf', 'sf.id = asfr.storage_facility_id', 'left');
            $this->db->join('tbl_storage_facility_location_relationship sflr', 'sflr.storage_facility_id = sf.id', 'left');
            $this->db->join('tbl_locations l', 'l.id = sflr.location_id', 'left');
            $this->db->join('tbl_auction_status_relationship asr', 'asr.auction_id = a.id', 'left');
            $this->db->join('tbl_auction_types ast', 'ast.id = asr.auction_type_id', 'left');

            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('a.id', $search);
                $this->db->or_like('sf.name', $search);
                $this->db->or_like('l.city', $search);
                $this->db->group_end();
            }

            if (!empty($statusFilter)) {
                $this->db->where('ast.id', $statusFilter);
            }

            // Get total count
            $totalQuery = clone $this->db;
            $totalRecords = $totalQuery->count_all_results('', false);

            // Apply pagination and ordering
            $this->db->limit($length, $start);
            $this->db->order_by('a.id', 'DESC');
            $query = $this->db->get();
            
            if (!$query) {
                $error = $this->db->error();
                throw new Exception("Database error: " . $error['message']);
            }

            $auctions = $query->result_array();

            ob_clean();
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => $draw,
                    'recordsTotal' => intval($totalRecords),
                    'recordsFiltered' => intval($totalRecords),
                    'data' => $auctions
                ]));
        } catch (Exception $e) {
            ob_clean();
            log_message('error', 'AdminDashboard::getAuctionsList Error: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => intval($draw ?? 0),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => $e->getMessage()
                ]));
        }
    }

    public function updateAuction()
    {
        $auctionId = $this->input->post('auction_id');
        $outstandingDebt = $this->input->post('outstanding_debt');
        $startingPrice = $this->input->post('starting_price');
        $statusId = $this->input->post('status_id');
        $isStaged = $this->input->post('is_staged');

        // Update auction details
        $this->db->where('id', $auctionId);
        $this->db->update('tbl_auctions', [
            'outstanding_dept' => $outstandingDebt,
            'starting_price' => $startingPrice,
            'is_staged' => $isStaged ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update status if provided
        if ($statusId) {
            $this->db->where('auction_id', $auctionId);
            $this->db->update('tbl_auction_status_relationship', [
                'auction_type_id' => $statusId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Auction updated successfully']);
    }

    public function cancelAuction()
    {
        $auctionId = $this->input->post('auction_id');

        // Update auction status to cancelled (assuming status ID 10 is cancelled)
        $this->db->where('auction_id', $auctionId);
        $this->db->update('tbl_auction_status_relationship', [
            'auction_type_id' => 10, // Cancelled status
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode(['success' => true, 'message' => 'Auction cancelled successfully']);
    }

    // Bidder Subscriptions Management
    public function bidderSubscriptions()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/bidder_subscriptions.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/bidder_subscriptions');
        $this->load->view('layout/scripts', $data);
    }

    public function getSubscriptionsList()
    {
        $search = $this->input->post('search');
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');

        // Get bidders with active subscriptions
        $this->db->select('u.id as user_id, u.first_name, u.last_name, uc.email,
            usr.subscription_id, usr.plan_name, usr.billing_cycle,
            usr.payment_status, usr.next_due_date, usr.amount');
        $this->db->from('tbl_users u');
        $this->db->join('tbl_user_credentials uc', 'uc.user_id = u.id', 'left');
        $this->db->join('tbl_user_subscription_relationship usr', 'usr.user_id = u.id', 'left');
        $this->db->join('tbl_user_role_relationship urr', 'urr.user_id = u.id', 'left');
        $this->db->where('urr.role_id', 2); // Bidders only
        $this->db->where('usr.subscription_id IS NOT NULL'); // Only subscribed users

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('u.first_name', $search);
            $this->db->or_like('u.last_name', $search);
            $this->db->or_like('uc.email', $search);
            $this->db->group_end();
        }

        // Get total count
        $totalQuery = clone $this->db;
        $totalRecords = $totalQuery->count_all_results('', false);

        // Apply pagination
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $subscriptions = $query->result_array();

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $subscriptions
        ]);
    }

    public function changeSubscriptionPlan()
    {
        $userId = $this->input->post('user_id');
        $planName = $this->input->post('plan_name');
        $billingCycle = $this->input->post('billing_cycle');

        // Update subscription details
        $this->db->where('user_id', $userId);
        $this->db->update('tbl_user_subscription_relationship', [
            'plan_name' => $planName,
            'billing_cycle' => $billingCycle,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode(['success' => true, 'message' => 'Subscription plan updated successfully']);
    }

    public function cancelSubscription()
    {
        $userId = $this->input->post('user_id');

        // Delete or mark subscription as cancelled
        $this->db->where('user_id', $userId);
        $this->db->delete('tbl_user_subscription_relationship');

        echo json_encode(['success' => true, 'message' => 'Subscription cancelled successfully']);
    }

    // Bulk Credit Management
    public function bulkCredit()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/bulk_credit.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/bulk_credit');
        $this->load->view('layout/scripts', $data);
    }

    public function getBulkCreditBidders()
    {
        $location = $this->input->post('location');
        $bidFilter = $this->input->post('bid_filter'); // 'with_bids', 'without_bids', 'all'
        $winFilter = $this->input->post('win_filter'); // 'with_wins', 'without_wins', 'all'

        $this->db->select('u.id, u.first_name, u.last_name, uc.email, l.city, l.state,
            COALESCE(COUNT(DISTINCT b.auction_id), 0) as total_bids,
            COALESCE(COUNT(DISTINCT aw.id), 0) as total_wins,
            COALESCE(w.balance, 0) as wallet_balance');
        $this->db->from('tbl_users u');
        $this->db->join('tbl_user_credentials uc', 'uc.user_id = u.id', 'left');
        $this->db->join('tbl_user_role_relationship urr', 'urr.user_id = u.id', 'left');
        $this->db->join('tbl_user_location_relationship ulr', 'ulr.user_id = u.id', 'left');
        $this->db->join('tbl_locations l', 'l.id = ulr.location_id', 'left');
        $this->db->join('tbl_bids b', 'b.user_id = u.id', 'left');
        $this->db->join('tbl_auction_won aw', 'aw.user_id = u.id', 'left');
        $this->db->join('tbl_user_wallet_relationship w', 'w.user_id = u.id', 'left');
        $this->db->where('urr.role_id', 2); // Bidders only

        // Apply location filter
        if (!empty($location)) {
            $this->db->where('l.city', $location);
        }

        $this->db->group_by(['u.id', 'u.first_name', 'u.last_name', 'uc.email', 'l.city', 'l.state', 'w.balance']);

        // Apply bid filter
        if ($bidFilter == 'with_bids') {
            $this->db->having('total_bids >', 0);
        } elseif ($bidFilter == 'without_bids') {
            $this->db->having('total_bids', 0);
        }

        // Apply win filter
        if ($winFilter == 'with_wins') {
            $this->db->having('total_wins >', 0);
        } elseif ($winFilter == 'without_wins') {
            $this->db->having('total_wins', 0);
        }

        $query = $this->db->get();
        $bidders = $query->result_array();

        echo json_encode(['success' => true, 'bidders' => $bidders]);
    }

    public function applyBulkCredit()
    {
        $bidderIds = $this->input->post('bidder_ids'); // Array of user IDs
        $creditAmount = $this->input->post('credit_amount');
        $expirationDays = $this->input->post('expiration_days');
        $note = $this->input->post('note');

        if (empty($bidderIds) || !is_array($bidderIds)) {
            echo json_encode(['success' => false, 'message' => 'No bidders selected']);
            return;
        }

        $expirationDate = date('Y-m-d H:i:s', strtotime("+{$expirationDays} days"));
        $successCount = 0;

        foreach ($bidderIds as $userId) {
            // Create a transaction record
            $this->db->insert('tbl_user_wallet_relationship', [
                'user_id' => $userId,
                'amount' => $creditAmount,
                'transaction_type_id' => 1, // Credit
                'wallet_type_id' => 1, // General
                'expires_at' => $expirationDate,
                'notes' => $note,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $successCount++;
        }

        echo json_encode([
            'success' => true,
            'message' => "Successfully added credit to {$successCount} bidders"
        ]);
    }

    // Bidder Fee Subscriptions Management
    public function bidderFeeSubscriptions()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/bidder_fee_subscriptions.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/bidder_fee_subscriptions');
        $this->load->view('layout/scripts', $data);
    }

    public function getBidderPlansList()
    {
        // Get all bidder fee reduction plans
        $this->db->select('*');
        $this->db->from('tbl_bidder_subscription_plans');
        $this->db->order_by('fee_reduction', 'ASC');
        $query = $this->db->get();
        $plans = $query->result_array();

        // Get base fee from admin settings (same as Platform Commission on dashboard)
        $this->db->select('commission_value');
        $this->db->from('tbl_system_commission_settings');
        $this->db->where('id', 1);
        $baseFeeQuery = $this->db->get();
        $baseFee = $baseFeeQuery->row_array();

        echo json_encode([
            'success' => true,
            'plans' => $plans,
            'base_fee' => $baseFee['commission_value'] ?? 17.5
        ]);
    }

    public function createBidderPlan()
    {
        $planName = $this->input->post('plan_name');
        $feeReduction = $this->input->post('fee_reduction');
        $price28Days = $this->input->post('price_28_days');
        $priceYear = $this->input->post('price_year');

        $this->db->insert('tbl_bidder_subscription_plans', [
            'plan_name' => $planName,
            'fee_reduction' => $feeReduction,
            'price_28_days' => $price28Days,
            'price_year' => $priceYear,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode(['success' => true, 'message' => 'Bidder plan created successfully']);
    }

    public function updateBidderPlan()
    {
        $planId = $this->input->post('plan_id');
        $feeReduction = $this->input->post('fee_reduction');
        $price28Days = $this->input->post('price_28_days');
        $priceYear = $this->input->post('price_year');

        $this->db->where('id', $planId);
        $this->db->update('tbl_bidder_subscription_plans', [
            'fee_reduction' => $feeReduction,
            'price_28_days' => $price28Days,
            'price_year' => $priceYear,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode(['success' => true, 'message' => 'Bidder plan updated successfully']);
    }

    public function deleteBidderPlan()
    {
        $planId = $this->input->post('plan_id');

        $this->db->where('id', $planId);
        $this->db->delete('tbl_bidder_subscription_plans');

        echo json_encode(['success' => true, 'message' => 'Bidder plan deleted successfully']);
    }

    public function updateBaseFee()
    {
        $baseFee = $this->input->post('base_fee');

        // Update commission_value in admin settings (same as Platform Commission on dashboard)
        $this->db->where('id', 1);
        $this->db->update('tbl_system_commission_settings', [
            'commission_value' => $baseFee,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode(['success' => true, 'message' => 'Platform commission updated successfully']);
    }

    // Facility Plan Definitions
    public function facilityPlanDefinitions()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/facility_plan_definitions.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/facility_plan_definitions');
        $this->load->view('layout/scripts', $data);
    }

    public function getFacilityPlansList()
    {
        // Get all facility subscription plans with features
        $this->db->select('fp.*, GROUP_CONCAT(ff.feature_name SEPARATOR "||") as features');
        $this->db->from('tbl_facility_subscription_plans fp');
        $this->db->join('tbl_facility_plan_features ff', 'ff.plan_id = fp.id', 'left');
        $this->db->group_by('fp.id');
        $this->db->order_by('fp.monthly_price', 'ASC');
        $query = $this->db->get();
        $plans = $query->result_array();

        // Parse features for each plan
        foreach ($plans as &$plan) {
            $plan['features'] = $plan['features'] ? explode('||', $plan['features']) : [];
        }

        echo json_encode(['success' => true, 'plans' => $plans]);
    }

    public function createFacilityPlan()
    {
        $planName = $this->input->post('plan_name');
        $monthlyPrice = $this->input->post('monthly_price');
        $annualPrice = $this->input->post('annual_price');
        $features = $this->input->post('features'); // Array of feature names

        // Insert plan
        $this->db->insert('tbl_facility_subscription_plans', [
            'plan_name' => $planName,
            'monthly_price' => $monthlyPrice,
            'annual_price' => $annualPrice,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $planId = $this->db->insert_id();

        // Insert features
        if (!empty($features)) {
            foreach ($features as $feature) {
                $this->db->insert('tbl_facility_plan_features', [
                    'plan_id' => $planId,
                    'feature_name' => $feature,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Facility plan created successfully']);
    }

    public function updateFacilityPlan()
    {
        $planId = $this->input->post('plan_id');
        $planName = $this->input->post('plan_name');
        $monthlyPrice = $this->input->post('monthly_price');
        $annualPrice = $this->input->post('annual_price');
        $features = $this->input->post('features'); // Array of feature names

        // Update plan
        $this->db->where('id', $planId);
        $this->db->update('tbl_facility_subscription_plans', [
            'plan_name' => $planName,
            'monthly_price' => $monthlyPrice,
            'annual_price' => $annualPrice,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Delete existing features
        $this->db->where('plan_id', $planId);
        $this->db->delete('tbl_facility_plan_features');

        // Insert new features
        if (!empty($features)) {
            foreach ($features as $feature) {
                $this->db->insert('tbl_facility_plan_features', [
                    'plan_id' => $planId,
                    'feature_name' => $feature,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Facility plan updated successfully']);
    }

    public function deleteFacilityPlan()
    {
        $planId = $this->input->post('plan_id');

        // Delete features first
        $this->db->where('plan_id', $planId);
        $this->db->delete('tbl_facility_plan_features');

        // Delete plan
        $this->db->where('id', $planId);
        $this->db->delete('tbl_facility_subscription_plans');

        echo json_encode(['success' => true, 'message' => 'Facility plan deleted successfully']);
    }

    // Location Analytics
    public function locationAnalytics()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/location_analytics.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/location_analytics');
        $this->load->view('layout/scripts', $data);
    }

    public function getLocationAnalytics()
    {
        $dateRange = $this->input->post('date_range'); // e.g., 30, 60, 90 days
        $region = $this->input->post('region');
        $facilityType = $this->input->post('facility_type');

        // Calculate date filter
        $dateFilter = date('Y-m-d', strtotime("-{$dateRange} days"));

        // Get location-based analytics
        $this->db->select('l.city, l.state,
            COUNT(DISTINCT a.id) as total_auctions,
            COUNT(DISTINCT u.id) as total_signups,
            AVG(COALESCE(wb.bid_amount, 0)) as avg_winning_bid');
        $this->db->from('tbl_locations l');
        $this->db->join('tbl_storage_facilities sf', 'sf.location_id = l.id', 'left');
        $this->db->join('tbl_auction_storage_facility_relationship asfr', 'asfr.storage_facility_id = sf.id', 'left');
        $this->db->join('tbl_auctions a', 'a.id = asfr.auction_id AND a.created_at >= "' . $dateFilter . '"', 'left');
        $this->db->join('tbl_user_location_relationship ulr', 'ulr.user_id = u.id', 'left');
        $this->db->join('tbl_users u', 'u.id = ulr.user_id AND u.created_at >= "' . $dateFilter . '"', 'left');
        $this->db->join('tbl_auction_won aw', 'aw.auction_id = a.id', 'left');
        $this->db->join('tbl_bids wb', 'wb.auction_id = aw.auction_id AND wb.user_id = aw.user_id', 'left');

        if (!empty($region)) {
            $this->db->where('l.state', $region);
        }

        $this->db->group_by('l.city, l.state');
        $this->db->order_by('total_auctions', 'DESC');
        $query = $this->db->get();
        $analytics = $query->result_array();

        // Mock data fallback for demonstration if DB is empty
        if (empty($analytics)) {
            $analytics = [
                ['city' => 'Sydney', 'state' => 'NSW', 'total_auctions' => 142, 'total_signups' => 12500, 'avg_winning_bid' => 450.50],
                ['city' => 'Melbourne', 'state' => 'VIC', 'total_auctions' => 98, 'total_signups' => 8400, 'avg_winning_bid' => 380.20],
                ['city' => 'Brisbane', 'state' => 'QLD', 'total_auctions' => 65, 'total_signups' => 4200, 'avg_winning_bid' => 310.00],
                ['city' => 'Perth', 'state' => 'WA', 'total_auctions' => 42, 'total_signups' => 3100, 'avg_winning_bid' => 290.40],
                ['city' => 'Adelaide', 'state' => 'SA', 'total_auctions' => 31, 'total_signups' => 2500, 'avg_winning_bid' => 275.60]
            ];
        }

        // Calculate totals
        $totalAuctions = array_sum(array_column($analytics, 'total_auctions'));
        $totalSignups = array_sum(array_column($analytics, 'total_signups'));
        $avgBid = $analytics ? array_sum(array_column($analytics, 'avg_winning_bid')) / count($analytics) : 0;

        // Find highest concentration
        $maxCity = $analytics[0] ?? null;
        $concentration = $maxCity ? ($maxCity['total_signups'] / max($totalSignups, 1)) * 100 : 0;

        echo json_encode([
            'success' => true,
            'analytics' => $analytics,
            'summary' => [
                'total_auctions' => $totalAuctions,
                'avg_winning_bid' => round($avgBid, 2),
                'total_signups' => $totalSignups,
                'highest_concentration' => [
                    'city' => $maxCity['city'] ?? 'N/A',
                    'state' => $maxCity['state'] ?? 'N/A',
                    'percentage' => round($concentration, 1)
                ]
            ]
        ]);
    }

    // Sign-Up Bonus Management
    public function signupBonus()
    {
        $data['scripts'] = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/dashboard/admin/signup_bonus.js'),
        ];

        $this->load->view('layout/head');
        $this->load->view('dashboard/admin/signup_bonus');
        $this->load->view('layout/scripts', $data);
    }

    public function getSignupBonus()
    {
        $this->db->select('*');
        $this->db->from('tbl_signup_bonus_settings');
        $this->db->where('id', 1);
        $query = $this->db->get();
        $settings = $query->row_array();

        echo json_encode([
            'success' => true,
            'settings' => $settings
        ]);
    }

    public function updateSignupBonus()
    {
        $enabled = $this->input->post('enabled');
        $amount = $this->input->post('amount');
        $expirationDays = $this->input->post('expiration_days');

        $this->db->where('id', 1);
        $this->db->update('tbl_signup_bonus_settings', [
            'enabled' => $enabled,
            'amount' => $amount,
            'expiration_days' => $expirationDays,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode(['success' => true, 'message' => 'Sign-up bonus settings updated successfully']);
    }

    public function getDashboardStats()
    {
        // 1. Total Facilities (role_id = 3)
        $this->db->select('COUNT(*) as count');
        $this->db->from('tbl_user_role_relationship');
        $this->db->where('role_id', 3);
        $totalFacilities = $this->db->get()->row()->count;

        // 2. Total Bidders (role_id = 2)
        $this->db->select('COUNT(*) as count');
        $this->db->from('tbl_user_role_relationship');
        $this->db->where('role_id', 2);
        $totalBidders = $this->db->get()->row()->count;

        // 3. Active Auctions (auction_type_id = 1)
        $this->db->select('COUNT(*) as count');
        $this->db->from('tbl_auctions a');
        $this->db->join('tbl_auction_status_relationship asr', 'a.id = asr.auction_id', 'inner');
        $this->db->where('asr.auction_type_id', 1);
        $activeAuctions = $this->db->get()->row()->count;

        // 4. Platform Revenue (Sum of winning bids)
        $this->db->select('SUM(b.bid_amount) as total');
        $this->db->from('tbl_auction_won aw');
        $this->db->join('tbl_bids b', 'b.auction_id = aw.auction_id AND b.user_id = aw.user_id', 'inner');
        // Filtering for the actual winning bid amounts
        $revenueResult = $this->db->get()->row();
        $revenue = $revenueResult ? $revenueResult->total : 0;

        echo json_encode([
            'success' => true,
            'stats' => [
                'total_facilities' => intval($totalFacilities),
                'total_bidders' => intval($totalBidders),
                'active_auctions' => intval($activeAuctions),
                'revenue' => number_format((float)($revenue ?? 0), 2, '.', '')
            ]
        ]);
        exit;
    }

    public function getStaffList()
    {
        $search = $this->input->post('search')['value'] ?? null;
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');

        // Select columns
        $this->db->select('u.id, u.first_name, u.last_name, uc.email, r.name as role_name, s.name as status_name');
        $this->db->from('tbl_users u');
        $this->db->join('tbl_user_credentials uc', 'uc.user_id = u.id', 'left');
        $this->db->join('tbl_user_role_relationship urr', 'urr.user_id = u.id', 'left');
        $this->db->join('tbl_role_types r', 'r.id = urr.role_id', 'left');
        $this->db->join('tbl_user_status_relationship usr', 'usr.user_id = u.id', 'left');
        $this->db->join('tbl_status_types s', 's.id = usr.status_type_id', 'left');
        
        // Filter by Admin Roles (IDs 3, 4, 5, 6)
        $this->db->where_in('urr.role_id', [3, 4, 5, 6]);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('u.first_name', $search);
            $this->db->or_like('u.last_name', $search);
            $this->db->or_like('uc.email', $search);
            $this->db->group_end();
        }

        // Get total count
        $totalQuery = clone $this->db;
        $totalRecords = $totalQuery->count_all_results('', false);

        // Apply pagination
        $this->db->limit($length, $start);
        $this->db->order_by('u.id', 'DESC');
        $query = $this->db->get();
        $staff = $query->result_array();

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $staff
        ]);
        exit;
    }

    public function addStaff()
    {
        // Check if user has permission (Super Admin only - role_id 4)
        $userSessionData = $this->session->userdata('auction_user_session_data');
        if($userSessionData['role_id'] != 4) {
             echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
             return;
        }

        $firstName = $this->input->post('first_name');
        $lastName = $this->input->post('last_name');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $roleId = $this->input->post('role_id');
        $password = $this->input->post('password');

        if(empty($firstName) || empty($lastName) || empty($email) || empty($roleId) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            return;
        }

        $this->load->model('UserModel');
        
        // Check if email already exists
        $existingUser = $this->UserModel->get_user_by_email($email);
        if($existingUser) {
            echo json_encode(['success' => false, 'message' => 'Email address already registered']);
            return;
        }

        // 1. Create User in tbl_users
        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_users', $userData);
        $userId = $this->db->insert_id();

        // 2. Create Credentials in tbl_user_credentials
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $credentialsData = [
            'user_id' => $userId,
            'email' => $email,
            'password' => $hashedPassword,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_user_credentials', $credentialsData);

        // 3. Assign Role in tbl_user_role_relationship
        $roleData = [
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_user_role_relationship', $roleData);

        // 4. Assign Status in tbl_user_status_relationship (Active = 1)
        $statusData = [
            'user_id' => $userId,
            'status_type_id' => 1, // Active
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_user_status_relationship', $statusData);

        // 5. Add Location
        $locationData = [
            'phone' => $phone,
            'street_address' => 'N/A',
            'apt_suite' => 'N/A',
            'city' => 'N/A',
            'state' => 'N/A',
            'postal_code' => '0000',
            'country' => 'N/A',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_locations', $locationData);
        $locationId = $this->db->insert_id();

        $userLocationData = [
            'user_id' => $userId,
            'location_id' => $locationId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_user_location_relationship', $userLocationData);

        echo json_encode(['success' => true, 'message' => 'Staff member added successfully']);
        exit;
    }

    public function getStaffDetails($id) {
         $userSessionData = $this->session->userdata('auction_user_session_data');
         if($userSessionData['role_id'] != 4) {
              echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
              return;
         }

         $this->db->select('u.id, u.first_name, u.last_name, uc.email, urr.role_id, l.phone');
         $this->db->from('tbl_users u');
         $this->db->join('tbl_user_credentials uc', 'uc.user_id = u.id', 'left');
         $this->db->join('tbl_user_role_relationship urr', 'urr.user_id = u.id', 'left');
         $this->db->join('tbl_user_location_relationship ulr', 'ulr.user_id = u.id', 'left');
         $this->db->join('tbl_locations l', 'l.id = ulr.location_id', 'left');
         $this->db->where('u.id', $id);
         
         $query = $this->db->get();
         $user = $query->row_array();

         if($user) {
             echo json_encode(['success' => true, 'data' => $user]);
         } else {
             echo json_encode(['success' => false, 'message' => 'User not found']);
         }
    }

    public function updateStaff() {
         $userSessionData = $this->session->userdata('auction_user_session_data');
         if($userSessionData['role_id'] != 4) {
              echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
              return;
         }

        $userId = $this->input->post('user_id');
        $firstName = $this->input->post('first_name');
        $lastName = $this->input->post('last_name');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $roleId = $this->input->post('role_id');
        $password = $this->input->post('password');

        if(empty($userId)) {
             echo json_encode(['success' => false, 'message' => 'User ID is missing']);
             return;
        }

        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $userId);
        $this->db->update('tbl_users', $userData);

        $credentialsData = [
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if(!empty($password)) {
            $credentialsData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $this->db->where('user_id', $userId);
        $this->db->update('tbl_user_credentials', $credentialsData);

        $roleData = [
            'role_id' => $roleId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('user_id', $userId);
        $this->db->update('tbl_user_role_relationship', $roleData);

        $this->db->select('location_id');
        $this->db->where('user_id', $userId);
        $query = $this->db->get('tbl_user_location_relationship');
        $locationRel = $query->row_array();
        
        if($locationRel) {
            $locationData = [
                'phone' => $phone,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->where('id', $locationRel['location_id']);
            $this->db->update('tbl_locations', $locationData);
        }

        echo json_encode(['success' => true, 'message' => 'Staff updated successfully']);
        exit;
    }

    public function toggleStaffStatus() {
         $userSessionData = $this->session->userdata('auction_user_session_data');
         if($userSessionData['role_id'] != 4) {
              echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
              return;
         }

         $userId = $this->input->post('user_id');
         
         if(empty($userId)) {
             echo json_encode(['success' => false, 'message' => 'User ID is missing']);
             return;
         }

         $this->db->where('user_id', $userId);
         $query = $this->db->get('tbl_user_status_relationship');
         $currentStatus = $query->row_array();

         if($currentStatus) {
             $newStatus = ($currentStatus['status_type_id'] == 1) ? 2 : 1;
             
             $this->db->where('user_id', $userId);
             $this->db->update('tbl_user_status_relationship', [
                 'status_type_id' => $newStatus,
                 'updated_at' => date('Y-m-d H:i:s')
             ]);
             
             $msg = ($newStatus == 1) ? 'activated' : 'deactivated';
             echo json_encode(['success' => true, 'message' => "User successfully $msg"]);
         } else {
             echo json_encode(['success' => false, 'message' => 'Status record not found']);
         }
    }
}

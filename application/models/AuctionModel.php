<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuctionModel extends CI_Model
{

    protected $auctions_table = 'tbl_auctions';
    protected $bids_table = 'tbl_bids';

    protected $users_table = 'tbl_users';

    protected $user_passwords_table = 'tbl_user_credentials';
    protected $auction_status_table = 'tbl_auction_status_relationship';
    protected $auction_status_type_table = 'tbl_auction_types';

    protected $auction_category_list_table = "tbl_auction_categories";

    protected $auction_favorites_table = 'tbl_auction_favorites';

    protected $auction_won_table = 'tbl_auction_won';

    protected $auction_status_list_table = 'tbl_status_types';

    protected $auction_view_table = "tbl_auction_views";

    protected $auction_types_list_table = 'tbl_auction_types';

    protected $user_company_relationship_table = 'tbl_user_company_relationship';

    protected $storage_facility_company_relationship_table = 'tbl_storage_facility_company_relationship';

    protected $auction_storage_facility_relationship_table = 'tbl_auction_storage_facility_relationship';

    protected $auction_status_relationship_table = 'tbl_auction_status_relationship';

    protected $auction_category_relationship_table = 'tbl_auction_category_relationship';

    protected $auction_confirmations_table = 'tbl_auction_confirmations';

    protected $auction_confirmation_type_table = 'tbl_auction_confirmation_types';

    protected $storage_facilities_table = 'tbl_storage_facilities';

    protected $storage_facility_location_relationship_table = 'tbl_storage_facility_location_relationship';

    protected $locations_table = 'tbl_locations';

    protected $invoice_auction_relationship_table = 'tbl_invoice_auction_relationship';

    protected $auction_upload_relationship_table = 'tbl_auction_upload_relationship';

    private function ensureAuctionPaymentColumns()
    {
        $columns = [
            'accept_cash' => "ALTER TABLE {$this->auctions_table} ADD COLUMN accept_cash TINYINT(1) NOT NULL DEFAULT 0 AFTER cleaning_deposit_payment_type",
            'accept_card' => "ALTER TABLE {$this->auctions_table} ADD COLUMN accept_card TINYINT(1) NOT NULL DEFAULT 0 AFTER accept_cash"
        ];

        foreach ($columns as $column => $ddl) {
            if (!$this->db->field_exists($column, $this->auctions_table)) {
                $this->db->query($ddl);
            }
        }
    }

    private function ensureAuctionConfirmColumns()
    {
        if (!$this->db->field_exists('auction_id', $this->auction_confirmations_table)) {
            $this->db->query("ALTER TABLE {$this->auction_confirmations_table} ADD COLUMN auction_id INT(11) NOT NULL AFTER winner_id");
        }
    }
    public function getAuctionCount()
    {
        $this->db->from($this->auctions_table . ' a');
        $this->db->join($this->auction_status_table . ' as asr', 'asr.auction_id = a.id', 'left');
        $this->db->where('asr.auction_type_id', 1);
        return $this->db->count_all_results();
    }

    public function countActiveAuctionsByBidder($user_id)
    {
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT a.id) as count');
        $this->db->from($this->bids_table . ' b');
        $this->db->join($this->auctions_table . ' a', 'a.id = b.auction_id', 'left');
        $this->db->join($this->auction_status_table . ' as asr', 'asr.auction_id = a.id', 'left');
        $this->db->where('b.user_id', $user_id);
        $this->db->where('asr.auction_type_id', 1); // 1 = Active
        return $this->db->get()->row()->count;
    }


    public function countAllWatchedAuctionsByUser($user_id)
    {
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT a.id) as count');
        $this->db->from($this->auction_favorites_table . ' af');
        $this->db->join($this->auctions_table . ' a', 'a.id = af.auction_id', 'inner');
        $this->db->join($this->auction_status_table . ' asr', 'asr.auction_id = a.id', 'left');
        $this->db->where('af.user_id', $user_id);
        $this->db->where('asr.auction_type_id !=', 11);

        $result = $this->db->get()->row();
        return $result ? (int)$result->count : 0;
    }


    public function countFilteredWatchedAuctionsByUser($search, $user_id)
    {
        $this->db->select('COUNT(DISTINCT a.id) as count');
        $this->db->from($this->auction_favorites_table . ' af');
        $this->db->join($this->auctions_table . ' a', 'a.id = af.auction_id', 'inner');
        $this->db->where('af.user_id', $user_id);

        if (!empty($search)) {
            $this->db->like('a.id', $search);
        }

        $result = $this->db->get()->row();
        return $result ? (int)$result->count : 0;
    }

    public function countWonAuctionsByBidder($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->count_all_results($this->auction_won_table);
    }

    public function countAllAuctionByBidderUser($user_id)
    {
        // ✅ Get count of DISTINCT auctions where the user has placed a bid
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT a.id) as count');
        $this->db->from($this->bids_table . ' b');
        $this->db->join($this->auctions_table . ' a', 'a.id = b.auction_id', 'left');
        $this->db->join($this->auction_status_table . ' asr', 'asr.auction_id = a.id', 'left');
        $this->db->where('b.user_id', $user_id);
        $this->db->where('asr.auction_type_id !=', 11);

        return $this->db->get()->row()->count;
    }


    public function countFilteredAuctionByBidderUser($search, $user_id)
    {
        $this->db->select('COUNT(DISTINCT b.auction_id) as count'); // ✅ Ensure unique auction count
        $this->db->from($this->bids_table . ' b');
        $this->db->join($this->auctions_table . ' a', 'a.id = b.auction_id', 'left'); // ✅ Fixed join condition
        $this->db->where('b.user_id', $user_id);

        if (!empty($search)) {
            $this->db->like('a.id', $search); // ✅ Example: Search by auction title (Modify as needed)
        }

        return $this->db->get()->row()->count;
    }

    public function getFilteredAuctionByBidderUser($start, $length, $search, $orderColumnIndex, $orderDirIndex, $user_id, $auction_type_ids, $auction_watched = false, $auction_won = false)
    {
        // ✅ Subquery: Get all auction IDs where the user has placed at least one bid
        $userAuctionsSubquery = "
            SELECT DISTINCT auction_id
            FROM tbl_bids
            WHERE user_id = " . $user_id;

        // ✅ Subquery: Get the highest bid placed by this user for each auction
        $latestBidsSubquery = "
            SELECT auction_id, MAX(bid_amount) as my_max_bid
            FROM tbl_bids
            WHERE user_id = " . $user_id . "
            GROUP BY auction_id
        ";

        $this->db->select(
            'a.id as auction_id,
            a.starting_price as auction_start_bid_amount,

            COALESCE(lb.my_max_bid, 0) as my_max_bid,

            (
                SELECT MAX(b3.bid_amount)
                FROM tbl_bids b3
                WHERE b3.auction_id = a.id
            ) as highest_bid_amount,

            COALESCE((SELECT MIN(b4.bid_amount) FROM tbl_bids b4 WHERE b4.auction_id = a.id), a.starting_price) as auction_min_bid_amount,

            COALESCE((SELECT AVG(b5.bid_amount) FROM tbl_bids b5 WHERE b5.auction_id = a.id), a.starting_price) as auction_avg_bid_amount,

            a.start_date as auction_start_date,
            a.start_time as auction_start_time,
            a.end_date as auction_end_date,
            a.end_time as auction_end_time,
            ast.id as auction_type_id,
                ast.name as auction_type_name,
            '
        );

        $this->db->from($this->auctions_table . ' a'); // ✅ Auctions table

        // ✅ Ensure we only filter by auctions the user has bid on unless explicitly viewing watchlist
        if ($auction_watched !== true) {
            $this->db->where("a.id IN ($userAuctionsSubquery)", NULL, FALSE);
        }

        // ✅ Join latest bid per auction for the user
        $this->db->join("($latestBidsSubquery) as lb", 'lb.auction_id = a.id', 'left');

        // ✅ Join auction status table
        $this->db->join($this->auction_status_table . ' as asr', 'asr.auction_id = a.id', 'left');

        // ✅ Join auction status type table
        $this->db->join($this->auction_status_type_table . ' as ast', 'ast.id = asr.auction_type_id', 'left');

        if ($auction_watched == true) {
            $this->db->join($this->auction_favorites_table . ' as af', 'af.auction_id = a.id', 'inner');
            $this->db->where('af.user_id', $user_id);
        } else if ($auction_won == true) {
            $this->db->join($this->auction_won_table . ' as aw', 'aw.auction_id = a.id', 'left');
            $this->db->where("aw.auction_id IN ($userAuctionsSubquery)", NULL, FALSE);

            $this->db->select(
                'aw.id as auction_winner_id,'
            );
        } else {
            $this->db->where_in('asr.auction_type_id', $auction_type_ids);
        }


        if (!empty($search)) {
            $this->db->like('a.id', $search); // ✅ Example: Search by auction title (Modify as needed)
        }

        // ✅ Ensure unique auctions (grouping by auction ID)
        $this->db->group_by('a.id');

        // ✅ Pagination and Ordering (if required)
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function countAllAuctionByStorageFacilityUser($auctionIds)
    {
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT a.id) as count');
        $this->db->from($this->user_company_relationship_table . ' cr');
        $this->db->join($this->storage_facility_company_relationship_table . ' fcr', 'fcr.company_id = cr.company_id', 'left');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asfr', 'asfr.storage_facility_id = fcr.storage_facility_id', 'left');
        $this->db->join($this->auctions_table . ' a', 'a.id = asfr.auction_id', 'left');
        $this->db->join($this->auction_status_table . ' asr', 'asr.auction_id = a.id', 'left');
        $this->db->where_in('a.id', $auctionIds);
        $this->db->where('IFNULL(asr.auction_type_id, 0) != 11', null, false);

        return $this->db->get()->row()->count;
    }

    public function countFilteredAuctionByStorageFacilityUser($search, $auctionIds, $auction_won = false, $auctionTypeId = null, $storageFacilityId = null)
    {
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT a.id) as count');
        $this->db->from($this->user_company_relationship_table . ' cr');
        $this->db->join($this->storage_facility_company_relationship_table . ' fcr', 'fcr.company_id = cr.company_id', 'inner');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asfr', 'asfr.storage_facility_id = fcr.storage_facility_id', 'left');
        $this->db->join($this->auctions_table . ' a', 'a.id = asfr.auction_id', 'left');
        $this->db->join($this->auction_status_table . ' astt', 'astt.auction_id = a.id', 'left');
        $this->db->join($this->auction_status_type_table . ' ast', 'ast.id = astt.auction_type_id', 'left');

        $this->db->where_in('a.id', $auctionIds);

        if ($auction_won == true) {
            $this->db->join($this->auction_won_table . ' as aw', 'aw.auction_id = a.id', 'left');
            $this->db->where_in('aw.auction_id', $auctionIds);

            $this->db->select(
                'aw.id as auction_winner_id,'
            );
        }


        if (!empty($auctionTypeId)) {
            $this->db->where('ast.id', $auctionTypeId);
        } else {
            $this->db->where('IFNULL(ast.id, 0) != 11', null, false);
        }

        if (!empty($storageFacilityId)) {
            $this->db->where('asfr.storage_facility_id', $storageFacilityId);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search);
            $this->db->group_end();
        }

        return $this->db->get()->row()->count;
    }


    public function getFilteredAuctionByStorageFacilityUser($start, $length, $search, $orderColumn, $orderDir, $auctionIds,  $auction_won = false, $auctionTypeId = null, $storageFacilityId = null)
    {
        $this->db->distinct();

        $this->db->select(
            'a.id,
            a.id as auction_id,
            a.unit_number,
            sf.name as storage_facility_name,
            a.starting_price as auction_start_bid_amount,

            (
                SELECT MAX(b3.bid_amount)
                FROM tbl_bids b3
                WHERE b3.auction_id = a.id
            ) as current_bid,
            (
                SELECT MAX(b3.bid_amount)
                FROM tbl_bids b3
                WHERE b3.auction_id = a.id
            ) as highest_bid_amount,

            COALESCE((SELECT MIN(b4.bid_amount) FROM tbl_bids b4 WHERE b4.auction_id = a.id), a.starting_price) as auction_min_bid_amount,

            COALESCE((SELECT AVG(b5.bid_amount) FROM tbl_bids b5 WHERE b5.auction_id = a.id), a.starting_price) as auction_avg_bid_amount,

            a.start_date,
            a.start_time,
            a.end_date,
            a.end_time,
            a.start_date as auction_start_date,
            a.start_time as auction_start_time,
            a.end_date as auction_end_date,
            a.end_time as auction_end_time,
            ast.id as auction_type_id,
            ast.name as auction_type_name
            '
        );




        $this->db->from($this->user_company_relationship_table . ' cr');
        $this->db->join($this->storage_facility_company_relationship_table . ' fcr', 'fcr.company_id = cr.company_id', 'inner');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asfr', 'asfr.storage_facility_id = fcr.storage_facility_id', 'left');
        $this->db->join($this->storage_facilities_table . ' sf', 'sf.id = asfr.storage_facility_id', 'left');
        $this->db->join($this->auctions_table . ' a', 'a.id = asfr.auction_id', 'left');

        // ✅ Join auction status & type
        $this->db->join($this->auction_status_table . ' astt', 'astt.auction_id = a.id', 'left');
        $this->db->join($this->auction_status_type_table . ' ast', 'ast.id = astt.auction_type_id', 'left');

        $this->db->where_in('a.id', $auctionIds);

        if ($auction_won == true) {
            $this->db->join($this->auction_won_table . ' as aw', 'aw.auction_id = a.id', 'left');

            $this->db->where_in('aw.auction_id', $auctionIds);
            $this->db->select(
                'aw.id as auction_winner_id,'
            );
        }

        if (!empty($auctionTypeId)) {
            $this->db->where('ast.id', $auctionTypeId);
        } else {
            $this->db->where('IFNULL(ast.id, 0) != 11', null, false);
        }

        if (!empty($storageFacilityId)) {
            $this->db->where('asfr.storage_facility_id', $storageFacilityId);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search);
            $this->db->group_end();
        }

        // $this->db->group_by('a.id');
        $this->db->order_by($orderColumn, $orderDir);

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return $this->db->get()->result_array();
    }

    public function getConfirmationsForWinnerId($winner_id)
    {
        $this->ensureAuctionConfirmColumns();
        $this->db->select('
            ac.id,
            ac.auction_id,
            ac.winner_id,
            ac.user_id,
            ac.confirmation_type_id,
            act.name as confirmation_type_name,
            ac.confirmed_at
        ');
        $this->db->from($this->auction_confirmations_table . ' as ac');
        $this->db->join($this->auction_confirmation_type_table . ' as act', 'act.id = ac.confirmation_type_id', 'left');
        $this->db->where('ac.winner_id', $winner_id);

        return $this->db->get()->result_array();
    }


    public function insertAuctionConfirmation($auctionConfirmData)
    {
        $this->ensureAuctionConfirmColumns();
        // Check if confirmation already exists for this user + winner
        $this->db->where('winner_id', $auctionConfirmData['winner_id']);
        $this->db->where('user_id', $auctionConfirmData['user_id']);
        $existing = $this->db->get($this->auction_confirmations_table)->row_array();

        if ($existing) {
            // Update the existing confirmation
            $this->db->where('id', $existing['id']); // or use winner_id + user_id combo
            return $this->db->update($this->auction_confirmations_table, [
                'confirmation_type_id' => $auctionConfirmData['confirmation_type_id'],
                'confirmed_at' => $auctionConfirmData['confirmed_at'],
                'updated_at' => $auctionConfirmData['updated_at']
            ]);
        } else {
            // Insert new confirmation
            return $this->db->insert($this->auction_confirmations_table, $auctionConfirmData);
        }
    }

    public function addAuctionToConfirm($auctionConfirmData)
    {
        return $this->insertAuctionConfirmation($auctionConfirmData);
    }








    // Check if auction is in user's favorites
    public function isAuctionFavorite($userId, $auctionId)
    {
        $this->db->where('user_id', $userId);
        $this->db->where('auction_id', $auctionId);
        $query = $this->db->get('tbl_auction_favorites');

        return $query->num_rows() > 0; // Returns true if auction is favorited
    }

    // Add auction to favorites
    public function addToFavorites($userId, $auctionId)
    {
        $data = [
            'user_id' => $userId,
            'auction_id' => $auctionId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')

        ];

        return $this->db->insert('tbl_auction_favorites', $data);
    }

    // Remove auction from favorites
    public function removeFromFavorites($userId, $auctionId)
    {
        $this->db->where('user_id', $userId);
        $this->db->where('auction_id', $auctionId);
        return $this->db->delete('tbl_auction_favorites');
    }

    // Check if user has already bid on this auction
    public function hasUserBidOnAuction($userId, $auctionId)
    {
        $this->db->where('user_id', $userId);
        $this->db->where('auction_id', $auctionId);
        $query = $this->db->get('tbl_bids');

        return $query->num_rows() > 0; // Returns true if a bid exists
    }

    // Add bid to the auction
    public function addBidToAuction($userId, $auctionId, $bidAmount)
    {
        $this->db->trans_start();

        $data = [
            'auction_id' => $auctionId,
            'user_id' => $userId,
            'bid_amount' => $bidAmount,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tbl_bids', $data);

        // NOTE: Coin reward is now handled in Auctions controller with tier-based multiplier

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function addToWonAuction($data)
    {

        return $this->db->insert($this->auction_won_table, $data);
    }

    // get lower bid users
    public function getUsersWithLowerMaxBid($userId, $auctionId, $bidAmount)
    {
        $this->db->select('user_id, MAX(bid_amount) as max_bid');
        $this->db->from('tbl_bids');
        $this->db->where('auction_id', $auctionId);
        $this->db->where('user_id !=', $userId); // Exclude current user
        $this->db->group_by('user_id');
        $this->db->having('max_bid <', $bidAmount);

        return $this->db->get()->result();
    }



    // Get the current highest bid for a specific auction
    public function getCurrentHighestBid($auctionId)
    {
        $this->db->select("COALESCE((SELECT MAX(bid_amount) FROM tbl_bids WHERE auction_id = a.id), a.starting_price) as current_highest_bid", false);
        $this->db->from("tbl_auctions a");
        $this->db->where("a.id", $auctionId);

        $query = $this->db->get();
        $result = $query->row_array();

        return isset($result['current_highest_bid']) ? (float)$result['current_highest_bid'] : 0.00;
    }

    public function getCurrentHighestBidWithUser($auctionId)
    {
        // Get top bidder (if any)
        $this->db->select('user_id, bid_amount');
        $this->db->from('tbl_bids');
        $this->db->where('auction_id', $auctionId);
        $this->db->order_by('bid_amount', 'DESC');
        $this->db->limit(1);

        $query = $this->db->get();
        $result = $query->row_array();

        // No bids? Return starting price, no user
        if (!$result) {
            $this->db->select('starting_price');
            $this->db->from('tbl_auctions');
            $this->db->where('id', $auctionId);
            $auction = $this->db->get()->row();

            return [
                'user_id' => null,
                'current_highest_bid' => $auction ? (float) $auction->starting_price : 0.00
            ];
        }

        // Bid exists: return user ID and bid amount
        return [
            'user_id' => $result['user_id'],
            'current_highest_bid' => (float) $result['bid_amount']
        ];
    }


    public function hasAuctionConfirm($auctionId, $auctionWinnerId, $userId, $confirmationTypeId)
    {
        $this->ensureAuctionConfirmColumns();
        $this->db->from($this->auction_confirmations_table);
        $this->db->where('auction_id', $auctionId);
        $this->db->where('winner_id', $auctionWinnerId);
        $this->db->where('user_id', $userId);

        // Assuming 1 is the ID for 'paid' status
        $this->db->where('confirmation_type_id', 1);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array(); // ✅ return payment row
        } else {
            return null; // ✅ not paid
        }
    }

    public function hasAuctionByBidderConfirm($auctionId, $userId)
    {
        $this->ensureAuctionConfirmColumns();
        $this->db->from($this->auction_confirmations_table);
        $this->db->where('auction_id', $auctionId);
        $this->db->where('user_id', $userId);

        // Assuming 1 is the ID for 'paid' status
        $this->db->where('confirmation_type_id', 1);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array(); // ✅ return payment row
        } else {
            return null; // ✅ not paid
        }
    }

    public function hasAuctionByStorageFacilityConfirm($auctionId, $userId)
    {
        $this->ensureAuctionConfirmColumns();
        $this->db->from($this->auction_confirmations_table);
        $this->db->where('auction_id', $auctionId);
        $this->db->where('user_id', $userId);

        // Assuming 1 is the ID for 'paid' status
        $this->db->where('confirmation_type_id', 1);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array(); // ✅ return payment row
        } else {
            return null; // ✅ not paid
        }
    }

    // --- Master Data Profiles ---
    public function get_master_profiles()
    {
        return $this->db->get('tbl_auction_master_profiles')->result_array();
    }

    public function get_master_profile($id)
    {
        return $this->db->get_where('tbl_auction_master_profiles', ['id' => $id])->row_array();
    }

    public function save_master_profile($data)
    {
        return $this->db->insert('tbl_auction_master_profiles', $data);
    }

    // --- Multi-Location Support ---
    public function set_auction_locations($auction_id, $location_ids)
    {
        $this->db->where('auction_id', $auction_id);
        $this->db->delete('tbl_auction_locations');
        foreach ($location_ids as $loc_id) {
            $this->db->insert('tbl_auction_locations', [
                'auction_id' => $auction_id,
                'location_id' => $loc_id
            ]);
        }
    }

    public function get_auction_locations($auction_id)
    {
        $this->db->select('l.*');
        $this->db->from('tbl_auction_locations al');
        $this->db->join('tbl_locations l', 'al.location_id = l.id');
        $this->db->where('al.auction_id', $auction_id);
        return $this->db->get()->result_array();
    }

    public function getAuctionById($auctionId)
    {
        $this->db->where('id', $auctionId);
        $query = $this->db->get('tbl_auctions');

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function getAuctionWinnerById($auctionWinnerId)
    {
        $this->db->where('id', $auctionWinnerId);
        $query = $this->db->get($this->auction_won_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    /**
     * Get all auctions with optional search, sorting, and limit
     */
    public function getAuctions($limit = null, $auctionId = null, $mode = "view", $search = null, $sortBy = 'a.auction_id', $sortOrder = 'DESC')
    {

        $this->db->select(
            'a.id as auction_id,
            a.description as auction_description,
            a.unit_number as auction_unit_number,
            a.tags as auction_tags,
            COALESCE(view_count.total_views, 0) as auction_total_views,
            c.id as auction_category_id,
            c.name as auction_category_name,
            a.disclose_unit_number_on_website as auction_disclose_unit_number_on_website,
            a.outstanding_dept as auction_outstanding_dept,
            a.starting_price as auction_start_bid_amount,
            COALESCE((SELECT MAX(bid_amount) FROM tbl_bids WHERE auction_id = a.id), a.starting_price) as auction_max_bid_amount,
            COALESCE((SELECT MIN(bid_amount) FROM tbl_bids WHERE auction_id = a.id), a.starting_price) as auction_min_bid_amount,
            COALESCE((SELECT AVG(bid_amount) FROM tbl_bids WHERE auction_id = a.id), a.starting_price) as auction_avg_bid_amount,
            COALESCE((SELECT COUNT(*) FROM tbl_bids WHERE auction_id = a.id), 0) as auction_total_bids,
            COALESCE((SELECT COUNT(DISTINCT user_id) FROM tbl_bids WHERE auction_id = a.id), 0) as auction_total_bidders,
            a.start_date as auction_start_date,
            a.start_time as auction_start_time,
            a.end_date as auction_end_date,
            a.end_time as auction_end_time,
            a.youtube_video_url as auction_youtube_video_url,
            a.cleaning_deposit_price as auction_cleaning_deposit_price,
            a.cleaning_deposit_payment_type as auction_cleaning_deposit_payment_type,
            a.unit_size as auction_unit_size,
            a.additional_features as auction_additional_features,
            asr.auction_type_id as auction_status_id,
            sf.id as auction_storage_facility_id,
            sf.name as auction_storage_facility_name,
            sf.website as auction_storage_facility_website,
            sf.description as auction_storage_facility_description,
            sf.timezone as auction_storage_facility_timezone,
            sf.contact_email as auction_storage_facility_contact_email,
            sf.additional_email as auction_storage_facility_additional_email,
            sf.office_hours as auction_storage_facility_office_hours,
            sf.payout_details as auction_storage_facility_payout_details,
            sf.youtube_video_url as auction_storage_facility_youtube_video_url,
            sf.branding_logo as auction_storage_facility_branding_logo,
            sf.notice_to_buyers as auction_storage_facility_notice_to_buyers,
            l.street_address as auction_storage_facility_street_address,
            l.city as auction_storage_facility_city,
            l.postal_code as auction_storage_facility_postal_code,
            l.state as auction_storage_facility_state,
            l.country as auction_storage_facility_country,
            l.phone as auction_storage_facility_phone,
            a.is_staged
            '
        );

        $this->db->from('tbl_auctions a');
        $this->db->where('a.is_staged', 0); // Exclude staged auctions by default
        
        // Data quality filters - exclude auctions with missing critical data
        $this->db->where('sf.name IS NOT NULL', NULL, FALSE);
        $this->db->where('l.city IS NOT NULL', NULL, FALSE);
        $this->db->where('l.state IS NOT NULL', NULL, FALSE);
        $this->db->where('a.unit_size IS NOT NULL', NULL, FALSE);
        $this->db->where('a.starting_price > 0', NULL, FALSE);

        if (!empty($auctionId)) {
            $this->db->where('a.id', $auctionId);
        }


        // Apply joins
        $this->getAuctionStatus($mode);

        $this->getAuctionCategory();
        //$this->getAuctionUploadImages();
        $this->getAuctionStorageFacility();
        $this->getAuctionStorageFacilityLocation();
        $this->getAuctionTotalViews();

        // Apply search
        if (!empty($search)) {
            $this->applySearch($search);
        }

        // Apply sorting
        $this->applySort($sortBy, $sortOrder);

        // Apply limit
        if (!empty($limit)) {
            $this->applyLimit($limit);
        }

        $query = $this->db->get();
        $auctions = $query->result_array();

        // Attach images to each auction
        foreach ($auctions as &$auction) {
            $auction['auction_cover_images'] = $this->getAuctionImages($auction['auction_id']);
        }

        // Attach images to each auction
        foreach ($auctions as &$storageFacility) {
            $storageFacility['storage_facility_cover_images'] = $this->getStorageFacilityImages($storageFacility['auction_storage_facility_id']);
        }

        return $auctions;
    }


    public function getAllAuctions($start = 0, $end = null, $auctionId = null, $mode = "view", $search = null, $sortBy = 'a.auction_id', $sortOrder = 'DESC')
    {

        $this->db->select(
            'a.id as auction_id,
            a.description as auction_description,
            a.unit_number as auction_unit_number,
            a.tags as auction_tags,
            COALESCE(view_count.total_views, 0) as auction_total_views,
            c.id as auction_category_id,
            c.name as auction_category_name,
            a.disclose_unit_number_on_website as auction_disclose_unit_number_on_website,
            a.outstanding_dept as auction_outstanding_dept,
            a.starting_price as auction_start_bid_amount,
            COALESCE((SELECT MAX(bid_amount) FROM tbl_bids WHERE auction_id = a.id), a.starting_price) as auction_max_bid_amount,
            COALESCE((SELECT MIN(bid_amount) FROM tbl_bids WHERE auction_id = a.id), a.starting_price) as auction_min_bid_amount,
            COALESCE((SELECT AVG(bid_amount) FROM tbl_bids WHERE auction_id = a.id), a.starting_price) as auction_avg_bid_amount,
            COALESCE((SELECT COUNT(*) FROM tbl_bids WHERE auction_id = a.id), 0) as auction_total_bids,
            COALESCE((SELECT COUNT(DISTINCT user_id) FROM tbl_bids WHERE auction_id = a.id), 0) as auction_total_bidders,
            a.start_date as auction_start_date,
            a.start_time as auction_start_time,
            a.end_date as auction_end_date,
            a.end_time as auction_end_time,
            a.youtube_video_url as auction_youtube_video_url,
            a.unit_size as auction_unit_size,
            a.additional_features as auction_additional_features,
            asr.auction_type_id as auction_status_id,
            sf.id as auction_storage_facility_id,
            sf.name as auction_storage_facility_name,
            sf.website as auction_storage_facility_website,
            sf.description as auction_storage_facility_description,
            sf.timezone as auction_storage_facility_timezone,
            sf.contact_email as auction_storage_facility_contact_email,
            sf.additional_email as auction_storage_facility_additional_email,
            sf.office_hours as auction_storage_facility_office_hours,
            sf.payout_details as auction_storage_facility_payout_details,
            sf.youtube_video_url as auction_storage_facility_youtube_video_url,
            sf.branding_logo as auction_storage_facility_branding_logo,
            l.street_address as auction_storage_facility_street_address,
            l.city as auction_storage_facility_city,
            l.postal_code as auction_storage_facility_postal_code,
            l.state as auction_storage_facility_state,
            l.country as auction_storage_facility_country,
            l.phone as auction_storage_facility_phone,
            a.is_staged
            '
        );

        $this->db->from('tbl_auctions a');
        $this->db->where('a.is_staged', 0); // Exclude staged auctions by default
        
        // Data quality filters - exclude auctions with missing critical data
        $this->db->where('sf.name IS NOT NULL', NULL, FALSE);
        $this->db->where('l.city IS NOT NULL', NULL, FALSE);
        $this->db->where('l.state IS NOT NULL', NULL, FALSE);
        $this->db->where('a.unit_size IS NOT NULL', NULL, FALSE);
        $this->db->where('a.starting_price > 0', NULL, FALSE);

        if (!empty($auctionId)) {
            $this->db->where('a.id', $auctionId);
        }


        // Apply joins
        $this->getAuctionStatus("details");

        $this->getAuctionCategory();
        // $this->getAuctionUploadImages();
        $this->getAuctionStorageFacility();
        $this->getAuctionStorageFacilityLocation();
        $this->getAuctionTotalViews();

        // Apply search
        if (!empty($search)) {
            $this->applySearch($search);
        }

        // Apply sorting
        $this->applySort($sortBy, $sortOrder);

        if (!is_null($end) && $end > $start) {
            $length = $end - $start; // How many records to fetch
            $this->db->limit($length, $start); // LIMIT length OFFSET start
        }

        $query = $this->db->get();
        $auctions = $query->result_array();

        // Attach images to each auction
        foreach ($auctions as &$auction) {
            $auction['auction_cover_images'] = $this->getAuctionImages($auction['auction_id']);
        }

        // Attach images to each auction
        foreach ($auctions as &$storageFacility) {
            $storageFacility['storage_facility_cover_images'] = $this->getStorageFacilityImages($storageFacility['auction_storage_facility_id']);
        }


        return $auctions;
    }

    private function getAuctionImages($auctionId)
    {
        $query = $this->db
            ->select('u.id, u.path')
            ->from('tbl_auction_upload_relationship au')
            ->join('tbl_uploads u', 'au.upload_id = u.id')
            ->where('au.auction_id', $auctionId)
            ->get();

        return $query->result_array(); // Returns array of ['id' => ..., 'path' => ...]
    }

    private function getStorageFacilityImages($storage_facility_id)
    {
        $query = $this->db
            ->select('u.id, u.path')
            ->from('tbl_storage_facility_upload_relationship sfu')
            ->join('tbl_uploads u', 'sfu.upload_id = u.id')
            ->where('sfu.storage_facility_id', $storage_facility_id)
            ->get();

        return $query->result_array(); // Returns array of ['id' => ..., 'path' => ...]
    }



    /**
     * Join auctions with their total unique views.
     */
    private function getAuctionTotalViews()
    {
        $this->db->join(
            '(SELECT av.auction_id, COUNT(DISTINCT av.user_id) as total_views
              FROM tbl_auction_views av
              GROUP BY av.auction_id) as view_count',
            'a.id = view_count.auction_id',
            'left'
        );
    }


    /**
     * Join auctions with their status
     */
    private function getAuctionStatus($mode)
    {
        $this->db->join('tbl_auction_status_relationship asr', 'a.id = asr.auction_id', 'inner');

        if ($mode == "details") {
            $this->db->where('asr.auction_type_id', 1); // Only active auctions status

            // Strict Time Filtering
            $now = date('Y-m-d H:i:s');
            $this->db->group_start();
            $this->db->where("STR_TO_DATE(CONCAT(a.start_date, ' ', a.start_time), '%Y-%m-%d %H:%i:%s') <=", $now);
            $this->db->where("STR_TO_DATE(CONCAT(a.end_date, ' ', a.end_time), '%Y-%m-%d %H:%i:%s') >=", $now);
            $this->db->group_end();
        }
    }


    /**
     * Join auctions with their category
     */
    private function getAuctionCategory()
    {
        $this->db->join('tbl_auction_category_relationship acr', 'a.id = acr.auction_id', 'inner');
        $this->db->join('tbl_auction_categories c', 'acr.auction_category_id = c.id', 'left');
    }


    /**
     * Join auctions with their related upload images
     */
    private function getAuctionUploadImages()
    {
        $this->db->join('tbl_auction_upload_relationship au', 'a.id = au.auction_id', 'left');
        $this->db->join('tbl_uploads u', 'au.upload_id = u.id', 'left');
    }

    /**
     * Join auctions with their related storage facility
     */
    private function getAuctionStorageFacility()
    {
        $this->db->join('tbl_auction_storage_facility_relationship asf', 'a.id = asf.auction_id', 'left');
        $this->db->join('tbl_storage_facilities sf', 'asf.storage_facility_id = sf.id', 'left');
    }

    /**
     * Join auctions with their related storage facility location
     */
    private function getAuctionStorageFacilityLocation()
    {
        $this->db->join('tbl_storage_facility_location_relationship sfl', 'sf.id  = sfl.storage_facility_id', 'left');
        $this->db->join('tbl_locations l', 'sfl.location_id = l.id', 'left');
    }

    /**
     * Apply search filters
     */
    private function applySearch($search)
    {
        $this->db->group_start();
        $this->db->like('a.unit_number', $search);
        $this->db->or_like('sf.name', $search);
        $this->db->group_end();
    }

    /**
     * Apply sorting
     */
    private function applySort($sortBy, $sortOder)
    {
        $allowedSortColumns = ['a.id'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'a.id'; // Default sort column
        }
        $sortOder = ($sortOder === 'ASC') ? 'ASC' : 'DESC'; // Default sorting order
        $this->db->order_by($sortBy, $sortOder);
    }

    /**
     * Apply limit
     */
    private function applyLimit($limit)
    {
        $this->db->limit($limit);
    }


    public function getAllStatuses()
    {
        $this->db->select('asl.id as status_id, asl.name as status_name');
        $this->db->from($this->auction_status_list_table . ' asl');

        return $this->db->get()->result_array(); // ✅ Fetches all rows
    }

    public function getAllCategories()
    {
        $this->db->select('c.id as category_id, c.name as category_name');
        $this->db->from($this->auction_category_list_table . ' c');

        return $this->db->get()->result_array(); // ✅ Fetches all rows
    }


    public function getAllTypes($filter_by = "all")
    {
        $this->db->select('atl.id as type_id, atl.name as type_name');
        $this->db->from($this->auction_types_list_table . ' atl');

        if ($filter_by == "specific") {
            $this->db->where_in('atl.id', [1, 6]);
        }

        return $this->db->get()->result_array(); // Fetches all rows
    }

    public function updateAuctionData($auctionId, $auctionData)
    {
        $this->ensureAuctionPaymentColumns();
        $this->db->where('id', $auctionId);
        $this->db->update($this->auctions_table, $auctionData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

    public function updateAuctionStorageFacilityData($auctionId, $auctionStorageFacilityData)
    {
        $this->db->where('auction_id', $auctionId); // Match the user ID
        $this->db->update($this->auction_storage_facility_relationship_table, $auctionStorageFacilityData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

    public function updateAuctionCategoryData($auction_id, $auctionCategoryData)
    {
        $this->db->where('auction_id', $auction_id); // Match the user ID
        $this->db->update($this->auction_category_relationship_table, $auctionCategoryData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }


    public function updateAuctionStatusTypeData($auction_id, $auctionStatusTypeData)
    {
        $this->db->where('auction_id', $auction_id); // Match the user ID
        $this->db->update($this->auction_status_table, $auctionStatusTypeData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

    public function insertAuctionData($auctionData)
    {
        $this->ensureAuctionPaymentColumns();
        $this->db->insert($this->auctions_table, $auctionData);
        return [
            'auction_id' => $this->db->insert_id()
        ];
    }



    public function updateAuctionStatus($auctionId, $statusId)
    {
        $this->db->where('auction_id', $auctionId);
        return $this->db->update($this->auction_status_table, ['auction_type_id' => $statusId]);
    }

    public function insertAuctionStorageFacilityData($auctionStorageFacilityData)
    {
        return $this->db->insert($this->auction_storage_facility_relationship_table, $auctionStorageFacilityData);
    }

    public function insertAuctionCategoryData($auctionCategoryData)
    {
        return $this->db->insert($this->auction_category_relationship_table, $auctionCategoryData);
    }

    public function insertAuctionStatusTypeData($auctionStatusTypeData)
    {
        return $this->db->insert($this->auction_status_table, $auctionStatusTypeData);
    }

    public function insertAuctionViewData($auctionViewData)
    {
        return $this->db->insert($this->auction_view_table, $auctionViewData);
    }


    public function insertAuctionUploadData($auctionUploadData)
    {
        return $this->db->insert_batch($this->auction_upload_relationship_table, $auctionUploadData);
    }

    public function deleteAuctionUploadData($auctionId, $uploadId)
    {
        $this->db->where('auction_id', $auctionId);
        $this->db->where('upload_id', $uploadId);
        return $this->db->delete('tbl_auction_upload_relationship');
    }


    public function deleteAuctionViewDataByUserId($userId)
    {
        return $this->db->delete($this->auction_view_table, ['user_id' => $userId]);
    }

    public function deleteAuctionViewDataByUserAuctionId($userId, $auctionId)
    {
        return $this->db->delete($this->auction_view_table, ['user_id' => $userId, 'auction_id' => $auctionId]);
    }

    public function getAuctionIdsByStorageFacilityIds($storageFacilityIds)
    {
        $this->db->where_in('storage_facility_id', $storageFacilityIds);
        $query = $this->db->get($this->auction_storage_facility_relationship_table);

        if ($query->num_rows() > 0) {
            $results = $query->result_array();
            $auctionIds = [];

            foreach ($results as $row) {
                $auctionIds[] = $row['auction_id'];
            }

            return $auctionIds; // Return array of auction IDs
        } else {
            return []; // Return empty array if not found
        }
    }


    public function getAuctionIdsByUserId($userId)
    {
        $this->db->distinct();
        $this->db->where('user_id', $userId);
        $query = $this->db->get($this->invoice_auction_relationship_table);

        if ($query->num_rows() > 0) {
            $results = $query->result_array();
            $auctionIds = [];

            foreach ($results as $row) {
                $auctionIds[] = $row['auction_id'];
            }

            return $auctionIds; // Return array of auction IDs
        } else {
            return []; // Return empty array if not found
        }
    }

    public function getAuctionsEndingNear($targetHour, $nowTimeFormatted)
    {

        $targetMinutes = $targetHour * 60;
        $escapedNow = $this->db->escape($nowTimeFormatted);

        $this->db->select(

            'a.id as auction_id,
            sf.name as auction_storage_facility_name,
            l.street_address as auction_storage_facility_street_address,
             l.city as auction_storage_facility_city,
            l.state as auction_storage_facility_state'


        );
        $this->db->from($this->auctions_table . ' a');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asf', 'a.id = asf.auction_id', 'left');
        $this->db->join($this->storage_facilities_table . ' sf', 'asf.storage_facility_id = sf.id', 'left');
        $this->db->join($this->storage_facility_location_relationship_table . ' sfl', 'sf.id  = sfl.storage_facility_id', 'left');
        $this->db->join($this->locations_table . ' l', 'sfl.location_id = l.id', 'left');
        $this->db->join($this->auction_status_table . ' as asr', 'asr.auction_id = a.id', 'left');
        $this->db->where('asr.auction_type_id', 1);

        $this->db->where("
        TIMESTAMPDIFF(
            MINUTE,
            STR_TO_DATE({$escapedNow}, '%Y-%m-%d %l:%i %p'),
            STR_TO_DATE(CONCAT(a.end_date, ' ', a.end_time), '%Y-%m-%d %l:%i %p')
        ) = {$targetMinutes}
    ", null, false);


        // 🔍 Show the SQL query
        //echo $this->db->get_compiled_select();
        //print_r($this->db->get()->result());
        // die();

        return $this->db->get()->result();
    }


    public function getAuctionInformationById($auctionId)
    {
        $this->db->select(

            'a.id as auction_id,
            sf.name as auction_storage_facility_name,
            l.street_address as auction_storage_facility_street_address,
             l.city as auction_storage_facility_city,
            l.state as auction_storage_facility_state'


        );
        $this->db->from($this->auctions_table . ' a');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asf', 'a.id = asf.auction_id', 'left');
        $this->db->join($this->storage_facilities_table . ' sf', 'asf.storage_facility_id = sf.id', 'left');
        $this->db->join($this->storage_facility_location_relationship_table . ' sfl', 'sf.id  = sfl.storage_facility_id', 'left');
        $this->db->join($this->locations_table . ' l', 'sfl.location_id = l.id', 'left');
        $this->db->join($this->auction_status_table . ' as asr', 'asr.auction_id = a.id', 'left');
        $this->db->where('asr.auction_type_id', 1);
        $this->db->where('a.id', $auctionId);

        return $this->db->get()->result();
    }

    public function getAuctionWonInformationById($auctionId)
    {
        $this->db->select(

            'a.id as auction_id,
            sf.name as auction_storage_facility_name,
            l.street_address as auction_storage_facility_street_address,
             l.city as auction_storage_facility_city,
            l.state as auction_storage_facility_state'


        );
        $this->db->from($this->auctions_table . ' a');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asf', 'a.id = asf.auction_id', 'left');
        $this->db->join($this->storage_facilities_table . ' sf', 'asf.storage_facility_id = sf.id', 'left');
        $this->db->join($this->storage_facility_location_relationship_table . ' sfl', 'sf.id  = sfl.storage_facility_id', 'left');
        $this->db->join($this->locations_table . ' l', 'sfl.location_id = l.id', 'left');
        $this->db->join($this->auction_status_table . ' as asr', 'asr.auction_id = a.id', 'left');
        $this->db->where('asr.auction_type_id', 9);
        $this->db->where('a.id', $auctionId);

        return $this->db->get()->result();
    }

    public function get_interested_users($auctionId)
    {
        $this->db->select('u.id as user_id, u.first_name as first_name, u.last_name as last_name, up.email as email');
        $this->db->from($this->bids_table . ' b');
        $this->db->join($this->users_table . ' u', 'u.id = b.user_id', 'inner');
        $this->db->join($this->user_passwords_table . ' up', 'up.user_id = u.id', 'inner');
        $this->db->join($this->auction_favorites_table . ' f', 'f.user_id = u.id AND f.auction_id = b.auction_id', 'inner');
        $this->db->where('b.auction_id', $auctionId);
        $this->db->group_by('u.id');

        return $this->db->get()->result();
    }

    public function get_interested_users_by_id($userId, $auctionId)
    {
        $this->db->select('u.id as user_id, u.first_name, u.last_name, up.email');
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_passwords_table . ' up', 'up.user_id = u.id', 'inner');
        $this->db->join($this->auction_favorites_table . ' f', 'f.user_id = u.id AND f.auction_id = ' . (int)$auctionId, 'inner');
        $this->db->where('u.id', $userId);

        return $this->db->get()->row();
    }


    public function getAuctionStatusById($auctionId)
    {
        $this->db->select('asr.auction_type_id as auction_type_id');
        $this->db->from($this->auction_status_relationship_table . ' asr');
        $this->db->where('asr.auction_id', $auctionId);

        return $this->db->get()->row();
    }

    /**
     * Get dashboard performance stats for a storage facility user
     */
    public function getDashboardStats($userId)
    {
        // Get company ID for the user
        $this->db->select('company_id');
        $this->db->from($this->user_company_relationship_table);
        $this->db->where('user_id', $userId);
        $companyRow = $this->db->get()->row_array();

        if (!$companyRow) return null;
        $companyId = $companyRow['company_id'];

        // Get all facility IDs for this company
        $this->db->select('storage_facility_id');
        $this->db->from($this->storage_facility_company_relationship_table);
        $this->db->where('company_id', $companyId);
        $facilityRows = $this->db->get()->result_array();

        $facilityIds = array_column($facilityRows, 'storage_facility_id');
        if (empty($facilityIds)) return null;

        // 1. Total Auctions (All statuses linked to these facilities, excluding deleted)
        $this->db->from($this->auction_storage_facility_relationship_table . ' asfr');
        $this->db->join($this->auction_status_table . ' asr', 'asr.auction_id = asfr.auction_id');
        $this->db->where_in('asfr.storage_facility_id', $facilityIds);
        $this->db->where('asr.auction_type_id !=', 11);
        $totalAuctions = $this->db->count_all_results();

        // 2. Total Sold (Count auctions in tbl_auction_won linked to these facilities)
        $this->db->from($this->auction_won_table . ' aw');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asfr', 'asfr.auction_id = aw.auction_id');
        $this->db->where_in('asfr.storage_facility_id', $facilityIds);
        $totalSold = $this->db->count_all_results();

        // 3. Avg Sale Price (Calculate from tbl_bids for won auctions)
        $this->db->select('AVG((SELECT MAX(bid_amount) FROM tbl_bids WHERE auction_id = aw.auction_id)) as avg_sale_price');
        $this->db->from($this->auction_won_table . ' aw');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asfr', 'asfr.auction_id = aw.auction_id');
        $this->db->where_in('asfr.storage_facility_id', $facilityIds);
        $avgSalePrice = $this->db->get()->row()->avg_sale_price ?? 0;

        // 4. Avg Price / m2 (Simplified check: placeholder for now)
        $avgPricePerM2 = 45.70; 

        return [
            'total_auctions' => $totalAuctions,
            'total_sold' => $totalSold,
            'avg_sale_price' => number_format($avgSalePrice, 2, '.', ''),
            'avg_price_m2' => number_format($avgPricePerM2, 2, '.', '')
        ];
    }

    /**
     * Get financial recovery metrics grouped by facility location
     */
    public function getFinancialRecoveryStats($userId)
    {
        // Get company ID
        $this->db->select('company_id');
        $this->db->from($this->user_company_relationship_table);
        $this->db->where('user_id', $userId);
        $companyRow = $this->db->get()->row_array();
        if (!$companyRow) return [];
        $companyId = $companyRow['company_id'];

        $this->db->select('
            l.city as location_name,
            SUM(a.outstanding_dept) as total_debt,
            SUM((SELECT MAX(bid_amount) FROM tbl_bids WHERE auction_id = a.id)) as recovered_revenue
        ');
        $this->db->from($this->storage_facilities_table . ' sf');
        $this->db->join($this->storage_facility_company_relationship_table . ' sfcr', 'sfcr.storage_facility_id = sf.id');
        $this->db->join($this->storage_facility_location_relationship_table . ' sflr', 'sflr.storage_facility_id = sf.id');
        $this->db->join($this->locations_table . ' l', 'l.id = sflr.location_id');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asfr', 'asfr.storage_facility_id = sf.id');
        $this->db->join($this->auctions_table . ' a', 'a.id = asfr.auction_id');
        $this->db->join($this->auction_won_table . ' aw', 'aw.auction_id = a.id');

        $this->db->where('sfcr.company_id', $companyId);
        $this->db->group_by('l.city');

        $results = $this->db->get()->result_array();

        foreach ($results as &$row) {
            $row['recovery_rate'] = $row['total_debt'] > 0
                ? round(($row['recovered_revenue'] / $row['total_debt']) * 100, 1)
                : 0;
        }

        return $results;
    }

    /**
     * Get sold auction counts per location for bar chart
     */
    public function getAuctionsSoldByLocation($userId)
    {
        // Get company ID
        $this->db->select('company_id');
        $this->db->from($this->user_company_relationship_table);
        $this->db->where('user_id', $userId);
        $companyRow = $this->db->get()->row_array();
        if (!$companyRow) return [];
        $companyId = $companyRow['company_id'];

        $this->db->select('l.city as location_name, COUNT(aw.id) as sold_count');
        $this->db->from($this->storage_facilities_table . ' sf');
        $this->db->join($this->storage_facility_company_relationship_table . ' sfcr', 'sfcr.storage_facility_id = sf.id');
        $this->db->join($this->storage_facility_location_relationship_table . ' sflr', 'sflr.storage_facility_id = sf.id');
        $this->db->join($this->locations_table . ' l', 'l.id = sflr.location_id');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asfr', 'asfr.storage_facility_id = sf.id');
        $this->db->join($this->auction_won_table . ' aw', 'aw.auction_id = asfr.auction_id');

        $this->db->where('sfcr.company_id', $companyId);
        $this->db->group_by('l.city');

        return $this->db->get()->result_array();
    }
    /**
     * Extend auction end time by specified minutes (Anti-Sniping)
     */
    /**
     * Extend auction end time by specified minutes (Anti-Sniping)
     */
    public function extendAuctionTime($auctionId, $minutes)
    {
        $auction = $this->getAuctionById($auctionId);
        if (!$auction) return false;

        try {
            $endDateTime = new DateTime($auction['end_date'] . ' ' . $auction['end_time']);
            $endDateTime->modify("+{$minutes} minutes");

            $data = [
                'end_date' => $endDateTime->format('Y-m-d'),
                'end_time' => $endDateTime->format('H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('id', $auctionId);
            return $this->db->update($this->auctions_table, $data);
        } catch (Exception $e) {
            log_message('error', "Failed to extend auction time for ID {$auctionId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Relist an auction by resetting its status and updating dates
     */
    public function relistAuction($auctionId, $startDate, $startTime, $endDate, $endTime)
    {
        $data = [
            'start_date' => $startDate,
            'start_time' => $startTime,
            'end_date'   => $endDate,
            'end_time'   => $endTime,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $auctionId);
        $this->db->update($this->auctions_table, $data);

        // Reset status to Live (1)
        return $this->updateAuctionStatusTypeData($auctionId, ['auction_type_id' => 1]);
    }

    /**
     * Clone an auction record
     */
    public function cloneAuctionRecord($auctionId)
    {
        $auction = $this->getAuctionById($auctionId);
        if (!$auction) return false;

        // Remove ID and other auto-generated fields
        unset($auction['id']);
        unset($auction['created_at']);
        unset($auction['updated_at']);
        
        // Set as Draft (6)
        $auction['created_at'] = date('Y-m-d H:i:s');
        $auction['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert($this->auctions_table, $auction);
        $newId = $this->db->insert_id();

        if ($newId) {
            // Default to Draft status
            $this->updateAuctionStatusTypeData($newId, ['auction_type_id' => 6]);
            
            // Clone links (Facility, Categories, Locations, Uploads)
            $this->cloneAuctionRelationships($auctionId, $newId);
        }

        return $newId;
    }

    protected function cloneAuctionRelationships($oldId, $newId)
    {
        // 1. Storage Facility
        $facility = $this->db->get_where($this->auction_storage_facility_relationship_table, ['auction_id' => $oldId])->row_array();
        if ($facility) {
            unset($facility['id']);
            $facility['auction_id'] = $newId;
            $this->db->insert($this->auction_storage_facility_relationship_table, $facility);
        }

        // 2. Categories
        $categories = $this->db->get_where($this->auction_category_relationship_table, ['auction_id' => $oldId])->result_array();
        foreach ($categories as $cat) {
            unset($cat['id']);
            $cat['auction_id'] = $newId;
            $this->db->insert($this->auction_category_relationship_table, $cat);
        }

        // 3. Locations
        $locations = $this->db->get_where('tbl_auction_locations', ['auction_id' => $oldId])->result_array();
        foreach ($locations as $loc) {
            $loc['auction_id'] = $newId;
            $this->db->insert('tbl_auction_locations', $loc);
        }

        // 4. Uploads (Photos)
        $uploads = $this->db->get_where($this->auction_upload_relationship_table, ['auction_id' => $oldId])->result_array();
        foreach ($uploads as $up) {
            unset($up['id']);
            $up['auction_id'] = $newId;
            $this->db->insert($this->auction_upload_relationship_table, $up);
        }
    }
}

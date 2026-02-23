<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Delete_all_auctions_and_locations extends CI_Migration {

    public function up()
    {
        // Delete all auction-related data
        $this->db->truncate('tbl_bids');
        $this->db->truncate('tbl_auction_won');
        $this->db->truncate('tbl_auction_upload_relationship');
        $this->db->truncate('tbl_auction_storage_facility_relationship');
        $this->db->truncate('tbl_auction_status_relationship');
        $this->db->truncate('tbl_auction_category_relationship');
        $this->db->truncate('tbl_auctions');
        
        // Delete all location-related data
        $this->db->truncate('tbl_storage_facility_location_relationship');
        $this->db->truncate('tbl_user_location_relationship');
        $this->db->truncate('tbl_locations');
        
        log_message('info', 'Migration: All auctions and locations deleted successfully');
    }

    public function down()
    {
        // This migration cannot be reversed as data is permanently deleted
        log_message('warning', 'Migration rollback attempted: Cannot restore deleted auctions and locations');
    }
}

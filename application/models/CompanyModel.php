<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CompanyModel extends CI_Model
{
    protected $companies_table = 'tbl_companies';

    protected $locations_table = 'tbl_locations';

    protected $company_status_table = 'tbl_company_status_relationship';

    protected $company_location_relationship_table = 'tbl_company_location_relationship';

    protected $company_user_relationship_table = 'tbl_user_company_relationship';

    protected $company_auction_settings_table = 'tbl_company_auction_settings';



    public function getAllCompaniesByUser($user_id)
    {
        $this->db->select('c.id as company_id, c.name as company_name');
        $this->db->from($this->companies_table . ' c');
        $this->db->join($this->company_user_relationship_table . ' cur', 'cur.company_id  = c.id');
        $this->db->join($this->company_status_table . ' cs', 'cs.company_id  = c.id');

        $this->db->where('cur.user_id', $user_id);
        $this->db->where('cs.status_type_id', 1);
        $this->db->where('cur.status_type_id', 1);

        return $this->db->get()->result_array(); // ✅ Fetches all rows
    }
    public function insertCompanyData($companyData)
    {
        $this->db->insert($this->companies_table, $companyData);
        return [
            'company_id' => $this->db->insert_id()
        ];
    }

    public function insertCompanyLocationData($companyLocationData)
    {
        $this->db->insert($this->locations_table, $companyLocationData);
        return [
            'company_location_id' => $this->db->insert_id()
        ];
    }

    public function insertCompanyLocationRelationshipData($companyLocationRelationshipData)
    {
        return $this->db->insert($this->company_location_relationship_table, $companyLocationRelationshipData);
    }

    public function insertCompanyStatusData($companyStatusData)
    {
        return $this->db->insert($this->company_status_table, $companyStatusData);
    }

    public function insertCompanyUserRelationshipData($companyUserRelationshipData)
    {
        return $this->db->insert($this->company_user_relationship_table, $companyUserRelationshipData);
    }

    public function updateCompanyData($company_id, $companyData)
    {
        $this->db->where('id', $company_id); // Match the user ID
        return $this->db->update($this->companies_table, $companyData);
    }


    public function updateCompanyLocationData($location_id, $companyLocationData)
    {
        $this->db->where('id', $location_id); // Match the user ID
        return $this->db->update($this->locations_table, $companyLocationData);
    }

    /**
     * Fetch company info details
     */
    public function getCompanyInformationById($company_id)
    {
        $this->db->select('c.id, c.name as company_name, c.abn as company_abn, c.website as company_website, c.email as company_email, l.street_address as company_street_address, l.apt_suite as company_apt_suite, l.city as company_city, l.postal_code as company_zip, l.country as company_country, l.state as company_state, l.phone as company_cell_phone');
        $this->db->from($this->companies_table . ' c');
        $this->db->join($this->company_location_relationship_table . ' clr', 'clr.company_id = c.id', 'left');
        $this->db->join($this->locations_table . ' l', 'l.id = clr.location_id', 'left');
        $this->db->where('c.id', $company_id);

        return $this->db->get()->row_array();
    }

    public function getCompanyDetailsById($company_id)
    {
        $this->db->select('c.id, c.name as company_name');
        $this->db->from($this->companies_table . ' c');
        $this->db->join($this->company_location_relationship_table . ' clr', 'clr.company_id = c.id', 'left');
        $this->db->join($this->locations_table . ' l', 'l.id = clr.location_id', 'left');
        $this->db->where('c.id', $company_id);

        return $this->db->get()->row_array();
    }

    public function getCompanyById($company_id)
    {
        $this->db->where('id', $company_id);
        $query = $this->db->get($this->companies_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return company details as an array
        } else {
            return null; // company not found
        }
    }

    public function getCompanyByIdWithUserId($company_id, $user_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('status_type_id', 1);
        $query = $this->db->get($this->company_user_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return company details as an array
        } else {
            return null; // company not found
        }
    }

    public function getCompanyByUserId($user_id)
    {
        // Validate user_id to prevent NULL value in query
        if (empty($user_id) || !is_numeric($user_id)) {
            return null;
        }

        $this->db->where('user_id', (int)$user_id);
        $this->db->where('status_type_id', 1);
        $query = $this->db->get($this->company_user_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return company details as an array
        } else {
            return null; // company not found
        }
    }

    public function getCompanyLocationIdByCompanyId($company_id)
    {
        $this->db->where('company_id', $company_id);
        $query = $this->db->get($this->company_location_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return company details as an array
        } else {
            return null; // company not found
        }
    }


    public function getUserInformationByCompanyId($company_id)
    {
        $this->db->where('company_id', $company_id);
        $query = $this->db->get($this->company_user_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return company details as an array
        } else {
            return null; // company not found
        }
    }

    public function getGlobalAuctionRules($company_id)
    {
        $this->db->where('company_id', $company_id);
        return $this->db->get($this->company_auction_settings_table)->row_array();
    }

    public function saveGlobalAuctionRules($company_id, $data)
    {
        $this->db->where('company_id', $company_id);
        $exists = $this->db->get($this->company_auction_settings_table)->num_rows() > 0;

        if ($exists) {
            $this->db->where('company_id', $company_id);
            return $this->db->update($this->company_auction_settings_table, $data);
        } else {
            $data['company_id'] = $company_id;
            return $this->db->insert($this->company_auction_settings_table, $data);
        }
    }
    
    protected $user_notification_settings_table = 'tbl_user_notification_settings';

    public function getNotificationSettings($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->get($this->user_notification_settings_table)->row_array();
    }

    public function saveNotificationSettings($user_id, $data)
    {
        $this->db->where('user_id', $user_id);
        $exists = $this->db->get($this->user_notification_settings_table)->num_rows() > 0;

        if ($exists) {
            $this->db->where('user_id', $user_id);
            return $this->db->update($this->user_notification_settings_table, $data);
        } else {
            $data['user_id'] = $user_id;
            return $this->db->insert($this->user_notification_settings_table, $data);
        }
    }
}

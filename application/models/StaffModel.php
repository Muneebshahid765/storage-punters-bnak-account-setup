<?php
defined('BASEPATH') or exit('No direct script access allowed');

class StaffModel extends CI_Model
{
    protected $users_table = 'tbl_users';
    protected $user_passwords_table = 'tbl_user_credentials';
    protected $user_status_table = 'tbl_user_status_relationship';
    protected $user_roles_table = 'tbl_user_role_relationship';

    protected $roles_table = 'tbl_role_types';
    protected $status_table = 'tbl_status_types';
    protected $storage_facility_staff_relationship_table = 'tbl_storage_facility_staff_relationship';
    protected $storage_facilities_table = 'tbl_storage_facilities';

    protected $user_company_relationship_table = 'tbl_user_company_relationship';
    private function applySearchFilter($search)
    {
        $this->db->group_start();
        $this->db->like('u.first_name', $search);
        $this->db->or_like('u.last_name', $search);
        $this->db->or_like('uc.email', $search);
        $this->db->group_end();
    }

    public function countAllStaff()
    {
        return $this->db->count_all($this->users_table);
    }

    public function countFilteredStaff($search, $company_id)
    {

        $this->db->select('COUNT(*) as count');
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_passwords_table . ' uc', 'uc.user_id = u.id', 'left');
        $this->db->join($this->user_status_table . ' us', 'us.user_id = u.id', 'left');
        $this->db->join($this->status_table . ' s', 's.id = us.status_type_id', 'left');
        $this->db->join($this->user_roles_table . ' ur', 'ur.user_id = u.id', 'left');
        $this->db->join($this->user_company_relationship_table . ' ucr', 'ucr.user_id = u.id', 'left');
        $this->db->where('ucr.company_id', $company_id);
        $this->db->where('ur.role_id', 3);
        if (!empty($search)) {
            $this->applySearchFilter($search);
        }

        return $this->db->get()->row()->count;
    }

    public function getFilteredStaffById($start, $length, $search, $orderColumnIndex, $orderDirIndex, $company_id)
    {


        $orderColumn = 'u.updated_at'; // Default column
        $orderDir = 'DESC'; // Default direction

        $this->db->select('u.id as staff_member_id,
        u.first_name as staff_member_first_name,
        u.last_name as staff_member_last_name,
        uc.email as staff_member_email,
        s.name as staff_member_status,
        u.created_at as staff_member_created_at,
        ucr.manage_all_locations
        ');
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_passwords_table . ' uc', 'uc.user_id = u.id', 'left');
        $this->db->join($this->user_roles_table . ' ur', 'ur.user_id = u.id', 'left');
        $this->db->join($this->user_company_relationship_table . ' ucr', 'ucr.user_id = u.id', 'left');
        $this->db->join($this->status_table . ' s', 's.id = ucr.status_type_id', 'left');
        $this->db->where('ucr.company_id', $company_id);
        $this->db->where('ur.role_id', 3);
        if (!empty($search)) {
            $this->applySearchFilter($search);
        }
        // Apply ordering
        $this->db->order_by($orderColumn, $orderDir);

        $this->db->limit($length, $start);

        return $this->db->get()->result_array();
    }


    public function insert_staff_data($staffMemberData)
    {
        $this->db->insert($this->users_table, $staffMemberData);
        return [
            'staff_member_id' => $this->db->insert_id()
        ];
    }

    public function insert_staff_credentials_data($staffMemberCredentialsData)
    {
        return $this->db->insert($this->user_passwords_table, $staffMemberCredentialsData);
    }

    public function insert_staff_role_data($staffMemberRoleData)
    {
        return $this->db->insert($this->user_roles_table, $staffMemberRoleData);
    }

    public function insert_staff_status_data($staffMemberStatusData)
    {
        return $this->db->insert($this->user_status_table, $staffMemberStatusData);
    }

    public function insert_staff_company_data($staffMemberCompanyData)
    {
        return $this->db->insert($this->user_company_relationship_table, $staffMemberCompanyData);
    }

    public function updateStaffMemberStatusData($staff_member_id,  $company_id, $staffMemberStatusData)
    {
        $this->db->where('user_id', $staff_member_id); // Match the user ID
        $this->db->where('company_id', $company_id); // Match the user ID
        $this->db->update($this->user_company_relationship_table, $staffMemberStatusData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

     public function updateStaffMemberAccountStatusData($staff_member_id, $staffMemberAccountStatusData)
    {
        $this->db->where('user_id', $staff_member_id); // Match the user ID
        $this->db->update($this->user_status_table, $staffMemberAccountStatusData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }
    public function updateStaffMemberData($staff_member_id, $staffMemberData)
    {
        $this->db->where('id', $staff_member_id); // Match the user ID
        $ok = $this->db->update($this->users_table, $staffMemberData);

        // Treat "no changes" as success if the query executed
        return (bool)$ok;
    }

    public function updateStaffManageAllLocations($staff_member_id, $company_id, $manage_all)
    {
        $this->db->where('user_id', $staff_member_id);
        $this->db->where('company_id', $company_id);
        return (bool)$this->db->update($this->user_company_relationship_table, ['manage_all_locations' => $manage_all]);
    }

    public function getStaffAssignedLocations($staff_member_id)
    {
        $this->db->select('storage_facility_id');
        $this->db->from($this->storage_facility_staff_relationship_table);
        $this->db->where('user_id', $staff_member_id);
        $query = $this->db->get();
        $result = $query->result_array();
        return array_column($result, 'storage_facility_id');
    }

    public function assignStaffLocations($staff_member_id, $location_ids)
    {
        // First remove all existing assignments for this staff
        $this->db->where('user_id', $staff_member_id);
        $this->db->delete($this->storage_facility_staff_relationship_table);

        // Then add new assignments
        if (!empty($location_ids)) {
            $data = [];
            foreach ($location_ids as $location_id) {
                $data[] = [
                    'user_id' => $staff_member_id,
                    'storage_facility_id' => $location_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            return $this->db->insert_batch($this->storage_facility_staff_relationship_table, $data);
        }
        return true;
    }

    public function getCompanyLocations($company_id)
    {
        $this->db->select('sf.id, sf.name');
        $this->db->from($this->storage_facilities_table . ' sf');
        $this->db->join('tbl_storage_facility_company_relationship sfcr', 'sfcr.storage_facility_id = sf.id');
        $this->db->where('sfcr.company_id', $company_id);
        return $this->db->get()->result_array();
    }

    public function deleteStaffMember($staff_member_id, $company_id)
    {
        $this->db->trans_start();

        // 1. Remove from staff-specific tables
        $this->db->where('user_id', $staff_member_id);
        $this->db->delete($this->storage_facility_staff_relationship_table);

        $this->db->where('user_id', $staff_member_id);
        $this->db->where('company_id', $company_id);
        $this->db->delete($this->user_company_relationship_table);

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_staff_permissions');

        // 2. Remove from global user relationship tables
        $this->db->where('user_id', $staff_member_id);
        $this->db->delete($this->user_roles_table);

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete($this->user_status_table);

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete($this->user_passwords_table);

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_user_activation_token');

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_user_wallet_relationship');

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_user_profile_relationship');

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_user_alert_settings');

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_user_payment_methods_relationship');

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_user_stripe_relationship');

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_user_subscription_relationship');

        $this->db->where('user_id', $staff_member_id);
        $this->db->delete('tbl_user_bank_informations_relationship');

        // 3. Finally remove user record
        $this->db->where('id', $staff_member_id);
        $this->db->delete($this->users_table);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
    public function getStaffPermissions($staff_member_id, $company_id)
    {
        $this->db->where('user_id', $staff_member_id);
        $this->db->where('company_id', $company_id);
        return $this->db->get('tbl_staff_permissions')->row_array();
    }

    public function updateStaffPermissions($staff_member_id, $company_id, $permissions)
    {
        // Filter payload to match actual table schema (prevents unknown column errors)
        $fields = $this->db->list_fields('tbl_staff_permissions');
        if (is_array($fields) && !empty($fields)) {
            $permissions = array_intersect_key($permissions, array_flip($fields));
        }

        $this->db->where('user_id', $staff_member_id);
        $this->db->where('company_id', $company_id);
        $exists = $this->db->get('tbl_staff_permissions')->num_rows() > 0;

        // Ensure we have a valid storage_facility_id to satisfy FK and NOT NULL constraint if schema fix failed
        if (!isset($permissions['storage_facility_id']) || empty($permissions['storage_facility_id'])) {
            // First check if staff is assigned to any locations
            $assigned = $this->getStaffAssignedLocations($staff_member_id);
            if (!empty($assigned)) {
                $permissions['storage_facility_id'] = $assigned[0];
            } else {
                // Otherwise try to find ANY valid facility for this company
                $facilities = $this->getCompanyLocations($company_id);
                if (!empty($facilities)) {
                    $permissions['storage_facility_id'] = $facilities[0]['id'];
                } else {
                    $permissions['storage_facility_id'] = NULL; // Last resort, might still fail if schema not fixed
                }
            }
        }

        if ($exists) {
            $this->db->where('user_id', $staff_member_id);
            $this->db->where('company_id', $company_id);
            return $this->db->update('tbl_staff_permissions', $permissions);
        } else {
            $permissions['user_id'] = $staff_member_id;
            $permissions['company_id'] = $company_id;
            return $this->db->insert('tbl_staff_permissions', $permissions);
        }
    }
}

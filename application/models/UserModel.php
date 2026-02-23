<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UserModel extends CI_Model
{
    protected $users_table = 'tbl_users';
    protected $user_passwords_table = 'tbl_user_credentials';
    protected $user_status_table = 'tbl_user_status_relationship';
    protected $user_roles_table = 'tbl_user_role_relationship';
    protected $user_wallet_relationship_table = 'tbl_user_wallet_relationship';
    protected $roles_table = 'tbl_role_types';
    protected $status_table = 'tbl_status_types';
    protected $user_profile_table = 'tbl_user_profile_relationship';
    protected $user_alert_settings_table = 'tbl_user_alert_settings';
    protected $tbl_user_payment_methods_table = 'tbl_user_payment_methods_relationship';
    protected $user_location_table = 'tbl_user_location_relationship';

    protected $user_stripe_relationship_table = 'tbl_user_stripe_relationship';
    protected $locations_table = 'tbl_locations';

    protected $user_subscription_relationship_table = "tbl_user_subscription_relationship";
    protected $user_company_relationship_table = 'tbl_user_company_relationship';

    protected $storage_facility_company_relationship_table = 'tbl_storage_facility_company_relationship';

    protected $storage_facilities_table = 'tbl_storage_facilities';
    protected $storage_facility_status_relationship_table = 'tbl_storage_facility_status_relationship';

    protected $activation_token_table = 'tbl_user_activation_token';

    protected $user_bank_informations_relationship_table = "tbl_user_bank_informations_relationship";
    protected $bank_table = "tbl_bank";

    protected $storage_facility_bank_relationship_table = "tbl_storage_facility_bank_relationship";

    private function ensureStripeConnectAccountColumn()
    {
        if (!$this->db->field_exists('stripe_connect_account_id', $this->users_table)) {
            if (!isset($this->dbforge)) {
                $this->load->dbforge();
            }
            $fieldDefinition = [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ];

            if ($this->db->field_exists('stripe_customer_id', $this->users_table)) {
                $fieldDefinition['after'] = 'stripe_customer_id';
            }

            $fields = ['stripe_connect_account_id' => $fieldDefinition];
            $this->dbforge->add_column($this->users_table, $fields);
        }
    }

    /**
     * Authenticate user by email and password
     */

    public function getBankAccountAllStorageFacilityById($bank_id)
    {
        $this->db->select('sft.id as storage_facility_id, sft.name as storage_facility_name');
        $this->db->from($this->storage_facility_bank_relationship_table . ' sfbr');
        $this->db->join($this->storage_facilities_table . ' sft', 'sfbr.storage_facility_id   = sft.id');

        $this->db->where('sfbr.bank_id', $bank_id);
        return $this->db->get()->result_array(); // ✅ Fetches all rows
    }
    public function authenticate($email, $password)
    {
        // DEBUG: Start authentication
        log_message('debug', 'UserModel::authenticate - Starting for email: ' . $email);

        // Step 1: Check if the user exists by email
        $user = $this->get_user_by_email($email);
        if ($user === false) {
            log_message('debug', 'UserModel::authenticate - Email not found');
            return ['success' => false, 'message' => 'email_not_found'];
        }
        log_message('debug', 'UserModel::authenticate - User found, user_id: ' . $user['user_id']);

        // Step 2: Check if the user is active
        if ($this->is_user_active($user['user_id']) === false) {
            log_message('debug', 'UserModel::authenticate - User is inactive');
            return ['success' => false, 'message' => 'user_inactive'];
        }
        log_message('debug', 'UserModel::authenticate - User is active');

        // Step 3: Verify password
        if ($this->verify_password($user['user_id'], $password) === false) {
            log_message('debug', 'UserModel::authenticate - Password incorrect. Expected: ' . $user['password'] . ', Got: ' . $password);
            return ['success' => false, 'message' => 'password_incorrect'];
        }
        log_message('debug', 'UserModel::authenticate - Password verified');


        // Step 4: Check user role
        if ($this->is_user_has_role($user['user_id']) === false) {
            log_message('debug', 'UserModel::authenticate - User has no role');
            return ['success' => false, 'message' => 'role_unauthorized'];
        }
        log_message('debug', 'UserModel::authenticate - User has role');

        // Step 5: Combine all user information and return it
        $user_data = $this->prepare_user_data($user['user_id']);
        if ($user_data === false) {
            log_message('debug', 'UserModel::authenticate - Failed to retrieve user data');
            return ['success' => false, 'message' => 'failed_to_retrieve_user_data'];
        }

        log_message('debug', 'UserModel::authenticate - Authentication successful');
        return ['success' => true, 'message' => 'login_successful', 'data' => $user_data];
    }

    /**
     * Step 1: Get user by email
     */
    public function get_user_by_email($email)
    {
        $query = $this->db->get_where($this->user_passwords_table, ['email' => $email], 1);
        $user = $query->row_array(); // Get result as an array

        if ($user) {
            return $user; // Return user data if found
        }
        return false; // Return false if not found
    }

    /**
     * Step 2: Check if the user is active
     */
    public function is_user_active($user_id)
    {
        $query = $this->db->get_where($this->user_status_table, [
            'user_id' => $user_id,
            'status_type_id' => 1 // Active status
        ], 1);

        $status = $query->row(); // Get row as an object

        return ($status !== null); // Return true if found, false otherwise
    }

    /**
     * Step 3: Verify password
     */
    public function verify_password($user_id, $password)
    {
        $query = $this->db->get_where($this->user_passwords_table, ['user_id' => $user_id], 1);
        $user_password = $query->row();

        // Return false if no password found or passwords don't match
        return ($user_password && $user_password->password === $password);
    }


    /**
     * Step 4: Check if the user has role assigned
     */
    public function is_user_has_role($user_id)
    {
        $query = $this->db->get_where($this->user_roles_table, ['user_id' => $user_id], 1);
        $role = $query->row(); // Get row as an object

        return ($role !== null); // Return true if found, false otherwise
    }

    /**
     * Fetch user role
     */
    public function get_user_role($user_id)
    {
        $this->db->select('r.id as role_id, r.name as role_name');
        $this->db->from($this->roles_table . ' r');
        $this->db->join($this->user_roles_table . ' ur', 'ur.role_id  = r.id');
        $this->db->where('ur.user_id', $user_id);
        $query = $this->db->get();
        $role = $query->row_array(); // Get result as array

        if ($role) {
            return ['success' => true, 'data' => $role]; // Return role data
        }
        return ['success' => false, 'message' => 'role_not_found']; // Return failure if no role found
    }


    /**
     * Fetch user application_status
     */
    public function get_user_application_status($user_id)
    {
        $this->db->select('ast.id as application_status_type_id, ast.name as application_status_type_name');
        $this->db->from($this->application_status_type_table . ' ast');
        $this->db->join($this->application_status_table . ' ur', 'ur.application_status_type_id  = ast.id');
        $this->db->where('ur.user_id', $user_id);
        $query = $this->db->get();
        $application_status = $query->row_array(); // Get result as array

        if ($application_status) {
            return ['success' => true, 'data' => $application_status]; // Return role data
        }
        return ['success' => false, 'message' => 'application_status_not_found']; // Return failure if no role found
    }



    /**
     * Prepare user data for session creation or response
     */
    public function prepare_user_data($user_id)
    {
        // Fetch basic user details
        $user = $this->db->get_where($this->users_table, ['id' => $user_id], 1)->row_array();

        if (!$user) {
            return false; // If user not found
        }

        $tbl_user_credentials = $this->db->get_where($this->user_passwords_table, ['user_id' => $user_id], 1)->row_array();

        if (!$tbl_user_credentials) {
            return false; // If user not found
        }

        // Fetch role
        $role = $this->get_user_role($user_id);
        if ($role['success'] === false) {
            return false;
        }


        // Fetch company and staff location flag
        $company_rel = $this->db->get_where($this->user_company_relationship_table, ['user_id' => $user_id], 1)->row_array();
        $manage_all_locations = $company_rel ? (int)$company_rel['manage_all_locations'] : 0;

        // Prepare the session data array
        return [
            'user_id'      => $user['id'],
            'first_name'    => $user['first_name'],
            'last_name'    => $user['last_name'],
            'email'        => $tbl_user_credentials['email'],
            'role_id'      => $role['data']['role_id'],
            'role_name'    => $role['data']['role_name'],
            'status'       => $this->is_user_active($user['id']) ? 'Active' : 'Inactive',
            'manage_all_locations' => $manage_all_locations
        ];
    }



    /**
     * Fetch user profile details
     */
    public function getBidderProfileById($user_id)
    {
        $this->db->select('u.id, u.first_name as firstname, u.last_name as lastname, psd.email as email, psd.password as password, p.date_of_birth as dob, p.time_zone as timezone, p.hear_us as hear_us, l.street_address as street_address, l.apt_suite as apt_suite, l.city as city, l.postal_code as zip, l.country as country, l.state as state, l.phone as cell_phone');
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_passwords_table . ' psd', 'psd.user_id = u.id', 'left');
        $this->db->join($this->user_profile_table . ' p', 'p.user_id = u.id', 'left');
        $this->db->join($this->user_location_table . ' ul', 'ul.user_id = u.id', 'left');
        $this->db->join($this->locations_table . ' l', 'l.id = ul.location_id', 'left');
        $this->db->where('u.id', $user_id);

        return $this->db->get()->row_array();
    }


    public function getBidderInformationById($user_id)
    {
        // Select only the fields you need
        $this->db->select('u.id as user_id, u.first_name as firstname, u.last_name as lastname, psd.email as email, l.phone as phone');
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_passwords_table . ' psd', 'psd.user_id = u.id', 'left');
        $this->db->join($this->user_location_table . ' ul', 'ul.user_id = u.id', 'left');
        $this->db->join($this->locations_table . ' l', 'l.id = ul.location_id', 'left');
        $this->db->where('u.id', $user_id);

        $query = $this->db->get();

        // Return user info if found
        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null; // No record found
        }
    }


    public function getFacilityOwnerInformationById($user_id)
    {
        // Select only the fields you need
        $this->db->select('u.id as user_id, u.first_name as firstname, u.last_name as lastname, psd.email as email, l.phone as phone');
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_passwords_table . ' psd', 'psd.user_id = u.id', 'left');
        $this->db->join($this->user_location_table . ' ul', 'ul.user_id = u.id', 'left');
        $this->db->join($this->locations_table . ' l', 'l.id = ul.location_id', 'left');
        $this->db->where('u.id', $user_id);

        $query = $this->db->get();

        // Return user info if found
        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null; // No record found
        }
    }

    public function getAdminProfileById($user_id)
    {
        $this->db->select('u.id, u.first_name as firstname, u.last_name as lastname, psd.email as email, psd.password as password, p.date_of_birth as dob, p.time_zone as timezone, p.hear_us as hear_us, l.street_address as street_address, l.apt_suite as apt_suite, l.city as city, l.postal_code as zip, l.country as country, l.state as state, l.phone as cell_phone');
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_passwords_table . ' psd', 'psd.user_id = u.id', 'left');
        $this->db->join($this->user_profile_table . ' p', 'p.user_id = u.id', 'left');
        $this->db->join($this->user_location_table . ' ul', 'ul.user_id = u.id', 'left');
        $this->db->join($this->locations_table . ' l', 'l.id = ul.location_id', 'left');
        $this->db->where('u.id', $user_id);

        return $this->db->get()->row_array();
    }

    private function applySearchFilter($search)
    {
        $this->db->group_start();
        $this->db->or_like('pm.card_holder_first_name', $search);
        $this->db->or_like('pm.card_holder_last_name', $search);
        $this->db->or_like('pm.card_type', $search);
        $this->db->or_like('pm.card_holder_card_number', $search);
        $this->db->or_like('s.name', $search);
        $this->db->group_end();
    }
    public function countAllPaymentMethod()
    {
        return $this->db->count_all($this->tbl_user_payment_methods_table);
    }


    public function countFilteredPaymentMethod($search, $user_id)
    {

        $this->db->select('COUNT(*) as count');
        $this->db->from($this->tbl_user_payment_methods_table . ' pm');
        $this->db->join($this->status_table . ' s', 's.id = pm.status_type_id', 'left');
        $this->db->where('pm.user_id', $user_id);
        if (!empty($search)) {
            $this->applySearchFilter($search);
        }

        return $this->db->get()->row()->count;
    }

    public function getFilteredBidderPaymentMethodById($start, $length, $search, $orderColumnIndex, $orderDirIndex, $user_id)
    {


        $orderColumn = 'pm.updated_at'; // Default column
        $orderDir = 'DESC'; // Default direction

        $this->db->select('pm.id as payment_method_id,
        pm.card_holder_first_name as card_holder_first_name,
        pm.card_holder_last_name as card_holder_last_name,
        pm.card_holder_email as card_holder_email,
        pm.card_holder_phone as card_holder_phone,
        pm.card_billing_address as card_billing_address,
        pm.card_billing_zip as card_billing_zip,
        pm.card_type as card_type,
        RIGHT(pm.card_holder_card_number, 4) as card_holder_card_number,
        pm.card_expiration_month as card_expiration_month,
        pm.card_expiration_year as card_expiration_year,
        s.name as card_status_type_name,
        pm.status_type_id as card_status_type_id,
        pm.updated_at as card_updated_at
        ');
        $this->db->from($this->tbl_user_payment_methods_table . ' pm');
        $this->db->join($this->status_table . ' s', 's.id = pm.status_type_id', 'left');
        $this->db->where('pm.user_id', $user_id);


        if (!empty($search)) {
            $this->applySearchFilter($search);
        }
        // Apply ordering
        $this->db->order_by($orderColumn, $orderDir);

        $this->db->limit($length, $start);

        return $this->db->get()->result_array();
    }


    public function getBidderAllPaymentMethodsById($user_id)
    {


        $this->db->select('pm.id as payment_method_id,
        pm.card_holder_first_name as card_holder_first_name,
        pm.card_holder_last_name as card_holder_last_name,
        pm.card_holder_email as card_holder_email,
        pm.card_holder_phone as card_holder_phone,
        pm.card_billing_address as card_billing_address,
        pm.card_billing_zip as card_billing_zip,
        pm.card_type as card_type,
        RIGHT(pm.card_holder_card_number, 4) as card_holder_card_number,
        pm.card_expiration_month as card_expiration_month,
        pm.card_expiration_year as card_expiration_year,
        s.name as card_status_type_name,
        pm.status_type_id as card_status_type_id,
        pm.updated_at as card_updated_at
        ');
        $this->db->from($this->tbl_user_payment_methods_table . ' pm');
        $this->db->join($this->status_table . ' s', 's.id = pm.status_type_id', 'left');
        $this->db->where('pm.user_id', $user_id);
        $this->db->where('pm.status_type_id', 1);

        return $this->db->get()->result_array();
    }

    public function getBidderPaymentMethodById($user_id)
    {
        $this->db->select('pm.id AS payment_method_id,
                           pm.stripe_payment_method_id AS stripe_payment_method_id,
                           pm.is_default AS card_is_default,
                           pm.updated_at AS card_updated_at,
                           pm.card_type,
                           pm.card_holder_card_number,
                           s.name AS card_status_type_name,
                           pm.status_type_id AS card_status_type_id');
        $this->db->from($this->tbl_user_payment_methods_table . ' pm');
        $this->db->join($this->status_table . ' s', 's.id = pm.status_type_id', 'left');
        $this->db->where('pm.user_id', $user_id);

        return $this->db->get()->result_array();
    }


    public function getStorageFacilityBankAccountById($user_id)
    {
        $this->db->select('bt.id AS bank_id,
                           bt.stripe_bank_account_id AS stripe_bank_account_id,
                           bt.stripe_customer_id AS stripe_customer_id,
                           bt.account_name AS account_name,
                           bt.is_default AS account_is_default,
                           bt.updated_at AS account_updated_at,
                           s.name AS account_status_type_name,
                           bt.status_type_id AS account_status_type_id');
        $this->db->from($this->user_bank_informations_relationship_table . ' bir');
        $this->db->join($this->bank_table  . ' bt', 'bt.id = bir.bank_id', 'left');
        $this->db->join($this->status_table . ' s', 's.id = bt.status_type_id', 'left');
        $this->db->where('bir.user_id', $user_id);

        return $this->db->get()->result_array();
    }

    public function get_user_by_email_address($email)
    {
        return $this->db->get_where($this->user_passwords_table, ['email' => $email])->row_array();
    }

    private function normalizePhoneNumber($phone)
    {
        if (!is_string($phone) || $phone === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 11 && substr($digits, 0, 2) === '61') {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }

    public function isPhoneInUse($phone, $excludeUserId = null)
    {
        $normalized = $this->normalizePhoneNumber($phone);
        if ($normalized === '') {
            return false;
        }

        $this->db->select('l.phone, ul.user_id');
        $this->db->from($this->locations_table . ' l');
        $this->db->join($this->user_location_table . ' ul', 'ul.location_id = l.id', 'inner');
        if ($excludeUserId) {
            $this->db->where('ul.user_id !=', (int)$excludeUserId);
        }
        $query = $this->db->get();

        foreach ($query->result_array() as $row) {
            $existing = $this->normalizePhoneNumber($row['phone']);
            if ($existing !== '' && $existing === $normalized) {
                return true;
            }
        }

        return false;
    }

    public function get_user_role_by_id($user_id)
    {
        return $this->db->get_where($this->user_roles_table, ['user_id' => $user_id])->row_array();
    }

    public function get_user_by_id($user_id)
    {
        return $this->db->get_where('tbl_users', ['id' => $user_id])->row_array();
    }

    public function get_user_by_company_id($user_id, $company_id)
    {

        $this->db->where('user_id', $user_id);
        $this->db->where('company_id', $company_id);
        $query = $this->db->get($this->user_company_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return company details as an array
        } else {
            return null; // company not found
        }
    }

    public function store_password_reset_token($user_id, $token, $expires_at)
    {
        $data = [
            'user_id' => $user_id,
            'reset_token' => $token,
            'reset_token_expires_at' => $expires_at,
        ];

        // Check if a token already exists for the user
        $existing = $this->db->where('user_id', $user_id)->get('tbl_users_reset_tokens')->row_array();

        if ($existing) {
            // Update the existing token
            $this->db->where('user_id', $user_id)->update('tbl_users_reset_tokens', $data);
        } else {
            // Insert a new token
            $this->db->insert('tbl_users_reset_tokens', $data);
        }
    }


    public function get_user_by_reset_token($token)
    {
        $this->db->select('u.*');
        $this->db->from('tbl_users u');
        $this->db->join('tbl_users_reset_tokens urt', 'u.id = urt.user_id');
        $this->db->where('urt.reset_token', $token);
        $this->db->where('urt.reset_token_expires_at >=', date('Y-m-d H:i:s'));
        return $this->db->get()->row_array();
    }

    public function update_password($user_id, $hashed_password, $updated_at)
    {
        $data = [
            'password' => $hashed_password,
            'updated_at' => $updated_at // Set current timestamp
        ];
        $this->db->where('id', $user_id)->update('tbl_user_password', $data);
    }


    public function invalidate_reset_token($user_id)
    {
        $this->db->where('user_id', $user_id)->delete('tbl_users_reset_tokens');
    }

    public function get_user_info($user_id)
    {
        $this->db->select('u.id, u.name, u.surname, u.department, u.email, u.phone_number, u.hourly_rate');
        $this->db->from($this->users_table . ' u');
        $this->db->where('u.id', $user_id);
        $query = $this->db->get();

        return $query->row_array(); // Return user data as an associative array
    }

    public function update_user_info($user_id, $data)
    {
        // Update the `users` table
        $this->db->where('id', $user_id);
        $this->db->update($this->users_table, $data);

        // Update the `user_profile` table
        if (isset($data['profile'])) {
            $profileData = $data['profile'];
            $this->db->where('user_id', $user_id);
            $this->db->update($this->user_profile_table, $profileData);
        }

        return true; // Success
    }

    /**
     * Get user by ID
     */
    public function getUserById($user_id)
    {
        $this->ensureStripeConnectAccountColumn();
        
        $this->db->select('*');
        $this->db->from($this->users_table);
        $this->db->where('id', (int) $user_id);
        $query = $this->db->get();
        
        return $query->row_array();
    }

    /**
     * Generic helper to update tbl_users columns
     */
    public function update_user($user_id, $data = [])
    {
        if (empty($user_id) || empty($data) || !is_array($data)) {
            return false;
        }

        // Ensure the stripe column exists before attempting to update it
        if (array_key_exists('stripe_connect_account_id', $data)) {
            $this->ensureStripeConnectAccountColumn();
        }

        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', (int) $user_id);
        return $this->db->update($this->users_table, $data);
    }

    public function verify_user_password($user_id, $old_password)
    {
        $this->db->select('password');
        $this->db->from($this->user_passwords_table);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();

        $user = $query->row();
        if (!$user) {
            return false; // User not found
        }

        // Directly compare the plain text passwords
        return $user->password === $old_password;
    }

    // Update the user's password
    public function update_user_password($user_id, $new_password)
    {

        $this->db->where('user_id', $user_id);
        return $this->db->update($this->user_passwords_table, ['password' => $new_password]);
    }

    /**
     * Update bidder profile data
     */
    public function updateBidderProfile($userId, $profileData)
    {
        $this->db->where('user_id', $userId);
        return $this->db->update($this->user_profile_table, $profileData);
    }

    public function updateBidderLocation($userId, $locationData)
    {
        // Step 1: Get location_id from tbl_user_location_relationship
        $this->db->select('location_id');
        $this->db->from($this->user_location_table);
        $this->db->where('user_id', $userId);
        $query = $this->db->get();
        $result = $query->row_array();

        if (!$result) {
            return false; // If no location found, return false
        }

        $locationId = $result['location_id'];

        // Step 2: Update tbl_locations using location_id
        $this->db->where('id', $locationId);
        return $this->db->update($this->locations_table, $locationData);
    }


    /**
     * Update bidder password securely
     */
    public function updateBidderPassword($userId, $passwordData)
    {
        $this->db->where('user_id', $userId);
        return $this->db->update($this->user_passwords_table, $passwordData);
    }


    /**
     * Update admin profile data
     */
    public function updateAdminProfile($userId, $profileData)
    {
        $this->db->where('user_id', $userId);
        return $this->db->update($this->user_profile_table, $profileData);
    }

    public function updateAdminLocation($userId, $locationData)
    {
        // Step 1: Get location_id from tbl_user_location_relationship
        $this->db->select('location_id');
        $this->db->from($this->user_location_table);
        $this->db->where('user_id', $userId);
        $query = $this->db->get();
        $result = $query->row_array();

        if (!$result) {
            return false; // If no location found, return false
        }

        $locationId = $result['location_id'];

        // Step 2: Update tbl_locations using location_id
        $this->db->where('id', $locationId);
        return $this->db->update($this->locations_table, $locationData);
    }


    /**
     * Update admin password securely
     */
    public function updateAdminPassword($userId, $passwordData)
    {
        $this->db->where('user_id', $userId);
        return $this->db->update($this->user_passwords_table, $passwordData);
    }


    public function get_customers_count($customer_status)
    {
        $this->db->select('COUNT(u.id) as customer_count'); // Count users
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_status_table . ' ust', 'ust.user_id = u.id', 'left');
        $this->db->join($this->user_roles_table . ' urt', 'urt.user_id = u.id', 'left');
        $this->db->where('urt.user_role_type_id', 2);
        $this->db->where('ust.user_status_type_id', $customer_status);
        $query = $this->db->get();

        return $query->row()->customer_count; // Return the count of users
    }

    public function fetch_dashboard_data()
    {
        return [
            'count_active_customers' => $this->get_customers_count(1),
            'count_inactive_customers' => $this->get_customers_count(2),
        ];
    }



    // Method to fetch customer data and count, combined in a single function
    public function get_users_data($filters = [], $search = null, $limit = 10, $offset = 0)
    {
        // Select customer data
        $this->db->select('u.id, u.name, u.surname, u.email, u.department, u.phone_number, s.name as status_name, ast.name as user_application_status, r.name as role_name, u.hourly_rate, u.created_at');

        $this->db->from('tbl_users u');
        $this->db->join('tbl_user_status_type_relationship us', 'us.user_id = u.id', 'left');
        $this->db->join('tbl_user_status_types s', 's.id = us.user_status_type_id ', 'left');
        $this->db->join('tbl_user_role_type_relationship ur', 'ur.user_id = u.id', 'left');
        $this->db->join('tbl_user_role_types r', 'r.id = ur.user_role_type_id', 'left');
        $this->db->join('tbl_user_application_status_type_relationship uast', 'uast.user_id = u.id', 'left');
        $this->db->join('tbl_application_status_types ast', 'ast.id = uast.application_status_type_id', 'left');


        $this->db->where('ur.user_role_type_id  !=', 1);



        // Apply dynamic filters
        foreach ($filters as $filter_type => $filter_value) {
            switch ($filter_type) {
                case 'Customer Status Based':
                    $this->db->where('s.id', $filter_value); // Filter based on status ID
                    break;
                default:
                    break;
            }
        }

        // Apply search filter if provided
        if ($search) {
            $this->db->group_start();
            $this->db->like('u.name', $search);
            $this->db->or_like('u.surname', $search);
            $this->db->or_like('u.phone_number', $search);
            $this->db->or_like('u.email', $search);
            $this->db->group_end();
        }

        // Order by user.id ASC
        $this->db->order_by('u.id', 'ASC');

        // Pagination
        $this->db->limit($limit, $offset);
        $query = $this->db->get();

        // Fetch the customer data
        $users = $query->result_array();


        // Count the total number of customers
        $this->db->select('COUNT(u.id) as total');
        $this->db->from('tbl_users u');
        $this->db->join('tbl_user_status_type_relationship us', 'us.user_id = u.id', 'left');
        $this->db->join('tbl_user_status_types s', 's.id = us.user_status_type_id ', 'left');
        $this->db->join('tbl_user_role_type_relationship ur', 'ur.user_id = u.id', 'left');
        $this->db->join('tbl_user_role_types r', 'r.id = ur.user_role_type_id', 'left');
        $this->db->where('ur.user_role_type_id  !=', 1);
        // Apply search filter if provided
        if ($search) {
            $this->db->group_start();
            $this->db->like('u.name', $search);
            $this->db->or_like('u.surname', $search);
            $this->db->or_like('u.phone_number', $search);
            $this->db->or_like('u.email', $search);
            $this->db->group_end();
        }

        $count_query = $this->db->get();
        $total_users = $count_query->row()->total;


        // Return the combined data and count
        return ['users' => $users, 'total' => $total_users];
    }


    // Function to update user status
    public function update_user_status($user_id, $status_id)
    {
        $this->db->where('user_id', $user_id); // Match the user ID
        $this->db->update($this->user_status_table, ['status_type_id' => $status_id, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

    // Function to update user status
    public function update_user_application_status($user_id, $status_id)
    {
        $this->db->where('user_id', $user_id); // Match the user ID
        $this->db->update('tbl_user_application_status_type_relationship', ['application_status_type_id' => $status_id, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

    // **📌 Insert User Payment Method Data**
    public function insert_bidder_payment_method_data($paymentMethodInfo)
    {
        $this->db->insert($this->tbl_user_payment_methods_table, $paymentMethodInfo);
        return [
            'payment_method_id' => $this->db->insert_id()
        ];
    }

    // Function to update user payment method
    public function update_bidder_payment_method_data($payment_method_id, $paymentMethodInfo)
    {
        $this->db->where('id', $payment_method_id); // Match the user ID
        $this->db->update($this->tbl_user_payment_methods_table, $paymentMethodInfo);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }


    // Function to update user payment method status
    public function update_bidder_payment_method_status_data($payment_method_id, $paymentMethodDefaultStatusInfo, $paymentMethodAttachDetachStatusData)
    {

        $this->db->where('id', $payment_method_id); // Match the user ID
        $this->db->update($this->tbl_user_payment_methods_table, $paymentMethodDefaultStatusInfo);


        $this->db->where('id', $payment_method_id); // Match the user ID
        $this->db->update($this->tbl_user_payment_methods_table, $paymentMethodAttachDetachStatusData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

    public function update_storage_facility_bank_account_status_data($bank_account_id, $bankAccountDefaultStatusInfo, $bankAccountAttachDetachStatusData)
    {

        $this->db->where('stripe_bank_account_id', $bank_account_id); // Match the user ID
        $this->db->update($this->bank_table, $bankAccountDefaultStatusInfo);


        $this->db->where('stripe_bank_account_id', $bank_account_id); // Match the user ID
        $this->db->update($this->bank_table, $bankAccountAttachDetachStatusData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }
    public function update_bidder_payment_method_card_status_data($user_id, $payment_method_id, $paymentMethodCardStatusData, $paymentMethodOtherCardStatusData)
    {
        // Step 1: Set all other cards for this user to is_default = false
        $this->db->where('user_id', $user_id);
        $this->db->where('id !=', $payment_method_id);
        $this->db->update($this->tbl_user_payment_methods_table, $paymentMethodOtherCardStatusData);

        // Step 2: Update the selected payment method with desired data (e.g., is_default = true)
        $this->db->where('id', $payment_method_id); // Match the user ID
        $this->db->update($this->tbl_user_payment_methods_table, $paymentMethodCardStatusData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }


    public function update_storage_facility_bank_account_account_status_data($user_id, $bank_account_id, $bankAccountAccountStatusData, $bankAccountOtherAccountStatusData)
    {
        // Step 1: Set all other cards for this user to is_default = false
        $this->db->where('user_id', $user_id);
        $this->db->where('stripe_bank_account_id !=', $bank_account_id);
        $this->db->update($this->bank_table, $bankAccountOtherAccountStatusData);

        // Step 2: Update the selected payment method with desired data (e.g., is_default = true)
        $this->db->where('stripe_bank_account_id', $bank_account_id); // Match the user ID
        $this->db->update($this->bank_table, $bankAccountAccountStatusData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

    // **📌 Insert User Registration Data**
    public function insert_user_register_data($userInfo)
    {
        $this->db->insert('tbl_users', $userInfo);
        return [
            'user_id' => $this->db->insert_id()
        ];
    }

    // **📌 Insert User Status**
    public function insert_user_status($userStatusInfo)
    {
        return $this->db->insert('tbl_user_status_type_relationship', $userStatusInfo);
    }

    // **📌 Insert User Application Status**
    public function insert_user_application_status($userApplicationInfo)
    {
        return $this->db->insert('tbl_user_application_status_type_relationship', $userApplicationInfo);
    }

    // **📌 Insert User Role**
    public function insert_user_role($userRoleInfo)
    {
        return $this->db->insert('tbl_user_role_type_relationship', $userRoleInfo);
    }

    // **📌 Insert User Password**
    public function insert_user_password($userPasswordInfo)
    {
        return $this->db->insert('tbl_user_password', $userPasswordInfo);
    }

    // **📌 Store OTP for Verification**
    public function user_verification($userOptData)
    {
        // Invalidate existing OTPs before inserting a new one
        $this->db->where('user_id', $userOptData['user_id'])
            ->where('type', 'Email Verification')
            ->where('status', 'Valid')
            ->update('tbl_user_verifications', ['status' => 'Invalid', 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->db->insert('tbl_user_verifications', $userOptData);
    }

    // **📌 Verify OTP**
    public function email_verify_otp($user_id, $otp, $date)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('type', 'Email Verification');
        $this->db->where('status', 'Valid');
        $this->db->where('otp', $otp);
        $this->db->where('otp_expiry >=', $date);
        return $this->db->get('tbl_user_verifications')->row_array();
    }


    public function getAllFacilitiesByUser($user_id)
    {
        $assignedBankIdSubquery = "(
            SELECT sfbr2.bank_id
            FROM {$this->storage_facility_bank_relationship_table} sfbr2
            JOIN {$this->bank_table} b2 ON b2.id = sfbr2.bank_id
            WHERE sfbr2.storage_facility_id = sft.id AND b2.status_type_id = 1
            LIMIT 1
        )";

        $assignedBankNameSubquery = "(
            SELECT b3.account_name
            FROM {$this->storage_facility_bank_relationship_table} sfbr3
            JOIN {$this->bank_table} b3 ON b3.id = sfbr3.bank_id
            WHERE sfbr3.storage_facility_id = sft.id AND b3.status_type_id = 1
            LIMIT 1
        )";

        $this->db->select(
            'sft.id as storage_facility_id, ' .
            'sft.name as storage_facility_name, ' .
            $assignedBankIdSubquery . ' as assigned_bank_id, ' .
            $assignedBankNameSubquery . ' as assigned_bank_name'
        );
        $this->db->from($this->user_company_relationship_table . ' ucr');
        $this->db->join($this->storage_facility_company_relationship_table . ' sfcr', 'ucr.company_id  = sfcr.company_id');
        $this->db->join($this->storage_facilities_table . ' sft', 'sfcr.storage_facility_id   = sft.id');
        
        // Join status relationship table (toggle state and deletion status)
        $this->db->join($this->storage_facility_status_relationship_table . ' sfs', 'sfs.storage_facility_id = sft.id', 'left');

        $this->db->where('ucr.user_id', $user_id);
        $this->db->where('ucr.status_type_id', 1); // User-Company relationship is active
        
        // Storage Facility must be active (status_type_id = 1)
        // Check sfs table which handles both the toggle and main status
        $this->db->where('sfs.status_type_id', 1);

        return $this->db->get()->result_array(); // ✅ Fetches all rows
    }


    public function getBankAccountById($bankAccountId)
    {
        $this->db->where('stripe_bank_account_id', $bankAccountId);
        $query = $this->db->get($this->bank_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function getPaymentMethodById($paymentMethodId)
    {
        $this->db->where('id', $paymentMethodId);
        $query = $this->db->get($this->tbl_user_payment_methods_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function getUserStripeIdById($userId)
    {
        // Validate userId to prevent NULL value in query
        if (empty($userId) || !is_numeric($userId)) {
            return null;
        }
        
        $this->db->where('user_id', (int)$userId);
        $query = $this->db->get($this->user_stripe_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }
    public function getBidderBalanceById($userId)
    {
        // First check if user has any transaction records
        $this->db->where('user_id', $userId);
        $count = $this->db->count_all_results($this->user_wallet_relationship_table);

        if ($count == 0) {
            return 0; // No records, balance = 0
        }

        // Sum of credits (excluding expired ones)
        $this->db->select_sum('amount');
        $this->db->where('user_id', $userId);
        $this->db->where('transaction_type_id', 1); // 1 = credit
        $this->db->group_start();
            $this->db->where('expires_at >', date('Y-m-d H:i:s'));
            $this->db->or_where('expires_at', NULL);
        $this->db->group_end();
        $credit = $this->db->get($this->user_wallet_relationship_table)->row()->amount;

        // Sum of debits
        $this->db->select_sum('amount');
        $this->db->where('user_id', $userId);
        $this->db->where('transaction_type_id', 2); // 2 = debit
        $debit = $this->db->get($this->user_wallet_relationship_table)->row()->amount;

        // Handle NULLs (when no records exist for one type)
        $credit = $credit ? $credit : 0;
        $debit  = $debit ? $debit : 0;

        // Final balance
        return $credit - $debit;
    }

    // **📌 Insert User Stripe**
    public function insert_user_with_stripe($userStripeData)
    {
        return $this->db->insert($this->user_stripe_relationship_table, $userStripeData);
    }

    public function get_users_information_by_id($userId)
    {
        $this->db->select('u.id, u.first_name, u.last_name, up.email');
        $this->db->from($this->users_table . ' u');
        $this->db->join($this->user_passwords_table . ' up', 'up.user_id = u.id', 'inner');
        $this->db->where('u.id', $userId);

        return $this->db->get()->row();
    }

    public function getUserSubscriptionIdByPriceId($priceId)
    {
        $this->db->where('price_id', $priceId);
        $query = $this->db->get($this->user_subscription_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return subscription details as an array
        } else {
            return null; // Subscription not found
        }
    }

    public function getUserSubscriptionIdById($subscriptionId)
    {
        $this->db->where('subscription_id', $subscriptionId);
        $query = $this->db->get($this->user_subscription_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return subscription details as an array
        } else {
            return null; // Subscription not found
        }
    }
    public function updateUserSubscriptionById($subscriptionId, $updateSubscriptionStatusData)
    {
        $this->db->where('subscription_id', $subscriptionId);
        $this->db->update($this->user_subscription_relationship_table, $updateSubscriptionStatusData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }

    public function getAllBidderSubscriptionPlans()
    {
        $this->db->order_by('fee_reduction', 'ASC'); // Optional: Order by most expensive/best plan?
        return $this->db->get('tbl_bidder_subscription_plans')->result_array();
    }

    public function getUserSubscriptionByUserId($userId)
    {
        $this->db->where('user_id', $userId);
        $this->db->order_by('id', 'DESC'); // Get the latest one
        $this->db->limit(1);
        $query = $this->db->get($this->user_subscription_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return subscription details as an array
        } else {
            return null; // Subscription not found
        }
    }
    public function insertBidderIdentityData($identityData)
    {
        $this->db->insert($this->users_table, $identityData);
        return [
            'user_id' => $this->db->insert_id()
        ];
    }

    public function insertBidderCredentialsData($credentialsData)
    {
        $this->db->insert($this->user_passwords_table, $credentialsData);
    }

    public function insertBidderBalanceData($balanceData)
    {
        $this->db->insert($this->user_wallet_relationship_table, $balanceData);
    }

    public function insertBidderSubscriptionRelationshiData($bidderSubscriptionRelationshipData)
    {
        return $this->db->insert($this->user_subscription_relationship_table, $bidderSubscriptionRelationshipData);
    }
    public function insertBidderRoleData($bidderRoleData)
    {
        $this->db->insert($this->user_roles_table, $bidderRoleData);
    }

    public function insertBidderStatusData($bidderStatusData)
    {
        $this->db->insert($this->user_status_table, $bidderStatusData);
    }

    public function insertBidderProfileData($bidderProfileData)
    {
        $this->db->insert($this->user_profile_table, $bidderProfileData);
    }

    public function insertBidderAlertSettingsData($bidderAlertSettingsData)
    {
        $this->db->insert_batch($this->user_alert_settings_table, $bidderAlertSettingsData);
    }

    public function insertBidderLocationData($bidderLocationData)
    {
        $this->db->insert($this->locations_table, $bidderLocationData);
        return [
            'location_id' => $this->db->insert_id()
        ];
    }

    public function insertBidderStripeData($bidderStripeData)
    {
        return $this->db->insert($this->user_stripe_relationship_table, $bidderStripeData);
    }

    public function insertBidderLocationRelationshipData($bidderLocationRelationshipData)
    {
        $this->db->insert($this->user_location_table, $bidderLocationRelationshipData);
    }

    public function insertActivationTokenData($activationTokenData)
    {
        $this->db->insert($this->activation_token_table, $activationTokenData);
    }


    public function getActivationByToken($token, $type)
    {
        return $this->db->get_where($this->activation_token_table, ['token' => $token, 'token_activation_type_id' => $type])->row_array();
    }

    public function deleteActivationToken($token, $type)
    {
        return $this->db->delete($this->activation_token_table, ['token' => $token, 'token_activation_type_id' => $type]);
    }

    public function getUserByStripeCustomerId($stripe_customer_id)
    {
        $this->db->select('u.id as user_id, u.subscription_tier, usr.stripe_customer_id');
        $this->db->from($this->users_table . ' u');
        $this->db->join('tbl_user_stripe_relationship usr', 'usr.user_id = u.id');
        $this->db->where('usr.stripe_customer_id', $stripe_customer_id);
        return $this->db->get()->row_array();
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class NotificationsModel extends CI_Model
{

    protected $notification_types_table = 'tbl_notification_types';
    protected $preference_types_table = 'tbl_preference_types';

    protected $status_types_table = 'tbl_status_types';

    protected $user_alert_settings_table = 'tbl_user_alert_settings';

    public function get_user_alert_details($user_id)
    {
        $this->db->select('
            uas.id as alert_setting_id,
            uas.user_id,
            pt.name AS preference_type,
            nt.name AS notification_type,
            st.id AS status_type_id,
            st.name AS status_type
        ');
        $this->db->from("$this->user_alert_settings_table uas");
        $this->db->join("$this->preference_types_table pt", 'uas.preference_type_id = pt.id', 'left');
        $this->db->join("$this->notification_types_table nt", 'uas.notification_type_id = nt.id', 'left');
        $this->db->join("$this->status_types_table st", 'uas.status_type_id = st.id', 'left');
        $this->db->where('uas.user_id', $user_id);

        $query = $this->db->get();
        return $query->result_array(); // Returns data as an array
    }

    // Update notification preference
    public function update_notification_status($alert_setting_id, $status_type_id, $updated_at)
    {

        $this->db->where('id', $alert_setting_id); // Match the user ID
        $this->db->update($this->user_alert_settings_table, ['status_type_id' => $status_type_id, 'updated_at' => $updated_at]);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }
}

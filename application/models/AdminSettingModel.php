<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminSettingModel extends CI_Model
{

    protected $system_credit_settings_table = 'tbl_system_credit_settings';
    protected $system_commission_settings_table = 'tbl_system_commission_settings';



    public function get_credit_value($creditId, $creditType)
    {
        $this->db->where('id', $creditId);
        $query = $this->db->get($this->system_credit_settings_table);

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $value = $row['credit_value'];
        } else {
            // Ensure the row exists with default 0
            $insertData = [
                'id' => $creditId,
                'credit_type' => $creditType,
                'credit_value' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if (!$this->db->insert($this->system_credit_settings_table, $insertData)) {
                log_message('error', 'DB Insert Failed in get_credit_value: ' . $this->db->last_query() . ' | Error: ' . json_encode($this->db->error()));
            }
            $value = 0;
        }

        return number_format((float)$value, 2, '.', '');
    }

    public function get_commission_value($commissionId, $commissionType)
    {
        $this->db->where('id', $commissionId);
        $query = $this->db->get($this->system_commission_settings_table);

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $value = $row['commission_value'];
        } else {
            // Ensure the row exists with default 17.5
            $insertData = [
                'id' => $commissionId,
                'commission_type' => $commissionType,
                'commission_value' => 17.5,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if (!$this->db->insert($this->system_commission_settings_table, $insertData)) {
                log_message('error', 'DB Insert Failed in get_commission_value: ' . $this->db->last_query() . ' | Error: ' . json_encode($this->db->error()));
            }
            $value = 17.5;
        }

        return number_format((float)$value, 2, '.', '');
    }


    public function updateCommissionSetting($commissionId, $commissionData)
    {
        $this->db->where('id', $commissionId);
        $query = $this->db->get($this->system_commission_settings_table);

        if ($query->num_rows() > 0) {
            $this->db->where('id', $commissionId);
            if (!$this->db->update($this->system_commission_settings_table, $commissionData)) {
                log_message('error', 'DB Update Failed in updateCommissionSetting: ' . $this->db->last_query() . ' | Error: ' . json_encode($this->db->error()));
                return false;
            }
            return true;
        } else {
            $commissionData['id'] = $commissionId;
            $commissionData['commission_type'] = 'percentage'; // Default if inserting
            if (!$this->db->insert($this->system_commission_settings_table, $commissionData)) {
                log_message('error', 'DB Insert Failed in updateCommissionSetting: ' . $this->db->last_query() . ' | Error: ' . json_encode($this->db->error()));
                return false;
            }
            return true;
        }
    }

    public function updateCreditSetting($creditId, $creditData)
    {
        $this->db->where('id', $creditId);
        $query = $this->db->get($this->system_credit_settings_table);

        if ($query->num_rows() > 0) {
            $this->db->where('id', $creditId);
            if (!$this->db->update($this->system_credit_settings_table, $creditData)) {
                log_message('error', 'DB Update Failed in updateCreditSetting: ' . $this->db->last_query() . ' | Error: ' . json_encode($this->db->error()));
                return false;
            }
            return true;
        } else {
            $creditData['id'] = $creditId;
            $creditData['credit_type'] = 'signup_bonus'; // Default if inserting
            if (!$this->db->insert($this->system_credit_settings_table, $creditData)) {
                log_message('error', 'DB Insert Failed in updateCreditSetting: ' . $this->db->last_query() . ' | Error: ' . json_encode($this->db->error()));
                return false;
            }
            return true;
        }
    }

    public function get_signup_bonus_settings()
    {
        $this->db->where('id', 1);
        $query = $this->db->get('tbl_signup_bonus_settings');

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            // Default settings if not found
            return [
                'enabled' => 0,
                'amount' => 0.00,
                'expiration_days' => 90,
                'signup_coins' => 100
            ];
        }
    }
}

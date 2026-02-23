<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CoinModel extends CI_Model
{
    protected $table_rules = 'tbl_coin_rules';
    protected $table_transactions = 'tbl_coin_transactions';
    protected $table_users = 'tbl_users';
    protected $table_coin_packages = 'tbl_entry_shop_coin_packages';
    protected $table_cash_packages = 'tbl_entry_shop_cash_packages';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_rules()
    {
        $query = $this->db->get_where($this->table_rules, ['id' => 1]);
        return $query->row_array();
    }

    public function getRules()
    {
        $this->db->limit(1);
        return $this->db->get($this->table_rules)->row();
    }

    public function update_rules($data)
    {
        $this->db->where('id', 1);
        return $this->db->update($this->table_rules, $data);
    }

    public function get_balance($user_id)
    {
        $this->db->select('coin_balance');
        $this->db->where('id', $user_id);
        $query = $this->db->get($this->table_users);
        $row = $query->row_array();
        return $row ? (int)$row['coin_balance'] : 0;
    }

    public function getBalance($user_id)
    {
        return $this->get_balance($user_id);
    }

    public function get_user_coins($user_id)
    {
        return $this->get_balance($user_id);
    }

    public function add_coins($user_id, $coins_delta, $type, $reference_type = null, $reference_id = null, $description = '')
    {
        $this->db->trans_start();

        // Update user balance
        $this->db->where('id', $user_id);
        $this->db->set('coin_balance', 'coin_balance + ' . (int)$coins_delta, FALSE);
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->update($this->table_users);

        // Log transaction
        $this->db->insert($this->table_transactions, [
            'user_id' => $user_id,
            'type' => $type,
            'coins_delta' => $coins_delta,
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function updateBalance($user_id, $amount, $type, $reference_type = null, $reference_id = null, $description = null)
    {
        return $this->add_coins($user_id, $amount, $type, $reference_type, $reference_id, $description);
    }

    public function deduct_coins($user_id, $coins_delta, $type, $reference_type = null, $reference_id = null, $description = '')
    {
        return $this->add_coins($user_id, -abs($coins_delta), $type, $reference_type, $reference_id, $description);
    }

    public function hasSufficientBalance($user_id, $cost)
    {
        $balance = $this->get_balance($user_id);
        return ($balance >= $cost);
    }

    public function getHistory($user_id, $limit = 20, $offset = 0)
    {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get($this->table_transactions)->result();
    }

    public function get_coin_packages($active_only = true)
    {
        $this->db->select('id, title, coin_price, entry_count, bonus_entries, is_active');
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        $query = $this->db->get($this->table_coin_packages);
        return $query->result_array();
    }

    public function get_cash_packages($active_only = true)
    {
        $this->db->select('id, title, (price_cents / 100) as price_aud, entry_count, bonus_entries, is_active');
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        $query = $this->db->get($this->table_cash_packages);
        return $query->result_array();
    }

    public function get_coin_package_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->table_coin_packages);
        return $query->row_array();
    }

    public function get_cash_package_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->table_cash_packages);
        return $query->row_array();
    }

    // DataTables: Count all transactions for user
    public function countAllHistory($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->count_all_results($this->table_transactions);
    }

    // DataTables: Count filtered transactions
    public function countFilteredHistory($search, $user_id)
    {
        $this->_get_history_query($search, $user_id);
        return $this->db->count_all_results();
    }

    // DataTables: Get filtered data
    public function getFilteredHistory($start, $length, $search, $orderColumn, $orderDir, $user_id)
    {
        $this->_get_history_query($search, $user_id);

        if ($length != -1) {
            $this->db->limit($length, $start);
        }

        $columns = ['created_at', 'description', 'type', 'coins_delta'];
        if (isset($columns[$orderColumn])) {
            $this->db->order_by($columns[$orderColumn], $orderDir);
        } else {
            $this->db->order_by('created_at', 'DESC');
        }

        return $this->db->get()->result();
    }

    private function _get_history_query($search, $user_id)
    {
        $this->db->from($this->table_transactions);
        $this->db->where('user_id', $user_id);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('description', $search);
            $this->db->or_like('type', $search);
            $this->db->group_end();
        }
    }
}
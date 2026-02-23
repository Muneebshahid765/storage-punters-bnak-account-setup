<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DisputeModel extends CI_Model
{
    protected $table = 'tbl_disputes';

    public function create_dispute($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function get_all_disputes()
    {
        $this->db->select('d.*, a.unit_number, u.first_name, u.last_name');
        $this->db->from($this->table . ' d');
        $this->db->join('tbl_auctions a', 'a.id = d.auction_id', 'left');
        $this->db->join('tbl_users u', 'u.id = d.user_id', 'left');
        $this->db->order_by('d.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_dispute_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table)->row_array();
    }

    public function update_dispute($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
}

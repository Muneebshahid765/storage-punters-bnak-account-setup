<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminEconomy extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('CoinModel');
        $this->load->model('UserModel');
        
        // Basic admin check
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            redirect('login');
        }
    }

    public function update_rules()
    {
        $data = $this->input->post();
        if ($this->CoinModel->update_rules($data)) {
            echo json_encode(['success' => true, 'message' => 'Rules updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update rules']);
        }
    }

    public function list_coin_packages()
    {
        $packages = $this->CoinModel->get_coin_packages(false);
        echo json_encode(['success' => true, 'data' => $packages]);
    }

    public function list_cash_packages()
    {
        $packages = $this->CoinModel->get_cash_packages(false);
        echo json_encode(['success' => true, 'data' => $packages]);
    }

    public function save_coin_package()
    {
        $id = $this->input->post('id');
        $data = [
            'title' => $this->input->post('package_name'),
            'coin_price' => $this->input->post('coin_cost'),
            'entry_count' => $this->input->post('entry_amount'),
            'bonus_entries' => $this->input->post('bonus_entries') ?? 0,
            'is_active' => $this->input->post('is_active') ? 1 : 0
        ];

        if ($id) {
            $this->db->where('id', $id)->update('tbl_entry_shop_coin_packages', $data);
        } else {
            $this->db->insert('tbl_entry_shop_coin_packages', $data);
        }
        echo json_encode(['success' => true, 'message' => 'Package saved']);
    }

    public function save_cash_package()
    {
        $id = $this->input->post('id');
        // Convert AUD to Cents for database storage
        $price_aud = $this->input->post('price_aud');
        $price_cents = round($price_aud * 100);

        $data = [
            'title' => $this->input->post('package_name'),
            'price_cents' => $price_cents,
            'entry_count' => $this->input->post('entry_amount'),
            'bonus_entries' => $this->input->post('bonus_entries') ?? 0,
            'is_active' => $this->input->post('is_active') ? 1 : 0
        ];

        if ($id) {
            $this->db->where('id', $id)->update('tbl_entry_shop_cash_packages', $data);
        } else {
            $this->db->insert('tbl_entry_shop_cash_packages', $data);
        }
        echo json_encode(['success' => true, 'message' => 'Package saved']);
    }
}

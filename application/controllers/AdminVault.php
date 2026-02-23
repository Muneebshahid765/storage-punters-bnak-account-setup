<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminVault extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('VaultModel');
        $this->load->model('UserModel');
        
        // Admin check
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            redirect('login');
        }
    }

    public function manage_draws()
    {
        $draws = $this->VaultModel->get_all_draws();
        // Add item count to each draw
        foreach ($draws as &$draw) {
            $draw['item_count'] = $this->db->where('vault_draw_id', $draw['id'])->count_all_results('tbl_vault_draw_items');
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $draws
            ]));
    }

    public function create_draw()
    {
        $prize_value = $this->input->post('prize_value') ?? 0;
        $data = [
            'draw_code' => 'DW-' . strtoupper(substr(md5(time() . rand()), 0, 6)),
            'title' => $this->input->post('title'),
            'prize_value_cents' => round($prize_value * 100),
            'description' => $this->input->post('description'),
            'draw_at' => $this->input->post('draw_at'),
            'status' => 'scheduled'
        ];

        $draw_id = $this->VaultModel->create_draw($data);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => !!$draw_id, 'draw_id' => $draw_id]));
    }

    public function add_prize()
    {
        $data = [
            'vault_draw_id' => $this->input->post('draw_id'),
            'item_name' => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'image_url' => $this->input->post('image_url'),
            'estimated_value' => $this->input->post('value')
        ];

        $this->db->insert('tbl_vault_draw_items', $data);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }

    /**
     * Trigger the random winner selection for a draw
     */
    public function pick_winner($draw_id)
    {
        // 1. Validate Draw
        $draw = $this->db->get_where('tbl_vault_draws', ['id' => $draw_id])->row();
        if (!$draw) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode(['success' => false, 'message' => 'Draw not found']));
        }

        if ($draw->status === 'completed') {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Draw is already completed. Winner exists.']));
        }

        // 2. Run the Lottery
        $winner = $this->VaultModel->drawWinner($draw_id);

        if ($winner) {
            // --- 3. Queue Announcement Emails ---
            $this->queue_winner_emails($draw, $winner);

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true, 
                    'message' => 'Winner Selected Successfully! Emails queued.',
                    'winner' => $winner
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'No eligible entries found for this draw.'
                ]));
        }
    }

    private function queue_winner_emails($draw, $winner)
    {
        $this->load->model('EmailQueueModel');
        $scheduleTime = date('Y-m-d H:i:s');

        // 1. Email the WINNER ("You Won!")
        $winnerUser = $this->db->get_where('tbl_user_credentials', ['user_id' => $winner['user_id']])->row();
        
        if ($winnerUser) {
            $winnerProfile = $this->db->get_where('tbl_users', ['id' => $winner['user_id']])->row();
            
            $this->EmailQueueModel->add([
                'to_email'      => $winnerUser->email,
                'subject'       => "🎉 CONGRATULATIONS! You Won " . $draw->title . "!",
                'template'      => "vault_you_won",
                'name'          => $winnerProfile->first_name,
                'user_id'       => $winner['user_id'],
                'auction_id'    => $draw->id, 
                'auction_item'  => $draw->title, // Mapped to draw_title
                'auction_amount'=> $winner['prize_value_cents'], 
                'auction_total' => 0,
                'auction_url'   => $winner['punter_id'], // Mapped to punter_id
                'scheduled_at'  => $scheduleTime,
                'status'        => 'pending',
                'error_message' => '',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ]);
        }

        // 2. Email PARTICIPANTS ("Winner Announced")
        // Select users who have > 0 entries for this draw, excluding the winner
        $sql = "SELECT DISTINCT u.id, u.first_name, c.email 
                FROM tbl_vault_entries e
                JOIN tbl_users u ON u.id = e.user_id
                JOIN tbl_user_credentials c ON u.id = c.user_id
                WHERE e.vault_draw_id = ? 
                AND e.entries > 0
                AND e.user_id != ?
                AND (u.notification_prefs IS NULL OR u.notification_prefs LIKE '%\"winner_announcements\":true%')";
        
        $participants = $this->db->query($sql, [$draw->id, $winner['user_id']])->result();

        foreach ($participants as $user) {
            $this->EmailQueueModel->add([
                'to_email'      => $user->email,
                'subject'       => "🏆 Winner Announced: " . $draw->title,
                'template'      => "vault_winner_announcement",
                'name'          => $user->first_name,
                'user_id'       => $user->id,
                'auction_id'    => $draw->id, 
                'auction_item'  => $winner['winner_display'], // Mapped to winner_name
                'auction_amount'=> $winner['prize_value_cents'], // Mapped to prize_value
                'auction_total' => 0,
                'auction_url'   => $winner['punter_id'], // Mapped to punter_id
                'scheduled_at'  => $scheduleTime,
                'status'        => 'pending',
                'error_message' => '',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function trigger_draw()
    {
        // This is a legacy method from HEAD, redirecting to pick_winner
        $draw_id = $this->input->post('draw_id');
        return $this->pick_winner($draw_id);
    }

    public function activate_draw()
    {
        $draw_id = $this->input->post('draw_id');
        
        // Deactivate others
        $this->db->where('status', 'active')->update('tbl_vault_draws', ['status' => 'scheduled']);
        
        // Activate this one
        $this->db->where('id', $draw_id)->update('tbl_vault_draws', ['status' => 'active']);
        
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }

    public function get_draw()
    {
        $id = $this->input->get('id');
        $draw = $this->VaultModel->get_draw_by_id($id);
        
        if ($draw) {
            $draw['items'] = $this->VaultModel->get_draw_items($id);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => !!$draw, 'data' => $draw]));
    }

    public function update_draw()
    {
        $id = $this->input->post('id');
        $data = [
            'title' => $this->input->post('title'),
            'draw_at' => $this->input->post('draw_at'),
            'description' => $this->input->post('description')
        ];

        $this->db->where('id', $id)->update('tbl_vault_draws', $data);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }

    public function delete_draw()
    {
        $id = $this->input->post('draw_id');
        
        $this->db->trans_start();
        $this->db->where('vault_draw_id', $id)->delete('tbl_vault_draw_items');
        $this->db->where('vault_draw_id', $id)->delete('tbl_vault_entries');
        $this->db->where('vault_draw_id', $id)->delete('tbl_vault_winners');
        $this->db->where('id', $id)->delete('tbl_vault_draws');
        $this->db->trans_complete();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => $this->db->trans_status()]));
    }

    public function delete_prize()
    {
        $id = $this->input->post('id');
        $this->db->where('id', $id)->delete('tbl_vault_draw_items');
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }

    public function get_stats()
    {
        $stats = $this->VaultModel->get_stats();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $stats]));
    }
}
<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Legacy Subscription Controller
 * Delegates all calls to SubscriptionGiveaway controller for unified handling.
 */
class Subscription extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('SubscriptionGiveawayModel');
        $this->load->model('UserModel');
        $this->load->model('VaultModel');
        $this->load->model('CoinModel');
    }

    public function status()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;
        $role_id = $session_data['role_id'] ?? null;

        if (!$user_id || (int)$role_id !== 2) {
            return $this->respond(false, 'Unauthorized: bidder access only.', [], 403);
        }

        $sub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
        $user = $this->db->get_where('tbl_users', ['id' => $user_id])->row_array();

        return $this->respond(true, 'Status fetched', [
            'tier' => $sub ? $sub['tier_key'] : ($user['subscription_tier'] ?? 'none'),
            'tier_name' => $sub ? $sub['tier_name'] : 'None',
            'punter_id' => $user['punter_id'] ?? ('SP-' . str_pad($user_id, 4, '0', STR_PAD_LEFT)),
            'coin_balance' => (int)($user['coin_balance'] ?? 0),
            'streak_months' => $sub ? (int)$sub['streak_months'] : 0,
            'has_subscription' => !empty($sub),
            'status' => $sub ? $sub['status'] : 'none'
        ]);
    }

    public function cancel_start()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;
        $role_id = $session_data['role_id'] ?? null;
        if (!$user_id || (int)$role_id !== 2) {
            return $this->respond(false, 'Unauthorized: bidder access only.', [], 403);
        }

        $sub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
        if (!$sub || $sub['status'] !== 'active') {
            return $this->respond(false, 'No active subscription found.');
        }

        $draw = $this->SubscriptionGiveawayModel->get_active_draw();
        $total_entries = $this->SubscriptionGiveawayModel->get_user_total_entries($user_id);

        return $this->respond(true, 'Cancellation info fetched', [
            'draw_title' => $draw ? $draw['title'] : 'No active draw',
            'draw_date' => $draw ? $draw['draw_at'] : null,
            'user_entries' => $total_entries,
            'streak_months' => (int)$sub['streak_months'],
            'days_remaining' => $draw ? max(0, ceil((strtotime($draw['draw_at']) - time()) / 86400)) : 0
        ]);
    }

    public function claim_retention_bonus()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;
        $role_id = $session_data['role_id'] ?? null;
        if (!$user_id || (int)$role_id !== 2) {
            return $this->respond(false, 'Unauthorized: bidder access only.', [], 403);
        }

        $draw = $this->SubscriptionGiveawayModel->get_active_draw();
        if (!$draw) {
            return $this->respond(false, 'No active draw');
        }

        // Check if already claimed for this draw
        $this->db->where('user_id', $user_id);
        $this->db->where('draw_id', $draw['id']);
        $this->db->where('source', 'retention_bonus');
        $exists = $this->db->get('tbl_entries_ledger')->num_rows() > 0;

        if ($exists) {
            return $this->respond(false, 'Bonus already claimed for this draw');
        }

        // Grant 5 retention bonus entries
        $this->db->insert('tbl_entries_ledger', [
            'user_id' => $user_id,
            'draw_id' => $draw['id'],
            'quantity' => 5,
            'source' => 'retention_bonus',
            'meta' => json_encode(['action' => 'cancelled_cancel']),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Also add to vault entries for compatibility
        $vault_draw = $this->VaultModel->get_active_draw();
        if ($vault_draw) {
            $this->VaultModel->add_entries($vault_draw['id'], $user_id, 5, 'retention_bonus', ['action' => 'cancelled_cancel']);
        }

        return $this->respond(true, 'You claimed 5 free entries! Your subscription remains active.');
    }

    public function cancel_confirm()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;
        $role_id = $session_data['role_id'] ?? null;

        if (!$user_id || (int)$role_id !== 2) {
            return $this->respond(false, 'Unauthorized: bidder access only.', [], 403);
        }

        $sub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
        if (!$sub || $sub['status'] !== 'active') {
            return $this->respond(false, 'No active subscription found.');
        }

        try {
            $secretKey = stripe_secret_key();
            if (!$secretKey) {
                throw new \Exception('Stripe secret key not configured');
            }

            \Stripe\Stripe::setApiKey($secretKey);

            if (!empty($sub['stripe_subscription_id'])) {
                \Stripe\Subscription::update($sub['stripe_subscription_id'], [
                    'cancel_at_period_end' => true,
                ]);
            }

            $this->SubscriptionGiveawayModel->cancel_subscription($sub['id'], $user_id);

            $this->SubscriptionGiveawayModel->log_cancellation([
                'user_id' => $user_id,
                'subscription_id' => $sub['id'],
                'stripe_subscription_id' => $sub['stripe_subscription_id'],
                'reason' => 'User requested cancellation',
                'entries_at_cancel' => $this->SubscriptionGiveawayModel->get_user_total_entries($user_id),
                'streak_at_cancel' => $sub['streak_months'],
                'undo_before' => $sub['current_period_end'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond(true, 'Subscription cancelled. You will have access until ' . date('M j, Y', strtotime($sub['current_period_end'])) . '.');

        } catch (\Stripe\Exception\ApiErrorException $e) {
            log_message('error', 'Stripe Cancellation Error: ' . $e->getMessage());
            return $this->respond(false, 'Failed to process cancellation with payment provider.');
        } catch (Exception $e) {
            return $this->respond(false, 'An unexpected error occurred.');
        }
    }

    private function respond($success, $message, $data = [], $status = 200)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($status)
            ->set_output(json_encode([
                'success' => $success,
                'message' => $message,
                'data' => $data
            ]));
    }
}

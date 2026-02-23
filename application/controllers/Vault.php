<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vault extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('VaultModel');
        $this->load->model('CoinModel');
        $this->load->model('UserModel');
        $this->load->model('VideosModel');
        $this->load->model('SubscriptionGiveawayModel');
    }

    /**
     * Auto-check and transition draws that have passed their draw time
     */
    private function auto_check_draw_status()
    {
        $now = date('Y-m-d H:i:s');
        
        // Auto-transition vault draws past their time from active/scheduled to 'locked'
        $this->db->where('status', 'active');
        $this->db->where('draw_at <', $now);
        $this->db->update('tbl_vault_draws', ['status' => 'locked', 'updated_at' => $now]);
        
        // Auto-transition giveaway draws past their time from active to 'locked'
        $this->db->where('status', 'active');
        $this->db->where('draw_at <', $now);
        $this->db->update('tbl_giveaway_draws', ['status' => 'locked', 'updated_at' => $now]);
    }

    /**
     * Get the best available draw (vault or giveaway), returns associative array
     */
    private function get_merged_draw()
    {
        $vault_draw = $this->VaultModel->get_active_draw();
        $giveaway_draw = $this->SubscriptionGiveawayModel->get_active_draw();
        
        // Prefer giveaway draw as it has richer data (2nd/3rd prizes)
        if ($giveaway_draw) {
            return ['draw' => $giveaway_draw, 'source' => 'giveaway', 'vault_draw' => $vault_draw, 'giveaway_draw' => $giveaway_draw];
        }
        if ($vault_draw) {
            return ['draw' => $vault_draw, 'source' => 'vault', 'vault_draw' => $vault_draw, 'giveaway_draw' => null];
        }
        return ['draw' => null, 'source' => null, 'vault_draw' => null, 'giveaway_draw' => null];
    }

    /**
     * The Vault Dashboard
     */
    public function index()
    {
        $this->auto_check_draw_status();
        
        $userSession = $this->session->userdata('auction_user_session_data');
        $user_id = $userSession['user_id'] ?? null;
        
        $merged = $this->get_merged_draw();
        $draw = $merged['draw'];
        $giveaway_draw = $merged['giveaway_draw'];
        
        // Convert to object for view compatibility
        $data['active_draw'] = $draw ? (object) $draw : null;

        $data['upcoming_draws'] = $this->VaultModel->getUpcomingDraws();
        $data['user_entries'] = 0;
        $data['coin_balance'] = $user_id ? $this->CoinModel->getBalance($user_id) : 0;
        $data['hall_of_fame'] = $this->VaultModel->getHallOfFame();
        $data['vault_settings'] = $this->SubscriptionGiveawayModel->get_all_settings();
        $data['seasons'] = $this->VideosModel->get_all_grouped_by_season();
        $data['has_active_subscription'] = false;
        $data['current_subscription_tier'] = null;

        if ($user_id) {
            $subscription = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
            if ($subscription && in_array($subscription['status'], ['active', 'past_due'])) {
                $data['has_active_subscription'] = true;
                $data['current_subscription_tier'] = $subscription['tier_name'] ?? null;
            }

            $vault_entries = 0;
            $giveaway_entries = 0;
            
            if ($merged['vault_draw']) {
                $vault_entries = $this->VaultModel->getUserEntries($user_id, $merged['vault_draw']['id']);
            }
            if ($giveaway_draw) {
                $giveaway_entries = $this->SubscriptionGiveawayModel->get_user_total_entries($user_id, $giveaway_draw['id']);
            }
            
            $data['user_entries'] = $vault_entries + $giveaway_entries;
        }

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('vault/index', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    /**
     * Legacy/AJAX endpoint for current draw
     */
    public function current()
    {
        $this->auto_check_draw_status();
        
        $merged = $this->get_merged_draw();
        $draw = $merged['draw'];
        $giveaway_draw = $merged['giveaway_draw'];
        
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;
        $user_entries = 0;
        $user_coins = 0;

        if ($user_id) {
            $user_coins = $this->CoinModel->getBalance($user_id);
            if ($merged['vault_draw']) {
                $user_entries += $this->VaultModel->getUserEntries($user_id, $merged['vault_draw']['id']);
            }
            if ($giveaway_draw) {
                $user_entries += $this->SubscriptionGiveawayModel->get_user_total_entries($user_id, $giveaway_draw['id']);
            }
        }

        if (!$draw) {
            return $this->respond(true, 'No active draw found', [
                'draw' => null,
                'items' => [],
                'user' => [
                    'id' => $user_id,
                    'entries' => $user_entries,
                    'coins' => $user_coins
                ]
            ]);
        }

        $items = $this->VaultModel->get_draw_items($draw['id']);
        
        // Check for a recently completed draw/winner for the reveal effect
        $last_winner = $this->db->select('w.*, u.first_name, u.last_name as last_initial, u.id as punter_id')
            ->from('tbl_vault_winners w')
            ->join('tbl_users u', 'u.id = w.user_id')
            ->order_by('w.won_at', 'DESC')
            ->limit(1)
            ->get()->row_array();

        // Obfuscate Last Name
        if ($last_winner) {
            $last_winner['last_initial'] = substr($last_winner['last_initial'], 0, 1) . '.';
            $last_winner['punter_id'] = 'SP ' . str_pad($last_winner['punter_id'], 4, '0', STR_PAD_LEFT);
            $last_winner['prize_title'] = $last_winner['winner_display'] ?? 'THE VAULT DROP';
        }

        // Also check giveaway winners
        if (!$last_winner) {
            $giveaway_winners = $this->SubscriptionGiveawayModel->get_recent_winners(1);
            if (!empty($giveaway_winners)) {
                $gw = $giveaway_winners[0];
                $last_winner = [
                    'first_name' => $gw['first_name'],
                    'last_initial' => substr($gw['last_name'], 0, 1) . '.',
                    'punter_id' => $gw['punter_id'] ?? ('SP ' . str_pad($gw['user_id'], 4, '0', STR_PAD_LEFT)),
                    'winner_display' => $gw['winner_display'],
                    'prize_title' => $gw['draw_title'] ?? 'CASH GIVEAWAY',
                    'won_at' => $gw['won_at']
                ];
            }
        }

        return $this->respond(true, 'Current draw fetched', [
            'draw' => $draw,
            'items' => $items,
            'last_winner' => $last_winner,
            'user' => [
                'id' => $user_id,
                'entries' => $user_entries,
                'coins' => $user_coins
            ]
        ]);
    }

    /**
     * AJAX Endpoint: Get Real-time Status (Merged with current logic)
     */
    public function status()
    {
        $this->auto_check_draw_status();
        
        $userSession = $this->session->userdata('auction_user_session_data');
        $user_id = $userSession['user_id'] ?? null;
        
        $merged = $this->get_merged_draw();
        $draw = $merged['draw'];
        $giveaway_draw = $merged['giveaway_draw'];
        
        $response = [
            'entries' => 0,
            'coins' => $user_id ? $this->CoinModel->getBalance($user_id) : 0,
            'draw_status' => $draw ? ($draw['status'] ?? 'none') : 'none'
        ];

        if ($draw) {
            if ($user_id) {
                if ($merged['vault_draw']) {
                    $response['entries'] += $this->VaultModel->getUserEntries($user_id, $merged['vault_draw']['id']);
                }
                if ($giveaway_draw) {
                    $response['entries'] += $this->SubscriptionGiveawayModel->get_user_total_entries($user_id, $giveaway_draw['id']);
                }
            }
            
            // Calculate time remaining using Brisbane timezone so countdown matches UI copy
            $brisbaneTz = new DateTimeZone('Australia/Brisbane');
            $now = new DateTime('now', $brisbaneTz);
            $draw_time = new DateTime($draw['draw_at'], $brisbaneTz);
            $seconds_remaining = $draw_time->getTimestamp() - $now->getTimestamp();
            $response['seconds_remaining'] = max(0, $seconds_remaining);
            $response['server_time'] = $now->getTimestamp();
            $response['draw_timestamp'] = $draw_time->getTimestamp();
            
            // If time has passed and draw is still active/scheduled, report as 'awaiting_draw'
            if ($seconds_remaining <= 0 && in_array($draw['status'], ['active', 'scheduled'])) {
                $response['draw_status'] = 'awaiting_draw';
            }

            // Get items from vault draw if available
            $items = [];
            if ($merged['vault_draw']) {
                $items = $this->VaultModel->getDrawItems($merged['vault_draw']['id']);
            }
            
            $response['draw_details'] = [
                'draw_code' => $draw['draw_code'] ?? '',
                'title' => $draw['title'] ?? '',
                'description' => $draw['description'] ?? '',
                'cover_image_url' => $draw['cover_image_url'] ?? '',
                'prize_formatted' => '$' . number_format(($draw['prize_value_cents'] ?? 0) / 100),
                'prize_2nd' => isset($draw['prize_2nd_cents']) && $draw['prize_2nd_cents'] > 0 ? '$' . number_format($draw['prize_2nd_cents'] / 100) : null,
                'prize_3rd' => isset($draw['prize_3rd_cents']) && $draw['prize_3rd_cents'] > 0 ? '$' . number_format($draw['prize_3rd_cents'] / 100) : null,
                'draw_date_formatted' => $draw_time->setTimezone($brisbaneTz)->format('M j, Y g:i A'),
                'items' => $items
            ];
        } else {
            // Check for most recently completed draw (vault vs giveaway)
            $vaultWinner = $this->db->order_by('won_at', 'DESC')->get('tbl_vault_winners', 1)->row();
            $giveawayWinners = $this->SubscriptionGiveawayModel->get_recent_winners(1);
            $giveawayWinner = !empty($giveawayWinners) ? $giveawayWinners[0] : null;

            $selectedSource = null;
            if ($vaultWinner && $giveawayWinner) {
                $selectedSource = (strtotime($vaultWinner->won_at) >= strtotime($giveawayWinner['won_at'])) ? 'vault' : 'giveaway';
            } elseif ($vaultWinner) {
                $selectedSource = 'vault';
            } elseif ($giveawayWinner) {
                $selectedSource = 'giveaway';
            }

            if ($selectedSource === 'vault') {
                $response['draw_status'] = 'completed';
                $response['last_winner_name'] = $vaultWinner->winner_display;
            } elseif ($selectedSource === 'giveaway') {
                $response['draw_status'] = 'completed';
                $response['last_winner_name'] = $giveawayWinner['winner_display'];
            }
        }

        // Hall of Fame (fetch extra so filtering never clears list)
        $response['hall_of_fame'] = $this->VaultModel->getHallOfFame(5);
        $showRevealBanner = (!$draw) || ($response['draw_status'] === 'completed');

        // Fallback: only when reveal banner is visible
        if ($showRevealBanner && empty($response['last_winner_name']) && !empty($response['hall_of_fame'])) {
            $recentWinner = $response['hall_of_fame'][0];
            if ($recentWinner && isset($recentWinner->winner_display)) {
                $response['last_winner_name'] = $recentWinner->winner_display;
            }
        }

        // Ensure the "previous winners" list doesn't duplicate the latest winner shown in the reveal banner
        if ($showRevealBanner && !empty($response['hall_of_fame']) && !empty($response['last_winner_name'])) {
            $latestDisplay = $response['last_winner_name'];
            $response['hall_of_fame'] = array_values(array_filter($response['hall_of_fame'], function($winner) use ($latestDisplay) {
                $displayMatch = isset($winner->winner_display) && strcasecmp($winner->winner_display, $latestDisplay) === 0;
                return !$displayMatch;
            }));
            // Limit to 3 cards after filtering for a tidy UI
            $response['hall_of_fame'] = array_slice($response['hall_of_fame'], 0, 3);
        } else {
            $response['hall_of_fame'] = array_slice($response['hall_of_fame'], 0, 3);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function winners()
    {
        $winners = $this->VaultModel->get_winners(20);
        return $this->respond(true, 'Hall of Fame fetched', ['winners' => $winners]);
    }

    public function buy_entries_with_coins()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;
        if (!$user_id) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $package_id = $this->input->post('package_id');
        $package = $this->CoinModel->get_coin_package_by_id($package_id);

        if (!$package || !$package['is_active']) {
            return $this->respond(false, 'Invalid package');
        }

        $user_coins = $this->CoinModel->getBalance($user_id);
        if ($user_coins < $package['coin_price']) {
            return $this->respond(false, 'Insufficient coins. Bid on more auctions to earn coins!', [], 400);
        }

        $draw = $this->VaultModel->get_active_draw();
        $giveaway_draw = $this->SubscriptionGiveawayModel->get_active_draw();
        
        if (!$draw && !$giveaway_draw) {
            return $this->respond(false, 'No active draw to add entries to');
        }

        $total_entries = $package['entry_count'] + ($package['bonus_entries'] ?? 0);

        // Deduct coins and add entries
        $this->db->trans_start();
        $this->CoinModel->deduct_coins($user_id, $package['coin_price'], 'coin_spent_entries', 'package', $package_id, 'Purchased ' . $package['entry_count'] . ' entries');
        
        if ($draw) {
            $this->VaultModel->add_entries($draw['id'], $user_id, $total_entries, 'coin_purchase', ['package_id' => $package_id]);
        }
        
        // Also log in giveaway entries ledger
        if ($giveaway_draw) {
            $this->db->insert('tbl_entries_ledger', [
                'user_id' => $user_id,
                'draw_id' => $giveaway_draw['id'],
                'quantity' => $total_entries,
                'source' => 'coin_purchase',
                'meta' => json_encode(['package_id' => $package_id]),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            $updated_coins = $this->CoinModel->getBalance($user_id);
            $vault_entries = $draw ? $this->VaultModel->getUserEntries($user_id, $draw['id']) : 0;
            $giveaway_entries = $giveaway_draw ? $this->SubscriptionGiveawayModel->get_user_total_entries($user_id, $giveaway_draw['id']) : 0;
            
            return $this->respond(true, 'Entries purchased successfully!', [
                'user' => [
                    'coins' => $updated_coins,
                    'entries' => $vault_entries + $giveaway_entries
                ]
            ]);
        } else {
            return $this->respond(false, 'Transaction failed');
        }
    }

    public function get_packages()
    {
        $coin_packages = $this->CoinModel->get_coin_packages();
        $cash_packages = $this->CoinModel->get_cash_packages();
        
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;
        $user_tier = 'none';
        
        if ($user_id) {
            $user = $this->UserModel->get_user_by_id($user_id);
            $user_tier = strtolower($user['subscription_tier_key'] ?? ($user['subscription_tier'] ?? 'none'));
        }

        // Apply dynamic tier-based discounts for cash packages
        foreach ($cash_packages as &$pkg) {
            if ($user_tier === 'shark') {
                $pkg['price_aud'] = $pkg['price_aud'] * 0.50;
            } elseif ($user_tier === 'whale') {
                $pkg['price_aud'] = $pkg['price_aud'] * 0.25;
            }
        }

        return $this->respond(true, 'Packages fetched', [
            'coin_packages' => $coin_packages,
            'cash_packages' => $cash_packages,
            'user_tier' => $user_tier
        ]);
    }

    /**
     * Get User Notification Preferences
     */
    public function get_notifications()
    {
        $userSession = $this->session->userdata('auction_user_session_data');
        $user_id = $userSession['user_id'] ?? null;

        if (!$user_id) {
             return $this->respond(false, 'Unauthorized', [], 401);
        }

        $user = $this->db->get_where('tbl_users', ['id' => $user_id])->row();
        $prefs = json_decode($user->notification_prefs, true);

        $data = [
            'vault_reminders' => $prefs['vault_reminders'] ?? true,
            'winner_announcements' => $prefs['winner_announcements'] ?? true
        ];

        return $this->respond(true, 'Notifications fetched', $data);
    }

    /**
     * Update User Notification Preferences
     */
    public function update_notifications()
    {
        $userSession = $this->session->userdata('auction_user_session_data');
        $user_id = $userSession['user_id'] ?? null;
        
        if (!$user_id) {
             return $this->respond(false, 'Unauthorized', [], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $key = $input['key'] ?? '';
        $value = $input['value'] ?? false;

        if (!in_array($key, ['vault_reminders', 'winner_announcements'])) {
            return $this->respond(false, 'Invalid setting key', [], 400);
        }

        $user = $this->db->get_where('tbl_users', ['id' => $user_id])->row();
        $prefs = json_decode($user->notification_prefs, true) ?? [];

        $prefs[$key] = $value;

        $this->db->where('id', $user_id);
        $this->db->update('tbl_users', ['notification_prefs' => json_encode($prefs)]);

        return $this->respond(true, 'Settings updated');
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
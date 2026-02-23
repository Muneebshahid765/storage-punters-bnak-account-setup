<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SubscriptionGiveawayModel extends CI_Model
{
    protected $tbl_tiers = 'tbl_subscription_tiers';
    protected $tbl_subscriptions = 'tbl_punter_subscriptions';
    protected $tbl_milestones = 'tbl_streak_milestones';
    protected $tbl_entries_ledger = 'tbl_entries_ledger';
    protected $tbl_draws = 'tbl_giveaway_draws';
    protected $tbl_cancellations = 'tbl_subscription_cancellations';
    protected $tbl_exports = 'tbl_draw_exports';
    protected $tbl_winners = 'tbl_draw_winners';
    protected $tbl_states = 'tbl_australian_states';
    protected $tbl_settings = 'tbl_vault_settings';

    public function __construct()
    {
        parent::__construct();
    }

    // ========== TIERS ==========

    public function get_all_tiers($active_only = true)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('sort_order', 'ASC');
        return $this->db->get($this->tbl_tiers)->result_array();
    }

    public function get_tier_by_key($key)
    {
        return $this->db->get_where($this->tbl_tiers, ['tier_key' => $key])->row_array();
    }

    public function get_tier_by_id($id)
    {
        return $this->db->get_where($this->tbl_tiers, ['id' => $id])->row_array();
    }

    public function update_tier_stripe_ids($tier_id, $product_id, $price_id)
    {
        $this->db->where('id', $tier_id);
        return $this->db->update($this->tbl_tiers, [
            'stripe_product_id' => $product_id,
            'stripe_price_id' => $price_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    // ========== SUBSCRIPTIONS ==========

    public function get_user_subscription($user_id)
    {
        $this->db->select('ps.*, st.tier_key, st.tier_name, st.base_entries, st.fee_rate, st.coin_multiplier, st.price_monthly_cents');
        $this->db->from($this->tbl_subscriptions . ' ps');
        $this->db->join($this->tbl_tiers . ' st', 'st.id = ps.tier_id');
        $this->db->where('ps.user_id', $user_id);
        $this->db->where_in('ps.status', ['active', 'cancelled', 'past_due']);
        $this->db->order_by('ps.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row_array();
    }

    public function get_subscription_by_stripe_id($stripe_subscription_id)
    {
        $this->db->select('ps.*, st.tier_key, st.tier_name, st.base_entries, st.fee_rate, st.coin_multiplier');
        $this->db->from($this->tbl_subscriptions . ' ps');
        $this->db->join($this->tbl_tiers . ' st', 'st.id = ps.tier_id');
        $this->db->where('ps.stripe_subscription_id', $stripe_subscription_id);
        $this->db->limit(1);
        return $this->db->get()->row_array();
    }

    public function create_subscription($data)
    {
        $this->db->insert($this->tbl_subscriptions, $data);
        return $this->db->insert_id();
    }

    public function update_subscription($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update($this->tbl_subscriptions, $data);
    }

    public function update_subscription_by_stripe_id($stripe_sub_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('stripe_subscription_id', $stripe_sub_id);
        return $this->db->update($this->tbl_subscriptions, $data);
    }

    public function cancel_subscription($sub_id, $user_id)
    {
        $this->db->where('id', $sub_id);
        $this->db->where('user_id', $user_id);
        return $this->db->update($this->tbl_subscriptions, [
            'cancel_at_period_end' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function undo_cancellation($sub_id, $user_id)
    {
        $this->db->where('id', $sub_id);
        $this->db->where('user_id', $user_id);
        return $this->db->update($this->tbl_subscriptions, [
            'cancel_at_period_end' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function expire_subscription($sub_id)
    {
        $this->db->where('id', $sub_id);
        return $this->db->update($this->tbl_subscriptions, [
            'status' => 'expired',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    // ========== COMPOUNDING ENTRY ENGINE ==========

    public function get_streak_multiplier($streak_months)
    {
        $this->db->where('month_threshold <=', $streak_months);
        $this->db->order_by('month_threshold', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get($this->tbl_milestones)->row_array();
        return $row ? (float)$row['multiplier'] : 1.0;
    }

    public function calculate_monthly_entries($base_entries, $streak_months)
    {
        // Get all milestones up to current streak
        $this->db->where('month_threshold <=', $streak_months);
        $this->db->order_by('month_threshold', 'ASC');
        $milestones = $this->db->get($this->tbl_milestones)->result_array();

        // Start with base entries
        $current_grant = $base_entries;

        foreach ($milestones as $milestone) {
            if ($milestone['month_threshold'] <= 1) {
                // Month 1 is the base (multiplier applied to base)
                $current_grant = $base_entries * (float)$milestone['multiplier'];
            } else {
                // Each milestone multiplies the current grant
                $current_grant = $current_grant * (float)$milestone['multiplier'];
            }
        }

        return (int)round($current_grant);
    }

    public function grant_monthly_entries($user_id, $draw_id, $base_entries, $streak_months)
    {
        $entries = $this->calculate_monthly_entries($base_entries, $streak_months);

        $this->db->trans_start();

        // Add to entries ledger
        $this->db->insert($this->tbl_entries_ledger, [
            'user_id' => $user_id,
            'draw_id' => $draw_id,
            'quantity' => $entries,
            'source' => 'subscription_streak',
            'streak_month' => $streak_months,
            'meta' => json_encode(['base' => $base_entries, 'streak' => $streak_months, 'calculated' => $entries]),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Also add to existing vault entries system for compatibility
        $this->load->model('VaultModel');
        $vault_draw = $this->VaultModel->get_active_draw();
        if ($vault_draw) {
            $this->VaultModel->add_entries($vault_draw['id'], $user_id, $entries, 'subscription_streak', [
                'streak_month' => $streak_months,
                'base_entries' => $base_entries
            ]);
        }

        // Update user totals
        $this->db->where('id', $user_id);
        $this->db->set('total_giveaway_entries', 'total_giveaway_entries + ' . (int)$entries, FALSE);
        $this->db->update('tbl_users');

        // Track total entries earned per subscription for admin reporting
        $sub = $this->get_user_subscription($user_id);
        if ($sub) {
            $this->db->where('id', $sub['id']);
            $this->db->set('total_entries_earned', 'total_entries_earned + ' . (int)$entries, FALSE);
            $this->db->update($this->tbl_subscriptions);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $entries : 0;
    }

    public function get_user_total_entries($user_id, $draw_id = null)
    {
        $this->db->select_sum('quantity');
        $this->db->where('user_id', $user_id);
        if ($draw_id) {
            $this->db->where('draw_id', $draw_id);
        }
        $row = $this->db->get($this->tbl_entries_ledger)->row_array();
        return $row ? (int)$row['quantity'] : 0;
    }

    public function get_user_entries_breakdown($user_id, $draw_id = null)
    {
        $this->db->select('source, SUM(quantity) as total, COUNT(*) as count');
        $this->db->where('user_id', $user_id);
        if ($draw_id) {
            $this->db->where('draw_id', $draw_id);
        }
        $this->db->group_by('source');
        return $this->db->get($this->tbl_entries_ledger)->result_array();
    }

    // ========== DRAWS ==========

    public function get_active_draw()
    {
        // First check for active draws
        $this->db->where('status', 'active');
        $this->db->order_by('draw_at', 'ASC');
        $this->db->limit(1);
        $draw = $this->db->get($this->tbl_draws)->row_array();

        // Then check locked draws (time passed, awaiting winner selection)
        if (!$draw) {
            $this->db->where('status', 'locked');
            $this->db->order_by('draw_at', 'DESC');
            $this->db->limit(1);
            $draw = $this->db->get($this->tbl_draws)->row_array();
        }

        // Finally check scheduled draws
        if (!$draw) {
            $this->db->where('status', 'scheduled');
            $this->db->where('draw_at >', date('Y-m-d H:i:s'));
            $this->db->order_by('draw_at', 'ASC');
            $this->db->limit(1);
            $draw = $this->db->get($this->tbl_draws)->row_array();
        }

        return $draw;
    }

    public function get_draw_by_id($id)
    {
        return $this->db->get_where($this->tbl_draws, ['id' => $id])->row_array();
    }

    public function get_all_draws()
    {
        $this->db->order_by('draw_at', 'DESC');
        return $this->db->get($this->tbl_draws)->result_array();
    }

    public function create_draw($data)
    {
        $this->db->insert($this->tbl_draws, $data);
        return $this->db->insert_id();
    }

    public function update_draw($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update($this->tbl_draws, $data);
    }

    public function lock_draw($draw_id)
    {
        // Get totals
        $this->db->select('COUNT(DISTINCT user_id) as participants, SUM(quantity) as total_entries');
        $this->db->where('draw_id', $draw_id);
        $stats = $this->db->get($this->tbl_entries_ledger)->row_array();

        return $this->update_draw($draw_id, [
            'status' => 'locked',
            'total_entries_at_lock' => $stats['total_entries'] ?? 0,
            'total_participants_at_lock' => $stats['participants'] ?? 0,
            'locked_at' => date('Y-m-d H:i:s')
        ]);
    }

    // ========== CSV EXPORT ==========

    public function get_draw_participants_for_export($draw_id)
    {
        $this->db->select('
            el.user_id as Unique_ID,
            SUM(el.quantity) as Weight,
            u.first_name as First_Name,
            LEFT(u.last_name, 1) as Last_Initial,
            u.residential_state as State
        ');
        $this->db->from($this->tbl_entries_ledger . ' el');
        $this->db->join('tbl_users u', 'u.id = el.user_id');
        $this->db->where('el.draw_id', $draw_id);
        $this->db->group_by('el.user_id');
        $this->db->having('Weight >', 0);
        $this->db->order_by('Weight', 'DESC');
        return $this->db->get()->result_array();
    }

    protected $export_log_schema_checked = false;
    protected $export_log_has_type = false;
    protected $export_log_has_sa = false;

    protected function ensure_export_log_schema()
    {
        if ($this->export_log_schema_checked) {
            return;
        }

        $this->export_log_has_type = $this->db->field_exists('export_type', $this->tbl_exports);
        $this->export_log_has_sa = $this->db->field_exists('sa_excluded', $this->tbl_exports);
        $this->export_log_schema_checked = true;
    }

    public function save_export_log($data)
    {
        $this->ensure_export_log_schema();

        $allowed = [
            'draw_id' => $data['draw_id'] ?? null,
            'exported_by' => $data['exported_by'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'total_participants' => $data['total_participants'] ?? 0,
            'total_weight' => $data['total_weight'] ?? 0,
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
        ];

        if ($this->export_log_has_type && isset($data['export_type'])) {
            $allowed['export_type'] = $data['export_type'];
        }

        if ($this->export_log_has_sa && isset($data['sa_excluded'])) {
            $allowed['sa_excluded'] = (int)$data['sa_excluded'];
        }

        $this->db->insert($this->tbl_exports, $allowed);
        return $this->db->insert_id();
    }

    // ========== WINNERS ==========

    public function save_winner($data)
    {
        $this->db->insert($this->tbl_winners, $data);
        return $this->db->insert_id();
    }

    public function get_draw_winners($draw_id)
    {
        $this->db->select('dw.*, u.first_name, u.last_name');
        $this->db->from($this->tbl_winners . ' dw');
        $this->db->join('tbl_users u', 'u.id = dw.user_id');
        $this->db->where('dw.draw_id', $draw_id);
        $this->db->order_by('dw.prize_position', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_recent_winners($limit = 10)
    {
        $this->db->select('dw.*, u.first_name, u.last_name, gd.title as draw_title');
        $this->db->from($this->tbl_winners . ' dw');
        $this->db->join('tbl_users u', 'u.id = dw.user_id');
        $this->db->join($this->tbl_draws . ' gd', 'gd.id = dw.draw_id');
        $this->db->order_by('dw.won_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    // ========== CANCELLATION ==========

    public function log_cancellation($data)
    {
        $this->db->insert($this->tbl_cancellations, $data);
        return $this->db->insert_id();
    }

    public function undo_cancellation_log($cancel_id)
    {
        $this->db->where('id', $cancel_id);
        return $this->db->update($this->tbl_cancellations, ['undone' => 1]);
    }

    public function get_pending_cancellation($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('undone', 0);
        $this->db->where('undo_before >', date('Y-m-d H:i:s'));
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get($this->tbl_cancellations)->row_array();
    }

    // ========== STATES ==========

    public function get_all_states()
    {
        return $this->db->get($this->tbl_states)->result_array();
    }

    public function is_state_eligible($state_code)
    {
        $row = $this->db->get_where($this->tbl_states, ['code' => $state_code])->row_array();
        return $row ? (bool)$row['eligible'] : true;
    }

    public function get_state_disclaimer($state_code)
    {
        $row = $this->db->get_where($this->tbl_states, ['code' => $state_code])->row_array();
        return $row ? $row['disclaimer'] : null;
    }

    // ========== SETTINGS ==========

    public function get_setting($key)
    {
        $row = $this->db->get_where($this->tbl_settings, ['setting_key' => $key])->row_array();
        return $row ? $row['setting_value'] : null;
    }

    public function get_all_settings($keys = null)
    {
        if (!empty($keys) && is_array($keys)) {
            $this->db->where_in('setting_key', $keys);
        }

        $rows = $this->db->get($this->tbl_settings)->result_array();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function update_setting($key, $value)
    {
        $this->db->where('setting_key', $key);
        return $this->db->update($this->tbl_settings, [
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    // ========== ADMIN STATS ==========

    public function get_subscription_stats()
    {
        $stats = [];
        $activeStatuses = ['active', 'cancelled', 'past_due'];

        // Total active subscribers
        $this->db->where_in('status', $activeStatuses);
        $stats['active_subscribers'] = $this->db->count_all_results($this->tbl_subscriptions);

        // By tier
        $this->db->select('st.tier_key, st.tier_name, COUNT(*) as count');
        $this->db->from($this->tbl_subscriptions . ' ps');
        $this->db->join($this->tbl_tiers . ' st', 'st.id = ps.tier_id');
        $this->db->where_in('ps.status', $activeStatuses);
        $this->db->group_by('ps.tier_id');
        $stats['by_tier'] = $this->db->get()->result_array();

        // Total entries granted
        $this->db->select_sum('quantity');
        $row = $this->db->get($this->tbl_entries_ledger)->row_array();
        $stats['total_entries_granted'] = $row ? (int)$row['quantity'] : 0;

        // Monthly revenue estimate
        $this->db->select('SUM(st.price_monthly_cents) as total_cents');
        $this->db->from($this->tbl_subscriptions . ' ps');
        $this->db->join($this->tbl_tiers . ' st', 'st.id = ps.tier_id');
        $this->db->where_in('ps.status', $activeStatuses);
        $rev = $this->db->get()->row_array();
        $stats['monthly_revenue_cents'] = $rev ? (int)$rev['total_cents'] : 0;

        return $stats;
    }

    // ========== ADMIN: Adjust entries ==========

    public function admin_adjust_entries($user_id, $draw_id, $quantity, $admin_id)
    {
        $this->db->insert($this->tbl_entries_ledger, [
            'user_id' => $user_id,
            'draw_id' => $draw_id,
            'quantity' => $quantity,
            'source' => 'admin_manual',
            'meta' => json_encode(['admin_id' => $admin_id, 'reason' => 'Manual adjustment']),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update user total
        $this->db->where('id', $user_id);
        $this->db->set('total_giveaway_entries', 'total_giveaway_entries + ' . (int)$quantity, FALSE);
        $this->db->update('tbl_users');

        return $this->db->insert_id();
    }

    // ========== Search users for admin ==========

    private function build_subscriber_query($search = '', $status_filter = 'active')
    {
        $this->db->from($this->tbl_subscriptions . ' ps');
        $this->db->join('tbl_users u', 'u.id = ps.user_id');
        $this->db->join('tbl_user_credentials uc', 'uc.user_id = ps.user_id');
        $this->db->join($this->tbl_tiers . ' st', 'st.id = ps.tier_id');

        if ($status_filter === 'active') {
            $this->db->where_in('ps.status', ['active', 'cancelled', 'past_due']);
        } elseif ($status_filter === 'expired') {
            $this->db->where('ps.status', 'expired');
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('u.first_name', $search);
            $this->db->or_like('u.last_name', $search);
            $this->db->or_like('uc.email', $search);
            $this->db->group_end();
        }
    }

    public function search_subscribers($search = '', $limit = 20, $offset = 0, $status_filter = 'active')
    {
        $this->db->select('ps.*, u.first_name, u.last_name, u.residential_state, u.coin_balance, u.total_giveaway_entries as total_entries, uc.email, st.tier_name, st.tier_key');
        $this->build_subscriber_query($search, $status_filter);

        $this->db->order_by('ps.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    public function count_subscribers($search = '', $status_filter = 'active')
    {
        $this->db->select('ps.id');
        $this->build_subscriber_query($search, $status_filter);
        return (int)$this->db->count_all_results();
    }

    // ========== State-based prize eligibility for CSV export ==========

    public function get_draw_participants_for_export_with_state_filter($draw_id, $exclude_states = ['SA'])
    {
        $escaped_states = array_map(function ($state) {
            return $this->db->escape_str($state);
        }, $exclude_states);
        $excluded_states_list = "'" . implode("','", $escaped_states) . "'";

        $this->db->select(" 
            u.id as User_ID,
            COALESCE(u.punter_id, CONCAT('SP-', LPAD(u.id, 4, '0'))) as Unique_ID,
            SUM(el.quantity) as Weight,
            SUM(el.quantity) as Entries,
            u.first_name as First_Name,
            LEFT(u.last_name, 1) as Last_Initial,
            u.residential_state as State,
            uc.email as Email,
            CASE WHEN u.residential_state IN ($excluded_states_list) THEN 0 ELSE 1 END as eligible_for_major_prize
        ");
        $this->db->from($this->tbl_entries_ledger . ' el');
        $this->db->join('tbl_users u', 'u.id = el.user_id');
        $this->db->join('tbl_user_credentials uc', 'uc.user_id = u.id', 'left');
        $this->db->where('el.draw_id', $draw_id);
        $this->db->group_by('el.user_id');
        $this->db->having('Weight >', 0);
        $this->db->order_by('Weight', 'DESC');
        return $this->db->get()->result_array();
    }
}

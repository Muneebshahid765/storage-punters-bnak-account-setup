<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VaultModel extends CI_Model
{
    protected $table_draws = 'tbl_vault_draws';
    protected $table_draw_items = 'tbl_vault_draw_items';
    protected $table_entries = 'tbl_vault_entries';
    protected $table_transactions = 'tbl_vault_entry_transactions';
    protected $table_entry_transactions = 'tbl_vault_entry_transactions';
    protected $table_winners = 'tbl_vault_winners';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_draws()
    {
        $this->db->order_by('CASE WHEN status = "active" THEN 1 WHEN status = "scheduled" THEN 2 ELSE 3 END', 'ASC', FALSE);
        $this->db->order_by('draw_at', 'DESC');
        $query = $this->db->get($this->table_draws);
        return $query->result_array();
    }

    public function get_active_draw()
    {
        // First check active draws
        $this->db->where('status', 'active');
        $this->db->order_by('draw_at', 'DESC');
        $query = $this->db->get($this->table_draws);
        $draw = $query->row_array();

        if ($draw) return $draw;

        // Then check locked draws (time passed, awaiting winner)
        $this->db->where('status', 'locked');
        $this->db->order_by('draw_at', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table_draws);
        $draw = $query->row_array();

        if ($draw) return $draw;

        // Then scheduled draws with items
        $this->db->select('d.*');
        $this->db->from($this->table_draws . ' d');
        $this->db->join($this->table_draw_items . ' di', 'di.vault_draw_id = d.id');
        $this->db->where('d.status', 'scheduled');
        $this->db->where('d.draw_at >', date('Y-m-d H:i:s'));
        $this->db->group_by('d.id');
        $this->db->order_by('d.draw_at', 'ASC');
        $this->db->limit(1);
        
        $query = $this->db->get();
        $draw = $query->row_array();

        if ($draw) return $draw;

        // Finally any scheduled draw
        $this->db->where('status', 'scheduled');
        $this->db->where('draw_at >', date('Y-m-d H:i:s'));
        $this->db->order_by('draw_at', 'ASC');
        $this->db->limit(1);
        $query = $this->db->get($this->table_draws);
        return $query->row_array();
    }

    public function getActiveDraw()
    {
        $res = $this->get_active_draw();
        return $res ? (object)$res : null;
    }

    public function get_draw_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->table_draws);
        return $query->row_array();
    }

    public function get_draw_items($draw_id)
    {
        $this->db->select('id, vault_draw_id, item_name as title, description, image_url, estimated_value');
        $this->db->where('vault_draw_id', $draw_id);
        $query = $this->db->get($this->table_draw_items);
        return $query->result_array();
    }

    public function getDrawItems($draw_id)
    {
        return $this->db->get_where($this->table_draw_items, ['vault_draw_id' => $draw_id])->result();
    }

    public function getUpcomingDraws()
    {
        $this->db->where('status', 'scheduled');
        $this->db->order_by('draw_at', 'ASC');
        return $this->db->get($this->table_draws)->result();
    }

    public function get_user_entries($draw_id, $user_id)
    {
        $this->db->where('vault_draw_id', $draw_id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get($this->table_entries);
        $row = $query->row_array();
        return $row ? (int)$row['entries'] : 0;
    }

    public function getUserEntries($user_id, $draw_id)
    {
        return $this->get_user_entries($draw_id, $user_id);
    }

    public function add_entries($draw_id, $user_id, $entries_delta, $type, $meta = null)
    {
        $this->db->trans_start();

        // 1. Record the transaction in the ledger
        $transactionData = [
            'vault_draw_id' => $draw_id,
            'user_id' => $user_id,
            'type' => $type,
            'entries_delta' => $entries_delta,
            'meta' => $meta ? json_encode($meta) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert($this->table_transactions, $transactionData);

        // 2. Update the aggregate entries table
        $this->db->where('vault_draw_id', $draw_id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get($this->table_entries);

        if ($query->num_rows() > 0) {
            $this->db->set('entries', 'entries + ' . (int)$entries_delta, FALSE);
            $this->db->set('updated_at', date('Y-m-d H:i:s'));
            $this->db->where('vault_draw_id', $draw_id);
            $this->db->where('user_id', $user_id);
            $this->db->update($this->table_entries);
        } else {
            $entryData = [
                'vault_draw_id' => $draw_id,
                'user_id' => $user_id,
                'entries' => $entries_delta,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert($this->table_entries, $entryData);
        }

        // 3. Update the user's cached total
        $this->db->set('current_draw_entries', 'current_draw_entries + ' . (int)$entries_delta, FALSE);
        $this->db->where('id', $user_id);
        $this->db->update('tbl_users');

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function addEntries($user_id, $draw_id, $amount, $type, $meta = null)
    {
        return $this->add_entries($draw_id, $user_id, $amount, $type, $meta);
    }

    public function get_participants($draw_id)
    {
        $this->db->select('ve.*, u.first_name, u.last_name, uc.email');
        $this->db->from($this->table_entries . ' ve');
        $this->db->join('tbl_users u', 'u.id = ve.user_id');
        $this->db->join('tbl_user_credentials uc', 'u.id = uc.user_id');
        $this->db->where('ve.vault_draw_id', $draw_id);
        $this->db->where('ve.entries >', 0);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function save_winner($winner_data)
    {
        return $this->db->insert($this->table_winners, $winner_data);
    }

    public function get_winners($limit = 10)
    {
        $this->db->select('vw.*, CONCAT(u.first_name, " ", u.last_name) as user_name, vw.winner_display, vw.winning_entries as entries_used');
        $this->db->from($this->table_winners . ' vw');
        $this->db->join('tbl_users u', 'u.id = vw.user_id');
        $this->db->order_by('vw.won_at', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getHallOfFame($limit = 10)
    {
        // Get vault winners with prize value
        $this->db->select('w.winner_display, w.punter_id, w.won_at, w.vault_draw_id, d.title as draw_title, d.prize_value_cents, NULL as certificate_url', FALSE);
        $this->db->from($this->table_winners . ' w');
        $this->db->join($this->table_draws . ' d', 'd.id = w.vault_draw_id');
        $this->db->order_by('w.won_at', 'DESC');
        $this->db->limit($limit);
        $vault_winners = $this->db->get()->result();

        // Attach items to vault winners
        foreach ($vault_winners as $winner) {
            $winner->items = $this->getDrawItems($winner->vault_draw_id);
            $winner->source = 'vault';
        }

        // Get giveaway winners
        $giveaway_winners = [];
        if ($this->db->table_exists('tbl_draw_winners')) {
            $this->db->select('dw.winner_display, dw.punter_id, dw.won_at, dw.prize_amount_cents as prize_value_cents, dw.certificate_url, gd.title as draw_title');
            $this->db->from('tbl_draw_winners dw');
            $this->db->join('tbl_giveaway_draws gd', 'gd.id = dw.draw_id');
            $this->db->order_by('dw.won_at', 'DESC');
            $this->db->limit($limit);
            $giveaway_winners = $this->db->get()->result();

            foreach ($giveaway_winners as $gw) {
                $gw->items = [];
                $gw->source = 'giveaway';
            }
        }

        // Merge and sort by won_at DESC
        $all_winners = array_merge($vault_winners, $giveaway_winners);
        usort($all_winners, function($a, $b) {
            return strtotime($b->won_at) - strtotime($a->won_at);
        });

        return array_slice($all_winners, 0, $limit);
    }

    public function update_draw_status($draw_id, $status)
    {
        $this->db->where('id', $draw_id);
        return $this->db->update($this->table_draws, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function create_draw($data, $items = [])
    {
        $this->db->trans_start();
        $this->db->insert($this->table_draws, $data);
        $draw_id = $this->db->insert_id();

        foreach ($items as $item_name) {
            $this->db->insert($this->table_draw_items, [
                'vault_draw_id' => $draw_id,
                'item_name' => $item_name
            ]);
        }

        $this->db->trans_complete();
        return $draw_id;
    }

    public function drawWinner($draw_id)
    {
        // 1. Get all users with entries > 0 for this draw
        $participants = $this->get_participants($draw_id);
        if (empty($participants)) return null;

        // 2. Calculate total weight
        $totalWeight = 0;
        foreach ($participants as $p) {
            $totalWeight += $p['entries'];
        }

        // 3. Pick a random number
        $rand = mt_rand(1, $totalWeight);

        // 4. Find the winner
        $currentWeight = 0;
        $winner_user_id = null;
        $winning_entries = 0;
        $winner_obj = null;

        foreach ($participants as $p) {
            $currentWeight += $p['entries'];
            if ($rand <= $currentWeight) {
                $winner_user_id = $p['user_id'];
                $winning_entries = $p['entries'];
                $winner_obj = $p;
                break;
            }
        }

        if (!$winner_user_id) return null;

        // Format: TOBIAS C SP 1729
        $punterId = "SP-" . str_pad($winner_user_id, 4, '0', STR_PAD_LEFT);
        $lastNameInitial = substr($winner_obj['last_name'], 0, 1);
        $token = strtoupper("{$winner_obj['first_name']} {$lastNameInitial} {$punterId}");

        // 5. Save to Winners Table
        $draw = $this->get_draw_by_id($draw_id);

        $winnerData = [
            'vault_draw_id' => $draw_id,
            'user_id' => $winner_user_id,
            'winner_display' => $token,
            'punter_id' => $punterId,
            'winning_entries' => $winning_entries,
            'prize_value_cents' => $draw['prize_value_cents'] ?? 0,
            'won_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert($this->table_winners, $winnerData);

        // 6. Update Drop Status to 'completed'
        $this->update_draw_status($draw_id, 'completed');

        return $winnerData;
    }

    public function get_stats()
    {
        $this->db->where('status', 'active');
        $active_count = $this->db->count_all_results($this->table_draws);

        $this->db->select_sum('entries_delta');
        $this->db->where('entries_delta >', 0);
        $query = $this->db->get($this->table_transactions);
        $total_entries = $query->row()->entries_delta ?? 0;

        $this->db->select_sum('estimated_value');
        $query = $this->db->get($this->table_draw_items);
        $total_value = $query->row()->estimated_value ?? 0;

        $recent_winners = $this->get_winners(5);

        return [
            'active_draws' => $active_count,
            'total_entries' => $total_entries,
            'total_prize_value' => $total_value,
            'recent_winners' => $recent_winners
        ];
    }

    public function getCoinPackages()
    {
        $this->db->where('is_active', 1);
        $this->db->order_by('coin_price', 'ASC');
        return $this->db->get('tbl_entry_shop_coin_packages')->result();
    }

    public function getCashPackages()
    {
        $this->db->where('is_active', 1);
        $this->db->order_by('price_cents', 'ASC');
        return $this->db->get('tbl_entry_shop_cash_packages')->result();
    }

    public function getCoinPackage($id)
    {
        return $this->db->get_where('tbl_entry_shop_coin_packages', ['id' => $id, 'is_active' => 1])->row();
    }

    public function getCashPackage($id)
    {
        return $this->db->get_where('tbl_entry_shop_cash_packages', ['id' => $id, 'is_active' => 1])->row();
    }
}
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminSubscriptions extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Check admin authentication
        $session = $this->session->userdata('auction_user_session_data');
        if (!$session || $session['role_id'] != 4) {
            redirect(base_url());
            return;
        }

        $this->load->model('SubscriptionGiveawayModel');
        $this->load->model('UserModel');
    }

    /**
     * Get subscription statistics
     */
    public function getStats()
    {
        header('Content-Type: application/json');

        $stats = $this->SubscriptionGiveawayModel->get_subscription_stats();

        $tierCounts = [
            'player' => 0,
            'shark' => 0,
            'whale' => 0
        ];

        if (!empty($stats['by_tier'])) {
            foreach ($stats['by_tier'] as $tier) {
                $key = strtolower($tier['tier_key'] ?? $tier['tier_name'] ?? '');
                if (isset($tierCounts[$key])) {
                    $tierCounts[$key] = (int)$tier['count'];
                }
            }
        }

        $response = [
            'total_subscribers' => (int)($stats['active_subscribers'] ?? 0),
            'players' => $tierCounts['player'],
            'sharks' => $tierCounts['shark'],
            'whales' => $tierCounts['whale'],
            'total_entries_granted' => (int)($stats['total_entries_granted'] ?? 0),
            'monthly_revenue' => round(((int)($stats['monthly_revenue_cents'] ?? 0)) / 100, 2)
        ];

        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
    }

    /**
     * Get all subscription tiers
     */
    public function getTiers()
    {
        header('Content-Type: application/json');

        // Admin list should include inactive tiers too so they can be re-enabled
        $tiers = $this->SubscriptionGiveawayModel->get_all_tiers(false);

        echo json_encode([
            'success' => true,
            'data' => $tiers
        ]);
    }

    /**
     * Get single tier by ID
     */
    public function getTier($id)
    {
        header('Content-Type: application/json');

        $tier = $this->SubscriptionGiveawayModel->get_tier_by_id($id);

        if ($tier) {
            echo json_encode([
                'success' => true,
                'data' => $tier
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Tier not found'
            ]);
        }
    }

    /**
     * Update subscription tier
     */
    public function updateTier($id)
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $updateData = [];
        if (isset($input['price_monthly_cents'])) $updateData['price_monthly_cents'] = $input['price_monthly_cents'];
        if (isset($input['base_entries'])) $updateData['base_entries'] = $input['base_entries'];
        if (isset($input['fee_rate'])) $updateData['fee_rate'] = $input['fee_rate'];
        if (isset($input['coin_multiplier'])) $updateData['coin_multiplier'] = $input['coin_multiplier'];
        if (isset($input['is_active'])) $updateData['is_active'] = $input['is_active'];
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $updated = $this->db->update('tbl_subscription_tiers', $updateData);

        if ($updated) {
            echo json_encode([
                'success' => true,
                'message' => 'Tier updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update tier'
            ]);
        }
    }

    /**
     * Get all active subscriptions with user details
     */
    public function getSubscriptions()
    {
        header('Content-Type: application/json');

        $subscriptions = $this->db->query("
            SELECT 
                ps.*,
                st.tier_name,
                st.tier_key,
                u.first_name,
                u.last_name,
                uc.email,
                COALESCE(u.total_giveaway_entries, 0) as total_entries,
                DATE_FORMAT(ps.current_period_end, '%d %b %Y') as next_billing_date
            FROM tbl_punter_subscriptions ps
            JOIN tbl_subscription_tiers st ON ps.tier_id = st.id
            JOIN tbl_users u ON ps.user_id = u.id
            LEFT JOIN tbl_user_credentials uc ON u.id = uc.user_id
            WHERE ps.status IN ('active', 'past_due')
            ORDER BY ps.created_at DESC
        ")->result_array();

        echo json_encode([
            'success' => true,
            'data' => $subscriptions
        ]);
    }

    /**
     * Cancel user subscription (admin action)
     */
    public function cancelSubscription($subscriptionId)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            exit;
        }

        $subscription = $this->db->get_where('tbl_punter_subscriptions', ['id' => $subscriptionId])->row_array();

        if (!$subscription) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Subscription not found']);
            exit;
        }

        // Cancel in Stripe
        try {
            $secretKey = stripe_secret_key();
            if (!$secretKey) {
                throw new \Exception('Stripe secret key not configured');
            }

            \Stripe\Stripe::setApiKey($secretKey);
            
            $stripeSub = \Stripe\Subscription::retrieve($subscription['stripe_subscription_id']);
            $stripeSub->cancel();

            // Update local record
            $this->SubscriptionGiveawayModel->expire_subscription($subscriptionId);

            echo json_encode([
                'success' => true,
                'message' => 'Subscription cancelled successfully'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Manually grant entries to a user
     */
    public function grantEntries()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['user_id'] ?? null;
        $entries = $input['entries'] ?? 0;
        $reason = $input['reason'] ?? 'Admin manual grant';

        if (!$userId || $entries <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $activeDraw = $this->SubscriptionGiveawayModel->get_active_draw();
        if (!$activeDraw) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No active draw']);
            exit;
        }

        // Grant entries
        $this->db->insert('tbl_entries_ledger', [
            'user_id' => $userId,
            'draw_id' => $activeDraw['id'],
            'quantity' => $entries,
            'source' => 'admin_grant',
            'description' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update user total
        $this->db->set('total_giveaway_entries', 'total_giveaway_entries + ' . $entries, FALSE);
        $this->db->where('id', $userId);
        $this->db->update('tbl_users');

        echo json_encode([
            'success' => true,
            'message' => "Granted {$entries} entries successfully"
        ]);
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Subscription as StripeSubscription;
use Stripe\Product;
use Stripe\Price;
use Stripe\Exception\InvalidRequestException;

class SubscriptionGiveaway extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $secretKey = stripe_secret_key();
        if ($secretKey) {
            Stripe::setApiKey($secretKey);
        } else {
            log_message('error', 'Stripe secret key missing; please configure STRIPE_SECRET_KEY in .env');
        }
        date_default_timezone_set('Australia/Brisbane');

        $this->load->model('SubscriptionGiveawayModel');
        $this->load->model('UserModel');
        $this->load->model('VaultModel');
        $this->load->model('CoinModel');
    }

    // ========== PUBLIC PAGES ==========

    /**
     * Subscription Pricing Page (Join as Punter)
     */
    public function plans()
    {
        $data['tiers'] = $this->SubscriptionGiveawayModel->get_all_tiers();
        $data['states'] = $this->SubscriptionGiveawayModel->get_all_states();
        $data['active_draw'] = $this->SubscriptionGiveawayModel->get_active_draw();
        $data['vault_settings'] = $this->SubscriptionGiveawayModel->get_all_settings();

        // Check if user already has subscription
        $session_data = $this->session->userdata('auction_user_session_data');
        $data['user_subscription'] = null;
        if ($session_data) {
            $data['user_subscription'] = $this->SubscriptionGiveawayModel->get_user_subscription($session_data['user_id']);
        }

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('subscription/plans', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    /**
     * Express Checkout Page
     */
    public function checkout($tier_key = null)
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $role_id = $session_data['role_id'] ?? null;
        
        // Check if user is logged in with non-bidder account
        if ($session_data && (int)$role_id !== 2) {
            // User is logged in but not a bidder - show warning but allow access
            $this->session->set_flashdata('warning', 'This subscription is for bidder accounts. Please create a bidder account or logout to continue.');
        }

        if (!$tier_key) {
            redirect('join');
            return;
        }

        $tier = $this->SubscriptionGiveawayModel->get_tier_by_key($tier_key);
        if (!$tier) {
            redirect('join');
            return;
        }

        $data['tier'] = $tier;
        $data['states'] = $this->SubscriptionGiveawayModel->get_all_states();
        $data['active_draw'] = $this->SubscriptionGiveawayModel->get_active_draw();
        $data['vault_settings'] = $this->SubscriptionGiveawayModel->get_all_settings();
        $data['is_logged_in'] = !empty($session_data);
        $data['is_bidder'] = $session_data && (int)$role_id === 2;

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('subscription/checkout', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    // ========== API ENDPOINTS ==========

    /**
     * API: Get all tiers
     */
    public function api_tiers()
    {
        $tiers = $this->SubscriptionGiveawayModel->get_all_tiers();
        $draw = $this->SubscriptionGiveawayModel->get_active_draw();

        return $this->respond(true, 'Tiers fetched', [
            'tiers' => $tiers,
            'active_draw' => $draw
        ]);
    }

    /**
     * API: Get countdown data
     */
    public function api_countdown()
    {
        $draw = $this->SubscriptionGiveawayModel->get_active_draw();
        $settings = $this->SubscriptionGiveawayModel->get_all_settings();
        $jackpot = $settings['current_jackpot_text'] ?? null;

        $response = [
            'jackpot_text' => $jackpot,
            'settings' => $settings,
            'draw' => null,
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => 'Australia/Brisbane'
        ];

        if ($draw) {
            $now = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
            $draw_time = new DateTime($draw['draw_at'], new DateTimeZone('Australia/Brisbane'));

            $response['draw'] = [
                'id' => $draw['id'],
                'title' => $draw['title'],
                'prize_value' => '$' . number_format($draw['prize_value_cents'] / 100),
                'prize_2nd' => $draw['prize_2nd_cents'] > 0 ? '$' . number_format($draw['prize_2nd_cents'] / 100) : null,
                'prize_3rd' => $draw['prize_3rd_cents'] > 0 ? '$' . number_format($draw['prize_3rd_cents'] / 100) : null,
                'draw_at' => $draw['draw_at'],
                'draw_date_formatted' => $draw_time->format('g:i A jS F Y'),
                'seconds_remaining' => max(0, $draw_time->getTimestamp() - $now->getTimestamp()),
                'status' => $draw['status']
            ];
        }

        return $this->respond(true, 'Countdown data', $response);
    }

    /**
     * API: Create Stripe Checkout Session for subscription
     */
    public function create_checkout_session()
    {
        // Set CORS headers to allow cross-origin requests
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);

        $tier_key = $input['tier_key'] ?? null;
        $first_name = $input['first_name'] ?? '';
        $last_name = $input['last_name'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $state = $input['state'] ?? '';

        if (!$tier_key || !$first_name || !$last_name || !$email || !$state) {
            return $this->respond(false, 'All fields are required.', [], 400);
        }

        // Check state eligibility
        if (!$this->SubscriptionGiveawayModel->is_state_eligible($state)) {
            $disclaimer = $this->SubscriptionGiveawayModel->get_state_disclaimer($state);
            return $this->respond(false, $disclaimer ?? 'Your state is not eligible for the draw.', [], 403);
        }

        $tier = $this->SubscriptionGiveawayModel->get_tier_by_key($tier_key);
        if (!$tier) {
            return $this->respond(false, 'Invalid subscription tier.', [], 400);
        }

        try {
            // Ensure Stripe product & price exist
            $this->ensure_stripe_tier($tier);
            $tier = $this->SubscriptionGiveawayModel->get_tier_by_key($tier_key); // Refresh

            if (empty($tier['stripe_price_id'])) {
                return $this->respond(false, 'Payment configuration error. Please contact support.', [], 500);
            }

            // Find or create Stripe customer
            $customers = Customer::all(['email' => strtolower($email), 'limit' => 1]);
            if (!empty($customers->data)) {
                $customer = $customers->data[0];
            } else {
                $customer = Customer::create([
                    'email' => strtolower($email),
                    'name' => $first_name . ' ' . $last_name,
                    'phone' => $phone,
                    'metadata' => [
                        'state' => $state,
                        'source' => 'giveaway_subscription'
                    ]
                ]);
            }

            // Check if user exists in our system
            $session_data = $this->session->userdata('auction_user_session_data');
            $user_id = $session_data['user_id'] ?? null;

            // Prevent duplicate subscriptions - check if user already has active subscription
            if ($user_id) {
                $existing_sub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
                if ($existing_sub && $existing_sub['status'] === 'active') {
                    $current_tier = $this->SubscriptionGiveawayModel->get_tier_by_id($existing_sub['tier_id']);
                    $new_tier = $tier;
                    
                    // Allow upgrade only if new tier is higher value
                    if ($new_tier['price_monthly_cents'] <= $current_tier['price_monthly_cents']) {
                        return $this->respond(false, 'You already have an active ' . $current_tier['tier_name'] . ' subscription. To change tiers, please cancel your current subscription first or upgrade to a higher tier.', [
                            'current_tier' => $current_tier['tier_name'],
                            'current_price' => $current_tier['price_monthly_cents']
                        ], 400);
                    }
                    
                    // Allow upgrade - webhook will handle canceling old subscription
                    log_message('info', 'User ' . $user_id . ' upgrading from ' . $current_tier['tier_name'] . ' to ' . $new_tier['tier_name']);
                }
            }

            // Create Stripe Checkout Session
            $checkout_session = StripeCheckoutSession::create([
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'line_items' => [[
                    'price' => $tier['stripe_price_id'],
                    'quantity' => 1,
                ]],
                'metadata' => [
                    'tier_key' => $tier_key,
                    'tier_id' => $tier['id'],
                    'user_id' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $phone,
                    'state' => $state,
                    'type' => 'punter_subscription'
                ],
                'subscription_data' => [
                    'metadata' => [
                        'tier_key' => $tier_key,
                        'tier_id' => $tier['id'],
                        'user_id' => $user_id,
                        'state' => $state,
                        'type' => 'punter_subscription'
                    ]
                ],
                'success_url' => site_url('subscription/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => site_url('join'),
                'allow_promotion_codes' => true,
            ]);

            return $this->respond(true, 'Checkout session created', [
                'checkout_url' => $checkout_session->url,
                'session_id' => $checkout_session->id
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            log_message('error', 'Stripe Checkout Error: ' . $e->getMessage());
            return $this->respond(false, 'Payment error: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Subscription Success Page
     */
    public function success()
    {
        $session_id = $this->input->get('session_id');

        $data = [
            'success' => true,
            'message' => 'Welcome to Storage Punters Rewards Club!'
        ];

        if ($session_id) {
            try {
                $session = StripeCheckoutSession::retrieve($session_id);
                $data['tier_key'] = $session->metadata->tier_key ?? 'player';
                $data['customer_email'] = $session->customer_details->email ?? '';
            } catch (\Exception $e) {
                log_message('error', 'Success page error: ' . $e->getMessage());
            }
        }

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('subscription/success', $data);
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    /**
     * API: Get user membership status
     */
    public function api_membership_status()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;

        if (!$user_id) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $sub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
        $user = $this->db->get_where('tbl_users', ['id' => $user_id])->row_array();
        $punter_id = $user['punter_id'] ?? ('SP-' . str_pad($user_id, 4, '0', STR_PAD_LEFT));

        if (!$sub) {
            return $this->respond(true, 'No subscription', [
                'has_subscription' => false,
                'punter_id' => $punter_id
            ]);
        }

        // Fix: If current_period_end is NULL or invalid (e.g. 1970 fallback), fetch from Stripe and update
        $next_billing_raw = $sub['current_period_end'] ?? null;
        $is_invalid_billing_date = empty($next_billing_raw) || ($timestamp = strtotime($next_billing_raw)) === false || $timestamp < strtotime('2000-01-01');
        if ($is_invalid_billing_date) {
            // Try to fetch from Stripe
            if (!empty($sub['stripe_subscription_id'])) {
                try {
                    $stripe_sub = StripeSubscription::retrieve($sub['stripe_subscription_id']);
                    if ($stripe_sub && $stripe_sub->current_period_end) {
                        $next_billing_raw = date('Y-m-d H:i:s', $stripe_sub->current_period_end);
                        
                        // Update database with correct dates
                        $this->SubscriptionGiveawayModel->update_subscription($sub['id'], [
                            'current_period_start' => date('Y-m-d H:i:s', $stripe_sub->current_period_start),
                            'current_period_end' => $next_billing_raw
                        ]);
                        
                        log_message('info', 'Fixed missing current_period_end for subscription ' . $sub['id'] . ' from Stripe');
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to fetch Stripe subscription for period dates: ' . $e->getMessage());
                }
            }

            // Fallback: derive billing cycle locally if Stripe data unavailable
            if ($is_invalid_billing_date) {
                $fallback_start = $sub['current_period_start'] ?? null;
                $fallback_ts = $fallback_start ? strtotime($fallback_start) : false;

                if ($fallback_ts === false || $fallback_ts < strtotime('2000-01-01')) {
                    $created_ts = isset($sub['created_at']) ? strtotime($sub['created_at']) : false;
                    if ($created_ts !== false && $created_ts > strtotime('2000-01-01')) {
                        $fallback_ts = $created_ts;
                    }
                }

                if ($fallback_ts !== false && $fallback_ts > strtotime('2000-01-01')) {
                    $next_billing_raw = date('Y-m-d H:i:s', strtotime('+1 month', $fallback_ts));
                    $this->SubscriptionGiveawayModel->update_subscription($sub['id'], [
                        'current_period_start' => date('Y-m-d H:i:s', $fallback_ts),
                        'current_period_end' => $next_billing_raw
                    ]);
                    log_message('warning', 'Subscription ' . $sub['id'] . ' billing date rebuilt locally (Stripe unavailable)');
                }
            }
        }

        $next_billing_display = $next_billing_raw ? date('M j, Y', strtotime($next_billing_raw)) : null;

        $draw = $this->SubscriptionGiveawayModel->get_active_draw();
        $total_entries = $this->SubscriptionGiveawayModel->get_user_total_entries($user_id);
        $draw_entries = $draw ? $this->SubscriptionGiveawayModel->get_user_total_entries($user_id, $draw['id']) : 0;
        $breakdown = $this->SubscriptionGiveawayModel->get_user_entries_breakdown($user_id);
        $pending_cancel = $this->SubscriptionGiveawayModel->get_pending_cancellation($user_id);

        return $this->respond(true, 'Membership status', [
            'has_subscription' => true,
            'punter_id' => $punter_id,
            'subscription' => [
                'tier_name' => $sub['tier_name'],
                'tier_key' => $sub['tier_key'],
                'status' => $sub['status'],
                'cancel_at_period_end' => (bool)$sub['cancel_at_period_end'],
                'current_period_end' => $next_billing_raw,
                'next_billing_date' => $next_billing_raw,
                'next_billing_display' => $next_billing_display,
                'streak_months' => (int)$sub['streak_months'],
                'price' => '$' . number_format($sub['price_monthly_cents'] / 100),
                'fee_rate' => $sub['fee_rate'],
                'coin_multiplier' => $sub['coin_multiplier'],
                'total_entries' => $total_entries,
                'current_draw_entries' => $draw_entries
            ],
            'entries' => [
                'total' => $total_entries,
                'current_draw' => $draw_entries,
                'breakdown' => $breakdown
            ],
            'pending_cancellation' => $pending_cancel,
            'active_draw' => $draw
        ]);
    }

    /**
     * API: Cancel subscription (with warning)
     */
    public function api_cancel_subscription()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;

        if (!$user_id) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $sub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
        if (!$sub || $sub['status'] !== 'active') {
            return $this->respond(false, 'No active subscription found.');
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $confirmed = $input['confirmed'] ?? false;

        if (!$confirmed) {
            // Return warning data
            $total_entries = $this->SubscriptionGiveawayModel->get_user_total_entries($user_id);
            $tier = $this->SubscriptionGiveawayModel->get_tier_by_id($sub['tier_id']);

            return $this->respond(true, 'Cancellation warning', [
                'warning' => true,
                'current_entries' => $total_entries,
                'streak_months' => $sub['streak_months'],
                'base_entries_on_rejoin' => $tier['base_entries'],
                'period_end' => $sub['current_period_end'],
                'message' => "Warning: You currently have {$total_entries} entries. If you cancel and sign up again later, your streak will reset and you will start back at {$tier['base_entries']} entries. Do you wish to proceed?"
            ]);
        }

        try {
            // Cancel on Stripe at period end
            if (!empty($sub['stripe_subscription_id'])) {
                StripeSubscription::update($sub['stripe_subscription_id'], [
                    'cancel_at_period_end' => true,
                ]);
            }

            // Update local DB
            $this->SubscriptionGiveawayModel->cancel_subscription($sub['id'], $user_id);

            // Log cancellation
            $this->SubscriptionGiveawayModel->log_cancellation([
                'user_id' => $user_id,
                'subscription_id' => $sub['id'],
                'stripe_subscription_id' => $sub['stripe_subscription_id'],
                'reason' => $input['reason'] ?? 'User requested cancellation',
                'entries_at_cancel' => $this->SubscriptionGiveawayModel->get_user_total_entries($user_id),
                'streak_at_cancel' => $sub['streak_months'],
                'undo_before' => $sub['current_period_end'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond(true, 'Subscription cancelled. You will have access until ' . date('M j, Y', strtotime($sub['current_period_end'])) . '.');

        } catch (\Stripe\Exception\ApiErrorException $e) {
            log_message('error', 'Cancel subscription error: ' . $e->getMessage());
            return $this->respond(false, 'Failed to cancel subscription. Please try again.');
        }
    }

    /**
     * API: Undo cancellation
     */
    public function api_undo_cancellation()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        $user_id = $session_data['user_id'] ?? null;

        if (!$user_id) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $sub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
        if (!$sub || !$sub['cancel_at_period_end']) {
            return $this->respond(false, 'No pending cancellation found.');
        }

        try {
            // Undo on Stripe
            if (!empty($sub['stripe_subscription_id'])) {
                StripeSubscription::update($sub['stripe_subscription_id'], [
                    'cancel_at_period_end' => false,
                ]);
            }

            // Update local DB
            $this->SubscriptionGiveawayModel->undo_cancellation($sub['id'], $user_id);

            // Mark cancellation log as undone
            $pending = $this->SubscriptionGiveawayModel->get_pending_cancellation($user_id);
            if ($pending) {
                $this->SubscriptionGiveawayModel->undo_cancellation_log($pending['id']);
            }

            return $this->respond(true, 'Cancellation reversed! Your streak and entries are preserved.');

        } catch (\Stripe\Exception\ApiErrorException $e) {
            log_message('error', 'Undo cancellation error: ' . $e->getMessage());
            return $this->respond(false, 'Failed to undo cancellation.');
        }
    }

    /**
     * API: Get states list
     */
    public function api_states()
    {
        $states = $this->SubscriptionGiveawayModel->get_all_states();
        return $this->respond(true, 'States fetched', ['states' => $states]);
    }

    // ========== ADMIN ENDPOINTS ==========

    /**
     * API: Admin - Get draw dashboard data
     */
    public function admin_draw_dashboard()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $draws = $this->SubscriptionGiveawayModel->get_all_draws();
        $stats = $this->SubscriptionGiveawayModel->get_subscription_stats();
        $active_draw = $this->SubscriptionGiveawayModel->get_active_draw();
        $settings = $this->SubscriptionGiveawayModel->get_all_settings([
            'current_jackpot_text',
            'next_prize_text',
            'nsw_permit_number',
            'sa_permit_number',
            'act_permit_number'
        ]);

        return $this->respond(true, 'Dashboard data', [
            'draws' => $draws,
            'stats' => $stats,
            'active_draw' => $active_draw,
            'settings' => $settings
        ]);
    }

    /**
     * API: Admin - Lock entries for draw
     */
    public function admin_lock_entries()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $draw_id = $input['draw_id'] ?? null;

        if (!$draw_id) {
            return $this->respond(false, 'Draw ID required', [], 400);
        }

        $this->SubscriptionGiveawayModel->lock_draw($draw_id);
        return $this->respond(true, 'Entries locked for draw.');
    }

    /**
     * API: Admin - Export CSV for RandomDraws (TPAL Integration)
     * Includes state-based eligibility flag for SA residents
     */
    public function admin_export_csv()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $draw_id = $this->input->get('draw_id');
        $export_type = $this->input->get('type') ?? 'all'; // 'all', 'major_only', 'minor_only'
        
        if (!$draw_id) {
            return $this->respond(false, 'Draw ID required', [], 400);
        }

        // Get participants with state eligibility flag
        $participants = $this->SubscriptionGiveawayModel->get_draw_participants_for_export_with_state_filter($draw_id);

        if (empty($participants)) {
            return $this->respond(false, 'No participants found for this draw.');
        }

        // Filter based on export type
        $filtered_participants = [];
        $sa_excluded_count = 0;
        
        foreach ($participants as $p) {
            $is_sa = strtoupper($p['State'] ?? '') === 'SA';
            
            if ($export_type === 'major_only' && $is_sa) {
                $sa_excluded_count++;
                continue; // Exclude SA residents from major prize draw
            }
            
            if ($export_type === 'minor_only' && !$is_sa) {
                continue; // Only include SA residents for minor prizes
            }
            
            $filtered_participants[] = $p;
        }

        if (empty($filtered_participants)) {
            return $this->respond(false, 'No eligible participants found for this export type.');
        }

        // Generate CSV
        $type_suffix = $export_type !== 'all' ? '_' . $export_type : '';
        $filename = 'draw_' . $draw_id . '_export' . $type_suffix . '_' . date('Ymd_His') . '.csv';
        $exportDir = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR;
        $filepath = $exportDir . $filename;

        // Ensure directory exists
        if (!is_dir($exportDir)) {
            $created = @mkdir($exportDir, 0755, true);
            if (!$created && !is_dir($exportDir)) {
                return $this->respond(false, 'Export folder could not be created on server.', [
                    'export_dir' => $exportDir,
                    'fcp' => FCPATH
                ], 500);
            }
        }

        if (!is_writable($exportDir)) {
            return $this->respond(false, 'Export folder is not writable on server.', [
                'export_dir' => $exportDir,
                'fcp' => FCPATH
            ], 500);
        }

        $fp = @fopen($filepath, 'w');
        if (!$fp) {
            return $this->respond(false, 'Unable to create CSV file on server.', [
                'file_path' => $filepath,
                'export_dir' => $exportDir
            ], 500);
        }
        fputcsv($fp, ['Unique_ID', 'User_ID', 'Entries', 'Weight', 'First_Name', 'Last_Initial', 'State', 'Email', 'Eligible_Major_Prize']);

        $total_weight = 0;
        foreach ($filtered_participants as $p) {
            $is_eligible = strtoupper($p['State'] ?? '') !== 'SA' ? 'YES' : 'NO';
            fputcsv($fp, [
                $p['Unique_ID'],
                $p['User_ID'],
                $p['Entries'],
                $p['Weight'],
                $p['First_Name'],
                $p['Last_Initial'],
                $p['State'],
                $p['Email'],
                $is_eligible
            ]);
            $total_weight += $p['Weight'];
        }
        fclose($fp);

        // Log export
        $this->SubscriptionGiveawayModel->save_export_log([
            'draw_id' => $draw_id,
            'exported_by' => $session_data['user_id'],
            'file_path' => $filepath,
            'total_participants' => count($filtered_participants),
            'total_weight' => $total_weight,
            'export_type' => $export_type,
            'sa_excluded' => $sa_excluded_count,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(true, 'CSV exported for TPAL/RandomDraws', [
            'download_url' => base_url('assets/exports/' . $filename),
            'participants' => count($filtered_participants),
            'total_weight' => $total_weight,
            'export_type' => $export_type,
            'sa_excluded' => $sa_excluded_count,
            'note' => $export_type === 'major_only' ? 'SA residents excluded from major prize draw per permit requirements' : null
        ]);
    }

    /**
     * API: Admin - Upload winner results
     */
    public function admin_upload_results()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $draw_id = $input['draw_id'] ?? null;
        $winners = $input['winners'] ?? [];

        if (!$draw_id || empty($winners)) {
            return $this->respond(false, 'Draw ID and winners required', [], 400);
        }

        $draw = $this->SubscriptionGiveawayModel->get_draw_by_id($draw_id);
        if (!$draw) {
            return $this->respond(false, 'Draw not found', [], 404);
        }

        $saved_winners = [];
        foreach ($winners as $idx => $winner) {
            $user_id = $winner['user_id'];
            $user = $this->db->get_where('tbl_users', ['id' => $user_id])->row();

            if (!$user) continue;

            $position = $idx + 1;
            $prize_cents = 0;
            if ($position === 1) $prize_cents = $draw['prize_value_cents'];
            elseif ($position === 2) $prize_cents = $draw['prize_2nd_cents'];
            elseif ($position === 3) $prize_cents = $draw['prize_3rd_cents'];

            $punter_id = 'SP-' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
            $display = strtoupper($user->first_name . ' ' . substr($user->last_name, 0, 1) . '. ' . $punter_id);

            $winner_data = [
                'draw_id' => $draw_id,
                'user_id' => $user_id,
                'prize_position' => $position,
                'prize_amount_cents' => $prize_cents,
                'winner_display' => $display,
                'punter_id' => $punter_id,
                'total_entries' => $this->SubscriptionGiveawayModel->get_user_total_entries($user_id, $draw_id),
                'certificate_url' => $winner['certificate_url'] ?? null,
                'won_at' => date('Y-m-d H:i:s')
            ];

            $this->SubscriptionGiveawayModel->save_winner($winner_data);
            $saved_winners[] = $winner_data;
        }

        // Update draw status
        $this->SubscriptionGiveawayModel->update_draw($draw_id, [
            'status' => 'completed',
            'winner_id' => $winners[0]['user_id'] ?? null,
            'winner_display' => $saved_winners[0]['winner_display'] ?? null,
            'certificate_url' => $winners[0]['certificate_url'] ?? null,
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(true, 'Winners uploaded successfully', ['winners' => $saved_winners]);
    }

    /**
     * API: Admin - Adjust user entries
     */
    public function admin_adjust_entries()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? null;
        $quantity = $input['quantity'] ?? 0;

        if (!$user_id || !$quantity) {
            return $this->respond(false, 'User ID and quantity required', [], 400);
        }

        $draw = $this->SubscriptionGiveawayModel->get_active_draw();
        $draw_id = $draw ? $draw['id'] : null;

        $this->SubscriptionGiveawayModel->admin_adjust_entries($user_id, $draw_id, $quantity, $session_data['user_id']);

        return $this->respond(true, 'Entries adjusted', [
            'user_id' => $user_id,
            'adjusted' => $quantity,
            'new_total' => $this->SubscriptionGiveawayModel->get_user_total_entries($user_id)
        ]);
    }

    /**
     * API: Admin - Adjust user coin balance
     */
    public function admin_adjust_coins()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? null;
        $amount = $input['amount'] ?? 0;
        $reason = $input['reason'] ?? 'Admin manual adjustment';

        if (!$user_id || $amount == 0) {
            return $this->respond(false, 'User ID and amount required', [], 400);
        }

        $type = $amount > 0 ? 'admin_credit' : 'admin_debit';
        $this->CoinModel->add_coins($user_id, $amount, $type, 'admin', $session_data['user_id'], $reason);

        return $this->respond(true, 'Coins adjusted', [
            'user_id' => $user_id,
            'adjusted' => $amount,
            'new_balance' => $this->CoinModel->get_balance($user_id)
        ]);
    }

    /**
     * API: Admin - Get user details with entries and coins
     */
    public function admin_get_user_details()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $user_id = $this->input->get('user_id');
        if (!$user_id) {
            return $this->respond(false, 'User ID required', [], 400);
        }

        $user = $this->db->get_where('tbl_users', ['id' => $user_id])->row_array();
        if (!$user) {
            return $this->respond(false, 'User not found', [], 404);
        }

        $creds = $this->db->get_where('tbl_user_credentials', ['user_id' => $user_id])->row_array();
        $sub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
        $draw = $this->SubscriptionGiveawayModel->get_active_draw();
        $entries_breakdown = $this->SubscriptionGiveawayModel->get_user_entries_breakdown($user_id);
        $coin_history = $this->CoinModel->getHistory($user_id, 10);

        return $this->respond(true, 'User details', [
            'user' => [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $creds['email'] ?? '',
                'residential_state' => $user['residential_state'] ?? '',
                'coin_balance' => $this->CoinModel->get_balance($user_id),
                'total_entries' => $this->SubscriptionGiveawayModel->get_user_total_entries($user_id),
                'current_draw_entries' => $draw ? $this->SubscriptionGiveawayModel->get_user_total_entries($user_id, $draw['id']) : 0
            ],
            'subscription' => $sub,
            'entries_breakdown' => $entries_breakdown,
            'coin_history' => $coin_history
        ]);
    }

    /**
     * API: Admin - Get draw winners list
     */
    public function admin_get_draw_winners()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $draw_id = $this->input->get('draw_id');
        if (!$draw_id) {
            return $this->respond(false, 'Draw ID required', [], 400);
        }

        $draw = $this->SubscriptionGiveawayModel->get_draw_by_id($draw_id);
        if (!$draw) {
            return $this->respond(false, 'Draw not found', [], 404);
        }

        $winners = $this->SubscriptionGiveawayModel->get_draw_winners($draw_id);

        return $this->respond(true, 'Winners fetched', [
            'draw' => $draw,
            'winners' => $winners
        ]);
    }

    /**
     * API: Admin - Search subscribers
     */
    public function admin_search_subscribers()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $search = $this->input->get('q') ?? '';
        $status_filter = $this->input->get('status') ?? 'active';
        $page = max(1, (int)($this->input->get('page') ?? 1));
        $per_page = max(1, min(100, (int)($this->input->get('per_page') ?? 10)));
        $load_all = (bool)$this->input->get('load_all');

        $limit = $load_all ? 0 : $per_page;
        $offset = $load_all ? 0 : ($page - 1) * $per_page;

        $total = $this->SubscriptionGiveawayModel->count_subscribers($search, $status_filter);
        $subscribers = $this->SubscriptionGiveawayModel->search_subscribers($search, $limit, $offset, $status_filter);

        $total_pages = $load_all ? 1 : ($per_page > 0 ? (int)ceil($total / $per_page) : 1);

        return $this->respond(true, 'Subscribers fetched', [
            'subscribers' => $subscribers,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => $total_pages,
                'load_all' => $load_all
            ]
        ]);
    }

    /**
     * API: Admin - Search users for winner selection
     */
    public function admin_search_users()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $query = $this->input->get('q') ?? '';
        $draw_id = (int)($this->input->get('draw_id') ?? 0);
        $limit = min(20, max(1, (int)($this->input->get('limit') ?? 10)));

        if (strlen($query) < 2) {
            return $this->respond(false, 'Query too short', [], 400);
        }

        if ($draw_id <= 0) {
            return $this->respond(false, 'Draw ID is required', [], 400);
        }

        $this->db->select('u.id, u.first_name, u.last_name, u.residential_state, uc.email, SUM(el.quantity) as total_entries');
        $this->db->from('tbl_entries_ledger el');
        $this->db->join('tbl_users u', 'u.id = el.user_id');
        $this->db->join('tbl_user_credentials uc', 'uc.user_id = u.id');
        $this->db->where('el.draw_id', $draw_id);
        
        $this->db->group_start();
        $this->db->like('u.first_name', $query);
        $this->db->or_like('u.last_name', $query);
        $this->db->or_like('uc.email', $query);
        $this->db->or_like('u.id', $query);
        $this->db->group_end();
        
        $this->db->group_by(['u.id', 'u.first_name', 'u.last_name', 'u.residential_state', 'uc.email']);
        $this->db->having('total_entries >', 0);
        $this->db->order_by('u.first_name', 'ASC');
        $this->db->limit($limit);
        
        $users = $this->db->get()->result_array();

        return $this->respond(true, 'Users found', ['users' => $users]);
    }

    /**
     * API: Admin - Update vault settings
     */
    public function admin_update_settings()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        foreach ($input as $key => $value) {
            $this->SubscriptionGiveawayModel->update_setting($key, $value);
        }

        return $this->respond(true, 'Settings updated');
    }

    /**
     * API: Admin - Create new draw
     */
    public function admin_create_draw()
    {
        $session_data = $this->session->userdata('auction_user_session_data');
        if (!$session_data || $session_data['role_id'] != 4) {
            return $this->respond(false, 'Unauthorized', [], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $requestedDate = isset($input['draw_at']) ? strtotime($input['draw_at']) : false;
        $minDate = strtotime('+30 days');
        if ($requestedDate === false || $requestedDate < $minDate) {
            $requestedDate = $minDate;
        }

        $draw_id = $this->SubscriptionGiveawayModel->create_draw([
            'draw_code' => 'DRAW-' . date('Ym') . '-' . strtoupper(substr(md5(time()), 0, 4)),
            'title' => 'Monthly Cash Giveaway',
            'description' => $input['description'] ?? '',
            'prize_value_cents' => ($input['prize_1st'] ?? 0) * 100,
            'prize_2nd_cents' => ($input['prize_2nd'] ?? 0) * 100,
            'prize_3rd_cents' => ($input['prize_3rd'] ?? 0) * 100,
            'draw_at' => date('Y-m-d H:i:s', $requestedDate),
            'status' => $input['status'] ?? 'scheduled',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(true, 'Draw created', ['draw_id' => $draw_id]);
    }

    // ========== WEBHOOK HANDLER ==========

    /**
     * Handle Stripe webhook for subscription events
     */
    public function stripe_webhook()
    {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

        if (ENVIRONMENT === 'development') {
            $event = json_decode($payload);
        } else {
            $webhookSecret = stripe_webhook_secret();
            if (!$webhookSecret) {
                log_message('error', 'Stripe webhook secret missing; please set STRIPE_WEBHOOK_SECRET in .env');
                return $this->respond(false, 'Webhook secret not configured', [], 500);
            }
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sig_header,
                    $webhookSecret
                );
            } catch (\Exception $e) {
                return $this->respond(false, 'Invalid webhook', [], 400);
            }
        }

        $type = $event->type;

        switch ($type) {
            case 'checkout.session.completed':
                $this->handle_checkout_completed($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handle_invoice_paid($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handle_subscription_updated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handle_subscription_deleted($event->data->object);
                break;
        }

        return $this->respond(true, 'Webhook processed');
    }

    private function handle_checkout_completed($session)
    {
        if (($session->metadata->type ?? '') !== 'punter_subscription') return;

        $tier_id = $session->metadata->tier_id;
        $tier_key = $session->metadata->tier_key;
        $user_id = $session->metadata->user_id;
        $state = $session->metadata->state;
        $first_name = $session->metadata->first_name ?? '';
        $last_name = $session->metadata->last_name ?? '';
        $stripe_sub_id = $session->subscription;
        $stripe_customer_id = $session->customer;

        // If user_id is null/empty, try to find user by email from Stripe customer
        if (empty($user_id)) {
            $customer_email = $session->customer_details->email ?? null;
            if (!$customer_email) {
                try {
                    $stripe_customer = Customer::retrieve($stripe_customer_id);
                    $customer_email = $stripe_customer->email;
                } catch (\Exception $e) {
                    log_message('error', 'Webhook: Could not retrieve Stripe customer: ' . $e->getMessage());
                }
            }

            if ($customer_email) {
                $user = $this->db->get_where('tbl_users', ['email' => strtolower($customer_email)])->row();
                if ($user) {
                    $user_id = $user->id;
                }
            }

            if (empty($user_id)) {
                log_message('error', "Webhook: Cannot find user for subscription. Email: {$customer_email}, tier: {$tier_key}");
                // Still create the subscription record so it can be linked later
            }
        }

        // Get subscription details from Stripe
        $stripe_sub = StripeSubscription::retrieve($stripe_sub_id);

        // Get tier details for applying features
        $tier = $this->SubscriptionGiveawayModel->get_tier_by_id($tier_id);

        // Create local subscription record
        $sub_id = $this->SubscriptionGiveawayModel->create_subscription([
            'user_id' => $user_id,
            'tier_id' => $tier_id,
            'stripe_customer_id' => $stripe_customer_id,
            'stripe_subscription_id' => $stripe_sub_id,
            'status' => 'active',
            'current_period_start' => date('Y-m-d H:i:s', $stripe_sub->current_period_start),
            'current_period_end' => date('Y-m-d H:i:s', $stripe_sub->current_period_end),
            'streak_months' => 1,
            'residential_state' => $state,
            'state_eligible' => $this->SubscriptionGiveawayModel->is_state_eligible($state) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update user record with full subscription features
        if ($user_id) {
            $user_update = [
                'punter_subscription_id' => $sub_id,
                'subscription_tier_key' => $tier_key,
                'streak_months' => 1,
                'residential_state' => $state,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Apply tier-specific features to user
            if ($tier) {
                if (isset($tier['fee_rate'])) {
                    $user_update['fee_rate'] = $tier['fee_rate'];
                }
                if (isset($tier['coin_multiplier'])) {
                    $user_update['coin_multiplier'] = $tier['coin_multiplier'];
                }
            }

            $this->db->where('id', $user_id);
            $this->db->update('tbl_users', $user_update);
        }

        // Grant initial entries
        $draw = $this->SubscriptionGiveawayModel->get_active_draw();
        if ($draw && $tier && $user_id) {
            $this->SubscriptionGiveawayModel->grant_monthly_entries(
                $user_id,
                $draw['id'],
                $tier['base_entries'],
                1 // Month 1
            );
        }

        // Grant signup coins
        if ($user_id) {
            $this->CoinModel->add_coins($user_id, 1000, 'subscription_signup', 'subscription', $sub_id, 'Welcome bonus for joining ' . $tier_key);
        }

        log_message('info', "Giveaway: New subscription created for user " . ($user_id ?: 'UNKNOWN') . ", tier {$tier_key}, sub_id {$sub_id}");
    }

    private function handle_invoice_paid($invoice)
    {
        if (empty($invoice->subscription)) return;

        $stripe_sub_id = $invoice->subscription;
        $sub = $this->SubscriptionGiveawayModel->get_subscription_by_stripe_id($stripe_sub_id);

        if (!$sub) return;

        // Skip first invoice (handled by checkout.session.completed)
        if ($invoice->billing_reason === 'subscription_create') return;

        // Increment streak
        $new_streak = $sub['streak_months'] + 1;

        $this->SubscriptionGiveawayModel->update_subscription($sub['id'], [
            'streak_months' => $new_streak,
            'status' => 'active',
            'cancel_at_period_end' => 0
        ]);

        // Get tier for applying features
        $tier = $this->SubscriptionGiveawayModel->get_tier_by_id($sub['tier_id']);

        // Update user with streak and tier features
        $user_update = [
            'streak_months' => $new_streak,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($tier) {
            $user_update['subscription_tier_key'] = $tier['tier_key'];
            if (isset($tier['fee_rate'])) $user_update['fee_rate'] = $tier['fee_rate'];
            if (isset($tier['coin_multiplier'])) $user_update['coin_multiplier'] = $tier['coin_multiplier'];
        }
        $this->db->where('id', $sub['user_id']);
        $this->db->update('tbl_users', $user_update);

        // Grant monthly entries with compounding
        $draw = $this->SubscriptionGiveawayModel->get_active_draw();
        $base_entries = $tier ? $tier['base_entries'] : ($sub['base_entries'] ?? 10);
        if ($draw) {
            $entries = $this->SubscriptionGiveawayModel->grant_monthly_entries(
                $sub['user_id'],
                $draw['id'],
                $base_entries,
                $new_streak
            );
            log_message('info', "Giveaway: Renewal for user {$sub['user_id']}, streak {$new_streak}, granted {$entries} entries");
        }

        // Award renewal coins
        $this->CoinModel->add_coins($sub['user_id'], 500, 'subscription_renewal', 'subscription', $sub['id'], "Month {$new_streak} renewal bonus");
    }

    private function handle_subscription_updated($stripe_sub)
    {
        $sub = $this->SubscriptionGiveawayModel->get_subscription_by_stripe_id($stripe_sub->id);
        if (!$sub) return;

        $update = [
            'current_period_start' => date('Y-m-d H:i:s', $stripe_sub->current_period_start),
            'current_period_end' => date('Y-m-d H:i:s', $stripe_sub->current_period_end),
            'cancel_at_period_end' => $stripe_sub->cancel_at_period_end ? 1 : 0
        ];

        if ($stripe_sub->status === 'past_due') {
            $update['status'] = 'past_due';
        }

        $this->SubscriptionGiveawayModel->update_subscription($sub['id'], $update);
    }

    private function handle_subscription_deleted($stripe_sub)
    {
        $sub = $this->SubscriptionGiveawayModel->get_subscription_by_stripe_id($stripe_sub->id);
        if (!$sub) return;

        // Expire subscription
        $this->SubscriptionGiveawayModel->expire_subscription($sub['id']);

        // Reset user streak
        $this->db->where('id', $sub['user_id']);
        $this->db->update('tbl_users', [
            'subscription_tier_key' => null,
            'streak_months' => 0,
            'punter_subscription_id' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        log_message('info', "Giveaway: Subscription expired for user {$sub['user_id']}");
    }

    // ========== HELPERS ==========

    private function ensure_stripe_tier($tier)
    {
        $product_id = $tier['stripe_product_id'] ?? null;
        $price_id = $tier['stripe_price_id'] ?? null;

        try {
            // Validate existing product/price IDs; if Stripe says resource missing, recreate.
            if (!empty($product_id)) {
                try {
                    Product::retrieve($product_id);
                } catch (InvalidRequestException $e) {
                    if ($e->getStripeCode() === 'resource_missing') {
                        log_message('warning', "Stripe product {$product_id} missing; recreating for tier {$tier['tier_key']}");
                        $product_id = null;
                    } else {
                        throw $e;
                    }
                }
            }

            if (empty($product_id)) {
                $product = Product::create([
                    'name' => 'Storage Punters - ' . $tier['tier_name'],
                    'description' => $tier['tier_name'] . ' subscription plan',
                    'metadata' => ['tier_key' => $tier['tier_key']]
                ]);
                $product_id = $product->id;
            }

            if (!empty($price_id)) {
                try {
                    Price::retrieve($price_id);
                } catch (InvalidRequestException $e) {
                    if ($e->getStripeCode() === 'resource_missing') {
                        log_message('warning', "Stripe price {$price_id} missing; recreating for tier {$tier['tier_key']}");
                        $price_id = null;
                    } else {
                        throw $e;
                    }
                }
            }

            if (empty($price_id)) {
                $price = Price::create([
                    'product' => $product_id,
                    'unit_amount' => $tier['price_monthly_cents'],
                    'currency' => 'aud',
                    'recurring' => ['interval' => 'month'],
                    'nickname' => $tier['tier_name'] . ' Monthly'
                ]);
                $price_id = $price->id;
            }

            $this->SubscriptionGiveawayModel->update_tier_stripe_ids($tier['id'], $product_id, $price_id);
        } catch (\Exception $e) {
            log_message('error', 'Stripe tier sync error: ' . $e->getMessage());
        }
    }

    /**
     * UTILITY: Fix all subscriptions with missing billing dates
     * Call this manually: /api/subscription/fix-billing-dates
     */
    public function fix_billing_dates()
    {
        $fixed = 0;
        $errors = 0;
        $details = [];

        // Helper closure to detect invalid billing dates
        $is_invalid = function ($date_string) {
            if (empty($date_string)) {
                return true;
            }
            $ts = strtotime($date_string);
            return $ts === false || $ts < strtotime('2000-01-01');
        };

        // Get all active subscriptions
        $this->db->select('id, user_id, stripe_subscription_id, current_period_start, current_period_end, created_at');
        $this->db->where_in('status', ['active', 'cancelled', 'past_due']);
        $subscriptions = $this->db->get('tbl_punter_subscriptions')->result_array();

        foreach ($subscriptions as $sub) {
            // Check if current_period_end is NULL or invalid
            if ($is_invalid($sub['current_period_end'] ?? null)) {
                if (!empty($sub['stripe_subscription_id'])) {
                    try {
                        // Fetch from Stripe
                        $stripe_sub = StripeSubscription::retrieve($sub['stripe_subscription_id']);

                        if ($stripe_sub && $stripe_sub->current_period_end) {
                            // Update database
                            $this->SubscriptionGiveawayModel->update_subscription($sub['id'], [
                                'current_period_start' => date('Y-m-d H:i:s', $stripe_sub->current_period_start),
                                'current_period_end' => date('Y-m-d H:i:s', $stripe_sub->current_period_end)
                            ]);

                            
                            $fixed++;
                            $details[] = [
                                'subscription_id' => $sub['id'],
                                'user_id' => $sub['user_id'],
                                'new_billing_date' => date('Y-m-d H:i:s', $stripe_sub->current_period_end),
                                'status' => 'fixed'
                            ];
                            
                            log_message('info', "Fixed billing date for subscription {$sub['id']}");
                        }
                    } catch (\Exception $e) {
                        $errors++;
                        $details[] = [
                            'subscription_id' => $sub['id'],
                            'user_id' => $sub['user_id'],
                            'error' => $e->getMessage(),
                            'status' => 'error'
                        ];
                        log_message('error', "Failed to fix subscription {$sub['id']}: " . $e->getMessage());
                    }
                }

                // Fallback to local calculation when Stripe unavailable
                if ($is_invalid($sub['current_period_end'] ?? null)) {
                    $fallback_ts = null;

                    if (!empty($sub['current_period_start']) && !$is_invalid($sub['current_period_start'])) {
                        $fallback_ts = strtotime($sub['current_period_start']);
                    }

                    if (!$fallback_ts && !empty($sub['created_at']) && !$is_invalid($sub['created_at'])) {
                        $fallback_ts = strtotime($sub['created_at']);
                    }

                    if ($fallback_ts) {
                        $rebuilt_end = date('Y-m-d H:i:s', strtotime('+1 month', $fallback_ts));
                        $this->SubscriptionGiveawayModel->update_subscription($sub['id'], [
                            'current_period_start' => date('Y-m-d H:i:s', $fallback_ts),
                            'current_period_end' => $rebuilt_end
                        ]);

                        $fixed++;
                        $details[] = [
                            'subscription_id' => $sub['id'],
                            'user_id' => $sub['user_id'],
                            'new_billing_date' => $rebuilt_end,
                            'status' => 'fixed_local'
                        ];

                        log_message('warning', 'Subscription ' . $sub['id'] . ' billing date rebuilt locally via fixer (Stripe unavailable)');
                        continue;
                    }
                }

                if ($is_invalid($sub['current_period_end'] ?? null)) {
                    $errors++;
                    $details[] = [
                        'subscription_id' => $sub['id'],
                        'user_id' => $sub['user_id'],
                        'error' => 'Unable to determine billing date (no Stripe data)',
                        'status' => 'error'
                    ];
                }
            }
        }

        return $this->respond(true, "Fixed {$fixed} subscriptions, {$errors} errors", [
            'total_checked' => count($subscriptions),
            'fixed' => $fixed,
            'errors' => $errors,
            'details' => $details
        ]);
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

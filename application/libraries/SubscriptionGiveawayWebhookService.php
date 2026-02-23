<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription as StripeSubscription;

class SubscriptionGiveawayWebhookService
{
    /** @var CI_Controller */
    protected $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('SubscriptionGiveawayModel');
        $this->ci->load->model('CoinModel');
        $this->ci->load->model('VaultModel');
        $this->ci->load->model('UserModel');
        
        // Initialize Stripe API key
        $stripe_key = stripe_secret_key();
        if (!empty($stripe_key)) {
            Stripe::setApiKey($stripe_key);
        } else {
            log_message('error', 'SubscriptionGiveawayWebhookService: Stripe secret key not configured');
        }
    }

    /**
     * Helper to extract metadata as array from object or array
     */
    private function getMetadata($obj)
    {
        if (!$obj) {
            return [];
        }
        
        $metadata = null;
        if (is_object($obj) && isset($obj->metadata)) {
            $metadata = $obj->metadata;
        } elseif (is_array($obj) && isset($obj['metadata'])) {
            $metadata = $obj['metadata'];
        }
        
        if (!$metadata) {
            return [];
        }
        
        if (is_object($metadata)) {
            return (array) $metadata;
        } elseif (is_array($metadata)) {
            return $metadata;
        }
        
        return [];
    }

    /**
     * Handle checkout.session.completed for new punter subscriptions
     */
    public function handleCheckoutCompleted($session)
    {
        if (!$session) {
            return;
        }
        
        // Handle both object and array metadata formats
        $metadata = $this->getMetadata($session);
        if (($metadata['type'] ?? '') !== 'punter_subscription') {
            return;
        }

        $tier_id = $metadata['tier_id'] ?? null;
        $tier_key = $metadata['tier_key'] ?? null;
        $user_id = $metadata['user_id'] ?? null;
        $state = $metadata['state'] ?? null;
        $stripe_sub_id = is_object($session) ? ($session->subscription ?? null) : ($session['subscription'] ?? null);
        $stripe_customer_id = is_object($session) ? ($session->customer ?? null) : ($session['customer'] ?? null);

        if (!$tier_id || !$stripe_sub_id || !$stripe_customer_id) {
            log_message('error', 'Giveaway webhook: Missing tier or subscription data for checkout.session.completed');
            return;
        }

        // Try to resolve user by email if not provided
        if (empty($user_id)) {
            $customer_email = null;
            if (is_object($session) && isset($session->customer_details->email)) {
                $customer_email = $session->customer_details->email;
            } elseif (is_array($session) && isset($session['customer_details']['email'])) {
                $customer_email = $session['customer_details']['email'];
            }
            if (!$customer_email && $stripe_customer_id) {
                try {
                    $stripe_customer = Customer::retrieve($stripe_customer_id);
                    $customer_email = $stripe_customer->email;
                } catch (\Exception $e) {
                    log_message('error', 'Giveaway webhook: Unable to retrieve Stripe customer - ' . $e->getMessage());
                }
            }

            if ($customer_email) {
                // Try tbl_users first
                $user = $this->ci->db->get_where('tbl_users', ['email' => strtolower($customer_email)])->row();
                if ($user) {
                    $user_id = $user->id;
                } else {
                    // Try tbl_user_credentials as fallback
                    $this->ci->db->select('user_id');
                    $this->ci->db->from('tbl_user_credentials');
                    $this->ci->db->where('email', strtolower($customer_email));
                    $cred = $this->ci->db->get()->row();
                    if ($cred) {
                        $user_id = $cred->user_id;
                    }
                }
            }

            if (empty($user_id)) {
                log_message('error', 'Giveaway webhook: Could not map checkout session to a user. Email: ' . ($customer_email ?? 'unknown'));
                return; // Exit early if no user found
            }
        }

        try {
            $stripe_sub = StripeSubscription::retrieve($stripe_sub_id);
        } catch (\Exception $e) {
            log_message('error', 'Giveaway webhook: Unable to retrieve Stripe subscription - ' . $e->getMessage());
            return;
        }

        $tier = $this->ci->SubscriptionGiveawayModel->get_tier_by_id($tier_id);
        
        // Check if subscription already exists for this user
        $existing_sub = $this->ci->SubscriptionGiveawayModel->get_user_subscription($user_id);
        if ($existing_sub) {
            // If same Stripe subscription ID, skip duplicate
            if ($existing_sub['stripe_subscription_id'] === $stripe_sub_id) {
                log_message('info', 'Giveaway webhook: Subscription already exists for user ' . $user_id . ', skipping duplicate creation');
                return;
            }
            
            // Different subscription = user upgraded/downgraded, expire old one
            log_message('info', 'Giveaway webhook: User ' . $user_id . ' has existing subscription, expiring old one for tier change');
            $this->ci->SubscriptionGiveawayModel->expire_subscription($existing_sub['id']);
            
            // Cancel old Stripe subscription if it exists
            if (!empty($existing_sub['stripe_subscription_id'])) {
                try {
                    $old_stripe_sub = \Stripe\Subscription::retrieve($existing_sub['stripe_subscription_id']);
                    if ($old_stripe_sub->status === 'active') {
                        $old_stripe_sub->cancel();
                        log_message('info', 'Giveaway webhook: Cancelled old Stripe subscription ' . $existing_sub['stripe_subscription_id']);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Giveaway webhook: Failed to cancel old Stripe subscription - ' . $e->getMessage());
                }
            }
        }
        
        $sub_id = $this->ci->SubscriptionGiveawayModel->create_subscription([
            'user_id' => $user_id,
            'tier_id' => $tier_id,
            'stripe_customer_id' => $stripe_customer_id,
            'stripe_subscription_id' => $stripe_sub_id,
            'status' => 'active',
            'current_period_start' => date('Y-m-d H:i:s', $stripe_sub->current_period_start),
            'current_period_end' => date('Y-m-d H:i:s', $stripe_sub->current_period_end),
            'streak_months' => 1,
            'residential_state' => $state,
            'state_eligible' => $this->ci->SubscriptionGiveawayModel->is_state_eligible($state) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        log_message('info', 'Giveaway webhook: Created subscription ID ' . $sub_id . ' for user ' . $user_id);

        if ($user_id) {
            $user_update = [
                'punter_subscription_id' => $sub_id,
                'subscription_tier_key' => $tier_key,
                'streak_months' => 1,
                'residential_state' => $state,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($tier) {
                if (isset($tier['fee_rate'])) {
                    $user_update['fee_rate'] = $tier['fee_rate'];
                }
                if (isset($tier['coin_multiplier'])) {
                    $user_update['coin_multiplier'] = $tier['coin_multiplier'];
                }
            }

            $this->ci->db->where('id', $user_id);
            $updated = $this->ci->db->update('tbl_users', $user_update);
            
            if ($updated) {
                log_message('info', 'Giveaway webhook: Updated tbl_users for user ' . $user_id . ' with tier_key=' . $tier_key);
            } else {
                log_message('error', 'Giveaway webhook: Failed to update tbl_users for user ' . $user_id);
            }
        }

        $draw = $this->ci->SubscriptionGiveawayModel->get_active_draw();
        if ($draw && $tier && $user_id) {
            $this->ci->SubscriptionGiveawayModel->grant_monthly_entries(
                $user_id,
                $draw['id'],
                $tier['base_entries'],
                1
            );
        }

        if ($user_id) {
            $this->ci->CoinModel->add_coins($user_id, 1000, 'subscription_signup', 'subscription', $sub_id, 'Welcome bonus for joining ' . $tier_key);
        }

        log_message('info', 'Giveaway webhook: Subscription created for user ' . ($user_id ?: 'UNKNOWN') . ' (tier ' . ($tier_key ?? 'n/a') . ')');
    }

    /**
     * Handle invoice.payment_succeeded (renewals)
     */
    public function handleInvoicePaid($invoice)
    {
        if (!$invoice || empty($invoice->subscription)) {
            return;
        }

        $sub = $this->ci->SubscriptionGiveawayModel->get_subscription_by_stripe_id($invoice->subscription);
        if (!$sub) {
            return;
        }

        if ($invoice->billing_reason === 'subscription_create') {
            // Initial payment already handled via checkout.session.completed
            return;
        }

        $new_streak = $sub['streak_months'] + 1;

        $this->ci->SubscriptionGiveawayModel->update_subscription($sub['id'], [
            'streak_months' => $new_streak,
            'status' => 'active',
            'cancel_at_period_end' => 0
        ]);

        $tier = $this->ci->SubscriptionGiveawayModel->get_tier_by_id($sub['tier_id']);

        $user_update = [
            'streak_months' => $new_streak,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($tier) {
            $user_update['subscription_tier_key'] = $tier['tier_key'];
            if (isset($tier['fee_rate'])) {
                $user_update['fee_rate'] = $tier['fee_rate'];
            }
            if (isset($tier['coin_multiplier'])) {
                $user_update['coin_multiplier'] = $tier['coin_multiplier'];
            }
        }
        $this->ci->db->where('id', $sub['user_id']);
        $this->ci->db->update('tbl_users', $user_update);

        $draw = $this->ci->SubscriptionGiveawayModel->get_active_draw();
        $base_entries = $tier ? $tier['base_entries'] : ($sub['base_entries'] ?? 10);
        if ($draw) {
            $entries = $this->ci->SubscriptionGiveawayModel->grant_monthly_entries(
                $sub['user_id'],
                $draw['id'],
                $base_entries,
                $new_streak
            );
            log_message('info', 'Giveaway webhook: Renewal for user ' . $sub['user_id'] . ' (streak ' . $new_streak . ', entries ' . $entries . ')');
        }

        $this->ci->CoinModel->add_coins($sub['user_id'], 500, 'subscription_renewal', 'subscription', $sub['id'], 'Month ' . $new_streak . ' renewal bonus');
    }

    public function handleSubscriptionUpdated($stripeSub)
    {
        if (!$stripeSub || empty($stripeSub->id)) {
            return;
        }

        $sub = $this->ci->SubscriptionGiveawayModel->get_subscription_by_stripe_id($stripeSub->id);
        if (!$sub) {
            return;
        }

        $update = [
            'current_period_start' => date('Y-m-d H:i:s', $stripeSub->current_period_start),
            'current_period_end' => date('Y-m-d H:i:s', $stripeSub->current_period_end),
            'cancel_at_period_end' => $stripeSub->cancel_at_period_end ? 1 : 0
        ];

        if ($stripeSub->status === 'past_due') {
            $update['status'] = 'past_due';
        }

        $this->ci->SubscriptionGiveawayModel->update_subscription($sub['id'], $update);
    }

    public function handleSubscriptionDeleted($stripeSub)
    {
        if (!$stripeSub || empty($stripeSub->id)) {
            return;
        }

        $sub = $this->ci->SubscriptionGiveawayModel->get_subscription_by_stripe_id($stripeSub->id);
        if (!$sub) {
            return;
        }

        $this->ci->SubscriptionGiveawayModel->expire_subscription($sub['id']);

        $this->ci->db->where('id', $sub['user_id']);
        $this->ci->db->update('tbl_users', [
            'subscription_tier_key' => null,
            'streak_months' => 0,
            'punter_subscription_id' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        log_message('info', 'Giveaway webhook: Subscription expired for user ' . $sub['user_id']);
    }

    public function subscriptionExists($stripeSubscriptionId)
    {
        if (empty($stripeSubscriptionId)) {
            return false;
        }

        $sub = $this->ci->SubscriptionGiveawayModel->get_subscription_by_stripe_id($stripeSubscriptionId);
        return !empty($sub);
    }
}

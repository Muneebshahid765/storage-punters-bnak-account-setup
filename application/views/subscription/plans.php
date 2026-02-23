<div class="sp-pricing-page">
    <div class="container">
        <!-- Header -->
        <div class="sp-pricing-header">
            <h1>STORAGE PUNTERS <span>REWARDS CLUB</span></h1>
            <p>Join the world's first rewards club for storage auction addicts. Bid on auctions, crack The Vault, and go in the draw to win monthly prizes.</p>
        </div>

        <!-- Tier Cards -->
        <div class="sp-tier-grid">
            <?php foreach ($tiers as $tier): ?>
            <div class="sp-tier-card <?= $tier['tier_key'] === 'shark' ? 'popular' : '' ?>">
                <?php if ($tier['tier_key'] === 'shark'): ?>
                    <div class="sp-tier-badge">MOST POPULAR</div>
                <?php endif; ?>

                <span class="sp-tier-icon <?= $tier['tier_key'] ?>">
                    <?php if ($tier['tier_key'] === 'player'): ?>
                        <i class="bi bi-trophy"></i>
                    <?php elseif ($tier['tier_key'] === 'shark'): ?>
                        <i class="bi bi-award"></i>
                    <?php else: ?>
                        <i class="bi bi-gem"></i>
                    <?php endif; ?>
                </span>

                <div class="sp-tier-name"><?= strtoupper($tier['tier_name']) ?></div>

                <div class="sp-tier-price">
                    <span class="amount">$<?= number_format($tier['price_monthly_cents'] / 100) ?></span>
                    <span class="period">/ PER MONTH</span>
                </div>

                <div class="sp-tier-cancel">CANCEL ANYTIME</div>

                <?php if ($user_subscription && $user_subscription['tier_key'] === $tier['tier_key']): ?>
                    <span class="sp-tier-btn red" style="opacity:0.7; cursor:default;">CURRENT PLAN</span>
                <?php else: ?>
                    <button type="button" class="sp-tier-btn red" onclick="handleTierSignup('<?= $tier['tier_key'] ?>')">SIGN UP</button>
                <?php endif; ?>

                <hr class="sp-tier-divider">

                <div class="sp-tier-inclusions">
                    <h4><?= strtoupper($tier['tier_name']) ?> INCLUSIONS</h4>
                    <ul>
                        <?php if ($tier['tier_key'] === 'player'): ?>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> 1 FREE entry per month</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> 10% fees on all auctions</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> $2 spent = 1 coin earned on bids</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> Standard vault access</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> Cancel anytime</li>
                        <?php elseif ($tier['tier_key'] === 'shark'): ?>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> <strong>5 FREE</strong> entries per month</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> <strong>0% fees</strong> on all auctions</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> $1 spent = 1 coin earned on bids</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> Priority vault access</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> Premium support</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> Cancel anytime</li>
                        <?php else: ?>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> <strong>20 FREE</strong> entries per month</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> <strong>0% fees</strong> on all auctions</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> <strong>EARN DOUBLE COINS</strong> on bids</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> Elite vault access</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> Exclusive Whale-only auctions</li>
                            <li><span class="check"><i class="bi bi-check-circle-fill"></i></span> Cancel anytime</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- How Entries Compound -->
        <div style="max-width: 800px; margin: 60px auto 0; text-align: center;">
            <h2 style="font-weight: 900; margin-bottom: 10px;">THE LONGER YOU STAY, <span style="color: #ffc107;">THE MORE YOU WIN</span></h2>
            <p style="color: #8a8f9e; margin-bottom: 30px;">Your monthly entries compound over time. The longer your streak, the more entries you earn each month.</p>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 40px;">
                <div style="background: rgba(255,255,255,0.04); border-radius: 12px; padding: 20px;">
                    <div style="font-size: 0.7rem; color: #8a8f9e; text-transform: uppercase; letter-spacing: 1px;">Month 1</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #ffc107;">5</div>
                    <div style="font-size: 0.75rem; color: #666;">Base Entries (Sharks)</div>
                </div>
                <div style="background: rgba(255,255,255,0.04); border-radius: 12px; padding: 20px;">
                    <div style="font-size: 0.7rem; color: #8a8f9e; text-transform: uppercase; letter-spacing: 1px;">Month 6</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #ffc107;">30</div>
                    <div style="font-size: 0.75rem; color: #666;">Entries (4x growth)</div>
                </div>
                <div style="background: rgba(255,255,255,0.04); border-radius: 12px; padding: 20px;">
                    <div style="font-size: 0.7rem; color: #8a8f9e; text-transform: uppercase; letter-spacing: 1px;">Month 24</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #ffc107;">120</div>
                    <div style="font-size: 0.75rem; color: #666;">Entries (24x growth)</div>
                </div>
            </div>

            <p style="color: #666; font-size: 0.85rem;">
                <i class="bi bi-info-circle me-1"></i> 
                Entries never expire while your subscription is active. If you cancel, your streak resets on re-subscription.
            </p>
        </div>

        <!-- Current Giveaway -->
        <?php if ($active_draw): ?>
        <div style="max-width: 600px; margin: 50px auto 0; text-align: center; background: rgba(255,255,255,0.03); border-radius: 16px; padding: 40px; border: 1px solid rgba(255,193,7,0.15);">
            <div style="font-size: 0.7rem; color: #8a8f9e; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;">CURRENT GIVEAWAY</div>
            <div style="font-size: 2rem; font-weight: 900; color: #ffc107; margin-bottom: 5px;">
                $<?= number_format($active_draw['prize_value_cents'] / 100) ?> CASH
            </div>
            <div style="font-size: 0.85rem; color: #666; margin-bottom: 20px;">
                DRAW DATE: <?= date('jS F Y', strtotime($active_draw['draw_at'])) ?>
            </div>
            <a href="<?= site_url('join/checkout/shark') ?>" class="sp-tier-btn red" style="display: inline-block; width: auto; padding: 14px 40px;">
                ENTER NOW
            </a>
        </div>
        <?php endif; ?>
    </div>

<?php $is_logged_in = $this->session->userdata('auction_user_session_data') ? 'true' : 'false'; ?>
<script>
    const IS_SUBSCRIPTION_LOGGED_IN = <?= $is_logged_in; ?>;

    function handleTierSignup(tierKey) {
        if (!IS_SUBSCRIPTION_LOGGED_IN) {
            const authModalEl = document.getElementById('unifiedAuthModal');
            if (authModalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getOrCreateInstance(authModalEl);
                modal.show();
            } else {
                alert('Please login to continue.');
            }
            return;
        }

        window.location.href = SITE_URL + 'join/checkout/' + tierKey;
    }
</script>

</div>

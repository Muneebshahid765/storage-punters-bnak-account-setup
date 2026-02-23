<div class="sp-success-page">
    <div class="container">
        <div class="sp-success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h1>WELCOME TO THE <span>REWARDS CLUB!</span></h1>
        <p>Your subscription is now active. You've been entered into the current giveaway draw. Good luck!</p>

        <?php if (!empty($tier_key)): ?>
        <div style="margin-bottom: 30px;">
            <span style="background: rgba(255,193,7,0.15); color: #ffc107; padding: 8px 20px; border-radius: 20px; font-weight: 700; font-size: 0.9rem; text-transform: uppercase;">
                <?= ucfirst($tier_key) ?> Plan Active
            </span>
        </div>
        <?php endif; ?>

        <div class="sp-success-actions">
            <a href="<?= site_url('vault') ?>" class="sp-success-btn primary">
                <i class="bi bi-lock-fill me-1"></i> Go to The Vault
            </a>
            <a href="<?= site_url('auctions') ?>" class="sp-success-btn secondary">
                <i class="bi bi-hammer me-1"></i> Browse Auctions
            </a>
            <a href="<?= site_url('show-dashboard/membership') ?>" class="sp-success-btn secondary">
                <i class="bi bi-person me-1"></i> My Membership
            </a>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= base_url('assets/css/admin_dark.css') ?>">
<?php $segment2 = $this->uri->segment(2); ?>
<aside class="sidebar-modern">
    <div class="sidebar-header-modern">
        <div class="sidebar-brand-name">Platform Admin</div>
    </div>

	<nav class="sidebar-nav-modern">

		<!-- DASHBOARD -->
		<a href="<?php echo site_url('my-dashboard/dashboard'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'dashboard') ? 'active' : ''; ?>">
			<i class="bi bi-house-door-fill me-2" style="color: #f97316;"></i> Platform Dashboard
		</a>

		<a href="<?php echo site_url('my-dashboard/edit-account'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'edit-account') ? 'active' : ''; ?>">
			<i class="bi bi-person-fill me-2" style="color: #22c55e;"></i> Edit Account
		</a>

		<div class="sidebar-section-divider">PLATFORM SETTINGS</div>

		<a href="<?php echo site_url('my-dashboard/platform-settings'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'platform-settings') ? 'active' : ''; ?>">
			<i class="bi bi-gear-fill me-2" style="color: #94a3b8;"></i> Platform Settings
		</a>

		<a href="<?php echo site_url('my-dashboard/bidder-fee-subscriptions'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'bidder-fee-subscriptions') ? 'active' : ''; ?>">
			<i class="bi bi-percent me-2" style="color: #f59e0b;"></i> Bidder Fee Subscriptions
		</a>

		<a href="<?php echo site_url('my-dashboard/facility-plan-definitions'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'facility-plan-definitions') ? 'active' : ''; ?>">
			<i class="bi bi-building-gear me-2" style="color: #94a3b8;"></i> Facility Plan Definitions
		</a>

		<a href="<?php echo site_url('my-dashboard/location-analytics'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'location-analytics') ? 'active' : ''; ?>">
			<i class="bi bi-bar-chart-line-fill me-2" style="color: #3b82f6;"></i> Location Analytics
		</a>

		<a href="<?php echo site_url('my-dashboard/signup-bonus'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'signup-bonus') ? 'active' : ''; ?>">
			<i class="bi bi-gift-fill me-2" style="color: #ec4899;"></i> Sign Up Bonus
		</a>

		<!-- BIDDERS -->
		<div class="sidebar-section-divider">BIDDERS</div>

		<a href="<?php echo site_url('my-dashboard/manage-bidders'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'manage-bidders') ? 'active' : ''; ?>">
			<i class="bi bi-person-fill me-2" style="color: #60a5fa;"></i> Manage Bidders (Punters)
		</a>

		<a href="<?php echo site_url('my-dashboard/bidder-subscriptions'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'bidder-subscriptions') ? 'active' : ''; ?>">
			<i class="bi bi-person-check-fill me-2" style="color: #60a5fa;"></i> Bidder Subscriptions
		</a>

		<a href="<?php echo site_url('my-dashboard/bulk-credit'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'bulk-credit') ? 'active' : ''; ?>">
			<i class="bi bi-wallet2 me-2" style="color: #eab308;"></i> Bulk Credit Management
		</a>

		<!-- FACILITIES -->
		<div class="sidebar-section-divider">FACILITIES</div>

		<a href="<?php echo site_url('my-dashboard/manage-facilities'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'manage-facilities') ? 'active' : ''; ?>">
			<i class="bi bi-building-fill me-2" style="color: #94a3b8;"></i> Manage Account Holders
		</a>

		<a href="<?php echo site_url('my-dashboard/facility-plan-definitions'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'facility-plan-definitions') ? 'active' : ''; ?>">
			<i class="bi bi-card-checklist me-2" style="color: #94a3b8;"></i> Facility Subscriptions
		</a>

		<!-- PLATFORM STAFF -->
		<div class="sidebar-section-divider">PLATFORM STAFF</div>

		<a href="<?php echo site_url('my-dashboard/manage-staff'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'manage-staff') ? 'active' : ''; ?>">
			<i class="bi bi-person-badge-fill me-2" style="color: #fbbf24;"></i> Manage Staff
		</a>

		<!-- GENERAL -->
		<div class="sidebar-section-divider">GENERAL</div>

		<a href="<?php echo site_url('my-dashboard/auctions'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'auctions') ? 'active' : ''; ?>">
			<i class="bi bi-hammer me-2" style="color: #a855f7;"></i> Auctions
		</a>

		<a href="<?php echo site_url('my-dashboard/prize-drawer'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'prize-drawer') ? 'active' : ''; ?>">
			<i class="bi bi-gift me-2" style="color: #f43f5e;"></i> Prize Drawer
		</a>

		<a href="<?php echo site_url('my-dashboard/videos'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'videos') ? 'active' : ''; ?>">
			<i class="bi bi-film me-2" style="color: #0ea5e9;"></i> Video Management
		</a>

		<a href="<?php echo site_url('my-dashboard/economy'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'economy') ? 'active' : ''; ?>">
			<i class="bi bi-grid-3x3-gap-fill me-2" style="color: #22c55e;"></i> Economy Control
		</a>

		<a href="<?php echo site_url('my-dashboard/giveaway-draws'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'giveaway-draws') ? 'active' : ''; ?>">
			<i class="bi bi-trophy me-2" style="color: #ffc107;"></i> Giveaway Draws
		</a>

		<a href="<?php echo site_url('my-dashboard/manage-subscriptions'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'manage-subscriptions') ? 'active' : ''; ?>">
			<i class="bi bi-star-fill me-2" style="color: #a855f7;"></i> Manage Subscriptions
		</a>

		<!-- LOGOUT -->
		<a href="#" class="sidebar-link-modern logout btn__logout">
			<i class="bi bi-box-arrow-right me-2" style="color: #ef4444;"></i> Logout
		</a>

	</nav>

</aside>

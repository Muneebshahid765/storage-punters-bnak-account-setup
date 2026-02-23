<?php
// Determine current page from URI for active state
$current_page = $this->uri->segment(2);
?>

<!-- Account Header Partial -->
<style>
    body {
        background-color: #f8f9fa; /* Light gray background */
        font-family: 'Inter', sans-serif;
    }

    .account-header {
        background: #fff;
        padding-top: 2rem;
        padding-bottom: 0;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 2rem;
    }

    .account-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #111827;
        margin-bottom: 1.5rem;
    }

    .nav-tabs-custom {
        border-bottom: none;
        display: flex;
        flex-wrap: nowrap !important; /* Force single line */
        gap: 1.5rem;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch; /* smooth scrolling on iOS */
        padding-bottom: 5px; /* space for scrollbar explicitly if needed */
        scrollbar-width: none; /* Firefox */
    }

    .nav-tabs-custom::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Opera */
    }

    .nav-link-custom {
        color: #4b5563; /* Gray-600 */
        font-weight: 500;
        padding-bottom: 1rem;
        border-bottom: 2px solid transparent;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 0.95rem;
        background: transparent !important; /* Force transparent bg for non-active */
    }

    .nav-link-custom:hover {
        color: #111827;
    }

    .nav-link-custom.active-tab {
        color: #fff !important; /* White text for active button */
        background-color: #00a65a !important; /* Green background */
        border-radius: 6px;
        padding: 8px 16px;
        font-weight: 600;
        border-bottom: none;
    }

    /* Override any residual button styles if class matches */
    .btn-success {
        background-color: #00a65a;
        border-color: #00a65a;
    }

    .dashboard-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        margin-bottom: 3rem;
        min-height: 600px;
    }

    .card-title-custom {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1.5rem;
    }
</style>

<div class="account-header">
    <div class="container">
        <h1 class="account-title">My Account</h1>

        <nav class="nav nav-tabs-custom">
            <a href="<?php echo site_url('show-dashboard/dashboard'); ?>"
               class="nav-link-custom <?php echo ($current_page == 'dashboard' || $current_page == '') ? 'active-tab' : ''; ?>">
               Dashboard
            </a>
            <a href="<?php echo site_url('show-dashboard/edit-account'); ?>"
               class="nav-link-custom <?php echo ($current_page == 'edit-account') ? 'active-tab' : ''; ?>">
               Edit Account
            </a>
            <a href="<?php echo site_url('show-dashboard/payment-method'); ?>"
               class="nav-link-custom <?php echo ($current_page == 'payment-method') ? 'active-tab' : ''; ?>">
               Payment Method
            </a>
            <a href="<?php echo site_url('show-dashboard/membership'); ?>"
               class="nav-link-custom <?php echo ($current_page == 'membership') ? 'active-tab' : ''; ?>">
               My Subscription
            </a>
            <a href="<?php echo site_url('show-dashboard/wallet'); ?>"
               class="nav-link-custom <?php echo ($current_page == 'wallet') ? 'active-tab' : ''; ?>">
               Wallet
            </a>

			<a href="<?php echo site_url('show-dashboard/coin-shop'); ?>"
			   class="nav-link-custom <?php echo ($current_page == 'coin-shop') ? 'active-tab' : ''; ?>">
				Coin Shop
			</a>
            <a href="<?php echo site_url('show-dashboard/notifications'); ?>"
               class="nav-link-custom <?php echo ($current_page == 'notifications') ? 'active-tab' : ''; ?>">
               Notifications
            </a>
        </nav>
    </div>
</div>

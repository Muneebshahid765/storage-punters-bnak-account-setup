<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/storage_facility/sidebar'); ?>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div class="d-flex align-items-center">
                 <button class="btn btn-link text-dark d-lg-none me-3 p-0 hamburger-menu-btn">
                    <i class="bi bi-list fs-1"></i>
                </button>
                <h1 class="dash-title-modern mb-0">Settings Hub</h1>
            </div>
        </header>
        
        <!-- Settings Tabs -->
        <nav class="settings-hub-nav mb-4">
            <a href="<?php echo site_url('company/global-auction-rules'); ?>" class="settings-tab-link">Global Auction Rules</a>
            <a href="<?php echo site_url('company'); ?>" class="settings-tab-link">Company Setup</a>
            <a href="<?php echo site_url('storage-facility/edit-account'); ?>" class="settings-tab-link active">Account Holder</a>
            <a href="<?php echo site_url('bank/setup/new'); ?>" class="settings-tab-link">Bank Account Setup</a>
            <a href="<?php echo site_url('storage-facility/manage'); ?>" class="settings-tab-link">Manage Storage Locations</a>
            <a href="<?php echo site_url('storage-facility/notifications'); ?>" class="settings-tab-link">Notifications</a>
            <a href="<?php echo site_url('storage-facility/subscription'); ?>" class="settings-tab-link">Subscription</a>
        </nav>

        <div class="settings-card-modern">
            <h2 class="settings-form-title">Account Holder</h2>

            <form id="user-profile-form">
                <div class="form-group-modern">
                    <input id="firstname" type="text" placeholder="First Name *" name="firstname" class="modern-form-control" value="" disabled>
                </div>

                <div class="form-group-modern">
                    <input id="lastname" type="text" placeholder="Last Name *" name="lastname" class="modern-form-control" value="" disabled>
                </div>

                <div class="form-group-modern">
                    <input id="standard_email_address" type="email" placeholder="Email Address *" name="standard_email_address" class="modern-form-control" value="" disabled>
                </div>

                <div class="form-group-modern">
                    <input id="cell_phone" type="tel" placeholder="Mobile Number *" name="cell_phone" class="modern-form-control" value="" disabled>
                </div>

                <div class="form-group-modern">
                    <input id="current_password" type="password" placeholder="New Password (Leave blank to keep current)" name="current_password" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="password_confirmation" type="password" placeholder="Confirm New Password" name="password_confirmation" class="modern-form-control" value="">
                </div>

                <button type="submit" id="save-updates-btn" class="btn-modern-save">
                    Save Updates
                </button>
            </form>
        </div>
    </main>
</div>
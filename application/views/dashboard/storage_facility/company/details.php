<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <aside class="sidebar-modern">
        <div class="sidebar-header-modern">
            <div class="sidebar-brand-name">Storage HQ</div>
            <div class="sidebar-facility-name"><?php echo $userSessionData['first_name'] ?? 'Facility Name'; ?></div>
        </div>
        
        <nav class="sidebar-nav-modern">
            <a href="<?php echo site_url('storage-facility/dashboard'); ?>" class="sidebar-link-modern">
                <i class="bi bi-grid-fill me-2"></i> Dashboard
            </a>
            <a href="<?php echo site_url('storage-facility/ai/new'); ?>" class="sidebar-link-modern">
                <i class="bi bi-plus-circle me-2"></i> New Listing (AI)
            </a>
            <a href="<?php echo site_url('company'); ?>" class="sidebar-link-modern active">
                <i class="bi bi-gear-fill me-2"></i> Settings Hub
            </a>
            <a href="<?php echo site_url('storage-facility/staff/manage'); ?>" class="sidebar-link-modern">
                <i class="bi bi-people-fill me-2"></i> Manage Staff
            </a>
            <a href="#" class="sidebar-link-modern logout btn__logout">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <h1 class="dash-title-modern">Settings Hub</h1>
        </header>

        <!-- Settings Tabs -->
        <nav class="settings-hub-nav mb-4">
            <a href="<?php echo site_url('company/global-auction-rules'); ?>" class="settings-tab-link">Global Auction Rules</a>
            <a href="<?php echo site_url('company'); ?>" class="settings-tab-link active">Company Setup</a>
            <a href="<?php echo site_url('storage-facility/edit-account'); ?>" class="settings-tab-link">Account Holder</a>
            <a href="<?php echo site_url('bank/setup/new'); ?>" class="settings-tab-link">Bank Account Setup</a>
            <a href="<?php echo site_url('storage-facility/manage'); ?>" class="settings-tab-link">Manage Storage Locations</a>
            <a href="<?php echo site_url('storage-facility/notifications'); ?>" class="settings-tab-link">Notifications</a>
            <a href="<?php echo site_url('storage-facility/subscription'); ?>" class="settings-tab-link">Subscription</a>
        </nav>

        <div class="settings-card-modern">
            <h2 class="settings-form-title">Company Setup</h2>

            <form id="company-update-form">
                <input type="hidden" name="companyId" id="companyId" value="">
                
                <div class="form-group-modern">
                    <input id="company_name" type="text" placeholder="Company Name (Required, e.g., Mammoth Storage)" name="company_name" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="abn" type="text" placeholder="ABN (Required, e.g., 51 824 753 556)" name="abn" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="company_email" type="email" placeholder="Company Email (Required, e.g., corporate@mammoth.com)" name="company_email" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="company_website" type="text" placeholder="Company Website (Optional, e.g., https://mammothselfstore.com.au)" name="company_website" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="cell_phone" type="tel" placeholder="Phone Number (Required)" name="company_cell_phone" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="address" type="text" placeholder="Street Address (Required)" name="company_street_address" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="apt_suite" type="text" placeholder="Apt/Suite (Optional)" name="company_apt_suite" class="modern-form-control" value="">
                </div>

                <div class="grid-row-4">
                    <div class="form-group-modern">
                        <input id="city" type="text" placeholder="City" name="company_city" class="modern-form-control" value="">
                    </div>
                    <div class="form-group-modern">
                        <input id="zip" type="text" placeholder="Post Code" name="company_zip" class="modern-form-control" value="">
                    </div>
                    <div class="form-group-modern">
                        <input id="state" type="text" placeholder="State/Province" name="company_state" class="modern-form-control" value="">
                    </div>
                    <div class="form-group-modern">
                        <input id="country" type="text" placeholder="Country" name="company_country" class="modern-form-control" value="Australia">
                    </div>
                </div>

                <button type="submit" id="save-company-updates-btn" class="btn-modern-save">
                    Save Updates
                </button>
            </form>
        </div>
    </main>
</div>
<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <aside class="sidebar-modern">
        <div class="sidebar-header-modern">
            <div class="sidebar-brand-name">Storage HQ</div>
            <div class="sidebar-facility-name"><?php echo $userSessionData['first_name'] ?? 'Facility Admin'; ?></div>
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
            <a href="#" class="settings-tab-link disabled" style="opacity:0.5; cursor:not-allowed">Global Auction Rules</a>
            <a href="#" class="settings-tab-link active">Company Setup</a>
            <a href="<?php echo site_url('storage-facility/edit-account'); ?>" class="settings-tab-link">Account Holder</a>
            <a href="#" class="settings-tab-link disabled" style="opacity:0.5; cursor:not-allowed">Bank Account Setup</a>
            <a href="#" class="settings-tab-link disabled" style="opacity:0.5; cursor:not-allowed">Manage Storage Locations</a>
            <a href="#" class="settings-tab-link disabled" style="opacity:0.5; cursor:not-allowed">Notifications</a>
            <a href="#" class="settings-tab-link disabled" style="opacity:0.5; cursor:not-allowed">Subscription</a>
        </nav>

        <div class="settings-card-modern">
            <h2 class="settings-form-title">Setup Company Details</h2>
            <p class="text-muted mb-4 small">Please configure your company profile to start listing auctions.</p>

            <form id="company-setup-add-form">
                
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
                    <input id="company_website" type="text" placeholder="Company Website (Required, e.g., https://mammothselfstore.com.au)" name="company_website" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="cell_phone" type="tel" placeholder="Phone Number (Required)" name="cell_phone" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="address" type="text" placeholder="Street Address (Required)" name="address" class="modern-form-control" value="">
                </div>

                <div class="form-group-modern">
                    <input id="apt_suite" type="text" placeholder="Apt/Suite (Optional)" name="apt_suite" class="modern-form-control" value="">
                </div>

                <div class="grid-row-4">
                    <div class="form-group-modern">
                        <input id="city" type="text" placeholder="City" name="city" class="modern-form-control" value="">
                    </div>
                    <div class="form-group-modern">
                        <input id="zip" type="text" placeholder="Post Code" name="zip" class="modern-form-control" value="">
                    </div>
                    <div class="form-group-modern">
                        <select id="state" name="state" class="modern-form-select">
                            <option value="" disabled selected>Select State</option>
                            <option value="New South Wales">New South Wales</option>
                            <option value="Victoria">Victoria</option>
                            <option value="Queensland">Queensland</option>
                            <option value="Western Australia">Western Australia</option>
                            <option value="South Australia">South Australia</option>
                            <option value="Tasmania">Tasmania</option>
                            <option value="Australian Capital Territory">Australian Capital Territory</option>
                            <option value="Northern Territory">Northern Territory</option>
                        </select>
                    </div>
                    <div class="form-group-modern">
                        <select id="country" name="country" class="modern-form-select">
                            <option value="Australia" selected>Australia</option>
                        </select>
                    </div>
                </div>

                <button type="submit" id="save-company-add-btn" class="btn-modern-save w-100 mt-3">
                    Add Company
                </button>
            </form>
        </div>
    </main>
</div>
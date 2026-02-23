<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern admin-dark-theme">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/admin/sidebar'); ?>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div>
                <h1 class="dash-title-modern">Platform Settings</h1>
                <p class="text-muted small mb-0">Configure global platform parameters and subscription plans</p>
            </div>
        </header>

        <!-- Global Settings Section -->
        <section class="content-section-modern mb-4">
            <h2 class="section-title-modern">Global Configuration</h2>
            <p class="section-subtitle-modern">Platform-wide settings for all users</p>
            
            <div class="row g-4">
                <!-- Credit Settings Form -->
                <div class="col-md-6">
                    <div class="settings-card-modern">
                        <h3 class="settings-form-title">Bidder Sign-up Bonus</h3>
                        <form id="credit-setting-form">
                            <div class="form-group-modern">
                                <label for="credit_value" class="form-label small fw-bold text-muted">Bonus Amount (AUD)</label>
                                <input id="credit_value" type="text" placeholder="e.g., 40.00" 
                                    name="credit_value" class="modern-form-control">
                                <small class="text-muted">Initial credit given to new bidders upon registration</small>
                            </div>

                            <button type="submit" id="credit-update-btn" class="btn-modern-save">
                                <span class="btn-text">Save Credit Settings</span>
                                <span class="spinner-border spinner-border-sm text-light ms-2 d-none" 
                                    id="credit-loader" role="status"></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Commission Settings Form -->
                <div class="col-md-6">
                    <div class="settings-card-modern">
                        <h3 class="settings-form-title">Platform Commission</h3>
                        <form id="commission-setting-form">
                            <div class="form-group-modern">
                                <label for="commission_value" class="form-label small fw-bold text-muted">Commission Rate (%)</label>
                                <input id="commission_value" type="text" placeholder="e.g., 17.5" 
                                    name="commission_value" class="modern-form-control">
                                <small class="text-muted">Standard platform fee charged to bidders (default: 17.5%)</small>
                            </div>

                            <button type="submit" id="commission-update-btn" class="btn-modern-save">
                                <span class="btn-text">Save Commission Settings</span>
                                <span class="spinner-border spinner-border-sm text-light ms-2 d-none" 
                                    id="commission-loader" role="status"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Subscription Plans Section -->
        <section class="content-section-modern">
            <h2 class="section-title-modern">Subscription Plans</h2>
            <p class="section-subtitle-modern">Manage facility and bidder subscription tiers</p>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> Subscription plan management will be available in Phase 2. Currently, plans are managed through the existing credit and commission settings above.
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="settings-card-modern">
                        <h3 class="settings-form-title">Facility Subscription Plans</h3>
                        <p class="text-muted small">Basic and Premium tiers for storage facilities</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success me-2"></i> Basic: Standard features</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i> Premium: Advanced AI + Analytics</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-card-modern">
                        <h3 class="settings-form-title">Bidder Subscription Plans</h3>
                        <p class="text-muted small">Free and Pro tiers for bidders</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success me-2"></i> Free: Standard 17.5% commission</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i> Pro: Reduced commission rate</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

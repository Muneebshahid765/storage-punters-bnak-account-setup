<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern admin-dark-theme">
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <?php $this->load->view('dashboard/admin/sidebar'); ?>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div class="d-flex align-items-center">
                <!-- Mobile Toggle Button -->
                <button type="button" class="btn btn-link text-white d-lg-none me-3 p-0" id="sidebarToggle">
                    <i class="bi bi-list fs-2"></i>
                </button>
                <div>
                    <h1 class="dash-title-modern">Platform Administrator Dashboard</h1>
                    <p class="text-muted small mb-0">Central view of platform health, total revenue, and key user statistics.</p>
                </div>
            </div>
        </header>

        <!-- KPI Stats Grid -->
        <div class="stats-grid-modern mb-4">
            <div class="stat-card-modern">
                <div class="stat-label-modern">Total Account Holders</div>
                <div class="stat-value-modern text-dark" id="stat-facilities">0</div>
                <div class="small text-muted mt-1">Account Holders</div>
            </div>
            <div class="stat-card-modern">
                <div class="stat-label-modern">Total Bidders Registered</div>
                <div class="stat-value-modern text-dark" id="stat-bidders">0</div>
                <div class="small text-muted mt-1">Registered Punters</div>
            </div>
            <div class="stat-card-modern">
                <div class="stat-label-modern">Platform Revenue (YTD)</div>
                <div class="stat-value-modern text-dark" id="stat-revenue">$0.00</div>
                <div class="small text-muted mt-1">Year-to-Date</div>
            </div>
            <div class="stat-card-modern">
                <div class="stat-label-modern">Active Auctions</div>
                <div class="stat-value-modern text-dark" id="stat-auctions">0</div>
                <div class="small text-muted mt-1">Currently Live</div>
            </div>
        </div>

        <!-- Platform Settings Section -->
        <section class="content-section-modern">
            <h2 class="section-title-modern">Platform Settings</h2>
            <p class="section-subtitle-modern">Configure global platform parameters</p>
            
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
    </main>
</div>
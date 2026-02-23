<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<style>
    .settings-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 12px;
    }
    .settings-info {
        flex: 1;
        padding-right: 20px;
    }
    .settings-title {
        font-weight: 600;
        font-size: 14px;
        color: #111827;
        margin-bottom: 4px;
    }
    .settings-desc {
        font-size: 12px;
        color: #6b7280;
    }
    .switch-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .switch-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider-toggle {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e5e7eb;
        transition: .4s;
        border-radius: 24px;
    }
    .slider-toggle:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider-toggle {
        background-color: #0d3b2e;
    }
    input:checked + .slider-toggle:before {
        transform: translateX(20px);
    }
    .range-container {
        width: 100%;
        margin-top: 10px;
    }
    .premium-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 4px;
        border-radius: 5px;
        background: #e5e7eb;
        outline: none;
    }
    .premium-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #0d3b2e;
        cursor: pointer;
        border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .reporting-access-block {
        background: #fff5f5;
        border: 1px solid #fee2e2;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .reporting-access-block.locked {
        opacity: 0.8;
    }
    .lock-badge {
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        display: none;
        align-items: center;
    }
    .lock-badge i {
        font-size: 12px;
        margin-right: 4px;
    }
    .modern-textarea {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        font-size: 14px;
        color: #374151;
        min-height: 150px;
        resize: vertical;
    }
    .input-row {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-bottom: 20px;
    }
    .modern-input-small {
        width: 80px;
        padding: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 14px;
    }
</style>

<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/storage_facility/sidebar'); ?>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <h1 class="dash-title-modern">Settings Hub</h1>
        </header>

        <!-- Settings Tabs -->
        <nav class="settings-hub-nav mb-4">
            <a href="<?php echo site_url('company/global-auction-rules'); ?>" class="settings-tab-link active">Global Auction Rules</a>
            <a href="<?php echo site_url('company'); ?>" class="settings-tab-link">Company Setup</a>
            <a href="<?php echo site_url('storage-facility/edit-account'); ?>" class="settings-tab-link">Account Holder</a>
            <a href="<?php echo site_url('bank/setup/new'); ?>" class="settings-tab-link">Bank Account Setup</a>
            <a href="<?php echo site_url('storage-facility/manage'); ?>" class="settings-tab-link">Manage Storage Locations</a>
            <a href="<?php echo site_url('storage-facility/notifications'); ?>" class="settings-tab-link">Notifications</a>
            <a href="<?php echo site_url('storage-facility/subscription'); ?>" class="settings-tab-link">Subscription</a>
        </nav>

        <form id="global-auction-rules-form">
            <h2 class="settings-form-title mb-4">Core Auction Rules & AI</h2>

            <!-- AI Photo Estimation -->
            <div class="settings-row">
                <div class="settings-info">
                    <div class="settings-title">Estimate and display my best photo (highest value) with AI</div>
                    <div class="settings-desc">AI selects the primary image based on perceived item value.</div>
                </div>
                <label class="switch-toggle">
                    <input type="checkbox" id="estimate_best_photo" name="estimate_best_photo">
                    <span class="slider-toggle"></span>
                </label>
            </div>

            <!-- Starting Price Slider -->
            <div class="settings-row flex-column align-items-start">
                <div class="settings-info w-100 mb-2">
                    <div class="settings-title">Always list my auction starting at: <span id="starting_price_value" style="color: #0d3b2e;">0%</span> of debt value</div>
                </div>
                <div class="range-container">
                    <input type="range" min="0" max="100" value="0" class="premium-slider" id="starting_price_percentage">
                </div>
            </div>

            <!-- Debt Value Toggle -->
            <div class="settings-row">
                <div class="settings-info">
                    <div class="settings-title">Display debt value on listing</div>
                    <div class="settings-desc">If enabled, the exact outstanding debt is public.</div>
                </div>
                <label class="switch-toggle">
                    <input type="checkbox" id="display_debt_value" name="display_debt_value">
                    <span class="slider-toggle"></span>
                </label>
            </div>

            <!-- Reporting Access Block -->
            <div class="reporting-access-block">
                <div class="settings-info">
                    <div class="settings-title" style="color: #991b1b;">Reporting access <i class="bi bi-exclamation-circle ms-1"></i></div>
                    <div class="settings-desc" style="color: #b91c1c;">Upgrade subscription for Debt-to-Revenue ratios & aggregated financials.</div>
                </div>
                <div class="lock-badge"><i class="bi bi-lock-fill"></i> Locked</div>
            </div>

            <!-- Auction Duration -->
            <div class="mb-4">
                <div class="settings-title mb-2">Default Auction Duration (Days)</div>
                <div class="d-flex align-items-center gap-3">
                    <input type="number" id="default_auction_duration" name="default_auction_duration" class="modern-input-small" value="7">
                    <span class="settings-desc">days (e.g., 4 days)</span>
                </div>
            </div>

            <!-- Start Immediately Toggle -->
            <div class="settings-row">
                <div class="settings-info">
                    <div class="settings-title">Start immediately after posting?</div>
                    <div class="settings-desc">Bypasses 'Draft' and moves straight to 'Active' upon finalization.</div>
                </div>
                <label class="switch-toggle">
                    <input type="checkbox" id="start_immediately" name="start_immediately">
                    <span class="slider-toggle"></span>
                </label>
            </div>

            <!-- Cleaning Deposit -->
            <div class="mb-4">
                <div class="settings-title mb-2">Default Cleaning Deposit</div>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-4">
                        <div class="input-group" style="width: 150px;">
                            <span class="input-group-text">$</span>
                            <input type="number" id="cleaning_deposit_amount" name="cleaning_deposit_amount" class="form-control" placeholder="150">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has_no_deposit" name="has_no_deposit">
                            <label class="form-check-label settings-desc" for="has_no_deposit">
                                I do not have a deposit
                            </label>
                        </div>
                    </div>
                    
                    <!-- Integrated Payment Method -->
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-600 text-dark" style="font-size: 0.9rem;">Payment Method:</span>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash">
                            <label class="form-check-label settings-desc" for="payment_cash">
                                Cash
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card">
                            <label class="form-check-label settings-desc" for="payment_card">
                                Card
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="mb-4">
                <div class="settings-title mb-2">Default Auction Terms & Conditions (Editable)</div>
                <textarea id="default_terms_conditions" name="default_terms_conditions" class="modern-textarea" placeholder="1. Collection must occur within 48 hours..."></textarea>
            </div>

            <button type="submit" id="save-global-rules-btn" class="btn-modern-save w-100" style="background-color: #0d3b2e; height: 50px;">
                Save Global Auction Rules
            </button>
        </form>
    </main>
</div>

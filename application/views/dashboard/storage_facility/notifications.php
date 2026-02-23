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
    .modern-select {
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        color: #374151;
        font-size: 14px;
        min-width: 200px;
        background-color: #fff;
    }
    
    /* Table like layout for notifications */
    .notification-grid {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
    }
    .notification-header {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        padding: 15px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .notification-item {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        padding: 20px;
        border-bottom: 1px solid #f3f4f6;
        align-items: center;
    }
    .notification-item:last-child {
        border-bottom: none;
    }
    .notification-label {
        font-weight: 600;
        font-size: 14px;
        color: #1f2937;
    }
    .radio-group {
        display: flex;
        gap: 20px;
    }
    .radio-option {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 14px;
        color: #374151;
        margin-bottom: 0;
    }
    .radio-option input[type="radio"] {
        accent-color: #0d3b2e; /* Verify color match */
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
            <a href="<?php echo site_url('company/global-auction-rules'); ?>" class="settings-tab-link">Global Auction Rules</a>
            <a href="<?php echo site_url('company'); ?>" class="settings-tab-link">Company Setup</a>
            <a href="<?php echo site_url('storage-facility/edit-account'); ?>" class="settings-tab-link">Account Holder</a>
            <a href="<?php echo site_url('bank/setup/new'); ?>" class="settings-tab-link">Bank Account Setup</a>
            <a href="<?php echo site_url('storage-facility/manage'); ?>" class="settings-tab-link">Manage Storage Locations</a>
            <a href="<?php echo site_url('storage-facility/notifications'); ?>" class="settings-tab-link active">Notifications</a>
            <a href="<?php echo site_url('storage-facility/subscription'); ?>" class="settings-tab-link">Subscription</a>
        </nav>

        <form id="notification-preferences-form">
            <div class="settings-card-modern">
                <h2 class="settings-form-title mb-4">Notification Preferences</h2>

                <!-- Audio Alert -->
                <div class="mb-4">
                    <label class="settings-title d-block mb-2">Audio Alert Setting</label>
                    <select class="modern-select" id="audio_alert_enabled" name="audio_alert_enabled">
                        <option value="1">Audio ON (Default)</option>
                        <option value="0">Audio OFF</option>
                    </select>
                </div>

                <!-- Notifications Grid -->
                <div class="notification-grid mb-4">
                    <div class="notification-header">
                        <div>Auction Activity</div>
                        <div>Email (Yes/No)</div>
                        <div>Text (SMS) (Yes/No)</div>
                    </div>

                    <!-- Winning Auction -->
                    <div class="notification-item">
                        <div class="notification-label">Winning Auction (Buyer Paid)</div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="winning_auction_email" value="1" checked> Yes
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="winning_auction_email" value="0"> No
                            </label>
                        </div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="winning_auction_sms" value="1" checked> Yes
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="winning_auction_sms" value="0"> No
                            </label>
                        </div>
                    </div>

                    <!-- Outbid Auction -->
                    <div class="notification-item">
                        <div class="notification-label">Outbid Auction (Urgent)</div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="outbid_auction_email" value="1"> Yes
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="outbid_auction_email" value="0" checked> No
                            </label>
                        </div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="outbid_auction_sms" value="1"> Yes
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="outbid_auction_sms" value="0" checked> No
                            </label>
                        </div>
                    </div>

                    <!-- Auction Closed -->
                    <div class="notification-item">
                        <div class="notification-label">Auction Closed (Unsold)</div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="auction_closed_email" value="1" checked> Yes
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="auction_closed_email" value="0"> No
                            </label>
                        </div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="auction_closed_sms" value="1"> Yes
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="auction_closed_sms" value="0" checked> No
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" id="save-notifications-btn" class="btn-modern-save w-100" style="background-color: #0d3b2e; height: 50px;">
                    Save Notification Preferences
                </button>
            </div>
        </form>
    </main>
</div>

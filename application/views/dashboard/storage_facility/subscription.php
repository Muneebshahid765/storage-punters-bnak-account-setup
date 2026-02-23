<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<style>
    .subscription-page-title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin-bottom: 24px;
    }

    .current-plan-card {
        background: #d4f4dd;
        border: 1px solid #b8e6c4;
        border-radius: 8px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .current-plan-title {
        font-size: 16px;
        font-weight: 600;
        color: #0d3b2e;
        margin-bottom: 8px;
    }

    .current-plan-desc {
        font-size: 14px;
        color: #4a5568;
        margin-bottom: 4px;
    }

    .billing-cycle-section {
        margin-bottom: 24px;
    }

    .billing-cycle-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }

    .billing-cycle-options {
        display: flex;
        gap: 24px;
        align-items: center;
    }

    .billing-cycle-option {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .billing-cycle-option input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #0d3b2e;
    }

    .billing-cycle-option label {
        font-size: 14px;
        color: #333;
        cursor: pointer;
        margin: 0;
    }

    .action-buttons-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-change-plan {
        background: #0d3b2e;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-change-plan:hover {
        background: #0a2f23;
        color: white;
    }

    .btn-cancel-sub {
        background: #dc3545;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-cancel-sub:hover {
        background: #c82333;
        color: white;
    }

    .btn-pause-sub {
        background: #ffc107;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-pause-sub:hover {
        background: #e0a800;
        color: white;
    }

    .no-subscription-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 32px;
        text-align: center;
    }

    .no-subscription-card h3 {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }

    .no-subscription-card p {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 24px;
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
            <a href="<?php echo site_url('storage-facility/notifications'); ?>" class="settings-tab-link">Notifications</a>
            <a href="<?php echo site_url('storage-facility/subscription'); ?>" class="settings-tab-link active">Subscription</a>
        </nav>

        <div class="settings-card-modern">
            <h2 class="subscription-page-title">Subscription & Billing (Facility)</h2>

            <!-- Subscription Container -->
            <div class="membership-container"></div>
        </div>
    </main>
</div>

<!-- Membership Modal -->
<div class="modal fade" id="membershipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #f1f0f0 !important;">
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h3 class="fw-bold mb-2 mt-2">Choose your Punters Package</h3>
                    <p class="text-muted mb-0">
                        Unlock premium benefits, reduce fees, and enjoy exclusive perks.
                        Select a plan that suits your needs — <strong>monthly flexibility</strong> or
                        <strong>yearly savings</strong>.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Toggle switch -->
                <div class="text-center mb-3">
                    <div class="d-flex justify-content-center align-items-center gap-3">
                        <button id="intervalToggle" type="button"
                            class="btn btn-lg btn-toggle active"
                            data-toggle="button" aria-pressed="true" autocomplete="off">
                            <div class="handle"></div>
                        </button>
                    </div>
                </div>

                <!-- Plans will render here -->
                <div id="membership-modal-body"></div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Membership Confirm Modal -->
<div class="modal fade" id="cancelMembershipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Cancellation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                <p class="mt-3">
                    Are you sure you want to <strong>cancel your membership</strong>?<br>
                    You will keep your benefits until the end of the current billing period.
                </p>
            </div>

            <div class="modal-footer">
                <div class="row w-100 g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-yellow text-white w-100 fw-bold" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left-circle me-2"></i> Keep Membership
                        </button>
                    </div>

                    <div class="col-6">
                        <button id="confirm-cancel-membership" type="button" class="btn btn-danger w-100 fw-bold">
                            <i class="bi bi-x-circle me-2"></i> Yes, Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('dashboard/storage_facility/payment_method_setup'); ?>

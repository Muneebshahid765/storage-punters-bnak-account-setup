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
            <div class="d-flex gap-2">
                <button type="button" class="btn-export-modern __btn__add__new__bank__account">
                    <i class="bi bi-plus-circle me-2"></i> Add New Bank Account
                </button>
            </div>
        </header>

        <!-- Settings Tabs -->
        <nav class="settings-hub-nav mb-4">
            <a href="<?php echo site_url('company/global-auction-rules'); ?>" class="settings-tab-link">Global Auction Rules</a>
            <a href="<?php echo site_url('company'); ?>" class="settings-tab-link">Company Setup</a>
            <a href="<?php echo site_url('storage-facility/edit-account'); ?>" class="settings-tab-link">Account Holder</a>
            <a href="<?php echo site_url('bank/setup/new'); ?>" class="settings-tab-link active">Bank Account Setup</a>
            <a href="<?php echo site_url('storage-facility/manage'); ?>" class="settings-tab-link">Manage Storage Locations</a>
            <a href="<?php echo site_url('storage-facility/notifications'); ?>" class="settings-tab-link">Notifications</a>
            <a href="<?php echo site_url('storage-facility/subscription'); ?>" class="settings-tab-link">Subscription</a>
        </nav>

        <div class="settings-card-modern">
            <h2 class="settings-form-title">Bank Accounts (For Payouts)</h2>
            
            <div class="table-responsive bank-table-wrapper">
                <table id="bank-account-table" class="table-modern bank-table w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Account Name</th>
                            <th>BSB / Account</th>
                            <th>Linked Locations</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bank-account-list-tbody">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add Bank Account Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold" id="addBankModalLabel">Add New Bank Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bank-account-add-form">
                <div class="modal-body p-4 pt-0">
                    
                    <!-- Stripe Onboarding Info Box -->
                    <div class="p-4 rounded-3 mb-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border: 1px solid #0f3460;">
                        <div class="d-flex align-items-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#635bff" viewBox="0 0 24 24" class="me-3">
                                <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                            </svg>
                            <div>
                                <h5 class="text-white mb-1 fw-bold">Secure Bank Account Setup</h5>
                                <p class="text-light mb-0" style="font-size: 13px; opacity: 0.9;">Powered by Stripe Connect</p>
                            </div>
                        </div>
                        <p class="text-light mb-0" style="font-size: 14px;">
                            <i class="bi bi-shield-check me-2"></i>
                            Click the button below to securely add your bank account through Stripe's verified platform. 
                            Your financial information is protected with bank-level encryption.
                        </p>
                    </div>

                    <!-- Location Linking Section -->
                    <div class="p-4 rounded-3 mb-3" style="background-color: #f9fafb; border: 1px solid #e5e7eb;">
                        <h6 class="fw-bold mb-2" style="font-size: 14px; color: #111827;">
                            <i class="bi bi-geo-alt-fill me-2 text-primary"></i>Link to Storage Locations (Optional)
                        </h6>
                        <p class="text-muted small mb-3">Select which storage locations should route auction payouts to this bank account. You can update this later.</p>
                        
                        <div id="modal-locations-list" class="row g-3">
                            <!-- Locations checkboxes will be injected here via JS -->
                            <div class="col-12 text-center py-3">
                                <span class="spinner-border spinner-border-sm text-secondary"></span>
                                <span class="ms-2 text-muted">Loading locations...</span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top-0 p-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="save-bank-btn" class="btn text-white px-4" style="background-color: #635bff; font-weight: 600;">
                        <i class="bi bi-box-arrow-up-right me-2"></i> Continue to Stripe Setup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
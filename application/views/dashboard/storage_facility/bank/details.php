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
            <a href="<?php echo site_url('storage-facility/edit-account'); ?>" class="settings-tab-link">Account Holder</a>
            <a href="<?php echo site_url('bank/setup/new'); ?>" class="settings-tab-link active">Bank Account Setup</a>
            <a href="<?php echo site_url('storage-facility/manage'); ?>" class="settings-tab-link">Manage Storage Locations</a>
            <a href="<?php echo site_url('storage-facility/notifications'); ?>" class="settings-tab-link">Notifications</a>
            <a href="<?php echo site_url('storage-facility/subscription'); ?>" class="settings-tab-link">Subscription</a>
        </nav>

        <div class="settings-card-modern">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="settings-form-title mb-1">Payout Account Management (Stripe Connect)</h2>
                    <p class="text-muted small">Set up and link bank accounts to specific facility locations for automated auction revenue payouts.</p>
                </div>
                <button type="button" class="btn btn-emerald px-4 py-2 __btn__add__new__bank__account" style="background-color: #0d3b2e; color: #ffffff; border: none; border-radius: 6px; font-weight: 600;">
                    Add New Bank Account
                </button>
            </div>
            
            <div class="table-responsive">
                <table id="bank-account-table" class="table-modern">
                    <thead>
                        <tr>
                            <th style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 600;">Account Name</th>
                            <th style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 600;">BSB/Account</th>
                            <th style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 600;">Linked Locations</th>
                            <th style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 600; text-align: right;">Actions</th>
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

<!-- Consolidated Add/Edit Bank Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold" id="addBankModalLabel">Add New Bank Account (Stripe Connect)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bank-setup-form">
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3 mb-4 bank-details-fields">
                        <div class="col-md-6">
                            <input type="text" id="bank_account_name" name="account_name" class="form-control p-2" placeholder="Account Name (Exact Legal Name, Required)" required>
                        </div>
                        <div class="col-md-6">
                            <select id="bank_institution" name="bank_institution" class="form-select p-2" required>
                                <option value="" selected disabled>Select Bank/Financial Institution</option>
                                <option value="Commonwealth Bank">Commonwealth Bank</option>
                                <option value="Westpac">Westpac</option>
                                <option value="ANZ">ANZ</option>
                                <option value="NAB">NAB</option>
                                <option value="Macquarie Bank">Macquarie Bank</option>
                                <option value="Suncorp">Suncorp</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="bank_bsb" name="bsb" class="form-control p-2" placeholder="BSB Number (6-digits, Required)" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="bank_account_number" name="account_number" class="form-control p-2" placeholder="Account Number (Required)" required>
                        </div>
                    </div>

                    <div class="p-4 rounded-3" style="background-color: #f9fafb; border: 1px solid #f3f4f6;">
                        <h6 class="fw-bold mb-3" style="font-size: 14px; color: #111827;">Use for These Locations (Payout Destinations)</h6>
                        <p class="text-muted small mb-3">Select which storage locations should automatically route auction revenue to this bank account.</p>
                        
                        <div id="modal-locations-list" class="row g-3">
                            <!-- Locations checkboxes will be injected here -->
                            <div class="col-12 text-center py-3">
                                <span class="spinner-border spinner-border-sm text-secondary"></span>
                                <span class="ms-2 text-muted">Loading locations...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="save-bank-btn" class="btn btn-emerald px-4" style="background-color: #0d3b2e; color: #ffffff; border: none; font-weight: 600;">
                        Save Bank Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
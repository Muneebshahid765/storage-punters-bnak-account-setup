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
                    <h1 class="dash-title-modern">Manage Bidders</h1>
                    <p class="text-muted small mb-0">View and manage all registered bidders (punters)</p>
                </div>
            </div>
        </header>

        <!-- Bidders Table Section -->
        <section class="content-section-modern">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <h2 class="section-title-modern mb-0">All Bidders</h2>
                <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                    <input type="text" id="bidder-search" class="modern-form-control" style="width: 250px;" placeholder="Search bidders...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="bidders-table" class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Total Bids</th>
                            <th>Total Wins</th>
                            <th>Avg Win Price</th>
                            <th>Wallet Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<!-- Edit Bidder Modal -->
<div class="modal fade" id="editBidderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bidder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-bidder-form">
                    <input type="hidden" id="edit-user-id" name="user_id">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit-first-name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit-last-name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit-email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="edit-phone" name="phone">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-bidder-btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Credit Modal -->
<div class="modal fade" id="addCreditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Credit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-credit-form">
                    <input type="hidden" id="credit-user-id" name="user_id">
                    <div class="mb-3">
                        <label class="form-label">Credit Amount (AUD)</label>
                        <input type="number" class="form-control" id="credit-amount" name="credit_amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="credit-note" name="note" rows="3" placeholder="e.g., Promotional credit"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add-credit-btn">Add Credit</button>
            </div>
        </div>
    </div>
</div>

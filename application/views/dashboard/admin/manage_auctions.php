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
                    <h1 class="dash-title-modern">Auctions Management</h1>
                    <p class="text-muted small mb-0">Centralized moderation and oversight of all listings</p>
                </div>
            </div>
        </header>

        <!-- Auctions Table Section -->
        <section class="content-section-modern">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <h2 class="section-title-modern mb-0">All Auctions</h2>
                <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                    <select id="status-filter" class="modern-form-control" style="width: 150px;">
                        <option value="">All Statuses</option>
                        <option value="1">Current</option>
                        <option value="2">Sold</option>
                        <option value="3">Draft</option>
                        <option value="4">Expired</option>
                        <option value="10">Cancelled</option>
                    </select>
                    <input type="text" id="auction-search" class="modern-form-control" style="width: 250px;" placeholder="Search auctions...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="auctions-table" class="table-modern">
                    <thead>
                        <tr>
                            <th>Auction ID</th>
                            <th>Unit Number</th>
                            <th>Facility</th>
                            <th>Location</th>
                            <th>Debt Value</th>
                            <th>Starting Price</th>
                            <th>Current Price</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Visibility</th>
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

<!-- Edit Auction Modal -->
<div class="modal fade" id="editAuctionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Auction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-auction-form">
                    <input type="hidden" id="edit-auction-id" name="auction_id">
                    <div class="mb-3">
                        <label class="form-label">Outstanding Debt (AUD)</label>
                        <input type="number" class="form-control" id="edit-outstanding-debt" name="outstanding_debt" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Starting Price (AUD)</label>
                        <input type="number" class="form-control" id="edit-starting-price" name="starting_price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="edit-status-id" name="status_id">
                            <option value="1">Current</option>
                            <option value="2">Sold</option>
                            <option value="3">Draft</option>
                            <option value="4">Expired</option>
                            <option value="10">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is-staged" name="is_staged" value="1">
                        <label class="form-check-label" for="edit-is-staged">Staged (Internal/Hidden)</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-auction-btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

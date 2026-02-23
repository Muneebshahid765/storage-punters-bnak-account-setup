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
                <h1 class="dash-title-modern">Bulk Credit Management</h1>
                <p class="text-muted small mb-0">Run targeted promotional campaigns to encourage bidding</p>
            </div>
        </header>

        <!-- Filters Section -->
        <section class="content-section-modern mb-4">
            <h2 class="section-title-modern">Targeting & Filtering</h2>
            <p class="section-subtitle-modern">Select bidders based on location and activity</p>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Location</label>
                    <select id="location-filter" class="modern-form-control">
                        <option value="">All Locations</option>
                        <option value="Sydney">Sydney</option>
                        <option value="Melbourne">Melbourne</option>
                        <option value="Brisbane">Brisbane</option>
                        <option value="Adelaide">Adelaide</option>
                        <option value="Perth">Perth</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Total Bids</label>
                    <select id="bid-filter" class="modern-form-control">
                        <option value="all">All Bidders</option>
                        <option value="with_bids">With Bids</option>
                        <option value="without_bids">Without Bids</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Total Wins</label>
                    <select id="win-filter" class="modern-form-control">
                        <option value="all">All Bidders</option>
                        <option value="with_wins">With Wins</option>
                        <option value="without_wins">Without Wins</option>
                    </select>
                </div>
            </div>
            
            <button type="button" id="load-bidders-btn" class="btn btn-primary">
                <i class="bi bi-search me-2"></i> Load Bidders
            </button>
        </section>

        <!-- Bidders Selection Section -->
        <section class="content-section-modern mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title-modern mb-0">Targeted Bidders (<span id="bidder-count">0</span>)</h2>
                <div class="d-flex gap-2">
                    <button type="button" id="select-all-btn" class="btn btn-sm btn-outline-primary">Select All</button>
                    <button type="button" id="deselect-all-btn" class="btn btn-sm btn-outline-secondary">Deselect All</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                            </th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Location</th>
                            <th>Total Bids</th>
                            <th>Total Wins</th>
                            <th>Wallet Balance</th>
                        </tr>
                    </thead>
                    <tbody id="bidders-list">
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Click "Load Bidders" to see targeted bidders
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Bulk Action Section -->
        <section class="content-section-modern">
            <h2 class="section-title-modern">Bulk Credit Application</h2>
            <p class="section-subtitle-modern">Configure and apply credit to selected bidders</p>
            
            <form id="bulk-credit-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Credit Amount (AUD)</label>
                        <input type="number" id="bulk-credit-amount" class="modern-form-control" 
                            placeholder="e.g., 20.00" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Expiration (Days)</label>
                        <input type="number" id="expiration-days" class="modern-form-control" 
                            placeholder="e.g., 30" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Customer Note</label>
                        <input type="text" id="customer-note" class="modern-form-control" 
                            placeholder="e.g., +$20 added by Storage Punters">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" id="apply-credit-btn" class="btn btn-success btn-lg">
                        <i class="bi bi-cash-coin me-2"></i> Apply Credit to Selected Bidders
                    </button>
                    <small class="text-muted ms-3">
                        <span id="selected-count">0</span> bidder(s) selected
                    </small>
                </div>
            </form>
        </section>
    </main>
</div>

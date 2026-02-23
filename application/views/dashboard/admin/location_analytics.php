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
                <h1 class="dash-title-modern">Location Analytics</h1>
                <p class="text-muted small mb-0">Visualize platform performance, bidder concentration, and auction activity based on geographical location.</p>
            </div>
        </header>

        <!-- Filters Section -->
        <section class="content-section-modern mb-4">
            <h2 class="section-title-modern">Filter Data</h2>
            <form id="analytics-filters" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Date Range</label>
                    <select class="modern-form-control" name="date_range">
                        <option value="30">Last 30 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Region / State</label>
                    <select class="modern-form-control" name="region">
                        <option value="">All Regions</option>
                        <option value="NSW">NSW</option>
                        <option value="VIC">VIC</option>
                        <option value="QLD">QLD</option>
                        <option value="WA">WA</option>
                        <option value="SA">SA</option>
                        <option value="TAS">TAS</option>
                        <option value="ACT">ACT</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Facility Type</label>
                    <select class="modern-form-control" name="facility_type">
                        <option value="all">All Types</option>
                        <option value="self-storage">Self Storage</option>
                        <option value="industrial">Industrial</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100 py-2">Apply Filters</button>
                </div>
            </form>
        </section>

        <!-- KPI Cards Section -->
        <section class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="settings-card-modern text-center">
                    <h2 class="text-success fw-bold" id="kpi-auctions">0</h2>
                    <p class="text-muted small mb-0">Auctions Listed</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="settings-card-modern text-center">
                    <h2 class="text-primary fw-bold" id="kpi-avg-bid">$0.00</h2>
                    <p class="text-muted small mb-0">Average Winning Bid Value</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="settings-card-modern text-center">
                    <h2 class="text-warning fw-bold" id="kpi-signups">0</h2>
                    <p class="text-muted small mb-0">New Bidder Signups</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="settings-card-modern text-center">
                    <h2 class="text-danger fw-bold" id="kpi-concentration">N/A</h2>
                    <p class="text-muted small mb-0">Highest Bidder Concentration</p>
                </div>
            </div>
        </section>

        <!-- table Section -->
        <section class="content-section-modern">
            <h2 class="section-title-modern">City Performance Breakdown</h2>
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>City</th>
                            <th>State</th>
                            <th>Auctions</th>
                            <th>Signups</th>
                            <th>Avg. Val</th>
                        </tr>
                    </thead>
                    <tbody id="city-performance-list">
                        <!-- Loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

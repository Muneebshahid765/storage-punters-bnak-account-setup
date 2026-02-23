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
                <div>
                    <h1 class="dash-title-modern">Auctions Dashboard</h1>
                    <p class="text-muted mb-0">Manage all auction lifecycle stages: Draft, Current, Pending, and Past.</p>
                </div>
            </div>
        </header>

        <div class="content-section-modern p-4 p-md-5">
            <!-- Filters Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Filter Auctions</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Status</label>
                            <select id="filter-status" class="form-select border rounded-3 p-2">
                                <option value="">All Statuses</option>
                                <option value="1">Live / Current</option>
                                <option value="2">Inactive</option>
                                <option value="3">Pending Review</option>
                                <option value="4">Sold / Past</option>
                                <option value="6">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Location</label>
                            <select id="filter-location" class="form-select border rounded-3 p-2">
                                <option value="">All Locations</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Search Auction Name / ID</label>
                            <input type="text" id="filter-search" class="form-control border rounded-3 p-2" placeholder="e.g., Unit 101 or Electronics">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button id="btn-reset-filters" class="btn btn-light w-100 rounded-3">Reset Filters</button>
                            <button id="btn-apply-filters" class="btn btn-emerald w-100 rounded-3 text-white" style="background-color: #2d5f3f;">Apply Filters</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auctions Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="auctions-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 small text-uppercase text-muted fw-bold">Auction ID</th>
                                <th class="py-3 small text-uppercase text-muted fw-bold">Auction Name</th>
                                <th class="py-3 small text-uppercase text-muted fw-bold">Status</th>
                                <th class="py-3 small text-uppercase text-muted fw-bold">Start/End Dates</th>
                                <th class="py-3 small text-uppercase text-muted fw-bold">Final Value / Current Bid</th>
                                <th class="pe-4 py-3 text-end small text-uppercase text-muted fw-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="auctions-list">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="p-4 bg-light border-top d-flex justify-content-between align-items-center">
                    <p class="mb-0 small text-muted" id="showing-entries">Showing entries...</p>
                    <nav id="pagination-container">
                        <!-- Populated via JS -->
                    </nav>
                </div>
            </div>
        </div>
    </main>
</div>

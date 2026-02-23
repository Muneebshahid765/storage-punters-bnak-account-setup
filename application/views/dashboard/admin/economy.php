<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/admin/sidebar'); ?>

    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div>
                <h1 class="dash-title-modern">Coin Economy & Shop Management</h1>
                <p class="text-muted small mb-0">Configure earning rates and manage entry packages for the Vault.</p>
            </div>
        </header>

        <section class="content-section-modern">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <!-- Tabs -->
                    <ul class="nav nav-pills mb-4" id="economyTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-rules">Global Rules</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-coin-packages">Coin -> Entries</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cash-packages">Cash -> Entries</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Global Rules Tab -->
                        <div class="tab-pane fade show active" id="tab-rules">
                            <form id="form-rules">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Bid Placed (Coins)</label>
                                        <input type="number" name="bid_placed_earn" class="form-control" value="<?= $rules['bid_placed_earn'] ?? 10 ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Auction Won (Coins)</label>
                                        <input type="number" name="auction_won_earn" class="form-control" value="<?= $rules['auction_won_earn'] ?? 150 ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Monthly Renewal (Coins)</label>
                                        <input type="number" name="subscription_renewal_earn" class="form-control" value="<?= $rules['subscription_renewal_earn'] ?? 500 ?>">
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-4">Update Global Rates</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Coin Packages Tab -->
                        <div class="tab-pane fade" id="tab-coin-packages">
                            <div class="d-flex justify-content-end mb-3">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="openPackageModal('coin')">
                                    <i class="bi bi-plus-lg me-1"></i> New Coin Package
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="table-coin-packages">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Package Name</th>
                                            <th>Coin Cost</th>
                                            <th>Entries</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Cash Packages Tab -->
                        <div class="tab-pane fade" id="tab-cash-packages">
                            <div class="d-flex justify-content-end mb-3">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="openPackageModal('cash')">
                                    <i class="bi bi-plus-lg me-1"></i> New Cash Package
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="table-cash-packages">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Package Name</th>
                                            <th>Price (AUD)</th>
                                            <th>Entries</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Package Modal -->
<div class="modal fade" id="packageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-package" class="modal-content border-0 shadow">
            <input type="hidden" name="id" id="package-id">
            <input type="hidden" name="type" id="package-type">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="packageModalLabel">Manage Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Package Name</label>
                    <input type="text" name="package_name" id="package-name" class="form-control" required>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold" id="label-cost">Cost</label>
                        <input type="number" step="0.01" name="cost" id="package-cost" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Entries Given</label>
                        <input type="number" name="entry_amount" id="package-entries" class="form-control" required>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="package-active" checked>
                        <label class="form-check-label small" for="package-active">Active and Visible</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Package</button>
            </div>
        </form>
    </div>
</div>

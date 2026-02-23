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
                <h1 class="dash-title-modern">Bidder Fee Subscriptions</h1>
                <p class="text-muted small mb-0">Configure subscription plans that offer fee reductions for bidders</p>
            </div>
        </header>

        <!-- Current Platform Fees Section -->
        <section class="content-section-modern mb-4">
            <h2 class="section-title-modern">Current Platform Fees</h2>
            <p class="section-subtitle-modern">The standard fee applied to all winning bids for non-subscribers is:</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="settings-card-modern">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="display-4 mb-0" id="base-fee-display">5.0</h3>
                                <p class="text-muted mb-0">Base Platform Fee (%)</p>
                            </div>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBaseFeeModal">
                                <i class="bi bi-pencil me-2"></i> Edit
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">All fee reductions below are calculated against this base rate.</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bidder Fee Reduction Plans Section -->
        <section class="content-section-modern">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="section-title-modern mb-0">Bidder Fee Reduction Plans</h2>
                    <p class="section-subtitle-modern mb-0">Subscription tiers that reduce platform fees</p>
                </div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                    <i class="bi bi-plus-circle me-2"></i> Create New Subscription Type
                </button>
            </div>
            
            <div class="row g-4" id="plans-container">
                <!-- Plans loaded via AJAX -->
            </div>
        </section>
    </main>
</div>

<!-- Edit Base Fee Modal -->
<div class="modal fade" id="editBaseFeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Base Platform Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-base-fee-form">
                    <div class="mb-3">
                        <label class="form-label">Base Fee (%)</label>
                        <input type="number" class="form-control" id="base-fee-input" name="base_fee" step="0.1" min="0" max="100" required>
                        <small class="text-muted">Standard fee for non-subscribers</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-base-fee-btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Subscription Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="create-plan-form">
                    <div class="mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" class="form-control" name="plan_name" placeholder="e.g., Ultra Bidder" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reduce Fees To (%)</label>
                        <input type="number" class="form-control" name="fee_reduction" step="0.1" min="0" max="100" placeholder="e.g., 3.0" required>
                        <small class="text-muted">Final fee percentage after reduction</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (28 Days) - AUD</label>
                        <input type="number" class="form-control" name="price_28_days" step="0.01" min="0" placeholder="e.g., 49.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (Year) - AUD</label>
                        <input type="number" class="form-control" name="price_year" step="0.01" min="0" placeholder="e.g., 500.00" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="create-plan-btn">Create New Subscription</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Subscription Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-plan-form">
                    <input type="hidden" id="edit-plan-id" name="plan_id">
                    <div class="mb-3">
                        <label class="form-label">Reduce Fees To (%)</label>
                        <input type="number" class="form-control" id="edit-fee-reduction" name="fee_reduction" step="0.1" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (28 Days) - AUD</label>
                        <input type="number" class="form-control" id="edit-price-28" name="price_28_days" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (Year) - AUD</label>
                        <input type="number" class="form-control" id="edit-price-year" name="price_year" step="0.01" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="update-plan-btn">Update</button>
            </div>
        </div>
    </div>
</div>

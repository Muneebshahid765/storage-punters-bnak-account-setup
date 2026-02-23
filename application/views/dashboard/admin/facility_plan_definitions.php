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
                <h1 class="dash-title-modern">Facility Subscription Plans</h1>
                <p class="text-muted small mb-0">Define pricing and feature inclusions for facility subscription tiers.</p>
            </div>
        </header>

        <section class="content-section-modern">
            <div class="row g-4">
                <!-- Current Facility Plans Column -->
                <div class="col-lg-8">
                    <h2 class="section-title-modern">Current Facility Plans</h2>
                    <div class="row g-4" id="facility-plans-list">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>

                <!-- Create New Plan Column -->
                <div class="col-lg-4">
                    <div class="settings-card-modern">
                        <h2 class="section-title-modern">Create New Facility Plan</h2>
                        <form id="create-facility-plan-form">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Plan Name</label>
                                <input type="text" class="modern-form-control" name="plan_name" placeholder="e.g., Enterprise" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Monthly Price ($)</label>
                                <input type="number" class="modern-form-control" name="monthly_price" placeholder="$ 299.00" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Annual Price ($)</label>
                                <input type="number" class="modern-form-control" name="annual_price" placeholder="$ 3289.00" step="0.01" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Feature Inclusions</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="Basic AI Auction creation process" id="feat-basic-ai">
                                    <label class="form-check-label small" for="feat-basic-ai">Basic AI Auction creation process</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="Advanced AI Auction creation process" id="feat-adv-ai">
                                    <label class="form-check-label small" for="feat-adv-ai">Advanced AI Auction creation process</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="AI Estimate value (based on images)" id="feat-ai-est">
                                    <label class="form-check-label small" for="feat-ai-est">AI Estimate value (based on images)</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="Run local ads for the duration of my auction" id="feat-ads">
                                    <label class="form-check-label small" for="feat-ads">Run local ads for the duration of my auction</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="Advanced reporting" id="feat-reports">
                                    <label class="form-check-label small" for="feat-reports">Advanced reporting</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="API Access" id="feat-api">
                                    <label class="form-check-label small" for="feat-api">API Access</label>
                                </div>
                            </div>

                            <button type="submit" class="btn-modern-save w-100">Create New Plan</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-4 pt-3 border-top">
             <button type="button" class="btn btn-success px-4 py-2" id="save-all-btn">Save Platform Settings</button>
             <p class="text-muted small mt-2">Changes to the feature inclusion list are saved when the corresponding plan is updated or created.</p>
        </section>
    </main>
</div>

<!-- Edit Facility Plan Modal -->
<div class="modal fade" id="editFacilityPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Facility Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-facility-plan-form">
                    <input type="hidden" name="plan_id" id="edit-f-plan-id">
                    <div class="mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" class="form-control" name="plan_name" id="edit-f-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Price ($)</label>
                        <input type="number" class="form-control" name="monthly_price" id="edit-f-monthly" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Annual Price ($)</label>
                        <input type="number" class="form-control" name="annual_price" id="edit-f-annual" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label d-block">Feature Inclusions</label>
                        <div id="edit-f-features-container">
                            <!-- Dynamic Checkboxes -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-edit-f-plan">Update Plan</button>
            </div>
        </div>
    </div>
</div>

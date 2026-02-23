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
                <h1 class="dash-title-modern">Bidder Subscriptions</h1>
                <p class="text-muted small mb-0">Manage active paid subscriptions for Pro Bidders</p>
            </div>
        </header>

        <!-- Subscriptions Table Section -->
        <section class="content-section-modern">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title-modern mb-0">Active Subscriptions</h2>
                <div class="d-flex gap-2">
                    <input type="text" id="subscription-search" class="modern-form-control" style="width: 250px;" placeholder="Search subscriptions...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="subscriptions-table" class="table-modern">
                    <thead>
                        <tr>
                            <th>Punter ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Tier/Cycle</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Next Due Date</th>
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

<!-- Change Plan Modal -->
<div class="modal fade" id="changePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Subscription Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="change-plan-form">
                    <input type="hidden" id="plan-user-id" name="user_id">
                    <div class="mb-3">
                        <label class="form-label">Plan Tier</label>
                        <select class="form-control" id="plan-name" name="plan_name" required>
                            <option value="Free Bidder">Free Bidder</option>
                            <option value="Pro Bidder">Pro Bidder</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Billing Cycle</label>
                        <select class="form-control" id="billing-cycle" name="billing_cycle" required>
                            <option value="Monthly">Monthly</option>
                            <option value="Annual">Annual</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-plan-btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

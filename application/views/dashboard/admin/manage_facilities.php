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
                    <h1 class="dash-title-modern">Manage Account Holders</h1>
                    <p class="text-muted small mb-0">View and manage all storage facility account holders</p>
                </div>
            </div>
        </header>

        <!-- Facilities Table Section -->
        <section class="content-section-modern">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <h2 class="section-title-modern mb-0">All Facilities</h2>
                <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                    <input type="text" id="facility-search" class="modern-form-control" style="width: 250px;" placeholder="Search facilities...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="facilities-table" class="table-modern">
                    <thead>
                        <tr>
                            <th>Account ID</th>
                            <th>Company Name</th>
                            <th>Contact Name</th>
                            <th>Email</th>
                            <th>Total Locations</th>
                            <th>Total Debt Listed</th>
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

<!-- Edit Facility Modal -->
<div class="modal fade" id="editFacilityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Facility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-facility-form">
                    <input type="hidden" id="edit-company-id" name="company_id">
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="edit-company-name" name="company_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="edit-facility-email" name="email" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-facility-btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Locations Modal -->
<div class="modal fade" id="locationsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Storage Locations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Location ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>State</th>
                            </tr>
                        </thead>
                        <tbody id="locations-list">
                            <!-- Locations loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

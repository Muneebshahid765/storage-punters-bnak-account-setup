<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
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
                <h1 class="dash-title-modern mb-0">Staff Management</h1>
            </div>
            <button class="btn-export-modern __btn__add__new__staff__member" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="bi bi-person-plus-fill"></i> Add New Staff
            </button>
        </header>

        <!-- Staff List Section -->
        <section class="content-section-modern __table__staff__member">
            <h2 class="section-title-modern">Active Team Members</h2>
            <p class="section-subtitle-modern">Manage your team members, roles, and system access permissions.</p>
            
            <div class="staff-table">
                <div class="table-responsive">
                    <table id="staff-member-table" class="table-modern">
                        <thead>
                            <tr>
                                <th>Staff #</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Access</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS Data Injection Point -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Helper Note -->
        <div class="alert alert-light border-0 shadow-sm rounded-4 p-4 mt-4" style="background-color: #F9FAFB;">
            <div class="d-flex gap-3">
                <i class="bi bi-info-circle-fill text-emerald-primary fs-4"></i>
                <div>
                    <h6 class="fw-bold mb-1">About Staff Access</h6>
                    <p class="text-muted small mb-0">Staff members can list auctions and manage facilities based on their roles. You can revoke access at any time from the actions menu.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 p-4">
                <div>
                    <h5 class="modal-title fw-bold fs-4" id="addStaffModalLabel">Invite New Staff</h5>
                    <p class="text-muted mb-0">Send an invitation to a new team member.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <form id="staffMemberAddForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstname" class="form-label fw-600">First Name *</label>
                            <input id="firstname" type="text" name="firstname" class="form-control border rounded-3 p-2" placeholder="First name">
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label fw-600">Last Name *</label>
                            <input id="lastname" type="text" name="lastname" class="form-control border rounded-3 p-2" placeholder="Last name">
                        </div>
                        <div class="col-12">
                            <label for="email_address2" class="form-label fw-600">Email Address *</label>
                            <input id="email_address2" type="email" name="email_address2" class="form-control border rounded-3 p-2" placeholder="name@company.com">
                        </div>

                        <div class="col-12 mt-3">
                            <h6 class="fw-bold fs-6 mb-2">Granular Permissions</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="manage_auctions" name="can_manage_auctions" value="1">
                                        <label class="form-check-label small fw-600" for="manage_auctions">Manage Auctions</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="view_reports" name="can_view_reports" value="1">
                                        <label class="form-check-label small fw-600" for="view_reports">View Reports</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="handle_disputes" name="can_handle_disputes" value="1">
                                        <label class="form-check-label small fw-600" for="handle_disputes">Handle Disputes</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="manage_users" name="can_manage_users" value="1">
                                        <label class="form-check-label small fw-600" for="manage_users">Manage Users</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <h6 class="fw-bold fs-6 mb-2">Location Access</h6>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="manage_all" name="manage_all">
                                <label class="form-check-label fw-600" for="manage_all">Manage All Locations</label>
                            </div>
                            
                            <div id="location-checkboxes-container" class="border rounded-3 p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                <p class="text-muted small mb-2">Select specific locations:</p>
                                <div id="location-list">
                                    <!-- Locations will be loaded here via AJAX -->
                                    <div class="text-center py-2">
                                        <div class="spinner-border spinner-border-sm text-emerald-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <button type="submit" class="w-100 btn btn-emerald py-2 fw-bold text-uppercase">
                                <i class="bi bi-send-fill me-2"></i> Send Invitation
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 p-4">
                <div>
                    <h5 class="modal-title fw-bold fs-4" id="editStaffModalLabel">Edit Staff Member</h5>
                    <p class="text-muted mb-0">Update team member details and permissions.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <form id="staffMemberUpdateForm">
                    <input type="hidden" id="edit_staff_member_id" name="staff_member_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_firstname" class="form-label fw-600">First Name *</label>
                            <input id="edit_firstname" type="text" name="firstname" class="form-control border rounded-3 p-2" placeholder="First name">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_lastname" class="form-label fw-600">Last Name *</label>
                            <input id="edit_lastname" type="text" name="lastname" class="form-control border rounded-3 p-2" placeholder="Last name">
                        </div>
                        <div class="col-12">
                            <label for="edit_email" class="form-label fw-600">Email Address (Cannot be changed)</label>
                            <input id="edit_email" type="email" class="form-control border rounded-3 p-2 bg-light" readonly>
                        </div>

                        <div class="col-12 mt-3">
                            <h6 class="fw-bold fs-6 mb-2">Granular Permissions</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_manage_auctions" name="can_manage_auctions" value="1">
                                        <label class="form-check-label small fw-600" for="edit_manage_auctions">Manage Auctions</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_view_reports" name="can_view_reports" value="1">
                                        <label class="form-check-label small fw-600" for="edit_view_reports">View Reports</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_handle_disputes" name="can_handle_disputes" value="1">
                                        <label class="form-check-label small fw-600" for="edit_handle_disputes">Handle Disputes</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_manage_users" name="can_manage_users" value="1">
                                        <label class="form-check-label small fw-600" for="edit_manage_users">Manage Users</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <h6 class="fw-bold fs-6 mb-2">Location Access</h6>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="edit_manage_all" name="manage_all">
                                <label class="form-check-label fw-600" for="edit_manage_all">Manage All Locations</label>
                            </div>
                            
                            <div id="edit-location-checkboxes-container" class="border rounded-3 p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                <p class="text-muted small mb-2">Select specific locations:</p>
                                <div id="edit-location-list">
                                    <!-- Locations will be loaded here via AJAX -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <button type="submit" class="w-100 btn btn-emerald py-2 fw-bold text-uppercase">
                                <i class="bi bi-save-fill me-2"></i> Update Staff Information
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
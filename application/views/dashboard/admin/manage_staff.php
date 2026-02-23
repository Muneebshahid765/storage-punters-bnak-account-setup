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
                <h1 class="dash-title-modern">👨‍💻 Manage Platform Staff</h1>
                <p class="text-muted small mb-0">Define access control and moderation permissions for platform administrators.</p>
            </div>
        </header>

        <!-- Staff Table Section -->
        <section class="content-section-modern">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <input type="text" id="search-staff" class="modern-form-control" style="width: 250px;" placeholder="Search staff...">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">+ Add New Staff</button>
            </div>
            
            <div class="table-responsive">
                <table id="staff-table" class="table-modern">
                    <thead>
                        <tr>
                            <th>STAFF ID</th>
                            <th>NAME / EMAIL</th>
                            <th>HIGHEST ACCESS LEVEL</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="staff-table-body">
                    <!-- Data loaded via Ajax -->
                </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Staff Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-staff-form" class="p-3">
                    <input type="hidden" name="user_id" id="staff_user_id">
                    <div class="row">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Access Level</label>
                        <select class="form-control" name="role_id" required>
                            <option value="">Select Role</option>
                            <option value="4">Super Admin</option>
                            <!-- <option value="5">Moderator</option> -->
                            <!-- <option value="6">Support Staff</option> -->
                        </select>
                        <div class="form-text text-muted">Note: Only Super Admin role is currently available.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-staff-btn">Create Staff Account</button>
            </div>
        </div>
    </div>
</div>

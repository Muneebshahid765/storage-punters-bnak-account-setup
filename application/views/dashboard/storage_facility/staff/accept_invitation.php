<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Header -->
                <div class="card-header bg-gradient bg-success text-white text-center py-4">
                    <h4 class="mb-1 fw-semibold">You're Invited to Join</h4>
                    <p class="mb-0 small opacity-75">Be part of our growing organization</p>
                </div>

                <!-- Body -->
                <div class="card-body p-5 text-center">
                    <!-- Hidden Fields -->
                    <input type="hidden" id="user_id" value="<?= $user_id ?>">
                    <input type="hidden" id="company_id" value="<?= $company_id ?>">

                    <!-- Company Info -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark mb-2">Company</h5>
                        <p class="company_name fs-5 text-muted mb-0">Loading...</p>
                    </div>

                    <!-- Divider -->
                    <hr class="my-4">

                    <!-- Instructions -->
                    <p class="text-muted mb-4">
                        Accept this invitation to join and collaborate with your team at
                        <strong class="company_name">Loading...</strong>.
                    </p>

                    <!-- Permissions -->
                    <div class="text-start bg-light rounded-3 shadow-sm p-4 mb-5 border">
                        <h6 class="fw-semibold text-success mb-3">
                            <i class="bi bi-shield-check me-1"></i> You will have permission to:
                        </h6>
                        <ul class="list-unstyled mb-0 text-muted">
                            <li class="mb-2">
                                <i class="bi bi-hammer me-2 text-success"></i>
                                Create, view, and update auctions
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-receipt me-2 text-success"></i>
                                View invoices
                            </li>
                            <li>
                                <i class="bi bi-person-lines-fill me-2 text-success"></i>
                                Update your profile
                            </li>
                        </ul>
                    </div>

                    <!-- Password Setup -->
                    <div class="text-start mb-4">
                        <label for="password" class="form-label fw-bold text-dark">Set Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" id="password" class="form-control border-start-0 py-2" placeholder="Enter your new password">
                        </div>
                    </div>

                    <div class="text-start mb-5">
                        <label for="confirm_password" class="form-label fw-bold text-dark">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                            <input type="password" id="confirm_password" class="form-control border-start-0 py-2" placeholder="Confirm your new password">
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" id="btnCancel" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="bi bi-x-circle me-1"></i> Reject
                        </button>
                        <button type="button" id="btnAccept" class="btn btn-success btn-lg px-4">
                            <span id="accept-text"><i class="bi bi-check-circle me-1"></i> Accept & Save</span>
                            <span id="accept-spinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Spinner overlay -->
<div id="spinner-overlay"
     style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);">
        <div class="spinner-border text-light" role="status"></div>
    </div>
</div>

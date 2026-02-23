<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/admin/sidebar'); // Assuming sidebar is extracted or just use the same structure ?>
    
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div>
                <h1 class="dash-title-modern">Prize Drawer</h1>
                <p class="text-muted small mb-0">Manage Vault giveaways, items, and winner selection</p>
            </div>
            <button class="btn-modern-save" data-bs-toggle="modal" data-bs-target="#createDrawModal">
                <i class="bi bi-plus-circle me-2"></i> Create New Draw
            </button>
        </header>

        <section class="content-section-modern">
            <!-- Analytics Cards -->
            <div class="row g-4 mb-4" id="vault-stats">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <h6 class="text-muted small">Active Giveaways</h6>
                            <h3 class="fw-bold mb-0" id="stat-active">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <h6 class="text-muted small">Total Entries</h6>
                            <h3 class="fw-bold mb-0" id="stat-entries">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <h6 class="text-muted small">Total Prize Value</h6>
                            <h3 class="fw-bold mb-0" id="stat-value">-</h3>
                        </div>
                    </div>
                </div>
                 <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <h6 class="text-muted small">Recent Winners</h6>
                            <h3 class="fw-bold mb-0" id="stat-winners">-</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4" id="draws-list">
                <!-- Draws will be loaded here -->
            </div>
        </section>
    </main>
</div>

<!-- Create Draw Modal -->
<div class="modal fade" id="createDrawModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Giveaway Draw</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="create-draw-form">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Draw Title</label>
                        <input type="text" name="title" class="modern-form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Draw Date & Time</label>
                        <input type="datetime-local" name="draw_at" class="modern-form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Create Draw</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Draw Modal -->
<div class="modal fade" id="editDrawModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Giveaway Draw</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-draw-form">
                    <input type="hidden" name="id" id="edit-draw-id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Draw Title</label>
                        <input type="text" name="title" id="edit-draw-title" class="modern-form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Draw Date & Time</label>
                        <input type="datetime-local" name="draw_at" id="edit-draw-at" class="modern-form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" id="edit-draw-description" class="modern-form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Manage Prizes Modal -->
<div class="modal fade" id="manageItemsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Prizes for <span id="items-draw-title"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <h6 class="fw-bold mb-3">Add New Prize</h6>
                        <form id="add-prize-form">
                            <input type="hidden" name="draw_id" id="items-draw-id">
                            <div class="mb-2">
                                <label class="small fw-bold">Item Name</label>
                                <input type="text" name="title" class="modern-form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Estimated Value ($)</label>
                                <input type="number" name="value" class="modern-form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Image URL</label>
                                <input type="text" name="image_url" class="modern-form-control form-control-sm" placeholder="https://...">
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold">Description</label>
                                <textarea name="description" class="modern-form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary w-100">Add Prize Item</button>
                        </form>
                    </div>
                    <div class="col-md-7 border-start">
                        <h6 class="fw-bold mb-3">Current Prizes</h6>
                        <div id="prizes-list" class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                            <!-- Prizes loaded via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

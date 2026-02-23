<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/admin/sidebar'); ?>

    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div>
                <h1 class="dash-title-modern">Video Library Management</h1>
                <p class="text-muted small mb-0">Organize seasons and upload new episodes</p>
            </div>
            <button class="btn-modern-save" data-bs-toggle="modal" data-bs-target="#createSeasonModal">
                <i class="bi bi-plus-circle me-2"></i> New Season
            </button>
        </header>

        <section class="content-section-modern">
            <div id="seasons-management-list">
                <!-- Managed seasons and episodes list -->
            </div>
        </section>
    </main>
</div>

<!-- Create Season Modal -->
<div class="modal fade" id="createSeasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Video Season</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="create-season-form">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Season Name</label>
                        <input type="text" name="name" class="modern-form-control" required placeholder="e.g. Storage Hunting Pro S1">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Create Season</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Episode Modal -->
<div class="modal fade" id="addEpisodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Episode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-episode-form">
                    <input type="hidden" name="season_id" id="episode-season-id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Episode Title</label>
                        <input type="text" name="title" class="modern-form-control" required placeholder="e.g. The Melbourne Haul">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">YouTube URL</label>
                        <input type="url" name="video_url" class="modern-form-control" required placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Thumbnail URL (Optional)</label>
                        <input type="url" name="thumbnail_url" class="modern-form-control" placeholder="https://...">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Episode #</label>
                            <input type="number" name="number" class="modern-form-control" value="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Duration (Seconds)</label>
                            <input type="number" name="duration" class="modern-form-control" value="600">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="modern-form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Episode</button>
                </form>
            </div>
        </div>
    </div>
</div>

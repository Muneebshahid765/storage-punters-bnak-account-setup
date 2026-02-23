<div class="dashboard-container-modern admin-dark-theme">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php $this->load->view('dashboard/admin/sidebar'); ?>

    <main class="main-content-modern">
        <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i> Giveaway Draw Manager</h3>
        <button class="btn btn-warning btn-sm fw-bold" onclick="showCreateDrawModal()">
            <i class="bi bi-plus-circle me-1"></i> New Draw
        </button>
    </div>

<!-- Draw Winners Modal -->
<div class="modal fade" id="gwViewWinnersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-award me-2"></i>Draw Winners</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="gw-winners-body">
                <div class="text-center text-muted py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 mb-0">Loading winners...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4" id="gw-stats-row">
        <div class="col-md-3">
            <div class="card bg-dark text-white border-0">
                <div class="card-body text-center">
                    <div class="h4 fw-bold text-warning mb-0" id="gw-stat-subscribers">0</div>
                    <small class="text-secondary">Active Subscribers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white border-0">
                <div class="card-body text-center">
                    <div class="h4 fw-bold text-success mb-0" id="gw-stat-entries">0</div>
                    <small class="text-secondary">Total Entries Granted</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white border-0">
                <div class="card-body text-center">
                    <div class="h4 fw-bold text-info mb-0" id="gw-stat-revenue">$0</div>
                    <small class="text-secondary">Monthly Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white border-0">
                <div class="card-body text-center">
                    <div class="h4 fw-bold text-danger mb-0" id="gw-stat-tiers">-</div>
                    <small class="text-secondary">Tier Breakdown</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Draws Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">All Draws</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="gw-draws-table">
                    <thead class="table-light">
                        <tr>
                            <th>Draw Code</th>
                            <th>Title</th>
                            <th>Prize</th>
                            <th>Draw Date</th>
                            <th>Status</th>
                            <th>Participants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="gw-draws-body">
                        <tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="border-top p-3 text-center" id="gw-draws-pagination"></div>
        </div>
    </div>

    <!-- Subscriber Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-search me-2"></i> User Search & Entry Management</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <input type="text" id="gw-search-input" class="form-control" placeholder="Search by name or email...">
                </div>
                <div class="col-md-3">
                    <select id="gw-status-filter" class="form-select">
                        <option value="active" selected>Active Only</option>
                        <option value="expired">Expired Only</option>
                        <option value="all">All Plans</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark w-100" onclick="gwSearchSubscribers()">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" onclick="gwSearchSubscribers(1, true)">
                        <i class="bi bi-people-fill me-1"></i> Load All
                    </button>
                </div>
            </div>
            <div id="gw-search-results"></div>
        </div>
    </div>

    <!-- Vault Settings -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-gear me-2"></i> Vault Settings</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Current Jackpot Text</label>
                    <input type="text" id="gw-setting-jackpot" class="form-control" placeholder="$12,953 CASH">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Next Prize Text</label>
                    <input type="text" id="gw-setting-next-prize" class="form-control" placeholder="Next prize: $17,963 CASH">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">NSW Permit #</label>
                    <input type="text" id="gw-setting-nsw" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">SA Permit #</label>
                    <input type="text" id="gw-setting-sa" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">ACT Permit #</label>
                    <input type="text" id="gw-setting-act" class="form-control">
                </div>
                <div class="col-12">
                    <button class="btn btn-success btn-sm fw-bold" onclick="gwSaveSettings()">
                        <i class="bi bi-check-circle me-1"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
        </div> <!-- /.container-fluid -->
    </main>
</div>

<!-- Create Draw Modal -->
<div class="modal fade" id="gwCreateDrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Create New Draw</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title</label>
                    <input type="text" id="gw-draw-title" class="form-control" value="Monthly Cash Giveaway">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <label class="form-label fw-bold">1st Prize ($)</label>
                        <input type="number" id="gw-draw-prize1" class="form-control" value="17963">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold">2nd Prize ($)</label>
                        <input type="number" id="gw-draw-prize2" class="form-control" value="2000">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold">3rd Prize ($)</label>
                        <input type="number" id="gw-draw-prize3" class="form-control" value="1000">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Draw Date & Time</label>
                    <input type="datetime-local" id="gw-draw-date" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning btn-sm fw-bold" onclick="gwCreateDraw()">Create Draw</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Winners Modal -->
<div class="modal fade" id="gwUploadWinnersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-success text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-trophy-fill me-2"></i>Select Draw Winners</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="gw-winner-draw-id">
                
                <!-- 1st Place -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-warning"><i class="bi bi-trophy-fill me-2"></i>1st Place Winner</label>
                    <div class="position-relative">
                        <input type="text" id="gw-winner-1st-search" class="form-control form-control-lg" placeholder="Search by name, email, or ID..." autocomplete="off">
                        <input type="hidden" id="gw-winner-1st">
                        <div id="gw-winner-1st-results" class="autocomplete-results"></div>
                    </div>
                    <div id="gw-winner-1st-selected" class="selected-user mt-2"></div>
                </div>

                <!-- 2nd Place -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary"><i class="bi bi-award-fill me-2"></i>2nd Place Winner</label>
                    <div class="position-relative">
                        <input type="text" id="gw-winner-2nd-search" class="form-control" placeholder="Search by name, email, or ID..." autocomplete="off">
                        <input type="hidden" id="gw-winner-2nd">
                        <div id="gw-winner-2nd-results" class="autocomplete-results"></div>
                    </div>
                    <div id="gw-winner-2nd-selected" class="selected-user mt-2"></div>
                </div>

                <!-- 3rd Place -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-info"><i class="bi bi-star-fill me-2"></i>3rd Place Winner</label>
                    <div class="position-relative">
                        <input type="text" id="gw-winner-3rd-search" class="form-control" placeholder="Search by name, email, or ID..." autocomplete="off">
                        <input type="hidden" id="gw-winner-3rd">
                        <div id="gw-winner-3rd-results" class="autocomplete-results"></div>
                    </div>
                    <div id="gw-winner-3rd-selected" class="selected-user mt-2"></div>
                </div>

                <hr class="my-4">

                <!-- Certificate URL -->
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="bi bi-file-earmark-check me-2"></i>TPAL Certificate URL</label>
                    <input type="url" id="gw-winner-cert" class="form-control" placeholder="https://randomdraws.com.au/certificate/...">
                    <small class="text-muted">Optional: Link to official draw certificate</small>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Cancel</button>
                <button type="button" class="btn btn-success fw-bold" onclick="gwUploadWinners()"><i class="bi bi-upload me-1"></i>Upload Winners</button>
            </div>
        </div>
    </div>
</div>

<style>
.autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1050;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: none;
}

.autocomplete-results.show {
    display: block;
}

.autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.autocomplete-item:hover {
    background: #f8f9fa;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item .user-name {
    font-weight: 600;
    color: #212529;
}

.autocomplete-item .user-email {
    font-size: 0.875rem;
    color: #6c757d;
}

.autocomplete-item .user-id {
    font-size: 0.75rem;
    color: #adb5bd;
}

.selected-user {
    display: none;
    padding: 0.75rem 1rem;
    background: #e7f5ff;
    border: 1px solid #74c0fc;
    border-radius: 0.375rem;
}

.selected-user.show {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.selected-user .user-info {
    flex: 1;
}

.selected-user .user-info .name {
    font-weight: 600;
    color: #1971c2;
}

.selected-user .user-info .email {
    font-size: 0.875rem;
    color: #495057;
}

.selected-user .btn-remove {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.gw-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
}

.gw-btn-compact {
    font-size: 0.8rem;
    padding: 0.2rem 0.55rem;
    line-height: 1.2;
}

.gw-action-buttons .dropdown-menu {
    min-width: 10rem;
}
</style>

<script>
const GW_SUB_PER_PAGE = 10;
const GW_DRAWS_PER_PAGE = 8;
let gwSubPage = 1;
let gwSubLoadAll = false;
let gwDrawPage = 1;
let gwDrawData = [];

document.addEventListener('DOMContentLoaded', function() {
    gwLoadDashboard();
    gwApplyDrawDateGuardrails();
});

function gwApplyDrawDateGuardrails() {
    const input = document.getElementById('gw-draw-date');
    if (!input) return;

    function setDefaultDate() {
        const now = new Date();
        const thirtyDaysOut = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);
        thirtyDaysOut.setMinutes(0, 0, 0);
        const formatted = thirtyDaysOut.toISOString().slice(0, 16);
        input.min = formatted;
        if (!input.value) {
            input.value = formatted;
        }
    }

    setDefaultDate();
    input.addEventListener('change', function() {
        if (!input.value) {
            input.value = input.min;
            return;
        }
        const chosen = new Date(input.value);
        const min = new Date(input.min);
        if (chosen < min) {
            input.value = input.min;
        }
    });
    document.getElementById('gwCreateDrawModal').addEventListener('show.bs.modal', setDefaultDate);
}

function gwLoadDashboard() {
    fetch(SITE_URL + 'api/admin/giveaway/dashboard')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) return;
            var d = data.data;

            // Stats
            var stats = d.stats || {};
            document.getElementById('gw-stat-subscribers').textContent = (stats.active_subscribers || 0);
            var totalEntries = stats.total_entries_granted || 0;
            document.getElementById('gw-stat-entries').textContent = totalEntries.toLocaleString();
            var revenue = stats.monthly_revenue_cents || 0;
            document.getElementById('gw-stat-revenue').textContent = '$' + (revenue / 100).toLocaleString();

            var tierText = (stats.by_tier || []).map(function(t) { return t.tier_name + ': ' + t.count; }).join(', ');
            document.getElementById('gw-stat-tiers').textContent = tierText || 'None';

            // Populate Vault Settings form inputs
            var settings = d.settings || {};
            if (document.getElementById('gw-setting-jackpot')) {
                document.getElementById('gw-setting-jackpot').value = settings.current_jackpot_text || '';
            }
            if (document.getElementById('gw-setting-next-prize')) {
                document.getElementById('gw-setting-next-prize').value = settings.next_prize_text || '';
            }
            if (document.getElementById('gw-setting-nsw')) {
                document.getElementById('gw-setting-nsw').value = settings.nsw_permit_number || '';
            }
            if (document.getElementById('gw-setting-sa')) {
                document.getElementById('gw-setting-sa').value = settings.sa_permit_number || '';
            }
            if (document.getElementById('gw-setting-act')) {
                document.getElementById('gw-setting-act').value = settings.act_permit_number || '';
            }

            // Draws table
            gwDrawData = d.draws || [];
            gwDrawPage = 1;
            gwRenderDraws();
        });
}

function gwRenderDraws() {
    var tbody = document.getElementById('gw-draws-body');
    var pager = document.getElementById('gw-draws-pagination');
    if (!gwDrawData || gwDrawData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No draws found</td></tr>';
        pager.innerHTML = '';
        return;
    }

    var totalPages = Math.ceil(gwDrawData.length / GW_DRAWS_PER_PAGE);
    if (gwDrawPage > totalPages) gwDrawPage = totalPages;
    var start = (gwDrawPage - 1) * GW_DRAWS_PER_PAGE;
    var current = gwDrawData.slice(start, start + GW_DRAWS_PER_PAGE);

    tbody.innerHTML = current.map(function(draw) {
        var participantsRaw = draw.total_participants_at_lock;
        var participantsCount = participantsRaw === null || participantsRaw === undefined ? null : parseInt(participantsRaw, 10);
        if (isNaN(participantsCount)) {
            participantsCount = null;
        }
        var hasParticipants = participantsCount > 0;
        var participantsCell;
        if (participantsCount === 0) {
            participantsCell = '<span class="badge bg-secondary">0 — No entries locked</span>';
        } else if (hasParticipants) {
            participantsCell = participantsCount.toLocaleString();
        } else {
            participantsCell = '-';
        }

        var statusBadge = {
            'scheduled': '<span class="badge bg-secondary">Scheduled</span>',
            'active': '<span class="badge bg-success">Active</span>',
            'locked': '<span class="badge bg-warning text-dark">Locked</span>',
            'completed': '<span class="badge bg-info">Completed</span>'
        }[draw.status] || '<span class="badge bg-dark">' + draw.status + '</span>';

        var actions = '';
        if (draw.status === 'active') {
            actions += '<button class="btn btn-warning btn-sm me-1" onclick="gwLockEntries(' + draw.id + ')"><i class="bi bi-lock"></i> Lock</button>';
        }
        if (draw.status === 'locked') {
            if (hasParticipants) {
                actions += '<div class="btn-group me-1"><button type="button" class="btn btn-info btn-sm gw-btn-compact dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-download"></i> CSV</button>';
                actions += '<ul class="dropdown-menu">';
                actions += '<li><button type="button" class="dropdown-item" onclick="gwDownloadCsv(this,' + draw.id + ',\'all\')"><i class="bi bi-people me-2"></i>All Participants</button></li>';
                actions += '<li><button type="button" class="dropdown-item" onclick="gwDownloadCsv(this,' + draw.id + ',\'major_only\')"><i class="bi bi-trophy me-2"></i>Major Prize (Excl. SA)</button></li>';
                actions += '<li><button type="button" class="dropdown-item" onclick="gwDownloadCsv(this,' + draw.id + ',\'minor_only\')"><i class="bi bi-gift me-2"></i>Minor Prize (SA Only)</button></li>';
                actions += '</ul></div>';
                actions += '<button class="btn btn-success btn-sm gw-btn-compact" onclick="showUploadWinners(' + draw.id + ')"><i class="bi bi-upload"></i> Winners</button>';
            } else {
                actions += '<span class="badge bg-light text-dark border">Awaiting participants</span>';
            }
        }

        var hasWinners = draw.status === 'completed' || draw.winner_id || draw.winner_user_id || draw.winner_display;

        if (hasWinners) {
            actions += '<button class="btn btn-outline-primary btn-sm gw-btn-compact" onclick="gwViewWinners(' + draw.id + ')"><i class="bi bi-award"></i> View Winners</button>';
        }

        return '<tr>' +
            '<td><code>' + (draw.draw_code || '-') + '</code></td>' +
            '<td>' + draw.title + '</td>' +
            '<td>$' + (draw.prize_value_cents / 100).toLocaleString() + '</td>' +
            '<td>' + new Date(draw.draw_at).toLocaleDateString('en-AU') + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + participantsCell + '</td>' +
            '<td><div class="gw-action-buttons">' + actions + '</div></td>' +
            '</tr>';
    }).join('');

    if (totalPages <= 1) {
        pager.innerHTML = '';
        return;
    }

    var prevDisabled = gwDrawPage === 1 ? 'disabled' : '';
    var nextDisabled = gwDrawPage === totalPages ? 'disabled' : '';
    pager.innerHTML = `
        <div class="btn-group" role="group">
            <button class="btn btn-outline-secondary btn-sm ${prevDisabled}" onclick="gwChangeDrawPage(-1)" ${prevDisabled}>Previous</button>
            <button class="btn btn-outline-dark btn-sm" disabled>Page ${gwDrawPage} / ${totalPages}</button>
            <button class="btn btn-outline-secondary btn-sm ${nextDisabled}" onclick="gwChangeDrawPage(1)" ${nextDisabled}>Next</button>
        </div>`;
}

function gwChangeDrawPage(delta) {
    var totalPages = Math.ceil((gwDrawData || []).length / GW_DRAWS_PER_PAGE);
    gwDrawPage = Math.min(Math.max(1, gwDrawPage + delta), totalPages);
    gwRenderDraws();
}

function showCreateDrawModal() {
    new bootstrap.Modal(document.getElementById('gwCreateDrawModal')).show();
}

function gwCreateDraw() {
    var payload = {
        title: document.getElementById('gw-draw-title').value,
        prize_1st: parseInt(document.getElementById('gw-draw-prize1').value) || 0,
        prize_2nd: parseInt(document.getElementById('gw-draw-prize2').value) || 0,
        prize_3rd: parseInt(document.getElementById('gw-draw-prize3').value) || 0,
        draw_at: document.getElementById('gw-draw-date').value,
        status: 'active'
    };

    fetch(SITE_URL + 'api/admin/giveaway/create-draw', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        bootstrap.Modal.getInstance(document.getElementById('gwCreateDrawModal')).hide();
        if (data.success) {
            Swal.fire('Created!', 'Draw created successfully.', 'success');
            gwLoadDashboard();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function gwViewWinners(drawId) {
    var modalEl = document.getElementById('gwViewWinnersModal');
    var modalBody = document.getElementById('gw-winners-body');
    modalBody.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading winners...</p></div>';
    new bootstrap.Modal(modalEl).show();

    fetch(SITE_URL + 'api/admin/giveaway/draw-winners?draw_id=' + drawId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                modalBody.innerHTML = '<p class="text-danger">' + (data.message || 'Failed to load winners') + '</p>';
                return;
            }

            var draw = data.data.draw;
            var winners = data.data.winners || [];

            if (!winners.length) {
                modalBody.innerHTML = '<div class="text-center py-4"><i class="bi bi-info-circle text-warning fs-1"></i><p class="mt-3 mb-0">No winners have been uploaded for this draw yet.</p></div>';
                return;
            }

            var html = '<div class="mb-3">';
            html += '<h5 class="fw-bold mb-1">' + draw.title + '</h5>';
            html += '<p class="text-muted mb-0">Draw Code: <code>' + draw.draw_code + '</code> • Drawn on ' + new Date(draw.draw_at).toLocaleString('en-AU') + '</p>';
            html += '</div>';

            html += '<div class="table-responsive"><table class="table table-striped"><thead><tr>' +
                '<th>Position</th><th>Winner</th><th>Punter ID</th><th>Total Entries</th><th>Certificate</th></tr></thead><tbody>';

            winners.forEach(function(w) {
                var prizeLabel = ['1st Prize', '2nd Prize', '3rd Prize'][w.prize_position - 1] || ('Prize #' + w.prize_position);
                html += '<tr>' +
                    '<td><span class="badge bg-primary">' + prizeLabel + '</span><div class="small text-muted">$' + (w.prize_amount_cents / 100).toLocaleString() + '</div></td>' +
                    '<td>' + w.first_name + ' ' + w.last_name + '</td>' +
                    '<td><code>' + (w.punter_id || ('SP-' + String(w.user_id).padStart(4, '0'))) + '</code></td>' +
                    '<td>' + (w.total_entries || 0) + '</td>' +
                    '<td>' + (w.certificate_url ? '<a href="' + w.certificate_url + '" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-check"></i> TPAL</a>' : '<span class="text-muted">-</span>') + '</td>' +
                    '</tr>';
            });

            html += '</tbody></table></div>';
            modalBody.innerHTML = html;
        })
        .catch(function() {
            modalBody.innerHTML = '<p class="text-danger">Failed to load winners.</p>';
        });
}

function gwLockEntries(drawId) {
    Swal.fire({
        title: 'Lock Entries?',
        text: 'This will freeze all entries for this draw. This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Lock'
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch(SITE_URL + 'api/admin/giveaway/lock-entries', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ draw_id: drawId })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire('Locked!', 'Entries are now frozen.', 'success');
                    gwLoadDashboard();
                }
            });
        }
    });
}

function showUploadWinners(drawId) {
    document.getElementById('gw-winner-draw-id').value = drawId;
    
    // Reset all fields
    ['1st', '2nd', '3rd'].forEach(function(place) {
        document.getElementById('gw-winner-' + place + '-search').value = '';
        document.getElementById('gw-winner-' + place).value = '';
        document.getElementById('gw-winner-' + place + '-results').classList.remove('show');
        document.getElementById('gw-winner-' + place + '-selected').classList.remove('show');
    });
    document.getElementById('gw-winner-cert').value = '';
    
    // Setup autocomplete for each position
    gwSetupWinnerAutocomplete('1st');
    gwSetupWinnerAutocomplete('2nd');
    gwSetupWinnerAutocomplete('3rd');
    
    new bootstrap.Modal(document.getElementById('gwUploadWinnersModal')).show();
}

function gwSetupWinnerAutocomplete(place) {
    var searchInput = document.getElementById('gw-winner-' + place + '-search');
    var resultsDiv = document.getElementById('gw-winner-' + place + '-results');
    var hiddenInput = document.getElementById('gw-winner-' + place);
    var selectedDiv = document.getElementById('gw-winner-' + place + '-selected');
    var drawIdInput = document.getElementById('gw-winner-draw-id');
    
    var searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        var query = this.value.trim();
        
        if (query.length < 2) {
            resultsDiv.classList.remove('show');
            return;
        }

        var drawId = drawIdInput ? drawIdInput.value : '';
        if (!drawId) {
            resultsDiv.innerHTML = '<div class="p-3 text-danger text-center">Draw ID missing.</div>';
            resultsDiv.classList.add('show');
            return;
        }
        
        searchTimeout = setTimeout(function() {
            var url = SITE_URL + 'api/admin/giveaway/search-users?q=' + encodeURIComponent(query) + '&draw_id=' + encodeURIComponent(drawId);
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success || !data.data.users || data.data.users.length === 0) {
                        resultsDiv.innerHTML = '<div class="p-3 text-muted text-center">No users found</div>';
                        resultsDiv.classList.add('show');
                        return;
                    }
                    
                    var html = '';
                    data.data.users.forEach(function(user) {
                        html += '<div class="autocomplete-item" data-user-id="' + user.id + '" data-user-name="' + user.first_name + ' ' + user.last_name + '" data-user-email="' + user.email + '" data-user-state="' + (user.residential_state || 'N/A') + '">';
                        html += '<div class="user-name">' + user.first_name + ' ' + user.last_name + '</div>';
                        html += '<div class="user-email">' + user.email + '</div>';
                        html += '<div class="user-id">ID: ' + user.id + ' | State: ' + (user.residential_state || 'N/A') + '</div>';
                        html += '</div>';
                    });
                    
                    resultsDiv.innerHTML = html;
                    resultsDiv.classList.add('show');
                    
                    // Add click handlers
                    resultsDiv.querySelectorAll('.autocomplete-item').forEach(function(item) {
                        item.addEventListener('click', function() {
                            gwSelectWinner(place, {
                                id: this.dataset.userId,
                                name: this.dataset.userName,
                                email: this.dataset.userEmail,
                                state: this.dataset.userState
                            });
                        });
                    });
                });
        }, 300);
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.classList.remove('show');
        }
    });
}

function gwSelectWinner(place, user) {
    var searchInput = document.getElementById('gw-winner-' + place + '-search');
    var resultsDiv = document.getElementById('gw-winner-' + place + '-results');
    var hiddenInput = document.getElementById('gw-winner-' + place);
    var selectedDiv = document.getElementById('gw-winner-' + place + '-selected');
    
    // Set hidden input
    hiddenInput.value = user.id;
    
    // Hide search input and results
    searchInput.style.display = 'none';
    resultsDiv.classList.remove('show');
    
    // Show selected user
    var stateClass = user.state === 'SA' ? 'bg-danger' : 'bg-secondary';
    selectedDiv.innerHTML = '<div class="user-info">' +
        '<div class="name">' + user.name + ' <span class="badge ' + stateClass + ' ms-2">' + user.state + '</span></div>' +
        '<div class="email">' + user.email + ' (ID: ' + user.id + ')</div>' +
        '</div>' +
        '<button type="button" class="btn btn-sm btn-outline-danger btn-remove" onclick="gwRemoveWinner(\'' + place + '\')"><i class="bi bi-x"></i> Remove</button>';
    selectedDiv.classList.add('show');
}

function gwRemoveWinner(place) {
    var searchInput = document.getElementById('gw-winner-' + place + '-search');
    var hiddenInput = document.getElementById('gw-winner-' + place);
    var selectedDiv = document.getElementById('gw-winner-' + place + '-selected');
    
    hiddenInput.value = '';
    searchInput.value = '';
    searchInput.style.display = 'block';
    selectedDiv.classList.remove('show');
}

function gwUploadWinners() {
    var drawId = document.getElementById('gw-winner-draw-id').value;
    var winners = [];
    var cert = document.getElementById('gw-winner-cert').value;

    var w1 = document.getElementById('gw-winner-1st').value;
    var w2 = document.getElementById('gw-winner-2nd').value;
    var w3 = document.getElementById('gw-winner-3rd').value;

    if (!w1) {
        Swal.fire('Error', 'Please select at least the 1st place winner', 'error');
        return;
    }

    if (w1) winners.push({ user_id: parseInt(w1), certificate_url: cert });
    if (w2) winners.push({ user_id: parseInt(w2), certificate_url: cert });
    if (w3) winners.push({ user_id: parseInt(w3), certificate_url: cert });

    fetch(SITE_URL + 'api/admin/giveaway/upload-results', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ draw_id: parseInt(drawId), winners: winners })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        bootstrap.Modal.getInstance(document.getElementById('gwUploadWinnersModal')).hide();
        if (data.success) {
            Swal.fire('Done!', 'Winners uploaded successfully.', 'success');
            gwLoadDashboard();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function gwDownloadCsv(btn, drawId, type) {
    var originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Downloading';

    fetch(SITE_URL + 'api/admin/giveaway/export-csv?draw_id=' + drawId + '&type=' + type)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success || !data.data || !data.data.download_url) {
                Swal.fire('Error', data.message || 'Download failed', 'error');
                return;
            }
            var link = document.createElement('a');
            link.href = data.data.download_url;
            link.target = '_blank';
            link.rel = 'noopener';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        })
        .catch(function(err) {
            console.error(err);
            Swal.fire('Error', 'Download failed', 'error');
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
}

function gwSearchSubscribers(page, loadAllFlag) {
    if (typeof loadAllFlag === 'boolean') {
        gwSubLoadAll = loadAllFlag;
        if (gwSubLoadAll) {
            gwSubPage = 1;
        }
    }

    if (typeof page === 'number') {
        gwSubPage = Math.max(1, page);
    }

    var q = document.getElementById('gw-search-input').value;
    var status = document.getElementById('gw-status-filter').value;
    var params = new URLSearchParams({
        q: q,
        status: status,
        page: gwSubPage,
        per_page: GW_SUB_PER_PAGE,
        load_all: gwSubLoadAll ? 1 : 0
    });

    fetch(SITE_URL + 'api/admin/giveaway/search-subscribers?' + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var container = document.getElementById('gw-search-results');
            if (!data.success || !data.data.subscribers.length) {
                container.innerHTML = '<p class="text-muted">No subscribers found.</p>';
                return;
            }

            var html = '<table class="table table-sm table-hover"><thead><tr><th>User</th><th>Email</th><th>State</th><th>Tier</th><th>Streak</th><th>Status</th><th>Entries</th><th>Coins</th><th>Actions</th></tr></thead><tbody>';
            data.data.subscribers.forEach(function(s) {
                var stateClass = s.residential_state === 'SA' ? 'bg-danger' : 'bg-secondary';
                html += '<tr>';
                html += '<td>' + s.first_name + ' ' + s.last_name + '</td>';
                html += '<td>' + s.email + '</td>';
                html += '<td><span class="badge ' + stateClass + '">' + (s.residential_state || 'N/A') + '</span></td>';
                html += '<td><span class="badge bg-warning text-dark">' + s.tier_name + '</span></td>';
                html += '<td>' + s.streak_months + ' months</td>';
                html += '<td>' + s.status + '</td>';
                html += '<td><strong>' + (s.total_entries || 0) + '</strong></td>';
                html += '<td><strong class="text-warning">' + (s.coin_balance || 0) + '</strong></td>';
                html += '<td>';
                html += '<button class="btn btn-outline-info btn-sm me-1" onclick="gwViewUserDetails(' + s.user_id + ')" title="View Details"><i class="bi bi-eye"></i></button>';
                html += '<button class="btn btn-outline-success btn-sm me-1" onclick="gwAdjustEntries(' + s.user_id + ', 1)" title="Add Entries"><i class="bi bi-ticket-perforated"></i>+</button>';
                html += '<button class="btn btn-outline-danger btn-sm me-1" onclick="gwAdjustEntries(' + s.user_id + ', -1)" title="Remove Entries"><i class="bi bi-ticket-perforated"></i>-</button>';
                html += '<button class="btn btn-outline-warning btn-sm me-1" onclick="gwAdjustCoins(' + s.user_id + ', 1)" title="Add Coins"><i class="bi bi-coin"></i>+</button>';
                html += '<button class="btn btn-outline-secondary btn-sm" onclick="gwAdjustCoins(' + s.user_id + ', -1)" title="Remove Coins"><i class="bi bi-coin"></i>-</button>';
                html += '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            var pagination = data.data.pagination || {};
            var totalPages = pagination.total_pages || 1;
            var total = pagination.total || data.data.subscribers.length;
            var disablePrev = gwSubLoadAll || gwSubPage <= 1;
            var disableNext = gwSubLoadAll || gwSubPage >= totalPages;

            html += '<div class="d-flex justify-content-between align-items-center mt-3">';
            html += '<div><small class="text-muted">Showing ' + (gwSubLoadAll ? total : Math.min(GW_SUB_PER_PAGE, total)) + ' of ' + total + ' subscribers ' + (gwSubLoadAll ? '(All Loaded)' : '(10 per page)') + '</small></div>';
            html += '<div class="btn-group">';
            html += '<button class="btn btn-outline-secondary btn-sm" ' + (disablePrev ? 'disabled' : '') + ' onclick="gwSearchSubscribers(' + (gwSubPage - 1) + ')">Prev</button>';
            html += '<button class="btn btn-outline-secondary btn-sm" ' + (disableNext ? 'disabled' : '') + ' onclick="gwSearchSubscribers(' + (gwSubPage + 1) + ')">Next</button>';
            html += '</div>';
            html += '<div class="btn-group">';
            if (gwSubLoadAll) {
                html += '<button class="btn btn-outline-dark btn-sm" onclick="gwSearchSubscribers(1, false)"><i class="bi bi-list-nested me-1"></i> Show 10 / page</button>';
            } else {
                html += '<button class="btn btn-outline-dark btn-sm" onclick="gwSearchSubscribers(1, true)"><i class="bi bi-people-fill me-1"></i> Load All</button>';
            }
            html += '</div>';
            html += '</div>';

            container.innerHTML = html;
        });
}

function gwAdjustEntries(userId, direction) {
    var qty = prompt('Enter number of entries to ' + (direction > 0 ? 'add' : 'remove') + ':');
    if (!qty) return;
    qty = parseInt(qty) * direction;

    fetch(SITE_URL + 'api/admin/giveaway/adjust-entries', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, quantity: qty })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Swal.fire('Done', 'Entries adjusted. New total: ' + data.data.new_total, 'success');
            gwSearchSubscribers();
        }
    });
}

function gwAdjustCoins(userId, direction) {
    var amount = prompt('Enter number of coins to ' + (direction > 0 ? 'add' : 'remove') + ':');
    if (!amount) return;
    amount = parseInt(amount) * direction;

    var reason = prompt('Enter reason for adjustment (optional):') || 'Admin manual adjustment';

    fetch(SITE_URL + 'api/admin/giveaway/adjust-coins', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, amount: amount, reason: reason })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Swal.fire('Done', 'Coins adjusted. New balance: ' + data.data.new_balance, 'success');
            gwSearchSubscribers();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function gwViewUserDetails(userId) {
    fetch(SITE_URL + 'api/admin/giveaway/user-details?user_id=' + userId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                Swal.fire('Error', data.message, 'error');
                return;
            }
            var u = data.data.user;
            var sub = data.data.subscription;
            var stateWarning = u.residential_state === 'SA' ? '<span class="badge bg-danger">SA - Ineligible for Major Prizes</span>' : '';
            
            var html = '<div class="text-start">';
            html += '<h5>' + u.first_name + ' ' + u.last_name + ' ' + stateWarning + '</h5>';
            html += '<p><strong>Email:</strong> ' + u.email + '</p>';
            html += '<p><strong>State:</strong> ' + (u.residential_state || 'Not Set') + '</p>';
            html += '<hr>';
            html += '<div class="row">';
            html += '<div class="col-6"><div class="card bg-success text-white p-3 text-center"><h4>' + u.total_entries + '</h4><small>Total Entries</small></div></div>';
            html += '<div class="col-6"><div class="card bg-warning text-dark p-3 text-center"><h4>' + u.coin_balance + '</h4><small>Coin Balance</small></div></div>';
            html += '</div>';
            
            if (sub) {
                html += '<hr><h6>Subscription</h6>';
                html += '<p><strong>Tier:</strong> ' + sub.tier_name + ' | <strong>Status:</strong> ' + sub.status + ' | <strong>Streak:</strong> ' + sub.streak_months + ' months</p>';
            }
            
            if (data.data.entries_breakdown && data.data.entries_breakdown.length) {
                html += '<hr><h6>Entries Breakdown</h6><ul class="list-unstyled">';
                data.data.entries_breakdown.forEach(function(e) {
                    html += '<li><strong>' + e.source + ':</strong> ' + e.total + ' entries (' + e.count + ' transactions)</li>';
                });
                html += '</ul>';
            }
            
            if (data.data.coin_history && data.data.coin_history.length) {
                html += '<hr><h6>Recent Coin History</h6><table class="table table-sm"><thead><tr><th>Date</th><th>Type</th><th>Amount</th></tr></thead><tbody>';
                data.data.coin_history.forEach(function(c) {
                    var amtClass = c.coins_delta > 0 ? 'text-success' : 'text-danger';
                    html += '<tr><td>' + c.created_at + '</td><td>' + c.type + '</td><td class="' + amtClass + '">' + (c.coins_delta > 0 ? '+' : '') + c.coins_delta + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            
            html += '</div>';
            
            Swal.fire({
                title: 'User Details',
                html: html,
                width: '600px',
                showCloseButton: true,
                showConfirmButton: false
            });
        });
}

function gwSaveSettings() {
    var settings = {
        current_jackpot_text: document.getElementById('gw-setting-jackpot').value,
        next_prize_text: document.getElementById('gw-setting-next-prize').value,
        nsw_permit_number: document.getElementById('gw-setting-nsw').value,
        sa_permit_number: document.getElementById('gw-setting-sa').value,
        act_permit_number: document.getElementById('gw-setting-act').value
    };

    fetch(SITE_URL + 'api/admin/giveaway/update-settings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) Swal.fire('Saved', 'Settings updated.', 'success');
    });
}
</script>

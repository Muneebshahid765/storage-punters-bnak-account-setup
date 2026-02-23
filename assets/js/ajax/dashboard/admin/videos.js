$(document).ready(function () {
    loadAdminSeasons();

    $('#create-season-form').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: SITE_URL + 'api/admin/videos/create-season',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire('Success', 'Season created', 'success');
                    $('#createSeasonModal').modal('hide');
                    loadAdminSeasons();
                }
            }
        });
    });
    $('#add-episode-form').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: SITE_URL + 'api/admin/videos/add-episode',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire('Success', 'Episode added', 'success');
                    $('#addEpisodeModal').modal('hide');
                    loadAdminSeasons();
                }
            }
        });
    });
});

function loadAdminSeasons() {
    $.ajax({
        url: SITE_URL + 'api/admin/videos/seasons',
        method: 'GET',
        success: function (res) {
            if (res.success) {
                renderAdminSeasons(res.data);
            }
        }
    });
}

function renderAdminSeasons(seasons) {
    const container = $('#seasons-management-list');
    container.html(seasons.map(season => `
        <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">${season.season_name}</h5>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addEpisode(${season.id})">
                        <i class="bi bi-plus-circle me-1"></i> Add Episode
                    </button>
                </div>
                <div class="episode-list">
                    ${season.episodes && season.episodes.length > 0 ? season.episodes.map(ep => `
                        <div class="d-flex align-items-center p-2 mb-2 bg-light rounded-3">
                            <img src="${ep.thumbnail_url || 'https://via.placeholder.com/120x68'}" class="rounded me-3" style="width: 100px; height: 56px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="fw-bold small">${ep.episode_name}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Episode ${ep.episode_number} • ${Math.floor(ep.duration_seconds / 60)}m ${ep.duration_seconds % 60}s</div>
                            </div>
                        </div>
                    `).join('') : '<p class="text-muted small mb-0">No episodes added yet.</p>'}
                </div>
            </div>
        </div>
    `).join(''));
}

function addEpisode(seasonId) {
    $('#add-episode-form')[0].reset();
    $('#episode-season-id').val(seasonId);
    $('#addEpisodeModal').modal('show');
}

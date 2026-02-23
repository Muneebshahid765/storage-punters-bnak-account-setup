$(document).ready(function () {
    loadAdminDraws();
    loadVaultStats();

    $('#create-draw-form').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: SITE_URL + 'api/admin/vault/create-draw',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire('Success', 'Draw created', 'success');
                    $('#createDrawModal').modal('hide');
                    loadAdminDraws();
                }
            }
        });
    });

    $('#edit-draw-form').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: SITE_URL + 'api/admin/vault/update-draw',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire('Success', 'Draw updated', 'success');
                    $('#editDrawModal').modal('hide');
                    loadAdminDraws();
                }
            }
        });
    });
});

function loadAdminDraws() {
    $.ajax({
        url: SITE_URL + 'api/admin/vault/draws',
        method: 'GET',
        success: function (res) {
            if (res.success) {
                renderAdminDraws(res.data);
            }
        }
    });
}

function loadVaultStats() {
    $.ajax({
        url: SITE_URL + 'api/admin/vault/stats',
        method: 'GET',
        success: function (res) {
            if (res.success) {
                const s = res.data;
                $('#stat-active').text(s.active_draws);
                $('#stat-entries').text(Number(s.total_entries).toLocaleString());
                $('#stat-value').text('$' + Number(s.total_prize_value).toLocaleString());
                $('#stat-winners').text(s.recent_winners.length);
            }
        }
    });
}

function renderAdminDraws(draws) {
    const container = $('#draws-list');
    container.html(draws.map(draw => `
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="fw-bold mb-0">${draw.title}</h5>
                        <div class="badge bg-${draw.status === 'active' ? 'success' : (draw.status === 'scheduled' ? 'warning' : 'secondary')} text-capitalize">${draw.status}</div>
                    </div>
                    <p class="small text-muted mb-2"><i class="bi bi-calendar-event me-2"></i> ${new Date(draw.draw_at).toLocaleString()}</p>
                    <p class="small text-muted mb-4"><i class="bi bi-gift me-2"></i> ${draw.item_count || 0} Prizes</p>
                    
                    <div class="d-flex gap-2 mt-auto">
                        <button class="btn btn-sm btn-yellow text-white fw-bold px-3 py-2 rounded-pill" onclick="triggerDraw(${draw.id})">
                            <i class="bi bi-play-fill me-1"></i> Trigger
                        </button>
                        ${draw.status === 'scheduled' ? `
                            <button class="btn btn-sm btn-outline-success px-3 py-2 rounded-pill" onclick="activateDraw(${draw.id})">
                                <i class="bi bi-check-circle me-1"></i> Activate
                            </button>
                        ` : ''}
                        <button class="btn btn-sm btn-outline-info px-3 py-2 rounded-pill" onclick="managePrizes(${draw.id}, '${draw.title.replace(/'/g, "\\'")}')">
                            <i class="bi bi-list-stars me-1"></i> Prizes
                        </button>
                        <button class="btn btn-sm btn-outline-primary px-3 py-2 rounded-pill" onclick="editDraw(${draw.id})">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger px-3 py-2 rounded-pill" onclick="deleteDraw(${draw.id})">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join(''));
}

function activateDraw(id) {
    $.ajax({
        url: SITE_URL + 'api/admin/vault/activate-draw',
        method: 'POST',
        data: { draw_id: id },
        success: function (res) {
            if (res.success) {
                Swal.fire('Activated', 'This draw is now active and featured on the frontend!', 'success');
                loadAdminDraws();
            }
        }
    });
}

function editDraw(id) {
    $.ajax({
        url: SITE_URL + 'api/admin/vault/get-draw',
        method: 'GET',
        data: { id: id },
        success: function (res) {
            if (res.success) {
                const draw = res.data;
                $('#edit-draw-id').val(draw.id);
                $('#edit-draw-title').val(draw.title);
                // Convert to datetime-local format
                const drawAt = draw.draw_at.replace(' ', 'T').substring(0, 16);
                $('#edit-draw-at').val(drawAt);
                $('#edit-draw-description').val(draw.description);
                $('#editDrawModal').modal('show');
            }
        }
    });
}

function deleteDraw(id) {
    Swal.fire({
        title: 'Delete Draw?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: SITE_URL + 'api/admin/vault/delete-draw',
                method: 'POST',
                data: { draw_id: id },
                success: function (res) {
                    if (res.success) {
                        Swal.fire('Deleted', 'Draw has been removed.', 'success');
                        loadAdminDraws();
                    }
                }
            });
        }
    });
}

function triggerDraw(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will pick a winner and broadcast the result!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Draw Winner!',
        confirmButtonColor: '#f39c12'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: SITE_URL + 'api/admin/vault/trigger-draw',
                method: 'POST',
                data: { draw_id: id },
                success: function (res) {
                    if (res.success) {
                        Swal.fire('Winner Selected!', `${res.winner.first_name} ${res.winner.last_name} won the ${res.prize.title}!`, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        }
    });
}

function managePrizes(id, title) {
    $('#items-draw-id').val(id);
    $('#items-draw-title').text(title);
    loadPrizes(id);
    $('#manageItemsModal').modal('show');
}

function loadPrizes(id) {
    $.ajax({
        url: SITE_URL + 'api/admin/vault/get-draw?id=' + id,
        method: 'GET',
        success: function (res) {
            const items = res.data.items || [];
            const container = $('#prizes-list');
            if (items.length === 0) {
                container.html('<div class="p-3 text-center text-muted">No prizes added yet</div>');
            } else {
                container.html(items.map(item => `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">${item.title}</h6>
                            <small class="text-muted">$${Number(item.estimated_value).toLocaleString()}</small>
                        </div>
                        <button class="btn btn-sm btn-link text-danger" onclick="deletePrize(${item.id}, ${id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `).join(''));
            }
        }
    });
}

$('#add-prize-form').submit(function (e) {
    e.preventDefault();
    $.ajax({
        url: SITE_URL + 'api/admin/vault/add-prize',
        method: 'POST',
        data: $(this).serialize(),
        success: function (res) {
            if (res.success) {
                $('#add-prize-form')[0].reset();
                loadPrizes($('#items-draw-id').val());
                loadAdminDraws();
            }
        }
    });
});

function deletePrize(itemId, drawId) {
    if (!confirm('Remove this prize?')) return;
    $.ajax({
        url: SITE_URL + 'api/admin/vault/delete-prize',
        method: 'POST',
        data: { id: itemId },
        success: function (res) {
            if (res.success) {
                loadPrizes(drawId);
                loadAdminDraws();
            }
        }
    });
}

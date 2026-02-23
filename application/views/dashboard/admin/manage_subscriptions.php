<div class="dashboard-container-modern admin-dark-theme">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php $this->load->view('dashboard/admin/sidebar'); ?>

    <main class="main-content-modern">
        <div class="container-fluid">
        <div class="page-header-modern mb-4">
            <h1 class="page-title-modern">
                <i class="bi bi-trophy-fill text-warning me-2"></i>
                Manage Subscriptions
            </h1>
            <p class="text-secondary">View and manage all user subscriptions and tiers</p>
        </div>

        <!-- Subscription Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card-modern bg-gradient-primary">
                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-details">
                        <div class="stat-value" id="total-subscribers">0</div>
                        <div class="stat-label">Total Subscribers</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-modern bg-gradient-success">
                    <div class="stat-icon"><i class="bi bi-trophy"></i></div>
                    <div class="stat-details">
                        <div class="stat-value" id="active-players">0</div>
                        <div class="stat-label">Players</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-modern bg-gradient-warning">
                    <div class="stat-icon"><i class="bi bi-award"></i></div>
                    <div class="stat-details">
                        <div class="stat-value" id="active-sharks">0</div>
                        <div class="stat-label">Sharks</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-modern bg-gradient-danger">
                    <div class="stat-icon"><i class="bi bi-gem"></i></div>
                    <div class="stat-details">
                        <div class="stat-value" id="active-whales">0</div>
                        <div class="stat-label">Whales</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Tiers Management -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5 class="mb-0">
                    <i class="bi bi-gear-fill me-2"></i>Subscription Tiers
                </h5>
            </div>
            <div class="card-body-modern">
                <div class="table-responsive">
                    <table class="table table-dark table-hover" id="tiers-table">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Price (Monthly)</th>
                                <th>Base Entries</th>
                                <th>Fee Rate</th>
                                <th>Coin Multiplier</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tiers-tbody">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="spinner-border text-warning" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>Active Subscriptions
                </h5>
            </div>
            <div class="card-body-modern">
                <div class="table-responsive">
                    <table class="table table-dark table-hover" id="subscriptions-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Tier</th>
                                <th>Streak</th>
                                <th>Total Entries</th>
                                <th>Status</th>
                                <th>Next Billing</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subscriptions-tbody">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-warning" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
    </main>
</div>

<!-- Edit Tier Modal -->
<div class="modal fade" id="editTierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Edit Subscription Tier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-tier-id">
                <div class="mb-3">
                    <label class="form-label">Tier Name</label>
                    <input type="text" class="form-control bg-secondary text-white border-0" id="edit-tier-name" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price (AUD/month)</label>
                    <input type="number" class="form-control bg-secondary text-white border-0" id="edit-tier-price" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Base Entries</label>
                    <input type="number" class="form-control bg-secondary text-white border-0" id="edit-tier-entries">
                </div>
                <div class="mb-3">
                    <label class="form-label">Fee Rate (%)</label>
                    <input type="number" class="form-control bg-secondary text-white border-0" id="edit-tier-fee" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Coin Multiplier</label>
                    <input type="number" class="form-control bg-secondary text-white border-0" id="edit-tier-multiplier" step="0.1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select bg-secondary text-white border-0" id="edit-tier-status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="saveTierChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- View Subscription Modal -->
<div class="modal fade" id="viewSubscriptionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">
                    <i class="bi bi-eye text-info me-2"></i>Subscription Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded border border-secondary">
                            <small class="text-uppercase text-secondary">Subscriber</small>
                            <h5 class="mb-1" id="view-sub-name">-</h5>
                            <div class="text-muted" id="view-sub-email">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded border border-secondary">
                            <small class="text-uppercase text-secondary">Tier</small>
                            <h5 class="mb-1" id="view-sub-tier">-</h5>
                            <div>
                                Status: <span class="badge bg-success" id="view-sub-status">active</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border border-secondary">
                            <small class="text-uppercase text-secondary">Streak</small>
                            <h5 class="mb-0" id="view-sub-streak">0 months</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border border-secondary">
                            <small class="text-uppercase text-secondary">Total Entries</small>
                            <h5 class="mb-0" id="view-sub-entries">0</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border border-secondary">
                            <small class="text-uppercase text-secondary">Next Billing</small>
                            <h5 class="mb-0" id="view-sub-next-billing">N/A</h5>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded border border-secondary">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-uppercase text-secondary">Subscription ID</small>
                            <div id="view-sub-id" class="fw-bold">-</div>
                        </div>
                        <div class="col-md-8">
                            <small class="text-uppercase text-secondary">Stripe Subscription</small>
                            <div id="view-sub-stripe" class="fw-bold text-warning">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let subscriptionCache = [];
document.addEventListener('DOMContentLoaded', function() {
    loadSubscriptionStats();
    loadTiers();
    loadSubscriptions();
});

function loadSubscriptionStats() {
    fetch(SITE_URL + 'api/admin/subscriptions/stats')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-subscribers').textContent = data.data.total_subscribers || 0;
                document.getElementById('active-players').textContent = data.data.players || 0;
                document.getElementById('active-sharks').textContent = data.data.sharks || 0;
                document.getElementById('active-whales').textContent = data.data.whales || 0;
            }
        });
}

function loadTiers() {
    fetch(SITE_URL + 'api/admin/subscriptions/tiers')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderTiers(data.data);
            }
        });
}

function renderTiers(tiers) {
    const tbody = document.getElementById('tiers-tbody');
    tbody.innerHTML = tiers.map(tier => {
        const isActive = Number(tier.is_active) === 1;
        return `
        <tr>
            <td>
                <i class="bi bi-${tier.icon_class || 'trophy'} me-2"></i>
                <strong>${tier.tier_name}</strong>
            </td>
            <td>$${(tier.price_monthly_cents / 100).toFixed(2)}</td>
            <td>${tier.base_entries}</td>
            <td>${tier.fee_rate}%</td>
            <td>${tier.coin_multiplier}x</td>
            <td>
                <span class="badge ${isActive ? 'bg-success' : 'bg-secondary'}">
                    ${isActive ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-warning" onclick="editTier(${tier.id})">
                    <i class="bi bi-pencil"></i> Edit
                </button>
            </td>
        </tr>
    `;
    }).join('');
}

function loadSubscriptions() {
    fetch(SITE_URL + 'api/admin/subscriptions/list')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                subscriptionCache = data.data || [];
                renderSubscriptions(subscriptionCache);
            }
        });
}

function renderSubscriptions(subs) {
    const tbody = document.getElementById('subscriptions-tbody');
    if (!subs || subs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-secondary">No active subscriptions</td></tr>';
        return;
    }
    
    tbody.innerHTML = subs.map(sub => `
        <tr>
            <td>${sub.first_name} ${sub.last_name}</td>
            <td>${sub.email}</td>
            <td>
                <span class="badge bg-warning text-dark">${sub.tier_name}</span>
            </td>
            <td>${sub.streak_months} months</td>
            <td>${sub.total_entries || 0}</td>
            <td>
                <span class="badge ${sub.status === 'active' ? 'bg-success' : 'bg-danger'}">
                    ${sub.status}
                </span>
            </td>
            <td>${sub.next_billing_date || 'N/A'}</td>
            <td>
                <button class="btn btn-sm btn-outline-info" onclick="viewSubscription(${sub.id})">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function editTier(tierId) {
    fetch(SITE_URL + 'api/admin/subscriptions/tier/' + tierId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const tier = data.data;
                document.getElementById('edit-tier-id').value = tier.id;
                document.getElementById('edit-tier-name').value = tier.tier_name;
                document.getElementById('edit-tier-price').value = (tier.price_monthly_cents / 100).toFixed(2);
                document.getElementById('edit-tier-entries').value = tier.base_entries;
                document.getElementById('edit-tier-fee').value = tier.fee_rate;
                document.getElementById('edit-tier-multiplier').value = tier.coin_multiplier;
                document.getElementById('edit-tier-status').value = tier.is_active;
                
                var modal = new bootstrap.Modal(document.getElementById('editTierModal'));
                modal.show();
            }
        });
}

function saveTierChanges() {
    const tierId = document.getElementById('edit-tier-id').value;
    const data = {
        price_monthly_cents: Math.round(parseFloat(document.getElementById('edit-tier-price').value) * 100),
        base_entries: parseInt(document.getElementById('edit-tier-entries').value),
        fee_rate: parseFloat(document.getElementById('edit-tier-fee').value),
        coin_multiplier: parseFloat(document.getElementById('edit-tier-multiplier').value),
        is_active: parseInt(document.getElementById('edit-tier-status').value)
    };

    fetch(SITE_URL + 'api/admin/subscriptions/tier/update/' + tierId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            Swal.fire('Success', 'Tier updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editTierModal')).hide();
            loadTiers();
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    });
}

function viewSubscription(subId) {
    if (!subscriptionCache || subscriptionCache.length === 0) {
        alert('Subscription list not loaded yet. Please refresh the page.');
        return;
    }

    const sub = subscriptionCache.find(s => Number(s.id) === Number(subId));
    if (!sub) {
        alert('Subscription details not found in cache. Reload the page and try again.');
        return;
    }

    document.getElementById('view-sub-name').textContent = `${sub.first_name} ${sub.last_name}`;
    document.getElementById('view-sub-email').textContent = sub.email || '-';
    document.getElementById('view-sub-tier').textContent = sub.tier_name || '-';
    document.getElementById('view-sub-status').textContent = sub.status || '-';
    document.getElementById('view-sub-streak').textContent = `${sub.streak_months || 0} months`;
    document.getElementById('view-sub-entries').textContent = sub.total_entries || 0;
    document.getElementById('view-sub-next-billing').textContent = sub.next_billing_date || 'N/A';
    document.getElementById('view-sub-id').textContent = sub.id;
    document.getElementById('view-sub-stripe').textContent = sub.stripe_subscription_id || '—';

    new bootstrap.Modal(document.getElementById('viewSubscriptionModal')).show();
}
</script>

<div class="sp-pricing-page" style="padding-top: 30px;">
    <div class="container" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" onclick="handleMembershipBack()" class="btn btn-outline-light btn-sm" style="border-color: rgba(255,255,255,0.2); color: #fff; border-radius: 999px; font-weight: 600;">
                <i class="bi bi-arrow-left-short me-1"></i> Go Back
            </button>
        </div>
        <h2 style="font-weight: 900; margin-bottom: 30px;">
            <i class="bi bi-person-badge me-2" style="color: #ffc107;"></i> MY MEMBERSHIP
        </h2>

        <div id="sp-membership-loading" class="text-center py-5">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="text-secondary mt-3">Loading your membership...</p>
        </div>

        <div id="sp-membership-content" style="display: none;">
            <!-- No Subscription -->
            <div id="sp-no-subscription" style="display: none; text-align: center; padding: 60px 20px;">
                <i class="bi bi-lock" style="font-size: 4rem; color: #666; margin-bottom: 20px; display: block;"></i>
                <h3 style="font-weight: 700; margin-bottom: 10px;">You're Not a Punter Yet!</h3>
                <p style="color: #8a8f9e; max-width: 400px; margin: 0 auto 25px;">Join the Storage Punters Rewards Club to earn entries into monthly cash giveaways.</p>
                <a href="<?= site_url('join') ?>" class="sp-tier-btn red" style="display: inline-block; width: auto; padding: 14px 40px;">
                    JOIN AS A PUNTER
                </a>
            </div>

            <!-- Active Subscription -->
            <div id="sp-active-subscription" style="display: none;">
                <!-- Tier Badge & Status -->
                <div class="sp-membership-card">
                    <div class="sp-membership-header">
                        <div class="sp-membership-tier-badge">
                            <i class="bi bi-award"></i>
                            <span id="sp-mem-tier-name">-</span>
                        </div>
                        <div>
                            <span id="sp-mem-status-badge" class="badge bg-success">Active</span>
                            <span id="sp-mem-price" style="color: #8a8f9e; font-size: 0.9rem; margin-left: 10px;">$0/mo</span>
                        </div>
                    </div>

                    <div style="font-size: 0.8rem; color: #8a8f9e; margin-bottom: 12px; letter-spacing: 0.5px;">
                        Punter ID: <code id="sp-mem-punter" style="font-size: 0.9rem; color: #ffc107;">SP-0000</code>
                    </div>

                    <!-- Stats -->
                    <div class="sp-membership-stats">
                        <div class="sp-membership-stat">
                            <div class="value" id="sp-mem-total-entries">0</div>
                            <div class="label">Total Entries</div>
                        </div>
                        <div class="sp-membership-stat">
                            <div class="value" id="sp-mem-streak">0</div>
                            <div class="label">Month Streak</div>
                        </div>
                        <div class="sp-membership-stat">
                            <div class="value" id="sp-mem-draw-entries">0</div>
                            <div class="label">Current Draw Entries</div>
                        </div>
                    </div>

                    <!-- Period Info -->
                    <div style="background: rgba(255,255,255,0.04); border-radius: 10px; padding: 16px; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <div style="font-size: 0.7rem; color: #666; text-transform: uppercase; letter-spacing: 1px;">Next Billing Date</div>
                                <div style="font-weight: 600; color: #fff;" id="sp-mem-next-billing">-</div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; color: #666; text-transform: uppercase; letter-spacing: 1px;">Fee Rate</div>
                                <div style="font-weight: 600; color: #4caf50;" id="sp-mem-fee-rate">0%</div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; color: #666; text-transform: uppercase; letter-spacing: 1px;">Coin Multiplier</div>
                                <div style="font-weight: 600; color: #ffc107;" id="sp-mem-coin-mult">1x</div>
                            </div>
                        </div>
                    </div>

                    <!-- Entries Breakdown -->
                    <div id="sp-mem-breakdown" style="margin-bottom: 15px;"></div>
                </div>

                <!-- Cancellation Pending -->
                <div id="sp-cancel-pending" class="sp-cancel-warning" style="display: none;">
                    <h4><i class="bi bi-exclamation-triangle me-2"></i> Cancellation Pending</h4>
                    <p>Your subscription will end on <strong id="sp-cancel-end-date">-</strong>. You will retain access until then.</p>
                    <div class="btn-group">
                        <button class="btn btn-warning btn-sm fw-bold" onclick="spUndoCancellation()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> UNDO CANCELLATION
                        </button>
                    </div>
                </div>

                <!-- Cancel Button -->
                <div id="sp-cancel-section" style="margin-top: 20px; text-align: center;">
                    <button class="btn btn-outline-danger btn-sm" onclick="spStartCancellation()">
                        Cancel Subscription
                    </button>
                </div>
            </div>
        </div>

        <!-- Cancel Confirmation Modal -->
        <div class="modal fade" id="spCancelModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background: #1a1e2e; color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Are You Sure?</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="sp-cancel-warning-text" style="color: #ccc;"></p>
                        <div style="background: rgba(198,40,40,0.1); border-radius: 8px; padding: 15px; margin-top: 15px;">
                            <div style="font-size: 0.8rem; color: #ef5350; font-weight: 600;">WHAT YOU'LL LOSE:</div>
                            <ul style="color: #ccc; font-size: 0.9rem; margin-top: 8px; padding-left: 20px;">
                                <li>Your <strong id="sp-cancel-entries-count">0</strong> accumulated entries</li>
                                <li>Your <strong id="sp-cancel-streak-count">0</strong> month streak</li>
                                <li>Compounding entry growth</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Keep My Subscription</button>
                        <button type="button" class="btn btn-danger btn-sm fw-bold" onclick="spConfirmCancel()">
                            Yes, Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadMembershipStatus();
});

function handleMembershipBack() {
    if (document.referrer) {
        window.history.back();
    } else {
        window.location.href = SITE_URL + 'show-dashboard/dashboard';
    }
}

function loadMembershipStatus() {
    fetch(SITE_URL + 'api/subscription/membership-status')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('sp-membership-loading').style.display = 'none';
            document.getElementById('sp-membership-content').style.display = 'block';

            if (!data.success || !data.data.has_subscription) {
                document.getElementById('sp-no-subscription').style.display = 'block';
                return;
            }

            document.getElementById('sp-active-subscription').style.display = 'block';
            var sub = data.data.subscription;
            var entries = data.data.entries;
            var punterId = data.data.punter_id || 'SP-0000';

            document.getElementById('sp-mem-tier-name').textContent = sub.tier_name.toUpperCase();
            document.getElementById('sp-mem-price').textContent = sub.price + '/mo';
            document.getElementById('sp-mem-punter').textContent = punterId;
            document.getElementById('sp-mem-total-entries').textContent = entries.total;
            document.getElementById('sp-mem-streak').textContent = sub.streak_months;
            document.getElementById('sp-mem-draw-entries').textContent = entries.current_draw;
            document.getElementById('sp-mem-fee-rate').textContent = sub.fee_rate + '%';
            document.getElementById('sp-mem-coin-mult').textContent = sub.coin_multiplier + 'x';

            if (sub.current_period_end) {
                var d = new Date(sub.current_period_end);
                document.getElementById('sp-mem-next-billing').textContent = d.toLocaleDateString('en-AU', { day: 'numeric', month: 'short', year: 'numeric' });
            }

            // Status badge
            var badge = document.getElementById('sp-mem-status-badge');
            if (sub.cancel_at_period_end) {
                badge.className = 'badge bg-warning text-dark';
                badge.textContent = 'Cancelling';
                document.getElementById('sp-cancel-pending').style.display = 'block';
                document.getElementById('sp-cancel-section').style.display = 'none';
                document.getElementById('sp-cancel-end-date').textContent = document.getElementById('sp-mem-next-billing').textContent;
            } else {
                badge.className = 'badge bg-success';
                badge.textContent = 'Active';
            }

            // Entries breakdown
            if (entries.breakdown && entries.breakdown.length > 0) {
                var html = '<div style="font-size: 0.75rem; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">ENTRIES BREAKDOWN</div>';
                entries.breakdown.forEach(function(b) {
                    html += '<div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.85rem; color: #aaa;">';
                    html += '<span>' + b.source.replace(/_/g, ' ') + ' (' + b.count + ' grants)</span>';
                    html += '<span style="color: #ffc107; font-weight: 600;">+' + b.total + '</span>';
                    html += '</div>';
                });
                document.getElementById('sp-mem-breakdown').innerHTML = html;
            }
        })
        .catch(function() {
            document.getElementById('sp-membership-loading').style.display = 'none';
            document.getElementById('sp-membership-content').style.display = 'block';
            document.getElementById('sp-no-subscription').style.display = 'block';
        });
}

function spStartCancellation() {
    fetch(SITE_URL + 'api/subscription/cancel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ confirmed: false })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.data.warning) {
            document.getElementById('sp-cancel-warning-text').textContent = data.data.message;
            document.getElementById('sp-cancel-entries-count').textContent = data.data.current_entries;
            document.getElementById('sp-cancel-streak-count').textContent = data.data.streak_months;
            new bootstrap.Modal(document.getElementById('spCancelModal')).show();
        }
    });
}

function spConfirmCancel() {
    fetch(SITE_URL + 'api/subscription/cancel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ confirmed: true })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        bootstrap.Modal.getInstance(document.getElementById('spCancelModal')).hide();
        if (data.success) {
            Swal.fire('Cancelled', data.message, 'info').then(function() {
                loadMembershipStatus();
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function spUndoCancellation() {
    fetch(SITE_URL + 'api/subscription/undo-cancel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Swal.fire('Restored!', data.message, 'success').then(function() {
                loadMembershipStatus();
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}
</script>

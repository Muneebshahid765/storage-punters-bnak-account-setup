$(document).ready(function () {
    loadMembershipStatus();

    $('#confirm-cancel-membership').click(function () {
        startCancellationFlow();
    });
});

function loadMembershipStatus() {
    $.ajax({
        url: SITE_URL + 'api/subscription/status',
        method: 'GET',
        success: function (res) {
            if (res.success) {
                renderMembershipUI(res.data);
            }
        }
    });
}

function renderMembershipUI(data) {
    // This would update the .membership-container in membership.php
    let html = `
        <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Punter Identity</h4>
                        <div class="badge bg-primary rounded-pill px-3">#${data.punter_id}</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Current Tier</div>
                        <h5 class="fw-bold text-uppercase mb-0">${data.tier}</h5>
                    </div>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <div class="small text-muted">Vault Coins</div>
                            <div class="h4 fw-bold mb-0">${data.coin_balance}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-outline-danger w-100 h-100 rounded-3" onclick="showCancellationInfo()">
                            <i class="bi bi-x-circle me-2"></i> Manage Subscription
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('.membership-container').html(html);
}

function showCancellationInfo() {
    $.ajax({
        url: SITE_URL + 'api/subscription/cancel-info',
        method: 'GET',
        success: function (res) {
            if (res.success) {
                Swal.fire({
                    title: 'Wait! Don\'t Lose Your Progress',
                    html: `
                        <div class="text-start">
                            <p>You currently have <strong>${res.data.user_entries} entries</strong> in the <strong>${res.data.draw_title}</strong> draw.</p>
                            <p>If you cancel now, you'll lose your daily entry bonus and coin rewards.</p>
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="bi bi-gift me-2"></i> <strong>STAY OFFER:</strong> Claim 5 FREE entries right now to stay with us!
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Claim 5 Free Entries',
                    cancelButtonText: 'No, Continue to Cancel',
                    confirmButtonColor: '#f39c12',
                    cancelButtonColor: '#d33',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        claimRetentionBonus();
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        finalCancellationConfirm();
                    }
                });
            }
        }
    });
}

function claimRetentionBonus() {
    $.ajax({
        url: SITE_URL + 'api/subscription/claim-bonus',
        method: 'POST',
        success: function (res) {
            if (res.success) {
                Swal.fire('Claimed!', '5 bonus entries have been added to your account. Your subscription remains active.', 'success');
                loadMembershipStatus();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}

function finalCancellationConfirm() {
    Swal.fire({
        title: 'Final Confirmation',
        text: "Your subscription will remain active until the end of this billing cycle. You will not be charged again.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirm Cancellation',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: SITE_URL + 'api/subscription/cancel-confirm',
                method: 'POST',
                success: function (res) {
                    if (res.success) {
                        Swal.fire('Cancelled', res.message, 'success');
                        loadMembershipStatus();
                    }
                }
            });
        }
    });
}

<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
$firstName = isset($userSessionData['firstname']) ? $userSessionData['firstname'] : 'User';
?>

<!-- Load Header Partial -->
<?php $this->load->view('dashboard/bidder/layout/header_account'); ?>

<!-- Custom Styles for this page only -->
<style>
    .welcome-text {
        font-size: 1.25rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 1.5rem;
    }

    /* Tab Pills Styling - Overriding Global Styles */
    .tab-pills-container {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 2rem;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px; /* space for scrollbar */
        width: 100%;
    }

    /* Force override legacy styles for these specific buttons */
    .tab-pill, 
    button.tab_btn, 
    button.btn__current__active_auctions, 
    button.btn__all_auctions,
    button.btn__watched_auctions,
    button.btn__won_auctions,
    button.btn__invoices_auctions {
        background: white !important;
        color: #111827 !important;
        border: 1px solid #d1d5db !important;
        padding: 0.5rem 1rem !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        font-size: 0.9rem !important;
        transition: all 0.2s !important;
        cursor: pointer !important;
        box-shadow: none !important;
        text-transform: none !important;
    }

    .tab-pill:hover,
    button.btn__current__active_auctions:hover {
        background: #f3f4f6 !important;
    }

    /* Active State for Pills */
    .tab-pill.active,
    button.active,
    button.btn__current__active_auctions.active {
        background-color: #00a65a !important;
        color: white !important;
        border-color: #00a65a !important;
    }

    /* Search Input Styling */
    .search-input-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .search-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #374151;
    }
    
    .form-control-search {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
    }

    /* Filters Bar */
    .filters-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    @media (max-width: 768px) {
        .filters-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .tab-pills-container {
            margin-bottom: 1rem;
            width: 100%;
        }
    }
    
    /* Table Styling Overrides (if needed) */
    .table thead th {
        color: #6b7280;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .table tbody td {
        vertical-align: middle;
        font-size: 0.9rem;
        color: #111827;
        border-bottom: 1px solid #f3f4f6;
        padding: 1rem 0.75rem;
    }

    /* Action Buttons */
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: white;
        transition: opacity 0.2s;
    }
    
    .btn-action:hover {
        opacity: 0.9;
        color: white;
    }
    
    .btn-view { background-color: #10b981; }
    .btn-edit { background-color: #374151; }

</style>

<!-- Main Content Section -->
<div class="container">
    <!-- Subscription Status Banner -->
    <div id="subscription-status-banner" style="display: none;" class="alert alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-star-fill me-3" style="font-size: 1.5rem;"></i>
            <div class="flex-grow-1">
                <strong id="sub-tier-name"></strong>
                <div class="small" id="sub-details"></div>
            </div>
            <a href="<?= site_url('show-dashboard/membership') ?>" class="btn btn-sm btn-outline-primary">
                Manage Subscription
            </a>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="welcome-text mb-0">Welcome back, <?= htmlspecialchars($firstName) ?></h2>
        </div>
        <div class="col-md-6">
            <!-- Vault Stats Card from jay_dev integrated into high-quality layout -->
            <div class="d-flex gap-3 align-items-center p-3 rounded shadow-sm bg-white border border-light">
                <div class="d-flex align-items-center me-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-coin fs-5" style="color: #d97706;"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Coin Balance</small>
                        <h4 class="mb-0 fw-bold text-dark"><?php echo number_format($user_coins ?? 0); ?></h4>
                    </div>
                </div>
                <div class="vr bg-secondary opacity-25" style="height: 30px;"></div>
                <div class="d-flex align-items-center ms-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-ticket-perforated-fill fs-5" style="color: #059669;"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Current Draw Entries</small>
                        <h4 class="mb-0 fw-bold text-dark"><?php echo number_format($user_entries ?? 0); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">

        <h3 class="card-title-custom">Auctions Dashboard</h3>

        <!-- Dashboard Filters / Tabs -->
        <div class="filters-bar">
            <!-- Left: Tabs -->
            <div class="__tab__auction__dashboard tab-pills-container mb-0">
                <button class="tab_pill btn__current__active_auctions active">Current/Active</button>
                <button class="tab_pill btn__all_auctions">All Auctions Bid On</button>
                <button class="tab_pill btn__watched_auctions">Watched Auctions</button>
                <button class="tab_pill btn__won_auctions">Auctions Won</button>
                <button class="tab_pill btn__invoices_auctions">Invoices</button>
            </div>
        </div>

        <!-- Filter Controls (Legacy inputs re-styled or wrapped) -->
        <div class="row g-3 mb-4 align-items-end">
            <div class="col-md-auto d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Show</label>
                <select class="form-select form-select-sm" style="width: 70px;">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
                <span class="small text-muted">entries</span>
            </div>
            
            <div class="col text-center text-muted small">
                <!-- Timezone placeholder -->
                Current Time Zone - Australia/Brisbane
            </div>

            <div class="col-md-auto">
                <div class="search-input-group">
                    <span class="search-label">Search:</span>
                    <input type="text" class="form-control-search" placeholder="">
                </div>
            </div>
        </div>

        <!-- Table Content Container -->
        <div class="__tab__table_auction__dashboard table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <!-- Legacy JS loads table here -->
            <div class="text-center p-5 text-muted">
                <div class="spinner-border text-success mb-3" role="status"></div>
                <p>Loading auctions...</p>
            </div>
        </div>
        
    </div>
</div>


<!-- Owner Contact Modal (Preserved) -->
<div class="modal fade" id="ownerContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #f7f7f7 !important; border-radius: 18px; overflow: hidden; box-shadow: 0 6px 24px rgba(0,0,0,0.15);">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h3 class="fw-bold mb-2 mt-2">
                        <i class="bi bi-building text-success me-2"></i> Owner Contact Information
                    </h3>
                    <p class="text-muted mb-0">
                        You’ve successfully paid your invoice. Below are verified details of the auction facility and owner.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Body -->
            <div class="modal-body px-4 py-4">
                <div id="ownerContactModalBody">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border text-primary mb-3" role="status"></div><br>
                        Fetching contact details...
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <div class="modal-footer border-0 pt-0 d-flex justify-content-center">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
// Show phone verification modal if needed
if ($this->session->userdata('show_phone_verification')): 
    echo '<div class="phone-verification-overlay"></div>';
    $this->load->view('auth/phone_verification_modal', [
        'prefilled_phone' => $user_phone ?? ''
    ]);
    $this->session->unset_userdata('show_phone_verification');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('phone-verification-locked');
    var phoneModal = new bootstrap.Modal(document.getElementById('phoneVerificationModal'));
    var modalEl = document.getElementById('phoneVerificationModal');
    modalEl.addEventListener('hidden.bs.modal', function(){
        document.body.classList.remove('phone-verification-locked');
        var overlay = document.querySelector('.phone-verification-overlay');
        if (overlay) overlay.remove();
    });
    phoneModal.show();
});
</script>
<?php endif; ?>

<script>
// Load user subscription status
document.addEventListener('DOMContentLoaded', function() {
    fetch(SITE_URL + 'api/subscription/membership-status')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data.subscription) {
                const sub = data.data.subscription;
                const banner = document.getElementById('subscription-status-banner');
                const tierName = document.getElementById('sub-tier-name');
                const details = document.getElementById('sub-details');
                
                // Set tier colors
                const tierColors = {
                    'player': 'alert-info',
                    'shark': 'alert-warning',
                    'whale': 'alert-success'
                };
                
                banner.classList.add(tierColors[sub.tier_key] || 'alert-primary');
                tierName.textContent = sub.tier_name + ' Member';
                details.innerHTML = `${sub.total_entries || 0} Total Entries • ${sub.streak_months} Month Streak • Next billing: ${sub.next_billing_date || 'N/A'}`;
                banner.style.display = 'block';
            }
        })
        .catch(err => console.log('Subscription status check:', err));
});
</script>

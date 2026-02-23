<?php
$userSessionData = $this->session->userdata('auction_user_session_data');

$coin_shop_prize_text = '$20,000 CASH';
if (!empty($active_draw)) {
    if (!empty($active_draw->prize_value_cents) && (int)$active_draw->prize_value_cents > 0) {
        $coin_shop_prize_text = '$' . number_format($active_draw->prize_value_cents / 100) . ' CASH';
    } elseif (!empty($active_draw->title)) {
        $coin_shop_prize_text = $active_draw->title;
    }
}
?>

<!-- Load Header Partial -->
<?php $this->load->view('dashboard/bidder/layout/header_account'); ?>

<div class="container">
    <div class="dashboard-card">
        <h3 class="card-title-custom">The Coin Shop</h3>

        <section class="content-section-modern">
            <div class="text-center mb-5">
                <p class="lead text-muted">Exchange your hard-earned Coins for chances to win <?php echo $coin_shop_prize_text; ?>!</p>
                
                <div class="d-inline-block bg-light px-4 py-2 rounded-pill shadow-sm mt-3">
                    <span class="text-muted mr-2">Your Balance:</span>
                    <h3 class="d-inline text-success font-weight-bold" id="user-coin-balance"><?php echo number_format($coin_balance); ?> <small>Coins</small></h3>
                </div>
            </div>

            <!-- Feedback Area -->
            <div id="shop-alert" class="alert d-none" role="alert"></div>

            <div class="row">
                <!-- Coin Packages -->
                <div class="col-12">
                    <h3 class="mb-4 text-dark font-weight-bold" style="letter-spacing: -0.5px;">
                        <i class="bi bi-coin text-warning me-2"></i> Spend Your Coins
                    </h3>
                </div>
                
                <?php foreach($coin_packages as $pkg): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden hover-lift" style="background: #fff; border: 1px solid #eee !important;">
                        <div class="card-header border-0 bg-warning bg-opacity-10 text-dark text-center py-3">
                            <h5 class="mb-0 fw-bold text-uppercase small" style="letter-spacing: 1px; color: #856404;"><?php echo htmlspecialchars($pkg->title); ?></h5>
                        </div>
                        <div class="card-body text-center d-flex flex-column align-items-center py-4">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <h2 class="mb-0 fw-black text-dark"><?php echo $pkg->entry_count; ?></h2>
                            </div>
                            <p class="text-muted fw-bold small text-uppercase mb-2">Total Entries</p>
                            
                            <?php if($pkg->bonus_entries > 0): ?>
                                <div class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 mb-3">
                                    <i class="bi bi-plus-lg me-1"></i><?php echo $pkg->bonus_entries; ?> BONUS ENTRIES
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <span class="text-muted small d-block mb-1 text-uppercase fw-bold">Cost</span>
                                <h3 class="text-dark fw-bold mb-0"><?php echo number_format($pkg->coin_price); ?> <small class="fs-6 text-muted">Coins</small></h3>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4 pt-0 px-4 text-center">
                            <button class="btn btn-yellow text-white w-100 py-2 fw-bold rounded-3 shadow-sm coin-buy-btn" 
                                    data-id="<?php echo $pkg->id; ?>"
                                    data-cost="<?php echo $pkg->coin_price; ?>">
                                Redeem Entries
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row mt-5">
                <!-- Cash Packages -->
                <div class="col-12">
                    <h3 class="mb-4 text-dark font-weight-bold" style="letter-spacing: -0.5px;">
                        <i class="bi bi-lightning-charge-fill text-success me-2"></i> Instant Top-Up
                    </h3>

                    <!-- Membership Savings Banner -->
                    <?php if ($user_tier === 'shark'): ?>
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" style="background-color: #e0f7fa; color: #006064;">
                            <i class="bi bi-gem fs-4 me-3"></i>
                            <div>
                                <strong>VIP Savings Active!</strong> Your <span class="text-uppercase fw-bold">Shark</span> membership saves you <span class="fw-bold">50%</span> on all cash entries below.
                            </div>
                        </div>
                    <?php elseif ($user_tier === 'whale'): ?>
                        <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center mb-4" style="background-color: #e3f2fd; color: #0d47a1;">
                            <i class="bi bi-trophy-fill fs-4 me-3"></i>
                            <div>
                                <strong>Elite Savings Active!</strong> Your <span class="text-uppercase fw-bold">Whale</span> membership saves you <span class="fw-bold">75%</span> on all cash entries below.
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border-0 shadow-sm d-flex align-items-center mb-4" style="background-color: #f8f9fa; border: 1px dashed #dee2e6 !important;">
                            <i class="bi bi-stars fs-4 me-3 text-warning"></i>
                            <div class="text-muted">
                                Want cheaper entries? <a href="<?php echo base_url('show-dashboard/membership'); ?>" class="fw-bold text-dark text-decoration-underline">Upgrade to Shark or Whale</a> to save up to <strong>75%</strong> on every purchase!
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if(empty($payment_methods)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning border-0 rounded-3 shadow-sm d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                Please <a href="<?php echo base_url('show-dashboard/payment-method'); ?>" class="fw-bold text-decoration-none">add a payment method</a> to purchase extra entries with cash.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach($cash_packages as $pkg): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden hover-lift" style="background: #fff; border: 1px solid #eee !important;">
                        <div class="card-header border-0 bg-success bg-opacity-10 text-success text-center py-3">
                            <h5 class="mb-0 fw-bold text-uppercase small" style="letter-spacing: 1px;"><?php echo htmlspecialchars($pkg->title); ?></h5>
                        </div>
                        <div class="card-body text-center d-flex flex-column align-items-center py-4">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <h2 class="mb-0 fw-black text-dark"><?php echo $pkg->entry_count; ?></h2>
                            </div>
                            <p class="text-muted fw-bold small text-uppercase mb-2">Total Entries</p>
                            
                            <?php if($pkg->bonus_entries > 0): ?>
                                <div class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-2 mb-3" style="color: #856404 !important;">
                                    <i class="bi bi-star-fill me-1"></i><?php echo $pkg->bonus_entries; ?> BONUS
                                </div>
                            <?php endif; ?>

                            <?php 
                                $original_price = $pkg->price_cents / 100;
                                $final_price = $original_price;
                                $discount_text = "";

                                if ($user_tier === 'shark') {
                                    $final_price = $original_price * 0.5;
                                    $discount_text = "50% Member Discount";
                                } elseif ($user_tier === 'whale') {
                                    $final_price = $original_price * 0.25;
                                    $discount_text = "75% Member Discount";
                                }
                            ?>

                            <div class="mt-auto">
                                <?php if($final_price < $original_price): ?>
                                    <span class="text-muted small d-block mb-1 text-decoration-line-through">$<?php echo number_format($original_price, 2); ?></span>
                                    <h3 class="text-success fw-bold mb-0">$<?php echo number_format($final_price, 2); ?></h3>
                                    <span class="badge bg-primary bg-opacity-10 text-primary mt-2"><?php echo $discount_text; ?></span>
                                <?php else: ?>
                                    <span class="text-muted small d-block mb-1 text-uppercase fw-bold">Price</span>
                                    <h3 class="text-success fw-bold mb-0">$<?php echo number_format($original_price, 2); ?></h3>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4 pt-0 px-4 text-center">
                            <?php if(!empty($payment_methods)): ?>
                                <div class="form-group mb-3 text-start">
                                    <label class="small text-muted fw-bold mb-1">PAYMENT METHOD</label>
                                    <select class="form-select form-select-sm rounded-3 payment-method-select" id="pm-<?php echo $pkg->id; ?>">
                                        <?php foreach($payment_methods as $pm): ?>
                                            <option value="<?php echo $pm['stripe_payment_method_id']; ?>">
                                                <?php echo $pm['card_type']; ?> •••• <?php echo $pm['card_holder_card_number']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button class="btn btn-success w-100 py-2 fw-bold rounded-3 shadow-sm cash-buy-btn" 
                                        data-id="<?php echo $pkg->id; ?>">
                                    Buy Now
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 py-2 fw-bold rounded-3 opacity-50" disabled>
                                    Add Card to Buy
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row mt-5">
                <div class="col-12">
                    <h3 class="mb-4 text-dark font-weight-bold" style="letter-spacing: -0.5px;">
                        <i class="bi bi-clock-history text-secondary me-2"></i> Transaction History
                    </h3>
                    
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="table-responsive p-3">
                            <table id="coin-history-table" class="table table-hover mb-0 align-middle w-100">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr>
                                        <th class="py-3 ps-4 border-0">Date</th>
                                        <th class="py-3 border-0">Description</th>
                                        <th class="py-3 border-0">Type</th>
                                        <th class="py-3 pe-4 border-0 text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated by DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const coinButtons = document.querySelectorAll('.coin-buy-btn');
    const alertBox = document.getElementById('shop-alert');
    const balanceDisplay = document.getElementById('user-coin-balance');

    coinButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const pkgId = this.dataset.id;
            const cost = parseInt(this.dataset.cost);
            
            // Disable button
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

            fetch('<?php echo base_url('coin-shop/buy_with_coins'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ package_id: pkgId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    showAlert('success', data.message);
                    // Update Balance UI
                    let currentBalance = parseInt(balanceDisplay.innerText.replace(/,/g, ''));
                    balanceDisplay.innerHTML = new Intl.NumberFormat().format(currentBalance - cost) + ' <small>Coins</small>';
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An error occurred. Please try again.');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = 'Buy with Coins';
            });
        });
    });

    const cashButtons = document.querySelectorAll('.cash-buy-btn');
    cashButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const pkgId = this.dataset.id;
            const pmId = document.getElementById('pm-' + pkgId).value;
            
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Charging Card...';

            fetch('<?php echo base_url('coin-shop/buy_with_cash'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ package_id: pkgId, payment_method_id: pmId })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Payment failed');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.status) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', error.message || 'An error occurred during payment.');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = 'Buy Now';
            });
        });
    });

    function showAlert(type, message) {
        alertBox.className = `alert alert-${type}`;
        alertBox.innerText = message;
        alertBox.classList.remove('d-none');
        window.scrollTo(0, 0);
        
        setTimeout(() => {
            alertBox.classList.add('d-none');
        }, 5000);
    }

    // Initialize Coin History DataTable
    $('#coin-history-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo base_url('BidderDashboard/getCoinHistory'); ?>",
            "type": "POST"
        },
        "columns": [
            { 
                "data": "created_at",
                "render": function(data) {
                    const date = new Date(data);
                    return date.toLocaleDateString('en-AU', { 
                        month: 'short', day: 'numeric', year: 'numeric'
                    }) + ' ' + date.toLocaleTimeString('en-AU', {
                        hour: 'numeric', minute: '2-digit'
                    });
                }
            },
            { "data": "description" },
            { 
                "data": "type",
                "render": function(data) {
                    return `<span class="badge bg-light text-dark border border-secondary text-uppercase" style="font-size: 0.7rem;">${data.replace(/_/g, ' ')}</span>`;
                }
            },
            { 
                "data": "coins_delta",
                "className": "text-end pe-4",
                "render": function(data) {
                    let val = parseInt(data);
                    if(val > 0) return `<span class="text-success fw-bold">+${val.toLocaleString()}</span>`;
                    return `<span class="text-danger fw-bold">${val.toLocaleString()}</span>`;
                }
            }
        ],
        "order": [[0, "desc"]], // Sort by Date desc
        "language": {
            "emptyTable": "No transactions found",
            "processing": '<div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });
});
</script>
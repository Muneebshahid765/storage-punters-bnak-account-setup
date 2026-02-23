<div class="sp-checkout-page">
    <div class="container">
        <div class="sp-checkout-container">
            <!-- Left: Form -->
            <div class="sp-checkout-form-wrapper">
                <h1 class="sp-checkout-title">EXPRESS CHECKOUT</h1>
                <p class="sp-checkout-subtitle">Enter in seconds. No forms, no fuss, 100% secure.</p>

                <div id="sp-checkout-error" class="sp-error-msg"></div>

                <form id="sp-checkout-form" onsubmit="return false;">
                    <input type="hidden" id="sp-tier-key" value="<?= htmlspecialchars($tier['tier_key']) ?>">

                    <!-- Enter Details -->
                    <h3 class="sp-checkout-section-title">ENTER DETAILS</h3>
                    <p style="color: #666; font-size: 0.85rem; margin-bottom: 20px;">Fill in your details so we know how to contact you if you win.</p>

                    <div class="sp-checkout-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>FIRST NAME <span class="required">*</span></label>
                                <input type="text" id="sp-first-name" placeholder="Enter your first name" required>
                            </div>
                            <div class="form-group">
                                <label>LAST NAME <span class="required">*</span></label>
                                <input type="text" id="sp-last-name" placeholder="Enter your last name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>MOBILE NUMBER <span class="required">*</span></label>
                            <div class="phone-group">
                                <div class="phone-prefix">
                                    <span>🇦🇺</span> +61
                                </div>
                                <input type="tel" id="sp-phone" placeholder="Enter mobile number" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>EMAIL ADDRESS <span class="required">*</span></label>
                            <input type="email" id="sp-email" placeholder="Enter email address" required>
                        </div>

                        <div class="form-group">
                            <label>RESIDENTIAL STATE <span class="required">*</span></label>
                            <select id="sp-state" required>
                                <option value="">Select residential state</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?= $state['code'] ?>" 
                                            data-eligible="<?= $state['eligible'] ?>"
                                            data-disclaimer="<?= htmlspecialchars($state['disclaimer'] ?? '') ?>">
                                        <?= $state['name'] ?> (<?= $state['code'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="sp-state-disclaimer" class="sp-state-disclaimer"></div>
                        </div>

                        <!-- Payment handled by Stripe Checkout -->
                        <div style="background: #f8f9ff; border-radius: 8px; padding: 16px; margin-top: 20px; border: 1px solid #e0e0e0;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <i class="bi bi-shield-lock-fill" style="color: #635bff; font-size: 1.2rem;"></i>
                                <span style="font-weight: 700; font-size: 0.85rem; color: #333;">SECURE PAYMENT VIA STRIPE</span>
                            </div>
                            <p style="font-size: 0.8rem; color: #666; margin: 0;">You'll be redirected to Stripe's secure checkout to enter your payment details. All major cards accepted.</p>
                        </div>

                        <button type="button" id="sp-submit-btn" class="sp-checkout-submit" data-original-label="CONTINUE TO PAYMENT" onclick="spProcessCheckout()">
                            CONTINUE TO PAYMENT
                        </button>

                        <div class="sp-checkout-disclaimer">
                            <strong>NO LOCK-INS, NO DRAMAS – CANCEL ANYTIME!</strong>
                        </div>

                        <div class="sp-checkout-secure">
                            <i class="bi bi-shield-check"></i>
                            <span>GUARANTEED SAFE & SECURE CHECKOUT</span>
                            <span>|</span>
                            <span style="font-weight: 700;">powered by <span style="color: #635bff;">Stripe</span></span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Right: Summary Sidebar -->
            <div class="sp-checkout-sidebar">
                <div class="sp-summary-card">
                    <div class="sp-summary-header">
                        <div class="sp-summary-tier">
                            <span class="sp-summary-tier-icon">
                                <?php if ($tier['tier_key'] === 'player'): ?>
                                    <i class="bi bi-trophy"></i>
                                <?php elseif ($tier['tier_key'] === 'shark'): ?>
                                    <i class="bi bi-award"></i>
                                <?php else: ?>
                                    <i class="bi bi-gem"></i>
                                <?php endif; ?>
                            </span>
                            <div>
                                <div class="sp-summary-tier-name"><?= strtoupper($tier['tier_name']) ?></div>
                                <div class="sp-summary-tier-label">PACKAGE</div>
                            </div>
                        </div>
                        <div class="sp-summary-price">
                            <div class="amount">$<?= number_format($tier['price_monthly_cents'] / 100) ?></div>
                            <div class="period">/MONTH</div>
                        </div>
                    </div>

                    <div class="sp-summary-body">
                        <!-- Current Giveaway -->
                        <?php if ($active_draw): ?>
                        <div class="sp-summary-giveaway">
                            <div class="sp-summary-giveaway-title">CURRENT GIVEAWAY</div>
                            <div class="sp-summary-giveaway-amount">
                                $<?= number_format($active_draw['prize_value_cents'] / 100) ?> CASH GIVEAWAY
                            </div>
                            <div class="sp-summary-giveaway-date">
                                DRAW DATE: <?= date('jS F Y', strtotime($active_draw['draw_at'])) ?>
                            </div>
                            <?php if ($active_draw['prize_2nd_cents'] > 0 || $active_draw['prize_3rd_cents'] > 0): ?>
                            <div class="sp-summary-giveaway-prizes">
                                EXTRA PRIZES<br>
                                <?php if ($active_draw['prize_2nd_cents'] > 0): ?>
                                    🥈 2nd Prize: $<?= number_format($active_draw['prize_2nd_cents'] / 100) ?> CASH
                                <?php endif; ?>
                                <?php if ($active_draw['prize_3rd_cents'] > 0): ?>
                                    <br>🥉 3rd Prize: $<?= number_format($active_draw['prize_3rd_cents'] / 100) ?> CASH
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Entry Summary -->
                        <div class="sp-summary-entries">
                            <h4>YOUR ENTRY SUMMARY</h4>
                            <div class="sp-summary-entry-row">
                                <span>Entries from joining today</span>
                                <span><?= $tier['base_entries'] ?></span>
                            </div>
                            <div class="sp-summary-entry-row">
                                <span><strong>Total entries in this giveaway</strong></span>
                                <span><strong><?= $tier['base_entries'] ?></strong></span>
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="sp-summary-features">
                            <div class="sp-summary-feature">
                                <i class="bi bi-check-circle-fill"></i> Vault Access
                            </div>
                            <div class="sp-summary-feature">
                                <i class="bi bi-check-circle-fill"></i> Daily Bids
                            </div>
                            <div class="sp-summary-feature">
                                <i class="bi bi-check-circle-fill"></i> 1000 Points
                            </div>
                            <div class="sp-summary-feature">
                                <i class="bi bi-check-circle-fill"></i> <?= $tier['fee_rate'] > 0 ? $tier['fee_rate'] . '% Fees' : '0% Fees' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="sp-error-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Subscription Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="sp-error-modal-body">
                There was a problem processing your request.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// State eligibility check
document.getElementById('sp-state').addEventListener('change', function() {
    var selected = this.options[this.selectedIndex];
    var eligible = selected.getAttribute('data-eligible');
    var disclaimer = selected.getAttribute('data-disclaimer');
    var disclaimerEl = document.getElementById('sp-state-disclaimer');
    var submitBtn = document.getElementById('sp-submit-btn');

    if (eligible === '0' && disclaimer) {
        disclaimerEl.textContent = disclaimer;
        disclaimerEl.classList.add('show');
        submitBtn.disabled = true;
    } else {
        disclaimerEl.classList.remove('show');
        submitBtn.disabled = false;
    }
});

// Process Checkout
function spShowCheckoutError(message) {
    var errorEl = document.getElementById('sp-checkout-error');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('show');
    }

    var modalEl = document.getElementById('sp-error-modal');
    if (window.bootstrap && bootstrap.Modal && modalEl) {
        var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        document.getElementById('sp-error-modal-body').textContent = message;
        modalInstance.show();
    }
}

function spProcessCheckout() {
    var btn = document.getElementById('sp-submit-btn');
    var errorEl = document.getElementById('sp-checkout-error');
    if (errorEl) {
        errorEl.classList.remove('show');
    }

    var defaultLabel = btn.getAttribute('data-original-label') || 'CONTINUE TO PAYMENT';

    var firstName = document.getElementById('sp-first-name').value.trim();
    var lastName = document.getElementById('sp-last-name').value.trim();
    var email = document.getElementById('sp-email').value.trim();
    var phone = document.getElementById('sp-phone').value.trim();
    var state = document.getElementById('sp-state').value;
    var tierKey = document.getElementById('sp-tier-key').value;

    if (!firstName || !lastName || !email || !state) {
        spShowCheckoutError('Please fill in all required fields.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="sp-spinner"></span> PROCESSING...';

    fetch(SITE_URL + 'api/subscription/create-checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            tier_key: tierKey,
            first_name: firstName,
            last_name: lastName,
            email: email,
            phone: '+61' + phone,
            state: state
        })
    })
    .then(function(res) {
        if (!res.ok) {
            console.error('HTTP Error:', res.status, res.statusText);
            throw new Error('Server returned ' + res.status + ': ' + res.statusText);
        }
        return res.json();
    })
    .then(function(data) {
        console.log('API Response:', data);
        if (data.success && data.data && data.data.checkout_url) {
            window.location.href = data.data.checkout_url;
        } else {
            var msg = data.message || 'Something went wrong. Please try again.';
            spShowCheckoutError(msg);
            btn.disabled = false;
            btn.innerHTML = defaultLabel;
        }
    })
    .catch(function(err) {
        console.error('Checkout Error:', err);
        var errorMsg = 'Network error: ' + (err.message || 'Unable to connect to server. Please check your internet connection and try again.');
        spShowCheckoutError(errorMsg);
        btn.disabled = false;
        btn.innerHTML = defaultLabel;
    });
}
</script>

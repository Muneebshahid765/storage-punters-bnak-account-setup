<!-- Unified Auth Modal (Redesigned) -->
<div class="modal fade" id="unifiedAuthModal" tabindex="-1" aria-labelledby="unifiedAuthModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- modal-lg for wider forms -->
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetAuthModal()"></button>
            </div>
            <div class="modal-body px-5 pb-5 pt-2">
                
                <!-- 1. SELECTION SCREEN (Default) -->
                <div id="auth-selection-view">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold mb-2">Sign In</h2>
                        <p class="text-muted">Select your account type to continue.</p>
                    </div>

                    <div class="row g-3 justify-content-center mb-3">
                        <div class="col-md-5">
                             <button class="btn btn-success w-100 py-3 fw-bold" onclick="showFacilityRegister()">
                                Facility Register
                             </button>
                        </div>
                        <div class="col-md-5">
                             <button class="btn btn-success w-100 py-3 fw-bold" onclick="showBidderRegister()">
                                Bidder Register
                             </button>
                        </div>
                    </div>

                    <div class="row justify-content-center mb-4">
                        <div class="col-md-10">
                            <button class="btn btn-outline-secondary w-100 py-3 fw-bold bg-white text-dark" onclick="showBidderLogin()">
                                Existing User Login
                            </button>
                        </div>
                    </div>
                     
                </div>

                <!-- 2. BIDDER FORMS -->
                <div id="bidder-auth-view" style="display:none;">
                    
                    <div id="bidder-login-form-container" style="display:none;">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">User Login</h3>
                            <p class="text-muted">Welcome back! Please login to your account.</p>
                        </div>
                        <form id="bidder-login-form">
                             <div class="mb-3">
                                <input type="text" name="email" class="form-control form-control-lg bg-light border-0" placeholder="Username / Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control form-control-lg bg-light border-0" placeholder="Password" required>
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-dark btn-lg fw-bold">Login</button>
                            </div>
                             <div class="text-center">
                                <a href="#" class="text-muted small text-decoration-none __system__user__forgot__password__btn">Forgot Password?</a>
                            </div>
                            <div class="text-center mt-3">
                                <a href="#" class="text-muted small" onclick="showSelectionView()">&larr; Back</a>
                            </div>
                        </form>
                    </div>

                    <!-- Bidder Register Step 1 -->
                    <div id="bidder-register-form-container" style="display:none;">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Bidder Registration: Step 1 of 2</h3>
                            <p class="text-muted">Basic account details for quick signup.</p>
                        </div>
                         <form id="bidder-register-form">
                             <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <input type="text" name="first_name" class="form-control form-control-lg" placeholder="First Name (Required)" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="last_name" class="form-control form-control-lg" placeholder="Last Name (Required)" required>
                                </div>
                             </div>
                             <div class="mb-3">
                                 <input type="email" name="email" class="form-control form-control-lg" placeholder="Email (Required)" required>
                             </div>
                             <div class="mb-3">
                                 <input type="tel" name="phone" class="form-control form-control-lg" placeholder="Mobile Number (Required)" required>
                             </div>
                             <div class="mb-3">
                                <select name="timezone" id="bidder_timezone_select" class="form-select form-select-lg text-muted" required>
                                    <option value="" selected disabled>-- Time Zone / State (Required) --</option>
                                    <option value="Australia/Sydney" data-state="NSW">New South Wales (NSW) — Australia/Sydney</option>
                                    <option value="Australia/Brisbane" data-state="QLD">Queensland (QLD) — Australia/Brisbane</option>
                                    <option value="Australia/Melbourne" data-state="VIC">Victoria (VIC) — Australia/Melbourne</option>
                                    <option value="Australia/Adelaide" data-state="SA">South Australia (SA) — Australia/Adelaide</option>
                                    <option value="Australia/Perth" data-state="WA">Western Australia (WA) — Australia/Perth</option>
                                    <option value="Australia/Hobart" data-state="TAS">Tasmania (TAS) — Australia/Hobart</option>
                                    <option value="Australia/Darwin" data-state="NT">Northern Territory (NT) — Australia/Darwin</option>
                                    <option value="Australia/Canberra" data-state="ACT">Australian Capital Territory (ACT) — Australia/Canberra</option>
                                </select>
                                <input type="hidden" name="residential_state" id="bidder_residential_state" value="">
                                <div id="state-eligibility-warning" class="text-danger small mt-1" style="display:none;"></div>
                             </div>
                             <div class="mb-3">
                                <select name="hear_us" class="form-select form-select-lg text-muted" required>
                                    <option value="" selected disabled>-- How Did You Hear About Us? (Required) --</option>
                                    <option value="google">Google</option>
                                    <option value="social">Social Media</option>
                                    <option value="friend">Referral</option>
                                    <option value="other">Other</option>
                                </select>
                             </div>
                             <div class="mb-3">
                                 <input type="password" name="password" class="form-control form-control-lg" placeholder="Password (Required)" required>
                             </div>
                             <div class="mb-3">
                                 <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirm Password (Required)" required>
                             </div>

                             <div id="bidder-otp-container" class="mb-3"></div>

                             <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-success btn-lg fw-bold text-white" id="bidder-reg-btn">REGISTER</button>
                             </div>
                              <div class="text-center mt-2">
                                <a href="#" class="text-muted small" onclick="showSelectionView()">&larr; Back</a>
                            </div>
                        </form>
                    </div>

                    <!-- Bidder Register Step 2: Payment & Activation -->
                    <div id="bidder-step-2" style="display:none;">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Bidder Activation: Step 2 of 2</h3>
                            <p class="text-muted">Complete required payment and verification details before placing your first bid.</p>
                        </div>
                        
                        <form id="bidder-payment-form">
                            <h5 class="text-success fw-bold mb-3">Payment Details (Required)</h5>
                            
                            <div class="mb-3">
                                <div id="card-element-number" class="form-control form-control-lg bg-white" style="padding-top: 12px; padding-bottom: 12px;"></div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div id="card-element-expiry" class="form-control form-control-lg bg-white" style="padding-top: 12px; padding-bottom: 12px;"></div>
                                </div>
                                <div class="col-6">
                                     <div id="card-element-cvc" class="form-control form-control-lg bg-white" style="padding-top: 12px; padding-bottom: 12px;"></div>
                                </div>
                            </div>

                            <h5 class="text-success fw-bold mb-3 mt-4">Billing Address</h5>
                            <div class="mb-3">
                                <input type="text" id="billing_address" class="form-control form-control-lg" placeholder="Street Address (Required)" required>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-4">
                                    <input type="text" id="billing_city" class="form-control form-control-lg" placeholder="City" required>
                                </div>
                                <div class="col-4">
                                     <input type="text" id="billing_zip" class="form-control form-control-lg" placeholder="Post Code" required>
                                </div>
                                <div class="col-4">
                                    <input type="text" id="billing_country" class="form-control form-control-lg" value="Australia" readonly>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms_check" required>
                                <label class="form-check-label small" for="terms_check">
                                    I accept the <a href="#" class="text-success">Terms & Conditions</a> (Required)
                                </label>
                            </div>



                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-dark btn-lg fw-bold text-white" id="bidder-activate-btn">ACTIVATE ACCOUNT & SAVE PAYMENT</button>
                            </div>
                             <div class="text-center mt-2">
                                <a href="#" class="text-muted small" onclick="showBidderStepOne()">&larr; Back to Step 1</a>
                                <p class="small text-muted mb-0 mt-1">Step 2 of 2</p>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- 3. FACILITY FORMS -->
                <div id="facility-auth-view" style="display:none;">
                    
                    <!-- Facility Login -->
                    <div id="facility-login-form-container" style="display:none;">
                         <div class="text-center mb-4">
                            <h3 class="fw-bold">Facility Login</h3>
                            <p class="text-muted">Access your facility dashboard.</p>
                        </div>
                        <form id="facility-login-form">
                             <div class="mb-3">
                                <input type="email" name="email" class="form-control form-control-lg bg-light border-0" placeholder="Email Address" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control form-control-lg bg-light border-0" placeholder="Password" required>
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-dark btn-lg fw-bold">Login</button>
                            </div>
                             <div class="text-center">
                                <a href="#" class="text-muted small text-decoration-none __system__user__forgot__password__btn">Forgot Password?</a>
                            </div>
                             <div class="text-center mt-3">
                                <a href="#" class="text-muted small" onclick="showSelectionView()">&larr; Back</a>
                            </div>
                        </form>
                    </div>

                    <!-- Facility Register (Wizard) -->
                    <div id="facility-register-form-container" style="display:none;">
                        
                        <!-- Step 1 -->
                        <div id="facility-step-1">
                             <div class="text-center mb-4">
                                <h3 class="fw-bold">Facility Registration: Step 1 of 2</h3>
                                <p class="text-muted">Create your facility account.</p>
                            </div>
                             <form id="facility-register-form">
                                 <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <input type="text" name="first_name" class="form-control form-control-lg" placeholder="First Name" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="last_name" class="form-control form-control-lg" placeholder="Last Name" required>
                                    </div>
                                 </div>
                                 <div class="mb-3">
                                     <input type="email" name="email" class="form-control form-control-lg" placeholder="Email Address" required>
                                 </div>
                                 <div class="mb-3">
                                     <input type="tel" name="phone" class="form-control form-control-lg" placeholder="Mobile Number" required>
                                 </div>
                                 <div class="mb-3">
                                     <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                                 </div>
                                 <div class="mb-3">
                                     <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirm Password" required>
                                 </div>
                                 <div class="mb-3">
                                    <select name="timezone" class="form-select form-select-lg text-muted" required>
                                        <option value="" selected disabled>-- Select Time Zone --</option>
                                        <option value="Australia/Brisbane">Australia/Brisbane</option>
                                        <option value="Australia/Sydney">Australia/Sydney</option>
                                        <option value="Australia/Melbourne">Australia/Melbourne</option>
                                        <option value="Australia/Perth">Australia/Perth</option>
                                        <option value="Australia/Adelaide">Australia/Adelaide</option>
                                    </select>
                                 </div>

                                 <div id="facility-otp-container" class="mb-3"></div>

                                 <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-success btn-lg fw-bold text-white" id="facility-reg-btn">Continue to Bank Setup</button>
                                 </div>
                                  <div class="text-center mt-2">
                                    <a href="#" class="text-muted small" onclick="showSelectionView()">&larr; Back</a>
                                </div>
                            </form>
                        </div>

                        <!-- Step 2: Bank Account Setup -->
                        <div id="facility-step-2" style="display:none;">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold">Facility Activation: Step 2 of 2</h3>
                                <p class="text-muted">Complete your registration to set up bank account.</p>
                            </div>
                            
                            <form id="facility-payment-form">
                                <!-- Stripe Onboarding Info Box -->
                                <div class="p-4 rounded mb-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border: 1px solid #0f3460;">
                                    <div class="d-flex align-items-center mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#635bff" viewBox="0 0 24 24" class="me-3">
                                            <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                                        </svg>
                                        <h5 class="text-white mb-0 fw-bold">Secure Bank Account Setup</h5>
                                    </div>
                                    <p class="text-light mb-0" style="font-size: 14px;">
                                        <i class="bi bi-shield-check me-2"></i>
                                        After registration, you'll be redirected to Stripe's secure platform to add your bank account details. 
                                        This ensures your financial information is protected with bank-level security and enables verified payouts.
                                    </p>
                                </div>

                                <h5 class="fw-bold mb-3">Billing Address</h5>
                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <label class="form-label small text-muted">Street Address *</label>
                                        <input type="text" id="facility_billing_address" class="form-control form-control-lg" placeholder="Street Address" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">City *</label>
                                        <input type="text" id="facility_billing_city" class="form-control form-control-lg" placeholder="City" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">State *</label>
                                        <input type="text" id="facility_billing_state" class="form-control form-control-lg" placeholder="State" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Post Code *</label>
                                        <input type="text" id="facility_billing_zip" class="form-control form-control-lg" placeholder="Post Code" required>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="facility_accept_terms" required>
                                    <label class="form-check-label small" for="facility_accept_terms">
                                        I accept the <a href="#" class="text-success">Terms & Conditions</a> (Required)
                                    </label>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-success btn-lg fw-bold text-white">COMPLETE REGISTRATION</button>
                                </div>
                                <div class="text-center mt-2">
                                    <a href="#" class="text-muted small" onclick="showFacilityStepOne()">← Back to Step 1</a>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Styles to match the screenshot "Green" */
    #unifiedAuthModal .btn-success {
        background-color: #198754; /* Bootstrap success green or adjust to match image exactly */
        border-color: #198754;
    }
    #unifiedAuthModal .btn-success:hover {
        background-color: #157347;
        border-color: #146c43;
    }
    #unifiedAuthModal .form-control-lg, #unifiedAuthModal .form-select-lg {
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    #unifiedAuthModal .form-control-lg::placeholder {
        color: #adb5bd;
    }
</style>

<script>
// State eligibility + combined timezone/state select handling
document.addEventListener('DOMContentLoaded', function() {
    var timezoneSelect = document.getElementById('bidder_timezone_select');
    var hiddenStateInput = document.getElementById('bidder_residential_state');
    var warning = document.getElementById('state-eligibility-warning');

    function handleStateChange(stateCode) {
        if (!warning) return;
        if (stateCode === 'SA') {
            warning.textContent = 'Residents of South Australia are currently ineligible for the giveaway draw due to local permit requirements.';
            warning.style.display = 'block';
        } else {
            warning.textContent = '';
            warning.style.display = 'none';
        }
    }

    if (timezoneSelect && hiddenStateInput) {
        timezoneSelect.addEventListener('change', function() {
            var selectedOption = timezoneSelect.options[timezoneSelect.selectedIndex];
            var stateCode = selectedOption ? selectedOption.getAttribute('data-state') : '';
            hiddenStateInput.value = stateCode || '';
            handleStateChange(stateCode);
        });
    }
});
</script>

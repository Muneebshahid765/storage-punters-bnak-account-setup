// --- NAVIGATION HELPERS (Global Scope) ---
window.resetAuthModal = function () {
    console.log("Resetting Auth Modal");
    showSelectionView();
};

window.showSelectionView = function () {
    console.log("Showing Selection View");
    $('#auth-selection-view').show();
    $('#bidder-auth-view').hide();
    $('#facility-auth-view').hide();
    // Reset sub-forms
    $('.auth-sub-view').hide(); // Helper class if we add it, or manual:
    $('#bidder-login-form-container').hide();
    $('#bidder-register-form-container').hide();
    $('#facility-login-form-container').hide();
    $('#facility-register-form-container').hide();
    $('#bidder-step-2').hide();
    $('#facility-step-2').hide();
    // Also reset Step 1 forms visibility just in case
    $('#facility-step-1').show();
    $('#bidder-register-form-container').hide(); // This is toggled by showBidderRegister, but good to reset logic
};

window.showFacilityLogin = function () {
    console.log("Showing Facility Login");
    $('#auth-selection-view').hide();
    $('#facility-auth-view').show();
    $('#facility-login-form-container').fadeIn();
    $('#facility-register-form-container').hide();
};

window.showFacilityRegister = function () {
    console.log("Showing Facility Register");
    $('#auth-selection-view').hide();
    $('#facility-auth-view').show();
    $('#facility-login-form-container').hide();
    $('#facility-register-form-container').fadeIn();
};

window.showBidderLogin = function () {
    console.log("Showing Bidder Login");
    $('#auth-selection-view').hide();
    $('#bidder-auth-view').show();
    $('#bidder-login-form-container').fadeIn();
    $('#bidder-register-form-container').hide();
};

window.showBidderRegister = function () {
    console.log("Showing Bidder Register");
    $('#auth-selection-view').hide();
    $('#bidder-auth-view').show();
    $('#bidder-login-form-container').hide();
    $('#bidder-register-form-container').fadeIn();
};

window.togglePassword = function (btn) {
    const input = $(btn).prev('input');
    if (input.attr('type') === "password") {
        input.attr('type', 'text');
        $(btn).html('<i class="bi bi-eye-slash"></i>');
    } else {
        input.attr('type', 'password');
        $(btn).html('<i class="bi bi-eye"></i>');
    }
};

window.showFacilityStepOne = function () {
    console.log("Showing Facility Step 1");
    $('#facility-step-2').hide();
    $('#facility-step-1').fadeIn();
    $('#facility-register-form-container').show();
};

window.showBidderStepOne = function () {
    console.log("Showing Bidder Step 1");
    $('#bidder-step-2').hide();
    $('#bidder-register-form-container').fadeIn();
};


$(document).ready(function () {

    // --- STRIPE INIT ---
    // Public Key from existing stripe.js
    const stripePublicKey = window.APP_CONFIG?.STRIPE_PUBLISHABLE_KEY || '';
    if (!stripePublicKey) {
        console.error('Stripe publishable key missing from APP_CONFIG');
    }
    let stripe = null;
    let elements = null;
    let cardNum = null, cardExp = null, cardCvc = null;

    if (typeof Stripe !== 'undefined') {
        stripe = Stripe(stripePublicKey);
    }

    // --- UI HELPERS ---
    function showSpinner() { $('#spinner-overlay').show(); }
    function hideSpinner() { $('#spinner-overlay').hide(); }
    function showNotification(msg, type = 'success') {
        if (window.showNotification) window.showNotification(msg, type);
        else alert(msg);
    }

    // --- STRIPE ELEMENTS FOR BIDDER (Card payments for bidders only) ---
    window.mountBidderStripeElements = function () {
        if (!stripe) return;
        if (cardNum) return; // Already mounted

        elements = stripe.elements();
        const style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': { color: '#aab7c4' }
            },
            invalid: { color: '#fa755a', iconColor: '#fa755a' }
        };

        cardNum = elements.create('cardNumber', { style: style });
        cardExp = elements.create('cardExpiry', { style: style });
        cardCvc = elements.create('cardCvc', { style: style });

        cardNum.mount('#card-element-number');
        cardExp.mount('#card-element-expiry');
        cardCvc.mount('#card-element-cvc');
    };

    // --- LOGIN HANDLERS ---
    async function handleLogin(email, password, formType) {
        showSpinner();
        try {
            const res = await $.ajax({
                url: SITE_URL + 'auth/login',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ email, password }),
                dataType: 'json'
            });

            if (res.status === true || res.success) {
                showNotification("Login successful!", "success");
                window.location.href = res.redirect_url || SITE_URL;
            } else {
                showNotification(res.message || "Login failed.", "error");
            }
        } catch (err) {
            let msg = "Login error.";
            if (err.responseJSON && err.responseJSON.message) msg = err.responseJSON.message;
            showNotification(msg, "error");
        } finally {
            hideSpinner();
        }
    }

    $('#facility-login-form').submit(function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        handleLogin(data.email, data.password, 'facility');
    });

    $('#bidder-login-form').submit(function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        handleLogin(data.email, data.password, 'bidder');
    });


    // --- FACILITY REGISTRATION ---
    $('#facility-register-form').submit(async function (e) {
        e.preventDefault();
        const pwd = $('#facility-register-form input[name="password"]').val();
        const confirm = $('#facility-register-form input[name="confirm_password"]').val();

        if (pwd.length < 6) return showNotification("Password too short", "error");
        if (pwd !== confirm) return showNotification("Passwords do not match", "error");

        const formData = {
            first_name: $('input[name="first_name"]', this).val(),
            last_name: $('input[name="last_name"]', this).val(),
            email: $('input[name="email"]', this).val(),
            standard_email_address: $('input[name="email"]', this).val(),
            cell_phone: $('input[name="phone"]', this).val(),
            current_password: pwd,
            password_confirmation: confirm,
            timezone: $('select[name="timezone"]', this).val(),
            hear_us: 'other'
        };

        showSpinner();
        try {
            // CALL 1: Prepare Registration (Create Customer + Setup Intent)
            const res = await $.ajax({
                url: SITE_URL + 'api/stripe/prepare-registration',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
            });

            if (res.status === 'success') {
                // Store data for Step 2
                window.tempFacilityCreds = {
                    ...formData,
                    stripe_client_secret: res.client_secret,
                    stripe_customer_id: res.stripe_customer_id
                };

                showNotification("Step 1 Validated. Please add bank details.", "success");

                // Show Step 2
                $('#facility-step-1').hide();
                $('#facility-step-2').fadeIn();

            } else {
                showNotification(res.message || "Validation failed.", "error");
            }

        } catch (err) {
            showNotification("Registration error: " + (err.responseJSON?.message || err.statusText), "error");
        } finally {
            hideSpinner();
        }
    });

    // --- FACILITY BANK ACCOUNT FORM (Step 2) ---
    $('#facility-payment-form').submit(async function (e) {
        e.preventDefault();
        if (!window.tempFacilityCreds) {
            showNotification("Session expired. Please restart registration.", "error");
            return;
        }

        const billingAddress = $('#facility_billing_address').val().trim();
        const billingCity = $('#facility_billing_city').val().trim();
        const billingState = $('#facility_billing_state').val().trim();
        const billingZip = $('#facility_billing_zip').val().trim();
        const termsAccepted = $('#facility_accept_terms').is(':checked');

        if (!billingAddress || !billingCity || !billingState || !billingZip) {
            showNotification("Please fill all billing address fields.", "error");
            return;
        }

        if (!termsAccepted) {
            showNotification("You must accept the Terms & Conditions.", "error");
            return;
        }

        showSpinner();
        try {
            const registrationData = {
                ...window.tempFacilityCreds,
                billing_address: billingAddress,
                billing_city: billingCity,
                billing_state: billingState,
                billing_zip: billingZip
            };

            const regRes = await $.ajax({
                url: SITE_URL + 'api/users/standard-seller/registration',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(registrationData)
            });

            if (regRes.status === 'success') {
                showNotification("Registration complete! Logging you in...", "success");
                
                // Auto-login after registration
                try {
                    const loginRes = await $.ajax({
                        url: SITE_URL + 'auth/login',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            email: window.tempFacilityCreds.standard_email_address,
                            password: window.tempFacilityCreds.current_password
                        }),
                        dataType: 'json'
                    });

                    delete window.tempFacilityCreds;

                    if (loginRes.status === true || loginRes.success) {
                        // Login successful, now redirect
                        if (regRes.stripe_onboarding_url) {
                            window.location.href = regRes.stripe_onboarding_url;
                        } else {
                            window.location.href = loginRes.redirect_url || SITE_URL + 'company/setup/new';
                        }
                    } else {
                        // Login failed, redirect to login page
                        showNotification("Registration successful but auto-login failed. Please login manually.", "warning");
                        setTimeout(() => {
                            window.location.href = SITE_URL;
                        }, 2000);
                    }
                } catch (loginErr) {
                    delete window.tempFacilityCreds;
                    showNotification("Registration successful but auto-login failed. Please login manually.", "warning");
                    setTimeout(() => {
                        window.location.href = SITE_URL;
                    }, 2000);
                }
            } else {
                throw new Error(regRes.message || 'Registration failed');
            }

        } catch (err) {
            showNotification(err.responseJSON?.message || err.message || "Error processing registration.", "error");
        } finally {
            hideSpinner();
        }
    });


    // --- BSB AUTO-FORMATTING FOR FACILITY BANK FORM ---
    $(document).on('input', '#facility_bank_bsb', function () {
        let val = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (val.length > 3) {
            val = val.slice(0, 3) + '-' + val.slice(3, 6);
        }
        $(this).val(val);
    });

    // Account number: only digits
    $(document).on('input', '#facility_bank_account_number', function () {
        const clean = $(this).val().replace(/\D/g, '').slice(0, 9);
        $(this).val(clean);
    });

    // --- BIDDER REGISTRATION ---

    // Step 1: Register User (PART 1: VALIDATE & PREPARE STRIPE)
    $('#bidder-register-form').submit(async function (e) {
        e.preventDefault();
        const pwd = $('#bidder-register-form input[name="password"]').val();
        const confirm = $('#bidder-register-form input[name="confirm_password"]').val();

        if (pwd.length < 6) return showNotification("Password too short", "error");
        if (pwd !== confirm) return showNotification("Passwords do not match", "error");

        const formData = {
            first_name: $('#bidder-register-form input[name="first_name"]').val(),
            last_name: $('#bidder-register-form input[name="last_name"]').val(),
            email: $('#bidder-register-form input[name="email"]').val(), // Changed from standard_email_address for prepare-registration
            standard_email_address: $('#bidder-register-form input[name="email"]').val(), // Keep for final registration
            cell_phone: $('#bidder-register-form input[name="phone"]').val(),
            current_password: pwd,
            password_confirmation: confirm,
            timezone: $('#bidder-register-form select[name="timezone"]').val(),
            hear_us: $('#bidder-register-form select[name="hear_us"]').val(),
            residential_state: $('#bidder-register-form select[name="residential_state"]').val()
        };

        showSpinner();
        try {
            // CALL 1: Prepare Registration (Create Customer + Setup Intent)
            const res = await $.ajax({
                url: SITE_URL + 'api/stripe/prepare-registration',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
            });

            if (res.status === 'success') {
                // Store data for Step 2
                window.tempBidderCreds = {
                    ...formData,
                    stripe_client_secret: res.client_secret,
                    stripe_customer_id: res.stripe_customer_id
                };

                showNotification("Step 1 Validated. Please add payment method.", "success");

                // Show Step 2
                $('#bidder-register-form-container').hide();
                $('#bidder-step-2').fadeIn();
                mountBidderStripeElements();

            } else {
                showNotification(res.message || "Validation failed.", "error");
            }
        } catch (err) {
            showNotification(err.responseJSON?.message || "Error validating details.", "error");
        } finally {
            hideSpinner();
        }
    });

    // Step 2: Payment (PART 2: CONFIRM & REGISTER)
    $('#bidder-payment-form').submit(async function (e) {
        e.preventDefault();
        if (!stripe || !cardNum) return;
        if (!window.tempBidderCreds || !window.tempBidderCreds.stripe_client_secret) {
            showNotification("Session expired. Please restart registration.", "error");
            return;
        }

        showSpinner();
        try {
            const clientSecret = window.tempBidderCreds.stripe_client_secret;
            const customerId = window.tempBidderCreds.stripe_customer_id;

            // 1. Confirm Card Setup
            let setupIntent, error;
            const confirmResult = await stripe.confirmCardSetup(clientSecret, {
                payment_method: {
                    card: cardNum,
                    billing_details: {
                        name: window.tempBidderCreds.first_name + ' ' + window.tempBidderCreds.last_name,
                        email: window.tempBidderCreds.standard_email_address,
                        address: {
                            line1: $('#billing_address').val(),
                            city: $('#billing_city').val(),
                            postal_code: $('#billing_zip').val(),
                            country: 'AU' // Fixed/Default for now as per previous code
                        }
                    }
                }
            });

            setupIntent = confirmResult.setupIntent;
            error = confirmResult.error;

            if (error) {
                // If SetupIntent already succeeded, retrieve it instead of confirming again
                if (error.code === 'setup_intent_unexpected_state') {
                    const retrievedIntent = await stripe.retrieveSetupIntent(clientSecret);
                    if (retrievedIntent.setupIntent && retrievedIntent.setupIntent.status === 'succeeded') {
                        // Use the already-confirmed payment method
                        setupIntent = retrievedIntent.setupIntent;
                        error = null; // Clear the error
                    } else {
                        throw new Error(error.message);
                    }
                } else {
                    throw new Error(error.message);
                }
            }

            // 2. Register User (ATOMICALLY)
            const registrationData = {
                ...window.tempBidderCreds,
                stripe_customer_id: customerId,
                payment_method_id: setupIntent.payment_method,
                address: $('#billing_address').val(),
                city: $('#billing_city').val(),
                zip: $('#billing_zip').val(),
                state: $('#billing_state').val(),
                country: 'Australia' // Defaulting to Aus based on context
            };

            const regRes = await $.ajax({
                url: SITE_URL + 'api/users/standard-bidder/registration',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(registrationData),
            });

            if (regRes.status === 'success') {
                showNotification("Registration complete! Logging in...", "success");

                // 3. Auto Login
                const loginRes = await $.ajax({
                    url: SITE_URL + 'auth/login',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        email: registrationData.standard_email_address,
                        password: registrationData.current_password
                    }),
                });

                if (loginRes.status === true || loginRes.success) {
                    window.location.href = loginRes.redirect_url || SITE_URL + 'show-dashboard/payment-method';
                } else {
                    window.location.href = SITE_URL; // Fallback
                }

            } else {
                throw new Error(regRes.message);
            }

        } catch (err) {
            console.error("Signup Submission Error:", err);
            showNotification(err.responseJSON?.message || err.message || "Error processing payment/registration.", "error");
        } finally {
            hideSpinner();
        }
    });

});

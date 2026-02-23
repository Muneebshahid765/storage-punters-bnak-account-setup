$(document).ready(function () {
    const $form = $('#bidder-signup-form');
    const $spinner = $('#spinner-overlay');
    const $actionBtn = $('#action-button');
    const $otpContainer = $('#otp-dynamic-container');

    let otpVerified = false;
    let canResendOtp = false;
    let resendTimer = null;

    function showSpinner() { $spinner.show(); }
    function hideSpinner() { $spinner.hide(); }

    function getFormData() {
        return {
            first_name: $('#first_name').val().trim(),
            last_name: $('#last_name').val().trim(),
            standard_email_address: $('#standard_email_address').val().trim(),
            cell_phone: $('#cell_phone_normalized').val().trim() || $('#cell_phone').val().trim(),
            cell_phone_display: $('#cell_phone').val().trim(),
            current_password: $('#current_password').val(),
            password_confirmation: $('#password_confirmation').val(),
            timezone: $('#timezone').val(),
            hear_us: $('#hear_us').val()
        };
    }

    function normalizeAustralianPhone(input) {
        if (!input) {
            return { valid: false, message: 'Mobile number is required.' };
        }

        let value = input.trim()
            .replace(/\s+/g, '')
            .replace(/(?!^)\+/g, '') // allow only leading +
            .replace(/[^0-9+]/g, '');

        if (value.startsWith('+')) {
            if (!value.startsWith('+61')) {
                return { valid: false, message: 'Australian numbers must start with +61.' };
            }
            if (!/^\+614\d{8}$/.test(value)) {
                return { valid: false, message: 'Australian mobile must be +614 followed by 8 digits.' };
            }
            return {
                valid: true,
                e164: value,
                formatted: `+61 ${value.slice(4, 7)} ${value.slice(7, 10)} ${value.slice(10)}`
            };
        }

        value = value.replace(/[^0-9]/g, '');

        if (value.startsWith('04') && value.length === 10) {
            const formatted = value.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');
            return {
                valid: true,
                e164: '+61' + value.slice(1),
                formatted
            };
        }

        if (value.startsWith('614') && value.length === 11) {
            const local = value.slice(2); // drop country code digits
            return {
                valid: true,
                e164: '+61' + local,
                formatted: `+61 ${local.slice(0, 3)} ${local.slice(3, 6)} ${local.slice(6)}`
            };
        }

        return {
            valid: false,
            message: 'Use Australian mobile format (04XXXXXXXX or +614XXXXXXXX).'
        };
    }

    $('#cell_phone').on('input', function () {
        let value = $(this).val();
        value = value.replace(/(?!^)\+/g, '').replace(/[^0-9+\s]/g, '');
        $(this).val(value);
    }).on('blur', function () {
        const result = normalizeAustralianPhone($(this).val());
        if (result.valid) {
            $('#cell_phone_normalized').val(result.e164);
            $(this).val(result.formatted);
        } else {
            $('#cell_phone_normalized').val('');
        }
    });

    function validateForm(data) {
        $('.error-message').remove();
        let valid = true;

        // Required fields
        const required = ['first_name', 'last_name', 'standard_email_address', 'cell_phone', 'current_password', 'password_confirmation', 'timezone'];
        required.forEach(field => {
            if (!data[field]) {
                $(`#${field}`).after(`<p class="error-message text-yellow text-error">This field is required.</p>`);
                valid = false;
            }
        });

        // Name validation
        const nameRegex = /^[A-Za-z\s'-]{2,50}$/; // allows letters, spaces, hyphen, apostrophe, min 2 chars
        if (data.first_name && !nameRegex.test(data.first_name)) {
            $('#first_name').after(`<p class="error-message text-yellow text-error">Enter a valid first name.</p>`);
            valid = false;
        }
        if (data.last_name && !nameRegex.test(data.last_name)) {
            $('#last_name').after(`<p class="error-message text-yellow text-error">Enter a valid last name.</p>`);
            valid = false;
        }

        // Password validation
        if (data.current_password) {
            if (data.current_password.length < 6) {
                $('#current_password').after(`<p class="error-message text-yellow text-error">Password must be at least 6 characters.</p>`);
                valid = false;
            } else if (data.current_password !== data.password_confirmation) {
                // only check mismatch if length is ok
                $('#password_confirmation').after(`<p class="error-message text-yellow text-error">Passwords do not match.</p>`);
                valid = false;
            }
        }

        // Email validation
        if (data.standard_email_address) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(data.standard_email_address)) {
                $('#standard_email_address').after(`<p class="error-message text-yellow text-error">Invalid email format.</p>`);
                valid = false;
            }
        }

        // Phone validation
        if (data.cell_phone) {
            const phoneResult = normalizeAustralianPhone(data.cell_phone);
            if (!phoneResult.valid) {
                $('#cell_phone').after(`<p class="error-message text-yellow text-error">${phoneResult.message}</p>`);
                valid = false;
            } else {
                $('#cell_phone_normalized').val(phoneResult.e164);
                data.cell_phone = phoneResult.e164;
            }
        }

        return valid;
    }


    function startResendTimer() {
        let countdown = 120;
        canResendOtp = false;
        updateActionButton("Verify Email OTP", verifyOtpHandler);
        const $timer = $('<div id="otp-timer" class="mt-2 text-muted">Resend available in 120s</div>');
        $otpContainer.append($timer);

        resendTimer = setInterval(() => {
            countdown--;
            $timer.text(`Resend available in ${countdown}s`);
            if (countdown <= 0) {
                clearInterval(resendTimer);
                canResendOtp = true;
                updateActionButton("Resend OTP", resendOtpHandler);
                $timer.text('');
            }
        }, 1000);
    }

    function updateActionButton(text, clickHandler) {
        $actionBtn
            .text(text)
            .off('click')
            .removeClass('disabled')
            .on('click', clickHandler);
    }


    $form.on('submit', function (e) {
        e.preventDefault(); // 🔒 prevent default HTML form submit
    });

    async function sendOtp(email, toName) {
        showSpinner();
        try {
            const res = await $.ajax({
                url: SITE_URL + 'api/users/send-otp',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ email, toName }),
                dataType: 'json'
            });

            if (res.success) {
                showNotification("OTP sent to your email.", 'success');

                $otpContainer.html(`
                    <div class="form-group mt-3">
                        <input type="text"
                               id="otp_code"
                               class="input-secondary text-center"
                               placeholder="Enter the 6-digit code sent to your email"
                               maxlength="6"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               autocomplete="off"
                               onkeypress="if(event.key.length === 1 && !/\\d/.test(event.key)) return false;"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,6);"
                        />
                    </div>
                `);


                startResendTimer();
                return true;
            } else {
                showNotification(res.message || "OTP send failed.", 'error');
                return false;
            }
        } catch {
            showNotification("Failed to send OTP.", 'error');
            return false;
        } finally {
            hideSpinner();
        }
    }

    async function verifyOtpHandler() {
        const data = getFormData();
        const otp = $('#otp_code').val().trim();
        if (!otp || otp.length !== 6) {
            return showNotification("Enter a valid 6-digit OTP.", "error");
        }

        showSpinner();
        try {
            const res = await $.ajax({
                url: SITE_URL + 'api/users/verify-otp',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ email: data.standard_email_address, otp }),
                dataType: 'json'
            });

            if (res.success) {
                otpVerified = true;
                clearInterval(resendTimer);
                showNotification("OTP verified successfully!", 'success');
                $otpContainer.html('<div class="text-success mt-3 font-weight-bold">OTP Verified.</div>');
                updateActionButton("Register Now", registerHandler);
            } else {
                showNotification(res.message || "Incorrect OTP.", 'error');
            }
        } catch {
            showNotification("Verification failed.", "error");
        } finally {
            hideSpinner();
        }
    }

    async function resendOtpHandler() {
        const data = getFormData();
        await sendOtp(data.standard_email_address, `${data.first_name} ${data.last_name}`);
    }

    async function registerHandler() {
        const data = getFormData();
        showSpinner();
        try {
            const res = await $.ajax({
                url: SITE_URL + 'api/users/standard-bidder/registration',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });

            if (res.success || res.status === 'success') {

                $form[0].reset();
                $otpContainer.empty();
                otpVerified = false;
                canResendOtp = false;
                updateActionButton("Register", registerStepOneHandler);

                showNotification("Registration complete!", 'success');

                // window.location.href = SITE_URL + 'login';


                const res2 = await $.ajax({
                    url: SITE_URL + 'api/users/send-welcome-email-to-bidder',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ email: data.standard_email_address, toName: `${data.first_name} ${data.last_name}` }),
                    dataType: 'json'
                });

                if (res2.success || res2.status === 'success') {

                    showNotification(res2.message, 'success');

                    const encodedEmail = encodeURIComponent(data.standard_email_address);
                    // window.location.href = SITE_URL + 'registration-success?email=' + encodedEmail;

                    window.location.href = SITE_URL + 'show-dashboard/payment-method';
                }

            } else {
                showNotification(res.message || "Registration failed.", "error");
            }
        } catch {
            showNotification("Registration error.", "error");
        } finally {
            hideSpinner();
        }
    }

    async function registerStepOneHandler() {
        const data = getFormData();
        if (!validateForm(data)) return;
        const success = await sendOtp(data.standard_email_address, `${data.first_name} ${data.last_name}`);
        if (success) {
            updateActionButton("Verify Email OTP", verifyOtpHandler);
        }
    }

    // Initialize
    updateActionButton("Register", registerStepOneHandler);

    // Input: allow only digits, no messages
    $(document).on('input', '#otp_code', function () {
        const clean = $(this).val().replace(/\D/g, '').slice(0, 6);
        $(this).val(clean);
    });

    // Paste: full validation with messages
    $(document).on('paste', '#otp_code', function (e) {
        const clipboardData = e.originalEvent.clipboardData || window.clipboardData;
        const pasted = clipboardData.getData('text');

        // Check length first
        if (pasted.length !== 6) {
            e.preventDefault();
            showNotification("OTP must be exactly 6 digits.", "error");
            return;
        }

        // Check if it's all digits
        if (!/^\d{6}$/.test(pasted)) {
            e.preventDefault();
            showNotification("OTP must contain only numeric digits (0-9).", "error");
            return;
        }

        // Valid OTP
        $('#otp_code').val(pasted);
        showNotification("OTP accepted", "success");
    });





    setDobField(18); // Set DOB field for 18+ age
});

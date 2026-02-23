<?php
$prefilled_phone = isset($prefilled_phone) ? trim($prefilled_phone) : '';
$display_phone = $prefilled_phone;
$normalized_phone = '';

if (!empty($prefilled_phone)) {
    // Convert +61 numbers back to local format for display (match signup experience)
    if (strpos($prefilled_phone, '+61') === 0) {
        $display_phone = '0' . substr($prefilled_phone, 3);
        $normalized_phone = $prefilled_phone;
    } else {
        // Normalize to Australian +61 format for backend
        $clean = preg_replace('/[^0-9]/', '', $prefilled_phone);
        if (strpos($clean, '0') === 0) {
            $normalized_phone = '+61' . substr($clean, 1);
        } elseif (strpos($prefilled_phone, '+') === 0) {
            $normalized_phone = $prefilled_phone;
        } else {
            $normalized_phone = '+61' . $clean;
        }
    }
} else {
    $normalized_phone = '';
}
?>

<!-- Phone Verification Modal -->
<div class="modal fade" id="phoneVerificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #1a1e2e; color: #fff; border: 1px solid rgba(255,255,255,0.1);">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-phone-fill text-warning me-2"></i>
                    Verify Your Phone Number
                </h5>
            </div>
            <div class="modal-body">
                <div id="phone-step-1">
                    <p class="text-secondary mb-3">To access your dashboard and place bids, please verify your phone number.</p>
                    
                    <div class="mb-3">
                        <label class="form-label small text-uppercase fw-bold">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark text-white border-secondary">
                                🇦🇺 +61
                            </span>
                            <input type="tel" class="form-control bg-dark text-white border-secondary" 
                                   id="verify-phone-input" placeholder="412 345 678" maxlength="10"
                                   value="<?= htmlspecialchars($display_phone, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-normalized="<?= htmlspecialchars($normalized_phone, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <small class="text-secondary">Enter your Australian mobile number</small>
                    </div>

                    <button type="button" class="btn btn-warning w-100 fw-bold" id="send-otp-btn" onclick="sendVerificationOTP()">
                        <i class="bi bi-send-fill me-2"></i>Send Verification Code
                    </button>
                </div>

                <div id="phone-step-2" style="display: none;">
                    <div class="alert alert-success border-0 mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Code sent to <strong id="phone-display"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-uppercase fw-bold">Enter 6-Digit Code</label>
                        <input type="text" class="form-control form-control-lg text-center bg-dark text-white border-secondary" 
                               id="otp-code-input" placeholder="000000" maxlength="6" 
                               style="letter-spacing: 8px; font-size: 1.5rem; font-weight: 700;">
                        <small class="text-secondary">Code expires in 10 minutes</small>
                    </div>

                    <div id="dev-code-display" class="alert alert-info border-0 mb-3" style="display: none;">
                        <strong>Development Mode:</strong> Use code <span id="dev-code" class="fw-bold fs-4"></span>
                    </div>

                    <button type="button" class="btn btn-success w-100 fw-bold" id="verify-otp-btn" onclick="verifyOTP()">
                        <i class="bi bi-shield-check me-2"></i>Verify Code
                    </button>
                </div>

                <div id="phone-error" class="alert alert-danger border-0 mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
var phoneInputEl = document.getElementById('verify-phone-input');
var currentPhone = phoneInputEl ? (phoneInputEl.dataset.normalized || '') : '';

function normalizeAuPhone(raw) {
    if (!raw) return '';
    let digits = String(raw).replace(/\D+/g, '');
    if (digits.startsWith('61')) {
        digits = '0' + digits.slice(2);
    }
    if (digits.startsWith('0')) {
        digits = digits.slice(1);
    }
    if (!/^4\d{8}$/.test(digits)) return '';
    return '+61' + digits;
}

function getCurrentPhoneFromInput() {
    if (!phoneInputEl) return currentPhone;
    const normalized = normalizeAuPhone(phoneInputEl.value);
    if (normalized) {
        currentPhone = normalized;
    }
    return currentPhone;
}

function maskPhone(phone) {
    const digits = String(phone || '').replace(/\D+/g, '');
    if (digits.length < 6) return phone || '';
    const prefix = digits.slice(0, 4);
    const suffix = digits.slice(-3);
    return '+' + prefix + '****' + suffix;
}

if (phoneInputEl) {
    phoneInputEl.addEventListener('input', function() {
        const clean = this.value.replace(/\D+/g, '').slice(0, 10);
        this.value = clean;
        const normalized = normalizeAuPhone(clean);
        if (normalized) {
            this.dataset.normalized = normalized;
            currentPhone = normalized;
        }
    });
}

function sendVerificationOTP() {
    const errorDiv = document.getElementById('phone-error');
    const sendBtn = document.getElementById('send-otp-btn');
    let phone = getCurrentPhoneFromInput();

    if (!phone) {
        errorDiv.textContent = 'Phone number unavailable. Please contact support.';
        errorDiv.style.display = 'block';
        return;
    }

    errorDiv.style.display = 'none';
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

    fetch(SITE_URL + 'api/sms/send-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: phone })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            currentPhone = phone;
            document.getElementById('phone-display').textContent = maskPhone(phone);
            document.getElementById('phone-step-1').style.display = 'none';
            document.getElementById('phone-step-2').style.display = 'block';
            
            // Show dev code if available
            if (data.data.dev_code) {
                document.getElementById('dev-code').textContent = data.data.dev_code;
                document.getElementById('dev-code-display').style.display = 'block';
            }
        } else {
            errorDiv.textContent = data.message || 'Failed to send verification code';
            errorDiv.style.display = 'block';
        }
    })
    .catch(err => {
        errorDiv.textContent = 'Network error. Please try again.';
        errorDiv.style.display = 'block';
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Send Verification Code';
    });
}

function verifyOTP() {
    const codeInput = document.getElementById('otp-code-input');
    const errorDiv = document.getElementById('phone-error');
    const verifyBtn = document.getElementById('verify-otp-btn');
    
    const code = codeInput.value.trim();
    
    if (!code || code.length !== 6) {
        errorDiv.textContent = 'Please enter the 6-digit code';
        errorDiv.style.display = 'block';
        return;
    }

    errorDiv.style.display = 'none';
    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';

    const phone = getCurrentPhoneFromInput();
    if (!phone) {
        errorDiv.textContent = 'Please enter a valid Australian mobile number.';
        errorDiv.style.display = 'block';
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = '<i class="bi bi-shield-check me-2"></i>Verify Code';
        return;
    }

    fetch(SITE_URL + 'api/sms/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: phone, code: code })
    })
    .then(async (res) => {
        const data = await res.json().catch(() => null);
        return { ok: res.ok, data };
    })
    .then(({ ok, data }) => {
        if (ok && data && data.success) {
            return updateUserPhoneVerification(phone);
        }
        const msg = (data && data.message) ? data.message : 'Invalid verification code';
        errorDiv.textContent = msg;
        errorDiv.style.display = 'block';
    })
    .catch(() => {
        errorDiv.textContent = 'Network error. Please try again.';
        errorDiv.style.display = 'block';
    })
    .finally(() => {
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = '<i class="bi bi-shield-check me-2"></i>Verify Code';
    });
}

function updateUserPhoneVerification(phone) {
    const errorDiv = document.getElementById('phone-error');
    return fetch(SITE_URL + 'api/users/update-phone-verification', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: phone })
    })
    .then(async (res) => {
        const data = await res.json().catch(() => null);
        if (!res.ok) {
            const msg = (data && data.message) ? data.message : 'Failed to update verification status.';
            throw new Error(msg);
        }
        return data;
    })
    .then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Phone Verified!',
            text: 'Your phone number has been verified successfully.',
            confirmButtonColor: '#00a65a'
        }).then(() => {
            const modalEl = document.getElementById('phoneVerificationModal');
            if (modalEl && window.bootstrap && bootstrap.Modal) {
                const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                instance.hide();
            }

            const overlay = document.querySelector('.phone-verification-overlay');
            if (overlay) overlay.remove();

            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');

            window.location.reload();
        });
    })
    .catch((err) => {
        if (errorDiv) {
            errorDiv.textContent = err.message || 'Failed to update verification status.';
            errorDiv.style.display = 'block';
        }
        throw err;
    });
}

function backToPhoneStep() {
    document.getElementById('phone-step-2').style.display = 'none';
    document.getElementById('phone-step-1').style.display = 'block';
    document.getElementById('otp-code-input').value = '';
    document.getElementById('phone-error').style.display = 'none';
}

// Auto-focus on OTP input when shown
document.getElementById('otp-code-input').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length === 6) {
        verifyOTP();
    }
});
</script>

<?php
/**
 * Phone Verification Gate
 * Shows blocking screen for facility/staff users until phone is verified.
 */
$userSessionData = $this->session->userdata('auction_user_session_data');
$displayName = trim(($userSessionData['first_name'] ?? '') . ' ' . ($userSessionData['last_name'] ?? ''));
?>

<section class="container py-5" style="max-width: 720px;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="mb-4">
                <span class="badge bg-warning text-dark px-3 py-2 fw-semibold">Security Check Required</span>
            </div>
            <h2 class="fw-bolder mb-3">Phone Verification Needed</h2>
            <p class="text-muted mb-4">
                Hi <strong><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></strong>, for security reasons we need to verify your
                phone number before you can continue using the facility dashboard. Please complete the OTP verification below.
            </p>

            <div class="text-start mx-auto" style="max-width: 420px;">
                <?php
                $this->load->view('auth/phone_verification_modal', [
                    'prefilled_phone' => $user_phone ?? ''
                ]);
                ?>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var phoneModalEl = document.getElementById('phoneVerificationModal');
        if (phoneModalEl) {
            var instance = new bootstrap.Modal(phoneModalEl, {backdrop: 'static', keyboard: false});
            instance.show();
        }
    });
</script>

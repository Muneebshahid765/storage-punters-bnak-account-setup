<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
$firstName = isset($userSessionData['firstname']) ? $userSessionData['firstname'] : 'User';
?>

<?php $this->load->view('dashboard/bidder/layout/header_account'); ?>

<style>
    .phone-lock-wrapper {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .phone-lock-card {
        width: 100%;
        max-width: 540px;
        border-radius: 20px;
        background: #0f172a;
        color: #f8fafc;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.45);
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
        z-index: 1041;
    }

    .phone-lock-card h2 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .phone-lock-card p {
        color: #cbd5f5;
        line-height: 1.6;
        margin-bottom: 1.25rem;
    }

    .phone-lock-badges {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .phone-lock-badge {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 999px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
    }

    .phone-lock-card .btn-primary {
        background: linear-gradient(135deg, #f7b733, #ff8800);
        border: none;
        font-weight: 600;
        width: 100%;
        padding: 0.85rem;
        border-radius: 999px;
    }

    .phone-lock-card small {
        display: block;
        margin-top: 1rem;
        color: rgba(255, 255, 255, 0.7);
    }
</style>

<div class="phone-lock-wrapper">
    <div class="phone-lock-card">
        <div class="mb-3">
            <i class="bi bi-shield-lock-fill" style="font-size: 2.5rem;"></i>
        </div>
        <h2>Security Check Required</h2>
        <p>Hi <?= htmlspecialchars($firstName); ?>, before we show your dashboard we just need to verify your mobile number. This quick step keeps your account and bidding history safe.</p>

        <div class="phone-lock-badges">
            <span class="phone-lock-badge">Encrypted Verification</span>
            <span class="phone-lock-badge">One-time setup</span>
            <span class="phone-lock-badge">Keeps bids protected</span>
        </div>

        <button class="btn btn-primary" type="button" onclick="triggerPhoneVerificationModal()">
            Verify Phone Number
        </button>

        <small>Have questions? Reach out to support and we’ll help instantly.</small>
    </div>
</div>

<?php
echo '<div class="phone-verification-overlay"></div>';
$this->load->view('auth/phone_verification_modal', [
    'prefilled_phone' => $user_phone ?? ''
]);
?>

<script>
function triggerPhoneVerificationModal() {
    var phoneModal = new bootstrap.Modal(document.getElementById('phoneVerificationModal'));
    phoneModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('phone-verification-locked');
    var modalEl = document.getElementById('phoneVerificationModal');
    var phoneModal = new bootstrap.Modal(modalEl);

    modalEl.addEventListener('hidden.bs.modal', function(){
        document.body.classList.remove('phone-verification-locked');
        var overlay = document.querySelector('.phone-verification-overlay');
        if (overlay) overlay.remove();
    });

    phoneModal.show();
});
</script>

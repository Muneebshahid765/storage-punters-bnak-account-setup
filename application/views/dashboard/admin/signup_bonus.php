<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern admin-dark-theme">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/admin/sidebar'); ?>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div>
                <h1 class="dash-title-modern">Bidder Sign-Up Bonus Management</h1>
                <p class="text-muted small mb-0">Control the initial credit amount and terms offered to new bidders upon registration.</p>
            </div>
        </header>

        <section class="content-section-modern">
            <div class="settings-card-modern" style="max-width: 600px;">
                <h2 class="section-title-modern">Default Sign-up Bonus Setup</h2>
                <form id="signup-bonus-form">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                         <div>
                             <h4 class="mb-1">Enable Sign-up Bonus</h4>
                             <p class="text-muted small mb-0">Automatically credit new bidders with the set bonus amount upon successful registration.</p>
                         </div>
                         <div class="form-check form-switch">
                             <input class="form-check-input" type="checkbox" id="bonus-toggle" name="enabled" value="1" style="width: 3rem; height: 1.5rem;">
                         </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Default Sign-up Amount (AUD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="modern-form-control" name="amount" id="bonus-amount" placeholder="40" step="0.01" required>
                        </div>
                        <p class="text-muted small mt-1">This amount is automatically credited to the new bidder's wallet as bonus credit.</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Default Sign-up Coins</label>
                        <div class="input-group">
                            <span class="input-group-text">🪙</span>
                            <input type="number" class="modern-form-control" name="signup_coins" id="signup-coins" placeholder="100" required>
                        </div>
                        <p class="text-muted small mt-1">Coins awarded to new bidders for vault entries and gamification features.</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Bonus Expiration Duration (Days)</label>
                        <div class="input-group">
                            <input type="number" class="modern-form-control" name="expiration_days" id="bonus-expiration" placeholder="90" required>
                            <span class="input-group-text">Days</span>
                        </div>
                        <p class="text-muted small mt-1">Credit expires after this many days if the bidder has not used it for a bid.</p>
                    </div>

                    <button type="submit" class="btn btn-success px-4 py-2">Save Sign-Up Bonus Settings</button>
                    <p class="text-muted small mt-3">All changes must be saved before they become active on the platform.</p>
                </form>
            </div>
        </section>
    </main>
</div>

<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>

<!-- Load Header Partial -->
<?php $this->load->view('dashboard/bidder/layout/header_account'); ?>

<div class="container">
    <div class="dashboard-card">
        <h3 class="card-title-custom">Notifications</h3>

        <section class="content-section-modern">
            <h5 class="fw-bold text-dark mb-3">Manage Notifications and Alerts</h5>
            
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted d-block">Audio Settings</label>
                <p class="small text-muted mb-2">Have sound enabled by default on auctions</p>
                <select name="audio_settings" id="filter" class="form-select w-auto" style="max-width: 300px;">
                    <option value="Enabled - Auctioneer Dan">Enabled - Auctioneer Dan</option>
                    <option value="Disabled">Disabled</option>
                </select>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-hover align-middle notifications-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-3">Auction Activity</th>
                            <th class="py-3">Preference Type</th>
                            <th class="py-3 text-center">Yes</th>
                            <th class="py-3 text-center">No</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows injected via JS -->
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

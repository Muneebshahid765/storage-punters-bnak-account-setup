<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>

<!-- Load Header Partial -->
<?php $this->load->view('dashboard/bidder/layout/header_account'); ?>

<div class="container">
    <div class="dashboard-card">
        <h3 class="card-title-custom">My Subscription</h3>

        <section class="content-section-modern">
            <div class="membership-container"></div>
        </section>
    </div>
</div>

<!-- Membership Modal -->
<div class="modal fade" id="membershipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #f1f0f0 !important;">
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h3 class="fw-bold mb-2 mt-2">Choose your Punters Package</h3>
                    <p class="text-muted mb-0">
                        Unlock premium bidding benefits, reduce fees, and enjoy exclusive perks.
                        Select a plan that suits your needs — <strong>monthly flexibility</strong> or
                        <strong>yearly savings</strong>.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <!-- Toggle switch -->
                <div class="text-center mb-3">
                    <div class="d-flex justify-content-center align-items-center gap-3">

                        <!-- Switch -->
                        <button id="intervalToggle" type="button"
                            class="btn btn-lg btn-toggle active"
                            data-toggle="button" aria-pressed="true" autocomplete="off">
                            <div class="handle"></div>
                        </button>

                    </div>
                </div>

                <!-- Plans will render here -->
                <div id="membership-modal-body"></div>

            </div>
        </div>
    </div>
</div>

<!-- Cancel Membership Confirm Modal -->
<div class="modal fade" id="cancelMembershipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Confirm Cancellation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                <p class="mt-3">
                    Are you sure you want to <strong>cancel your membership</strong>?<br>
                    You will keep your benefits until the end of the current billing period.
                </p>
            </div>

            <div class="modal-footer">
                <div class="row w-100 g-2">

                    <div class="col-6">
                        <button type="button" class="btn btn-yellow text-white w-100 fw-bold" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left-circle me-2"></i> Keep Membership
                        </button>
                    </div>

                    <div class="col-6">
                        <button id="confirm-cancel-membership" type="button" class="btn btn-danger w-100 fw-bold">
                            <i class="bi bi-x-circle me-2"></i> Yes, Cancel
                        </button>
                    </div>

                </div>
            </div>



        </div>
    </div>
</div>

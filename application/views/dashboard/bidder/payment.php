<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>

<!-- Load Header Partial -->
<?php $this->load->view('dashboard/bidder/layout/header_account'); ?>

<div class="container">
    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="card-title-custom mb-0">Payment Methods</h3>
            <button type="button" class="__btn__add__new__payment__method btn text-white px-4 py-2" style="background-color: #0d3b2e; border-radius: 8px;">
                <i class="bi bi-credit-card me-2"></i> Add New Card
            </button>
        </div>

        <section class="content-section-modern p-0 overflow-hidden">
            <div class="__table__payment__method">
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table id="payment-method-table" class="table table-hover align-middle text-nowrap w-100">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4">Card #</th>
                                <th class="py-3">Name</th>
                                <th class="py-3">Ending In</th>
                                <th class="py-3">Type</th>
                                <th class="py-3">Exp</th>
                                <th class="py-3">Default</th>
                                <th class="py-3">Attach/Detach</th>

                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Add Card Modal -->
<div class="modal fade" id="addCardModal" tabindex="-1" aria-labelledby="addCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addCardModalLabel">Add New Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentMethodAddForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Card Holder Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter name on card" value="<?php echo $userSessionData['first_name'] . ' ' . $userSessionData['last_name']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email_address3" name="email_address3" value="<?php echo $userSessionData['email']; ?>" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter phone number">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Billing Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Enter billing address">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Post Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="zip" name="zip" placeholder="Enter post code">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                             <label class="form-label fw-bold">Card Number <span class="text-danger">*</span></label>
                             <div id="card_number" class="form-control" style="padding: 10px;"></div>
                             <div id="card_number_error" class="text-danger small mt-1"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                             <label class="form-label fw-bold">Expiration <span class="text-danger">*</span></label>
                             <div id="card_expiry" class="form-control" style="padding: 10px;"></div>
                             <div id="card_expiry_error" class="text-danger small mt-1"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                             <label class="form-label fw-bold">CVC <span class="text-danger">*</span></label>
                             <div id="card_security_code" class="form-control" style="padding: 10px;"></div>
                             <div id="card_cvc_error" class="text-danger small mt-1"></div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-lg btn-add-new-payment-method text-white" style="background-color: #0d3b2e;">
                            <i class="bi bi-shield-lock-fill me-2"></i> Save Securely
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

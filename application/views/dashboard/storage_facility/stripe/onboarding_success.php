<div class="p-0 d-flex flex-column h-100 container-fluid">

    <main style="flex-grow:1">
        <div class="customContainer mt-4 mb-4 decline-link  container-fluid">
            <div class="row">
                <div class="container-fluid">
                    <div class="">
                        <div class="">


                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                    </div>

                                    <h3 class="fw-bold mb-3">Onboarding Complete!</h3>
                                    <p class="text-muted mb-4">
                                        Your Stripe account is now connected and ready for payouts.
                                    </p>

                                    <div class="border rounded p-3 text-start bg-light mb-4">
                                        <p class="mb-2"><strong>Business:</strong> <?= htmlspecialchars($business_name) ?></p>
                                        <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($account_email) ?></p>
                                        <p class="mb-2"><strong>Type:</strong> <?= htmlspecialchars($account_type) ?></p>
                                        <p class="mb-0">
                                            <strong>Status:</strong>
                                            <?= $details_submitted ? '<span class="text-success">✅ Complete</span>' : '<span class="text-danger">⚙️ Pending</span>' ?>
                                        </p>
                                    </div>

                                    <a href="<?= base_url('bank/setup/new') ?>"
                                        class="__btn__go__to__dashboard w-100 btn-yellow btn-contact px-md-4 d-flex align-items-center justify-content-center gap-1 btn btn-sm"
                                        style="border-radius: 12px; border: medium; font-weight: 500; color:white !important;">
                                        <span>
                                            <i class="bi bi-speedometer2" style="font-size: 20px;"></i>
                                        </span>
                                        <span class="__btn__go__to__dashboard__text">Go to Dashboard</span>
                                    </a>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
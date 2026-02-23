<div class="p-0 d-flex flex-column h-100 container-fluid">
    <main style="flex-grow:1">
        <div class="customContainer mt-4 mb-4 decline-link container-fluid">
            <div class="row">
                <div class="container-fluid">
                    <div class="">
                        <div class="">

                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>

                                    <h3 class="fw-bold mb-3">Redirecting to Stripe...</h3>
                                    <p class="text-muted mb-4">
                                        Please wait while we reopen your onboarding session.
                                    </p>

                                    <div class="border rounded p-3 text-start bg-light mb-4">
                                        <p class="mb-0">
                                            <strong>Note:</strong> You’ll be redirected automatically in a moment.
                                        </p>
                                    </div>

                                    <a href="<?= base_url('bank/setup/new') ?>"
                                        class="__btn__go__to__dashboard w-100 btn-yellow btn-contact px-md-4 d-flex align-items-center justify-content-center gap-1 btn btn-sm"
                                        style="border-radius: 12px; border: medium; font-weight: 500; color:white !important;">
                                        <span>
                                            <i class="bi bi-arrow-clockwise" style="font-size: 20px;"></i>
                                        </span>
                                        <span class="__btn__go__to__dashboard__text">Reload Manually</span>
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

<script>
    setTimeout(() => {
        window.location.href = "<?= htmlspecialchars($onboarding_url) ?>";
    }, 1500);
</script>
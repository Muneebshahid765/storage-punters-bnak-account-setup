<div class="p-0 d-flex flex-column h-100 container-fluid">
    <main style="flex-grow:1">
        <div class="customContainer mt-4 mb-4 decline-link container-fluid">
            <div class="row">
                <div class="container-fluid">
                    <div class="">
                        <div class="">

                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                                    </div>

                                    <h3 class="fw-bold mb-3"><?= htmlspecialchars($title) ?></h3>
                                    <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>

                                    <div class="border rounded p-3 text-start bg-light mb-4">
                                        <p class="mb-0">
                                            <strong>Tip:</strong> Try refreshing the page or come back later.
                                            If this issue persists, please contact support.
                                        </p>
                                    </div>

                                    <a href="<?= base_url('bank/setup/new') ?>"
                                        class="__btn__go__to__dashboard w-100 btn-danger btn-contact px-md-4 d-flex align-items-center justify-content-center gap-1 btn btn-sm"
                                        style="border-radius: 12px; border: medium; font-weight: 500; color:white !important;">
                                        <span>
                                            <i class="bi bi-house-door" style="font-size: 20px;"></i>
                                        </span>
                                        <span class="__btn__go__to__dashboard__text">Back to Dashboard</span>
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
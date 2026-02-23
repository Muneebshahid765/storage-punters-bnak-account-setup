$(document).ready(function () {

    // ═══════════════════════════════════════════════════════════════
    //  INIT
    // ═══════════════════════════════════════════════════════════════

    loadBankAccounts();

    // ═══════════════════════════════════════════════════════════════
    //  OPEN ADD BANK MODAL
    // ═══════════════════════════════════════════════════════════════

    $(document).on('click', '.__btn__add__new__bank__account', function () {
        resetBankForm();
        loadModalLocations(); // Load locations when opening modal
        $('#addBankModal').modal('show');
    });

    // ═══════════════════════════════════════════════════════════════
    //  BSB AUTO FORMAT  →  000-000
    // ═══════════════════════════════════════════════════════════════

    $(document).on('input', '#bank_bsb', function () {
        let digits = $(this).val().replace(/\D/g, '').slice(0, 6);
        if (digits.length > 3) {
            digits = digits.slice(0, 3) + '-' + digits.slice(3);
        }
        $(this).val(digits);
    });

    // ═══════════════════════════════════════════════════════════════
    //  ACCOUNT NUMBER — DIGITS ONLY, MAX 9
    // ═══════════════════════════════════════════════════════════════

    $(document).on('input', '#bank_account_number', function () {
        $(this).val($(this).val().replace(/\D/g, '').slice(0, 9));
    });

    // ═══════════════════════════════════════════════════════════════
    //  RESET FORM ON MODAL CLOSE
    // ═══════════════════════════════════════════════════════════════

    $('#addBankModal').on('hidden.bs.modal', function () {
        resetBankForm();
    });

    // ═══════════════════════════════════════════════════════════════
    //  EDIT BANK LOCATIONS
    // ═══════════════════════════════════════════════════════════════

    $(document).on('click', '.btn-edit-locations', async function () {
        const bankId = $(this).data('bank-id');
        const accountName = $(this).data('account-name') || 'this account';

        if (!bankId) {
            showNotification('Unable to determine bank account.', 'error');
            return;
        }

        // Open modal for editing locations
        $('#addBankModalLabel').text('Edit Linked Locations for ' + accountName);
        $('#bank-account-add-form').data('edit-mode', 'locations');
        $('#bank-account-add-form').data('bank-id', bankId);
        $('.bank-details-fields').hide();
        loadModalLocations(bankId);
        updateContinueButtonState();
        $('#addBankModal').modal('show');
    });

    // ═══════════════════════════════════════════════════════════════
    //  LOAD LOCATIONS IN MODAL
    // ═══════════════════════════════════════════════════════════════

    function loadModalLocations(bankId = null) {
        $('#modal-locations-list').html('<div class="col-12 text-center py-3"><span class="spinner-border spinner-border-sm text-secondary"></span><span class="ms-2 text-muted">Loading locations...</span></div>');

        $.when(
            $.ajax({ url: SITE_URL + 'api/user/fetch_storage_facilities_by_user', type: 'POST' }),
            bankId ? $.ajax({
                url: SITE_URL + 'api/users/storage-facility/bank-account/facilities',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ bankId: bankId })
            }) : $.Deferred().resolve({ data: [] })
        ).done(function (allRes, linkedRes) {
            const all = allRes.data || allRes[0]?.data || [];
            const linked = linkedRes.data || linkedRes[0]?.data || [];
            const linkedIds = linked.map(f => parseInt(f.storage_facility_id));

            let html = '';
            if (all.length === 0) {
                html = `
                    <div class="col-12">
                        <div class="alert alert-warning py-3 px-4 mb-0" role="alert">
                            <strong>No locations found.</strong> Please complete your company setup and add a storage location first.<br>
                            Go to <em>Settings Hub → Manage Storage Locations</em> to create one.
                        </div>
                    </div>`;
                $('#save-bank-btn').prop('disabled', true).text('Add a location to continue');
            } else {
                $('#save-bank-btn').prop('disabled', true).text('Select a location to continue');
                all.forEach(facility => {
                    const facilityId = parseInt(facility.storage_facility_id);
                    const isChecked = linkedIds.includes(facilityId);
                    const assignedBankId = facility.assigned_bank_id ? parseInt(facility.assigned_bank_id) : null;
                    const assignedBankName = facility.assigned_bank_name || 'another bank';
                    const alreadyAssignedElsewhere = assignedBankId && (!bankId || assignedBankId !== parseInt(bankId));

                    const disabledAttr = alreadyAssignedElsewhere ? 'disabled data-bs-toggle="tooltip" data-bs-placement="top" title="Linked to ' + escapeHtml(assignedBankName) + '"' : '';
                    const warningPill = alreadyAssignedElsewhere ? '<span class="badge bg-warning text-dark ms-2" style="font-size:10px;">In use</span>' : '';

                    html += `
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input location-checkbox" type="checkbox" value="${facility.storage_facility_id}" id="loc_${facility.storage_facility_id}" ${isChecked ? 'checked' : ''} ${disabledAttr}>
                                <label class="form-check-label small" for="loc_${facility.storage_facility_id}">
                                    ${escapeHtml(facility.storage_facility_name)} ${warningPill}
                                </label>
                            </div>
                        </div>
                    `;
                });
            }
            $('#modal-locations-list').html(html);
            if (window.bootstrap) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('#modal-locations-list [data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            updateContinueButtonState();
        }).fail(function () {
            $('#modal-locations-list').html(`
                <div class="col-12">
                    <div class="alert alert-danger py-3 px-4 mb-0" role="alert">
                        <strong>Unable to load your storage locations.</strong><br>
                        Please refresh the page or visit <em>Settings Hub → Manage Storage Locations</em> to ensure at least one location exists before adding a bank account.
                    </div>
                </div>`);
            $('#save-bank-btn').prop('disabled', true).text('Add a location to continue');
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  SUBMIT — ADD BANK ACCOUNT
    // ═══════════════════════════════════════════════════════════════

    $(document).on('submit', '#bank-account-add-form', async function (e) {
        e.preventDefault();

        const editMode = $(this).data('edit-mode');
        const bankId = $(this).data('bank-id');

        // If editing locations only
        if (editMode === 'locations' && bankId) {
            await updateBankLocations(bankId);
            return;
        }

        // Get selected location IDs for linking after onboarding
        const locationIds = $('.location-checkbox:checked').map(function() {
            return parseInt($(this).val());
        }).get();

        if (!locationIds.length) {
            showNotification('Please select at least one storage location to continue.', 'warning');
            updateContinueButtonState();
            return;
        }

        showSpinner();

        try {
            // Request Stripe onboarding link from backend
            const response = await $.ajax({
                url: SITE_URL + 'api/stripe/create-onboarding-link',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    location_ids: locationIds
                })
            });

            hideSpinner();

            if (response.status === 'success' && response.onboarding_url) {
                $('#addBankModal').modal('hide');
                resetBankForm();

                // Redirect to Stripe onboarding
                showNotification('Redirecting to secure bank setup...', 'info');
                setTimeout(() => {
                    window.location.href = response.onboarding_url;
                }, 1000);

            } else {
                showNotification(response.message || 'Failed to create onboarding link.', 'error');
            }

        } catch (err) {
            hideSpinner();
            const msg = err.responseJSON?.message || err.message || 'Error adding bank account.';
            showNotification(msg, 'error');
        }
    });

    // ═══════════════════════════════════════════════════════════════
    //  SET DEFAULT BANK
    // ═══════════════════════════════════════════════════════════════

    $(document).on('click', '.btn-set-default-bank', async function () {
        const bankId      = $(this).data('bank-id');
        const accountName = $(this).data('account-name') || 'this account';

        if (!bankId) {
            showNotification('Unable to determine bank account.', 'error');
            return;
        }

        const result = await Swal.fire({
            title: 'Set as Default?',
            html: `Set <strong>${accountName}</strong> as your default payout account?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Yes, Set Default',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn text-white fw-bold px-4',
                cancelButton: 'btn btn-outline-secondary ms-2 px-4'
            },
            buttonsStyling: false,
            didOpen: function () {
                document.querySelector('.swal2-confirm').style.backgroundColor = '#0d3b2e';
            }
        });

        if (!result.isConfirmed) return;

        showSpinner();

        try {
            const response = await $.ajax({
                url: SITE_URL + 'api/bank/set-default',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ bank_id: bankId })
            });

            hideSpinner();

            if (response.status === 'success') {
                showNotification(response.message || 'Default bank updated.', 'success');
                loadBankAccounts();
            } else {
                showNotification(response.message || 'Unable to update default bank.', 'error');
            }
        } catch (err) {
            hideSpinner();
            showNotification(err.responseJSON?.message || 'Unable to update default bank.', 'error');
        }
    });

    // ═══════════════════════════════════════════════════════════════
    //  DETACH BANK ACCOUNT
    // ═══════════════════════════════════════════════════════════════

    $(document).on('click', '.btn-detach-bank', async function () {
        const bankId      = $(this).data('bank-id');
        const accountName = $(this).data('account-name') || 'this account';

        if (!bankId) {
            showNotification('Unable to determine bank account.', 'error');
            return;
        }

        const result = await Swal.fire({
            title: 'Detach Bank Account?',
            html: `
                Are you sure you want to detach <strong>${accountName}</strong>?<br>
                <small class="text-muted">You will need to add it again to receive payouts.</small>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Yes, Detach',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger fw-bold px-4',
                cancelButton: 'btn btn-outline-secondary ms-2 px-4'
            },
            buttonsStyling: false
        });

        if (!result.isConfirmed) return;

        showSpinner();

        try {
            const response = await $.ajax({
                url: SITE_URL + 'api/bank/detach-account',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ bank_id: bankId })
            });

            hideSpinner();

            if (response.status === 'success') {
                showNotification(response.message || 'Bank account detached.', 'success');
                loadBankAccounts();
            } else {
                showNotification(response.message || 'Failed to detach bank account.', 'error');
            }
        } catch (err) {
            hideSpinner();
            showNotification(err.responseJSON?.message || 'Failed to detach bank account.', 'error');
        }
    });

    // ═══════════════════════════════════════════════════════════════
    //  RESUME STRIPE ONBOARDING
    // ═══════════════════════════════════════════════════════════════

    $(document).on('click', '.btn-resume-onboarding', async function () {
        const stripeAccountId = $(this).data('stripe-account-id');

        if (!stripeAccountId) {
            showNotification('Stripe account ID not found.', 'error');
            return;
        }

        showSpinner();

        try {
            const response = await $.ajax({
                url: SITE_URL + 'api/stripe/connected-account/session',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ account_id: stripeAccountId })
            });

            hideSpinner();

            if (response.status === 'success' && response.onboarding_url) {
                window.open(response.onboarding_url, '_blank');
            } else {
                showNotification(response.message || 'Failed to generate onboarding link.', 'error');
            }
        } catch (err) {
            hideSpinner();
            showNotification(err.responseJSON?.message || 'Error connecting to server.', 'error');
        }
    });

    // ═══════════════════════════════════════════════════════════════
    //  LOAD BANK ACCOUNTS — AJAX
    // ═══════════════════════════════════════════════════════════════

    function loadBankAccounts() {
        showSpinner();

        $.ajax({
            url: SITE_URL + 'api/bank/get-accounts',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                hideSpinner();
                if (response.status === 'success' && Array.isArray(response.data)) {
                    renderBankAccounts(response.data);
                } else {
                    renderEmptyState();
                }
            },
            error: function (err) {
                hideSpinner();
                console.error('Error loading bank accounts:', err);
                $('#bank-account-list-tbody').html(
                    '<tr><td colspan="6" class="text-center text-danger py-4">' +
                    '<i class="bi bi-exclamation-triangle me-2"></i>Error loading bank accounts. Please refresh.</td></tr>'
                );
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  RENDER BANK ACCOUNTS TABLE
    // ═══════════════════════════════════════════════════════════════

    function renderBankAccounts(accounts) {
        if (!accounts || accounts.length === 0) {
            renderEmptyState();
            return;
        }

        let html = '';

        accounts.forEach(function (account, index) {

            // ── Data extraction ───────────────────────────────────
            const accountName  = (account.account_name || '').trim() || 'Not provided';
            const institution  = account.bank_institution || '—';

            const rawBsb       = (account.bsb || '').replace(/\D/g, '');
            const formattedBsb = rawBsb.length === 6
                ? rawBsb.slice(0, 3) + '-' + rawBsb.slice(3)
                : 'N/A';

            const rawAccNum    = (account.account_number || '').replace(/\D/g, '');
            const maskedAcc    = rawAccNum.length >= 4
                ? '****' + rawAccNum.slice(-4)
                : (rawAccNum || 'N/A');

            const stripeId     = account.stripe_bank_account_id || '';
            const isDefault    = account.is_default === 'true' || account.is_default === true;
            const isActive     = parseInt(account.status_type_id, 10) === 1;

            // Onboarding incomplete hone ki check
            // Stripe account IDs start with "acct_"
            const needsOnboarding = stripeId.startsWith('acct_') && !isDefault && isActive;

            // ── Linked locations chips ────────────────────────────
            const locationNames = (account.linked_locations || '')
                .split(',')
                .map(function (l) { return l.trim(); })
                .filter(Boolean);

            const locationSummary = locationNames.length
                ? `${locationNames.length} location link${locationNames.length > 1 ? 's' : ''}`
                : 'No location links';

            const locationBadgeClass = locationNames.length ? '' : ' empty';
            const locationBadge = `<span class="location-count-pill${locationBadgeClass}">${locationSummary}</span>`;

            // ── Status pill ───────────────────────────────────────
            const statusPill = isActive
                ? '<span class="status-pill success">Active</span>'
                : '<span class="status-pill warning">Inactive</span>';

            // ── Actions column ────────────────────────────────────
            let actionsHtml = '';

            if (needsOnboarding && stripeId) {
                actionsHtml += `
                    <button class="bank-action-btn warning btn-resume-onboarding me-1"
                            data-stripe-account-id="${escapeHtml(stripeId)}"
                            title="Complete Stripe verification">
                        <i class="bi bi-arrow-repeat me-1"></i>Verify
                    </button>`;
            }

            if (isActive) {
                actionsHtml += `
                    <button class="bank-action-btn outline btn-edit-locations me-1"
                            data-bank-id="${account.id}"
                            data-account-name="${escapeHtml(accountName)}"
                            title="Link storage locations">
                        <i class="bi bi-geo-alt me-1"></i>Edit Links
                    </button>`;

                actionsHtml += `
                    <button class="bank-action-btn danger btn-detach-bank"
                            data-bank-id="${account.id}"
                            data-account-name="${escapeHtml(accountName)}"
                            title="Detach this bank account">
                        <i class="bi bi-trash me-1"></i>Detach
                    </button>`;
            } else {
                actionsHtml += '<span class="text-muted small">Detached</span>';
            }

            // ── Onboarding badge (incomplete verification warning) ─
            const onboardingBadge = needsOnboarding
                ? '<br><span class="badge bg-warning text-dark mt-1" style="font-size:10px;">' +
                  '<i class="bi bi-exclamation-triangle me-1"></i>Verification Pending</span>'
                : '';

            // ── Row HTML ──────────────────────────────────────────
            html += `
                <tr>
                    <td>
                        <span class="account-index-badge">${index + 1}</span>
                    </td>
                    <td>
                        <div class="bank-account-name">${escapeHtml(accountName)}</div>
                        <div class="text-muted-500">${escapeHtml(institution)}</div>
                        ${onboardingBadge}
                    </td>
                    <td>
                        <div class="bank-meta-row">
                            <span class="bank-meta-label">BSB</span>
                            <span class="bank-meta-value">${escapeHtml(formattedBsb)}</span>
                        </div>
                        <div class="bank-meta-row">
                            <span class="bank-meta-label">Account</span>
                            <span class="bank-meta-value">${escapeHtml(maskedAcc)}</span>
                        </div>
                    </td>
                    <td>
                        ${locationBadge}
                    </td>
                    <td>${statusPill}</td>
                    <td class="bank-actions-cell">
                        ${actionsHtml}
                    </td>
                </tr>
            `;
        });

        $('#bank-account-list-tbody').html(html);
    }

    // ═══════════════════════════════════════════════════════════════
    //  EMPTY STATE
    // ═══════════════════════════════════════════════════════════════

    function renderEmptyState() {
        $('#bank-account-list-tbody').html(`
            <tr>
                <td colspan="6" class="text-center py-5">
                    <i class="bi bi-bank2 fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-3">No bank accounts found.</p>
                    <button class="btn text-white px-4 __btn__add__new__bank__account"
                            style="background-color:#0d3b2e; font-weight:600;">
                        <i class="bi bi-plus-circle me-2"></i>Add Your First Bank Account
                    </button>
                </td>
            </tr>
        `);
    }

    // ═══════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════

    // ═══════════════════════════════════════════════════════════════
    //  UPDATE BANK LOCATIONS
    // ═══════════════════════════════════════════════════════════════

    async function updateBankLocations(bankId) {
        showSpinner();

        try {
            // Get currently selected location IDs
            const selectedIds = $('.location-checkbox:checked').map(function() {
                return parseInt($(this).val());
            }).get();

            // Get previously selected location IDs (from data attribute or fetch)
            const previousResponse = await $.ajax({
                url: SITE_URL + 'api/users/storage-facility/bank-account/facilities',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ bankId: bankId })
            });

            const previousIds = (previousResponse.data || []).map(f => parseInt(f.storage_facility_id));

            // Calculate added and deleted
            const added = selectedIds.filter(id => !previousIds.includes(id)).map(id => ({ facility_id: id }));
            const deleted = previousIds.filter(id => !selectedIds.includes(id)).map(id => ({ facility_id: id }));

            // Update via API
            const response = await $.ajax({
                url: SITE_URL + 'api/users/storage-facility/bank-account/update-facilities',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    bank_id: bankId,
                    added: added,
                    deleted: deleted
                })
            });

            hideSpinner();

            if (response.status === 'success') {
                showNotification(response.message || 'Locations updated successfully!', 'success');
                $('#addBankModal').modal('hide');
                resetBankForm();
                loadBankAccounts();
            } else {
                showNotification(response.message || 'Failed to update locations.', 'error');
            }
        } catch (err) {
            hideSpinner();
            const msg = err.responseJSON?.message || err.message || 'Error updating locations.';
            showNotification(msg, 'error');
        }
    }

    $(document).on('change', '.location-checkbox', function () {
        updateContinueButtonState();
    });

    function resetBankForm() {
        var form = document.getElementById('bank-account-add-form');
        if (form) {
            form.reset();
            $(form).removeData('edit-mode').removeData('bank-id');
        }
        $('#addBankModalLabel').text('Add New Bank Account (Stripe Connect)');
        $('.bank-details-fields').show();
        $('#save-bank-btn').prop('disabled', true).text('Select a location to continue');
    }

    function updateContinueButtonState() {
        const $button = $('#save-bank-btn');
        const mode = $('#bank-account-add-form').data('edit-mode');
        const hasCheckboxes = $('.location-checkbox').length > 0;

        if (!hasCheckboxes) {
            $button.prop('disabled', true).text('Add a location to continue');
            return;
        }

        if (mode === 'locations') {
            $button.prop('disabled', false).text('Save Location Links');
            return;
        }

        const selectedCount = $('.location-checkbox:checked').length;
        if (selectedCount === 0) {
            $button.prop('disabled', true).text('Select a location to continue');
        } else {
            $button.prop('disabled', false).text('Continue to Stripe Setup');
        }
    }

    /**
     * XSS prevention — escape HTML special characters
     */
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,  '&amp;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;')
            .replace(/"/g,  '&quot;')
            .replace(/'/g,  '&#039;');
    }

});
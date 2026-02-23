$(document).ready(function () {


    function openBankFacilitiesModal(bankId) {
        $("#bankFacilitiesModal").data("bankId", bankId);

        // Show loader in list container
        $("#facilitiesList").html(`
        <div class="text-center p-4">
            <i class="bi bi-arrow-repeat spin fs-3"></i>
            <p class="mt-2 mb-0">Loading facilities...</p>
        </div>
    `);

        // 👉 Open modal right away (user sees loading state)
        $("#bankFacilitiesModal").modal("show");

        // Run both AJAX calls together
        $.when(
            getAllAuctionStorageFacilities(),
            $.ajax({
                url: SITE_URL + "api/users/storage-facility/bank-account/facilities",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({ bankId: bankId }),
                dataType: "json"
            })
        ).done(function (allFacilitiesRes, linkedFacilitiesRes) {
            const allFacilitiesResp = allFacilitiesRes[0];
            const linkedFacilitiesResp = linkedFacilitiesRes[0];

            const $list = $("#facilitiesList");
            $list.empty();

            // If no facilities found
            if (allFacilitiesResp.status !== "success" || !Array.isArray(allFacilitiesResp.data)) {
                $list.html(`<div class="alert alert-warning">${allFacilitiesResp.message || "No facility data available."}</div>`);
                return;
            }

            // Linked facilities (may be empty)
            const linkedIds = (linkedFacilitiesResp.status === "success" && Array.isArray(linkedFacilitiesResp.data))
                ? linkedFacilitiesResp.data.map(f => parseInt(f.storage_facility_id))
                : [];

            // Save current linked IDs for submit comparison later
            $("#bankFacilitiesModal").data("linkedFacilities", linkedIds);

            // Render facility list with checkboxes
            allFacilitiesResp.data.forEach(facility => {
                const fid = parseInt(facility.storage_facility_id);
                const isChecked = linkedIds.includes(fid);
                $list.append(`
    <label class="facility-option d-flex align-items-center">
        <input type="checkbox" class="form-check-input facility-checkbox me-3" value="${fid}" ${isChecked ? "checked" : ""}>
        <span class="facility-name">${facility.storage_facility_name}</span>
    </label>
`);

            });
        }).fail(function () {
            // If either request fails
            $("#facilitiesList").html(`<div class="alert alert-danger">Failed to load facilities. Please try again.</div>`);
        });
    }



    $("#bankFacilitiesForm").on("submit", function (e) {
        e.preventDefault();

        const bankId = $("#bankFacilitiesModal").data("bankId");

        // Collect current selections
        const selected = $(".facility-checkbox:checked").map(function () {
            return parseInt(this.value);
        }).get();

        // Get previous linked (saved in modal data on open)
        const prev = $("#bankFacilitiesModal").data("linkedFacilities") || [];

        // Calculate diffs
        const added = selected.filter(id => !prev.includes(id)).map(fid => ({ facility_id: fid }));
        const deleted = prev.filter(id => !selected.includes(id)).map(fid => ({ facility_id: fid }));

        // ⚡ If nothing changed → show swal instead of closing
        if (added.length === 0 && deleted.length === 0) {
            Swal.fire({
                title: "No Changes",
                text: "You did not make any changes to the facilities.",
                icon: "question",
                customClass: {
                    popup: 'swal2-custom-width',
                    confirmButton: 'btn btn-yellow text-white fw-bold',
                    cancelButton: 'btn btn-secondary ms-2'
                },
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Ok',
            });
            return; // don't proceed
        }

        // Otherwise confirm save
        Swal.fire({
            title: "Are you sure?",
            html: `
        <div class="text-start">
            <p class="mb-2">You are about to update linked facilities for this bank account.</p>
            <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #ddd;">
                <p class="mb-0 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    These changes will be applied immediately once saved.
                </p>
            </div>
        </div>
    `,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Yes, Confirm',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
            focusConfirm: true,
            customClass: {
                popup: 'swal2-custom-width',
                confirmButton: 'btn btn-yellow text-white fw-bold',
                cancelButton: 'btn btn-secondary ms-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                showUniversalLoaderForTable("bank-account-table");

                $.ajax({
                    url: SITE_URL + "api/users/storage-facility/bank-account/update-facilities",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        bank_id: bankId,
                        added: added,
                        deleted: deleted
                    }),
                    success: function (resp) {
                        hideUniversalLoader();

                        if (resp.status === "success") {
                            showNotification(resp.message || "Facilities updated successfully", "success");
                            $("#bankFacilitiesModal").modal("hide"); // ✅ close only if changes saved
                            bankAccountListTable(); // refresh
                        } else {
                            showNotification(resp.message || "Something went wrong", "error");
                        }
                    },
                    error: function () {
                        hideUniversalLoader();
                        showNotification("Failed to update facilities. Please try again.", "error");
                    }
                });
            }
        });
    });


    function bankAccountListTable() {


        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#bank-account-table')) {
            // DataTable is already initialized, destroy the existing instance
            $('#bank-account-table').DataTable().destroy();
        }

        // Initialize DataTable
        $('#bank-account-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": SITE_URL + "api/users/storage-facility/bank-account/get", // Change this to your backend URL
                "type": "POST",
                "beforeSend": function () {
                    showUniversalLoaderForTable('bank-account-table');
                },
                "dataSrc": function (json) {

                    hideUniversalLoader();

                    if (json.status === "success" && json.result) {
                        showNotification(json.message, "success");

                        json.draw = json.result.draw;
                        json.recordsTotal = json.result.recordsTotal;
                        json.recordsFiltered = json.result.recordsFiltered;
                        return json.result.data;
                    }

                    showNotification(json.message, "error");
                    return [];
                },
                "error": function (xhr, status, error) {

                    hideUniversalLoader();

                    console.error("DataTables AJAX error:", status, error);
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            console.error("Server error:", response.message);
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                    }
                },
            },
            "columns": [
                {
                    "data": null, // combining multiple fields manually
                    "title": "Account Name",
                    "render": function (data, type, row) {
                        const accountName = row.account_name ? row.account_name : 'N/A';
                        const accountId = row.account_id ? row.account_id : '—';
                        const onboardingUrl = row.onboarding_url ? row.onboarding_url : null;

                        let onboardingButton = '';
                        let linkStorageBtn = '';
                        if (onboardingUrl) {
                            // open in a new tab
                            onboardingButton = `
        <span class="badge bg-danger mt-1 __btn__onboarding__link__"
              role="button"
              style="cursor:pointer;"
              data-bank-id="${row.bank_id}" 
              data-account-id="${row.account_id}"
              data-onboarding-link="${row.onboarding_url}">
            <i class="bi bi-arrow-repeat me-1"></i> Resume Onboarding
        </span>`;
                        } else {


                            if (row.bank_id) {

                                linkStorageBtn = `
        <span class="badge bg-success mt-1 __btn__link__storage"
              role="button"
              style="cursor:pointer;"
              data-bank-id="${row.bank_id}" data-account-status-id="${row.account_status_type_id}">
            <i class="bi bi-link-45deg me-1"></i> Link Storage
        </span>`;

                            } else {
                                // ⚪ Non-clickable gray badge
                                linkStorageBtn = `
        <span class="badge bg-secondary mt-1">
            <i class="bi bi-dash-circle me-1"></i> No link
        </span>`;
                            }

                        }

                        return `
        <div class="d-flex flex-column">
            <div>
                <strong>${accountName}</strong><br>
                <small class="text-muted">${accountId}</small>
            </div>
            <div>
                ${onboardingButton}
                ${linkStorageBtn}
            </div>
        </div>
    `;
                    }
                }
                ,
                { "data": "account_email", "title": "Account Email" },
                { "data": "account_type", "title": "Account Type" },
                {
                    "data": "charges_enabled",
                    "title": "Charges Enabled",
                    "render": function (data, type, row) {
                        return data === "Yes"
                            ? '<span>Yes</span>'
                            : '<span>No</span>';
                    }
                },
                {
                    "data": "payouts_enabled",
                    "title": "Payouts Enabled",
                    "render": function (data, type, row) {
                        return data === "Yes"
                            ? '<span>Yes</span>'
                            : '<span>No</span>';
                    }
                },
                {
                    "data": "details_submitted",
                    "title": "KYC Submitted",
                    "render": function (data, type, row) {
                        return data === "Yes"
                            ? '<span>Complete</span>'
                            : '<span>Incomplete</span>';
                    }
                },
                {
                    "data": "account_status",
                    "title": "Account Status",
                    "render": function (data, type, row) {
                        if (data === "Complete") {
                            return '<span><i></i>Complete</span>';
                        } else {
                            return '<span><i></i>Pending</span>';
                        }
                    }
                },
                {
                    "data": "created_at",
                    "title": "Created At",
                    "render": function (data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    "data": "updated_at",
                    "title": "Updated At",
                    "render": function (data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                }
            ],

            "order": [[1, 'desc']], // Set the combined name column as default sort
            "initComplete": function (settings, json) {

            },
            "createdRow": function (row, data, dataIndex) {
                $('td:eq(0)', row).attr('data-label', 'Account Name');
                $('td:eq(1)', row).attr('data-label', 'Account Email');
                $('td:eq(2)', row).attr('data-label', 'Account Type');
                $('td:eq(3)', row).attr('data-label', 'Charges Enabled');
                $('td:eq(4)', row).attr('data-label', 'Payouts Enabled');
                $('td:eq(5)', row).attr('data-label', 'KYC Submitted');
                $('td:eq(6)', row).attr('data-label', 'Account Status');
                $('td:eq(7)', row).attr('data-label', 'Created At');
                $('td:eq(8)', row).attr('data-label', 'Updated At');
            },
            "drawCallback": function (settings) {
                // Initialize Bootstrap Switch for new elements
                $('[data-toggle="toggle"]').bootstrapSwitch();

                // Handle switch change event for elements with data-type='card'
                $('[data-toggle="toggle"][data-action="activation"]').on('switchChange.bootstrapSwitch', function (event, state) {
                    var bankAccountId = $(this).data('bank-account-system-id');
                    var status = state ? 'Active' : 'Inactive';

                    let actionMessage = status.toLowerCase() === "active"
                        ? "You are about to <strong>attached</strong> this bank account. Do you want to proceed?"
                        : "You are about to <strong>detach</strong> this bank account. Do you want to continue?";

                    Swal.fire({
                        title: 'Are you sure?',
                        html: actionMessage, // ✅ Clear message for activation & deactivation
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                        if (result.isConfirmed) {

                            $.ajax({
                                url: SITE_URL + 'api/users/storage-facility/bank-account/update-status',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    bank_account_id: bankAccountId,
                                    bank_account_status_name: status
                                }),
                                beforeSend: function () {

                                },
                                success: function (response) {

                                    if (response.status === "success") {

                                        showNotification(response.message, "success");
                                        bankAccountListTable();

                                    } else {
                                        showNotification(response.message, "error");
                                    }
                                    // Additional success handling
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    let errorMessage = "Something went wrong. Try again.";

                                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                        errorMessage = jqXHR.responseJSON.message;
                                    }
                                    $('[data-toggle="toggle"][data-action="activation"][data-bank-account-system-id="' + bankAccountId + '"]').bootstrapSwitch('toggleState', true);


                                    showNotification(errorMessage, "error");
                                }
                            });

                        } else {
                            // User clicked No or closed the dialog, revert the switch state

                            $('[data-toggle="toggle"][data-action="activation"][data-bank-account-system-id="' + bankAccountId + '"]').bootstrapSwitch('toggleState', true);

                        }
                    });
                });


                // Handle switch change event for elements with data-type='card'
                $('[data-toggle="toggle"][data-action="bank-default"]').on('switchChange.bootstrapSwitch', function (event, state) {
                    var bankAccountId = $(this).data('bank-account-system-id');
                    var status = state ? 'Yes' : 'No';

                    let actionMessage = status.toLowerCase() === "yes"
                        ? "You are about to <strong>default</strong> this bank account. Do you want to proceed?"
                        : "You are about to <strong>delete</strong> this bank account from default. Do you want to continue?";

                    Swal.fire({
                        title: 'Are you sure?',
                        html: actionMessage, // ✅ Clear message for activation & deactivation
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                        if (result.isConfirmed) {

                            $.ajax({
                                url: SITE_URL + 'api/users/storage-facility/bank-account/update-bank-status',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    bank_account_id: bankAccountId,
                                    bank_account_status_name: status
                                }),
                                beforeSend: function () {
                                    showUniversalLoaderForTable('bank-account-table');
                                },
                                success: function (response) {

                                    hideUniversalLoader();

                                    if (response.status === "success") {

                                        showNotification(response.message, "success");
                                        bankAccountListTable();

                                    } else {
                                        showNotification(response.message, "error");
                                    }


                                    // Additional success handling
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    let errorMessage = "Something went wrong. Try again.";

                                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                        errorMessage = jqXHR.responseJSON.message;
                                    }
                                    $('[data-toggle="toggle"][data-action="bank-default"][data-bank-account-system-id="' + bankAccountId + '"]').bootstrapSwitch('toggleState', true);


                                    showNotification(errorMessage, "error");

                                    hideUniversalLoader();
                                }
                            });

                        } else {
                            // User clicked No or closed the dialog, revert the switch state

                            $('[data-toggle="toggle"][data-action="bank-default"][data-bank-account-system-id="' + bankAccountId + '"]').bootstrapSwitch('toggleState', true);

                        }
                    });
                });


            },
            "rowCallback": function (row, data, index) {
                /*$(row).off("click").on("click", function (event) {
                    // Prevent opening on clicks for buttons/links/inputs
                    if (!$(event.target).is('button') && !$(event.target).is('a') && !$(event.target).is('input')) {

                        // ✅ Only open modal if the bank account is active
                        if (data.account_status_type_id == 1) {
                            openBankFacilitiesModal(data.bank_id);
                        } else {
                            // Optional: give user feedback
                            showNotification("This bank account is not active, facilities cannot be managed.", "warning");
                        }
                    }
                });*/

                $(row).hover(
                    function () { $(this).addClass('table-hover'); },
                    function () { $(this).removeClass('table-hover'); }
                ).css('cursor', 'pointer');
            },
        });

    }

    bankAccountListTable();


    /*$(document).on("click", ".__btn__add__new__bank__account", async function () {
        let formContainer = $(".__table__bank__account");


        // Toggle form: If exists → Remove; Else → Append
        if ($(".__update__bank_account__form").length > 0 || $(".__new__bank_account_add__form").length > 0) {
            $(".__update__bank_account__form").slideUp(300, function () {
                $(this).remove();
                $(".__btn__update__bank__account__text").text("Edit Card");
                $(".__btn__add__new__bank__account__text").text("Add New");
            });
            $(".__new__bank_account_add__form").slideUp(300, function () {
                $(this).remove();
                $(".__btn__add__new__bank__account__text").text("Add New");
                $(".__btn__update__bank__account__text").text("Edit Card");
            });
        } else {

            try {
                let user = await getUser();


                let formHTML = `
             <div class="mt-5 mb-5 __new__bank_account_add__form">
                 <form id="bankAccountAddForm">
                     <div class="row">
                         <div class="mt-4 col-xl-12 col-md-12 col-12">
                                            <label for="account_holder_name" class="input-label-secondary">Account Holder Name *</label>
                                            <div class="input-field-container">
                                                <input id="account_holder_name" type="text" placeholder="Account Holder Name" name="account_holder_name" class="input-secondary text-300" value="${user.first_name + " " + user.last_name}">
                                            </div>
                                        </div>

                                        <div class="mt-4 col-xl-12 col-md-12 col-12">
                                                    <label for="account_holder_email" class="input-label-secondary">Email Address *</label>
                                                    <div class="input-field-container">
                                                        <input id="account_holder_email" type="text" placeholder="Email Address" name="account_holder_email" value="${user.email}" class="input-secondary text-300" readonly>
                                                    </div>
                                                </div>
                     </div>
                     <div class="row">

                     <div class="mt-4 col-xl-6 col-md-6">
                                            <label for="account_routing_number" class="input-label-secondary">BSB *</label>
                                            <div class="input-field-container">
                                                <input id="account_routing_number" type="text" placeholder="Enter BSB" name="account_routing_number" class="input-secondary text-300">
                                            </div>
                                        </div>

                                        <div class="mt-4 col-xl-6 col-md-6">
                                            <label for="account_number" class="input-label-secondary">Account Number *</label>
                                            <div class="input-field-container">
                                                <input id="account_number" type="text" placeholder="Enter Account Number" name="account_number" class="input-secondary text-300">
                                            </div>
                                        </div>
                     </div>

                      <div style="width: max-content;" class="mt-4 mx-auto col">
                         <button type="submit" class="w-100 btn-yellow border-0 btn-contact btn-add-new-bank-account text-uppercase px-md-4 btn btn-primary btn-md">
                             Save Bank Information
                         </button>
                     </div>
                 </form>
             </div>`;


                formContainer.append(formHTML);

                $(".__new__bank_account_add__form").hide().slideDown(300);
                $(".__btn__add__new__bank__account__text").text("Hide");


            } catch (err) {
                console.warn("Auth error:", err.message);
            }



        }
    });*/

    /*$(document).on("submit", "#bankAccountAddForm", async function (e) {
        e.preventDefault();

        $(".error-message").remove(); // Remove existing errors
        let isValid = true;

        let formData = {
            account_holder_name: $("#account_holder_name").val().trim(),
            account_holder_email: $("#account_holder_email").val().trim(),
            account_routing_number: $("#account_routing_number").val().trim(),
            account_number: $("#account_number").val().trim()
        };

        // ✅ Show validation errors under fields
        function showError(inputId, message) {
            $(`#${inputId}`).after(`<p class="error-message text-yellow text-error">${message}</p>`);
            isValid = false;
        }

        // ✅ Validate required fields
        $.each(formData, function (key, value) {
            if (!value) {
                showError(key, "This field is required.");
            }
        });

        // ✅ Routing Number: AU BSB format (6 digits)
        if (!/^\d{6}$/.test(formData.account_routing_number)) {
            showError("routing_number", "Routing number must be 6 digits (BSB).");
        }

        // ✅ Account Number: 6–9 digits
        if (!/^\d{6,9}$/.test(formData.account_number)) {
            showError("account_number", "Account number must be 6–9 digits.");
        }

        if (!isValid) return; // ⛔ Stop if validation fails


        // showNotification("Thanks for your interest! We're actively working on this feature and will release it soon.", "success");

        // $('#btn-add-new-bank-account').html('<span class="spinner-border spinner-border-sm"></span> Creating your bank...');

        showSpinner();

        try {

            // 1️⃣ Search or Create Stripe Customer
            let customer_id = null;
            const { exists, customer } = await search_customer_by_email();

            if (exists) {
                customer_id = customer.id;
            } else {
                const create = await create_stripe_customer();
                if (!create.success) {
                    showNotification(create.message, "error");
                    return;
                }
                customer_id = create.customer.id;
            }

            console.log("Creating token with:", formData);

            // 1️⃣ Create AU BECS Direct Debit PaymentMethod
            const result = await stripe.createPaymentMethod({
                type: "au_becs_debit",
                au_becs_debit: {
                    bsb_number: formData.account_routing_number,
                    account_number: formData.account_number,
                },
                billing_details: {
                    name: formData.account_holder_name,
                    email: formData.account_holder_email,
                },
            });


            if (result.error) {

                hideSpinner();

                showNotification("Bank account confirmation failed: " + result.error.message, "error");

                return;
            }

            const bank_account_id = result.paymentMethod.id;

            // 3️⃣ Attach bank to customer
            const attach = await attach_bank_account(customer_id, bank_account_id);
            if (attach.success) {

                hideSpinner();

                showNotification("Bank account added successfully!", "success");

                $("#bankAccountAddForm")[0].reset();


                $(".__new__bank_account_add__form").slideUp(300, function () {
                    $(this).remove();
                    $(".__btn__add__new__bank__account__text").text("Add New");
                    $(".__btn__update__bank__account__text").text("Edit Bank Account");
                });

                bankAccountListTable();

            } else {

                hideSpinner();

                showNotification("Failed to attach bank account: " + attach.message, "error");

            }

        } catch (err) {

            hideSpinner();

            showNotification("Something went wrong: " + err.message, "error");

        }


    });*/

    $(document).on("click", ".__btn__link__storage", function () {
        const bankId = $(this).data("bank-id");
        const accountStatusId = $(this).data("account-status-id");

        if (accountStatusId == 1) {
            // 🟡 Optional: quick confirmation (remove if not needed)
            Swal.fire({
                title: "Link Storage Locations",
                html: `
        <div class="text-start">
            <p class="mb-2">You’re about to link your storage locations to this bank account:</p>
            <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #ddd;">
                <p class="mb-1 text-muted">
                    <i class="bi bi-hdd-stack me-1"></i>
                    This will connect your <strong>storage facilities</strong> to the selected payout account.
                </p>
                <p class="mb-0 small text-muted">
                    <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                    Make sure the correct bank is selected before proceeding.
                </p>
            </div>
        </div>
    `,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Yes, Proceed',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
                focusConfirm: true,
                customClass: {
                    popup: 'swal2-custom-width',
                    confirmButton: 'btn btn-yellow text-white fw-bold',
                    cancelButton: 'btn btn-secondary ms-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    openBankFacilitiesModal(bankId);
                }
            });
        } else {
            showNotification("This bank account is not active, facilities cannot be managed.", "warning");
        }
    });

    // 🟢 Initialize Stripe Connect (Lazy load when needed)
    let stripeConnectInstance = null;
    const STRIPE_PUBLISHABLE_KEY = window.APP_CONFIG?.STRIPE_PUBLISHABLE_KEY || '';
    if (!STRIPE_PUBLISHABLE_KEY) {
        console.error('Stripe publishable key missing from APP_CONFIG');
    }

    function initializeStripeConnect(clientSecret) {
        if (!window.StripeConnect) {
            console.error("Stripe Connect JS not loaded.");
            return null;
        }
        return window.StripeConnect.init({
            publishableKey: STRIPE_PUBLISHABLE_KEY,
            clientSecret: clientSecret,
            appearance: {
                overlays: 'dialog',
                variables: {
                    colorPrimary: '#ffcc29', // Matching your theme
                },
            },
        });
    }

    // 🟢 Open Onboarding Modal
    function openStripeConnectOnboarding(clientSecret, bankId) {
        const modalEl = document.getElementById('stripeOnboardingModal');
        const container = document.getElementById('stripe-onboarding-container');

        // Clear previous content and reset any existing instance
        container.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100"><div class="spinner-border text-primary" role="status"></div><p class="mt-3 text-muted">Loading secure onboarding...</p></div>';
        
        // Reset previous instance to ensure fresh connection with new clientSecret
        stripeConnectInstance = null;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        try {
            // Always create fresh instance with the new clientSecret
            stripeConnectInstance = initializeStripeConnect(clientSecret);
            console.log("Stripe Connect initialized with new clientSecret for bank:", bankId);
            const onboarding = stripeConnectInstance.create('account-onboarding');

            // Clear loader
            container.innerHTML = '';
            onboarding.mount('#stripe-onboarding-container');

            // Handle Exit
            onboarding.setOnExit(() => {
                console.log("User exited onboarding.");
                modal.hide();
                bankAccountListTable(); // Refresh table to show updated status

                // Optional: Check status and show success if complete
                // checkAccountStatus(bankId); 
            });

        } catch (error) {
            console.error("Failed to mount Stripe component:", error);
            container.innerHTML = '<div class="alert alert-danger m-4">Failed to load onboarding. Please try again.</div>';
        }
    }


    $(document).on("click", ".__btn__onboarding__link__", function () {
        const bankId = $(this).data("bank-id");
        // We no longer use the static link. We generate a new session.

        // 1️⃣ Fetch new Client Secret for this account
        showSpinner();
        $.ajax({
            url: SITE_URL + "api/stripe/connected-account/session",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({ account_id: $(this).data('account-id') || $(this).data('bank-account-id') }), // Ensure you have the account ID (acct_...)
            success: function (resp) {
                hideSpinner();
                if (resp.status === "success" && resp.client_secret) {
                    openStripeConnectOnboarding(resp.client_secret, bankId);
                } else {
                    showNotification("Failed to start onboarding session.", "error");
                }
            },
            error: function () {
                hideSpinner();
                showNotification("Error connecting to server.", "error");
            }
        });
    });

    $(document).on("click", ".__btn__add__new__bank__account", async function () {
        // 🟡 Step 1: Show styled confirmation
        const { value: confirmed } = await Swal.fire({
            title: "Add New Bank Account",
            html: `
            <div class="text-start">
                <p class="mb-2">You are about to create a new Stripe connected account.</p>
                <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #ddd;">
                    <p class="mb-1">
                        <i class="bi bi-bank2 me-1 text-primary"></i>
                        This will use <strong>Embedded Verification</strong> to set up your payouts securely without leaving the dashboard.
                    </p>
                </div>
            </div>
        `,
            icon: "info",
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Yes, Proceed',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
            focusConfirm: true,
            customClass: {
                popup: 'swal2-custom-width',
                confirmButton: 'btn btn-yellow text-white fw-bold',
                cancelButton: 'btn btn-secondary ms-2'
            },
            buttonsStyling: false
        });

        // If user cancels, stop here
        if (!confirmed) return;

        // 🌀 Proceed with creation
        showSpinner();

        try {
            // 1️⃣ Search or Create Stripe Customer
            let customer_id = null;
            const { exists, customer } = await search_customer_by_email();

            if (exists) {
                customer_id = customer.id;
            } else {
                const create = await create_stripe_customer();
                if (!create.success) {
                    showNotification(create.message, "error");
                    hideSpinner();
                    return;
                }
                customer_id = create.customer.id;
            }

            // 2️⃣ Attach connected account (New Flow returns client_secret)
            const attach = await attach_connected_account(customer_id);
            
            // Debug logging - check what account is being created
            console.log("=== NEW BANK ACCOUNT RESPONSE ===");
            console.log("Account ID:", attach.data?.account_id);
            console.log("Bank Account ID:", attach.data?.bank_account_id);
            console.log("Client Secret:", attach.data?.client_secret ? "Present" : "Missing");
            console.log("Full Response:", attach);

            hideSpinner();

            if (attach.success && attach.data.client_secret) {
                showNotification("✅ Connected account created! Account: " + attach.data.account_id, "success");

                // Wait a moment for table refresh/modal ready
                setTimeout(() => {
                    openStripeConnectOnboarding(attach.data.client_secret, attach.data.bank_account_id);
                }, 500);

            } else {
                showNotification("Failed to create connected account: " + (attach.message || "Unknown error"), "error");
            }

        } catch (err) {
            hideSpinner();
            showNotification("Something went wrong: " + err.message, "error");
        }
    });



    /*$(document).on("submit", "#bankAccountAddForm", async function (e) {
        e.preventDefault();

        $(".error-message").remove(); // Remove existing errors
        let isValid = true;

        showSpinner();

        try {

            // 1️⃣ Search or Create Stripe Customer
            let customer_id = null;
            const { exists, customer } = await search_customer_by_email();

            if (exists) {
                customer_id = customer.id;
            } else {
                const create = await create_stripe_customer();
                if (!create.success) {
                    showNotification(create.message, "error");
                    return;
                }
                customer_id = create.customer.id;
            }

            // 3️⃣ Attach bank to customer
            const attach = await attach_connected_account(customer_id);
            if (attach.success) {

                hideSpinner();

                showNotification("Connected account added successfully!", "success");

                $("#bankAccountAddForm")[0].reset();


                $(".__new__bank_account_add__form").slideUp(300, function () {
                    $(this).remove();
                    $(".__btn__add__new__bank__account__text").text("Add New");
                    $(".__btn__update__bank__account__text").text("Edit Bank Account");
                });

                bankAccountListTable();

                window.location.href = response.data.onboarding_url; // Redirect to Stripe onboarding

            } else {

                hideSpinner();

                showNotification("Failed to attach connected account: " + attach.message, "error");

            }

        } catch (err) {

            hideSpinner();

            showNotification("Something went wrong: " + err.message, "error");

        }


    });*/

});

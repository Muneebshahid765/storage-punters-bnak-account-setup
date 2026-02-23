$(document).ready(function () {

    function paymentMethodListTable() {


        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#payment-method-table')) {
            // DataTable is already initialized, destroy the existing instance
            $('#payment-method-table').DataTable().destroy();
        }

        // Initialize DataTable
        $('#payment-method-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": SITE_URL + "api/users/storage-facility/payment-method/get", // Change this to your backend URL
                "type": "POST",
                "beforeSend": function () {
                    showUniversalLoaderForTable('payment-method-table');
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
                { "data": "payment_method_id" },
                {
                    "data": null, // Use "data": null to access the entire data row
                    "render": function (data, type, row) {
                        return row.card_holder_first_name;
                    }
                },
                {
                    "data": "card_holder_card_number",
                    "render": function (data, type, row) {
                        if (data) {
                            return "**** **** **** " + data.slice(-4); // ✅ Show only last 4 digits
                        }
                        return "N/A"; // If no card number, show "N/A"
                    }
                },
                { "data": "card_type" },
                {
                    "data": null, // Use "data": null to access the entire data row
                    "render": function (data, type, row) {
                        return row.card_expiration_month + '/' + row.card_expiration_year;
                    }
                },
                {
                    "data": "card_is_default",
                    "orderable": false, // Disable sorting for this column
                    "render": function (data, type, row) {

                        if (row.card_status_type_id == 1) {

                            // Check the status and generate the Bootstrap Switch button accordingly
                            var isChecked = (data.toLowerCase() === 'true');
                            var switchHtml = '<input type="checkbox" data-toggle="toggle" data-type="account" data-action="card-default" data-on="True" data-off="False" ' +
                                'data-onstyle="success" data-offstyle="danger" ' +
                                'data-payment-method-system-id="' + row.payment_method_id + '" ' +
                                (isChecked ? 'checked' : '') + '>';
                        } else {

                            var switchHtml = '<span class="text-muted"><i class="bi bi-x-circle me-1"></i>Card not available</span>';


                        }

                        return switchHtml;
                    }
                },
                {
                    "data": "card_status_type_name",
                    "orderable": false, // Disable sorting for this column
                    "render": function (data, type, row) {

                        if (row.card_status_type_id == 1) {
                            // Check the status and generate the Bootstrap Switch button accordingly
                            var isChecked = (data.toLowerCase() === 'active');
                            var switchHtml = '<input type="checkbox" data-toggle="toggle" data-type="account" data-action="activation" data-on="Active" data-off="Inactive" ' +
                                'data-onstyle="success" data-offstyle="danger" ' +
                                'data-payment-method-system-id="' + row.payment_method_id + '" ' +
                                (isChecked ? 'checked' : '') + '>';
                        } else {

                            var switchHtml = '<span class="text-muted"><i class="bi bi-slash-circle me-1"></i>Detached</span>';



                        }
                        return switchHtml;
                    }
                },
                /*{
                    "data": null,
                    "orderable": false, // Disable sorting for this column
                    "render": function (data, type, row) {
                        return `<button type="button" style="border-radius: 12px; border: medium; font-weight: 500;"
                                    class="__btn__view__payment__method btn-yellow btn-contact px-md-4 d-flex align-items-center align-center gap-1  btn btn-primary btn-sm"
                                    data-payment-method-id="${row.payment_method_id}"
                                    data-first-name="${row.card_holder_first_name}"
                                    data-email-address="${row.card_holder_email}"
                                    data-phone="${row.card_holder_phone}"
                                    data-address="${row.card_billing_address}"
                                    data-zip="${row.card_billing_zip}"
                                    data-card-type="${row.card_type}"
                                    data-card-number="${row.card_holder_card_number}"
                                    data-exp-month="${row.card_expiration_month}"
                                    data-exp-year="${row.card_expiration_year}">
                                    <span>
                                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 16 16" height="20" width="20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z">
                                            </path>
                                            <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"></path>
                                        </svg>
                                    </span><span class="__btn__update__payment__method__text">Edit Card</span>
                                </button>`;
                    }
                },*/
            ],
            "order": [[1, 'desc']], // Set the combined name column as default sort
            "initComplete": function (settings, json) {

            },
            "drawCallback": function (settings) {
                // Initialize Bootstrap Switch for new elements
                $('[data-toggle="toggle"]').bootstrapSwitch();

                // Handle switch change event for elements with data-type='card'
                $('[data-toggle="toggle"][data-action="activation"]').on('switchChange.bootstrapSwitch', function (event, state) {
                    var paymentMethodId = $(this).data('payment-method-system-id');
                    var status = state ? 'Active' : 'Inactive';

                    let actionMessage = status.toLowerCase() === "active"
                        ? "You are about to <strong>attached</strong> this card. Do you want to proceed?"
                        : "You are about to <strong>detach</strong> this card. Do you want to continue?";

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
                                url: SITE_URL + 'api/users/storage-facility/payment-method/update-status',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    payment_method_id: paymentMethodId,
                                    payment_method_status_name: status
                                }),
                                beforeSend: function () {

                                },
                                success: function (response) {

                                    if (response.status === "success") {

                                        showNotification(response.message, "success");
                                        paymentMethodListTable();

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
                                    $('[data-toggle="toggle"][data-action="activation"][data-payment-method-system-id="' + paymentMethodId + '"]').bootstrapSwitch('toggleState', true);


                                    showNotification(errorMessage, "error");
                                }
                            });

                        } else {
                            // User clicked No or closed the dialog, revert the switch state

                            $('[data-toggle="toggle"][data-action="activation"][data-payment-method-system-id="' + paymentMethodId + '"]').bootstrapSwitch('toggleState', true);

                        }
                    });
                });


                // Handle switch change event for elements with data-type='card'
                $('[data-toggle="toggle"][data-action="card-default"]').on('switchChange.bootstrapSwitch', function (event, state) {
                    var paymentMethodId = $(this).data('payment-method-system-id');
                    var status = state ? 'Yes' : 'No';

                    let actionMessage = status.toLowerCase() === "yes"
                        ? "You are about to <strong>default</strong> this card. Do you want to proceed?"
                        : "You are about to <strong>delete</strong> this card from default. Do you want to continue?";

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
                                url: SITE_URL + 'api/users/storage-facility/payment-method/update-card-status',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    payment_method_id: paymentMethodId,
                                    payment_method_status_name: status
                                }),
                                beforeSend: function () {
                                    showUniversalLoaderForTable('payment-method-table');
                                },
                                success: function (response) {

                                    hideUniversalLoader();

                                    if (response.status === "success") {

                                        showNotification(response.message, "success");
                                        paymentMethodListTable();

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
                                    $('[data-toggle="toggle"][data-action="card-default"][data-payment-method-system-id="' + paymentMethodId + '"]').bootstrapSwitch('toggleState', true);


                                    showNotification(errorMessage, "error");

                                    hideUniversalLoader();
                                }
                            });

                        } else {
                            // User clicked No or closed the dialog, revert the switch state

                            $('[data-toggle="toggle"][data-action="card-default"][data-payment-method-system-id="' + paymentMethodId + '"]').bootstrapSwitch('toggleState', true);

                        }
                    });
                });


            },
            "rowCallback": function (row, data, index) {
                // Add click event to the entire row except for the buttons
                $(row).on('click', function (event) {
                    // Check if the clicked element is not a button or a link
                    if (!$(event.target).is('button') && !$(event.target).is('a')) {


                    }
                });

                // Add hover effect
                $(row).hover(
                    function () {
                        $(this).addClass('table-hover');
                    },
                    function () {
                        $(this).removeClass('table-hover');
                    }
                ).css('cursor', 'pointer');
            },
        });


        /*$(document).on("click", ".__btn__view__payment__method", function () {

            let formContainer = $(".__table__payment_method");

            // Extract values from `data-` attributes
            let paymentMethodId = $(this).data("payment-method-id");
            let firstName = $(this).data("first-name");
            let lastName = $(this).data("last-name");
            let email = $(this).data("email-address");
            let phone = $(this).data("phone");
            let address = $(this).data("address");
            let zip = $(this).data("zip");
            let cardType = $(this).data("card-type");
            let expMonth = $(this).data("exp-month");
            let expYear = $(this).data("exp-year");



            // Toggle form: If exists → Remove; Else → Append
            if ($(".__update__payment_method__form").length > 0 || $(".__new__payment_method_add__form").length > 0) {
                $(".__update__payment_method__form").slideUp(300, function () {
                    $(this).remove();
                    // Find the button where `data-payment-method-id` matches the selected ID
                    $(`.__btn__update__payment__method__text`).text("Edit Card");
                    $(".__btn__add__new__payment__method__text").text("Add New");
                });
                $(".__new__payment_method_add__form").slideUp(300, function () {
                    $(this).remove();
                    $(".__btn__add__new__payment__method__text").text("Add New");
                    // Find the button where `data-payment-method-id` matches the selected ID
                    $(`.__btn__update__payment__method__text`).text("Edit Card");
                });
            } else {
                let formHTML = `
                <div class="mt-5 mb-5 __update__payment_method__form">
                    <form id="paymentMethodUpdateForm">
                        <div class="row">

                       <input type="hidden" id="payment_method_id" value="${paymentMethodId}" class="input-secondary text-300">

                            <div class="mt-4 col-lg-8">
                                <label class="input-label-secondary">Card Holder Name *</label>
                                <div class="input-field-container">
                                    <input type="text" placeholder="Enter your  name" id="firstname" name="firstname" value="${firstName}" class="input-secondary text-300">
                                </div>
                            </div>
                            <div class="mt-4 col-lg-4">
                                <label class="input-label-secondary">Card Holder Email *</label>
                                <div class="input-field-container">
                                    <input type="email" placeholder="Enter your email" id="email_address" name="email_address" value="${email}" class="input-secondary text-300">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mt-4 col-lg-4">
                                <label class="input-label-secondary">Card Holder Phone *</label>
                                <div class="input-field-container">
                                    <input type="tel" placeholder="Enter your phone number" id="phone" name="phone" value="${phone}" class="input-secondary text-300">
                                </div>
                            </div>
                            <div class="mt-4 col-lg-4">
                                <label class="input-label-secondary">Card Billing Address *</label>
                                <div class="input-field-container">
                                    <input type="text" placeholder="Enter your billing address" id="address" name="address"  value="${address}"  class="input-secondary text-300">
                                </div>
                            </div>
                            <div class="mt-4 col-lg-4">
                                <label class="input-label-secondary">Card Billing ZIP *</label>
                                <div class="input-field-container">
                                    <input type="text" placeholder="Enter your billing ZIP" id="zip" name="zip" value="${zip}"  class="input-secondary text-300" required>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                         <div class="mt-4 col-lg-4">
                             <label class="input-label-secondary">Card Number *</label>
                            <div class="input-field-container">
                                <div id="card_number" class="input-secondary text-300" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></div>
                            </div>
                         </div>

                         <div class="mt-4 col-lg-4">
                             <label class="input-label-secondary">Card Expiration *</label>
                             <div class="input-field-container">
                                <div id="card_expiry" class="input-secondary text-300" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></div>
                            </div>
                         </div>

                         <div class="mt-4 col-lg-4">
                             <label class="input-label-secondary">Card Security Code *</label>
                             <div class="input-field-container">
                                <div id="card_security_code" class="input-secondary text-300" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></div>
                            </div>
                         </div>

                     </div>

                         <div style="width: max-content;" class="mt-4 mx-auto col">
                            <button type="submit" class="w-100 btn-yellow btn-update-payment-method btn-contact text-uppercase px-md-4 btn btn-primary btn-md">
                                Update Card Information
                            </button>
                        </div>
                    </form>
                </div>`;

                formContainer.append(formHTML);

                mountStripeElements();


                $(".__update__payment_method__form").hide().slideDown(300);
                $(`.__btn__update__payment__method__text`).text("Edit Card");
                $(`button[data-payment-method-id="${paymentMethodId}"] .__btn__update__payment__method__text`).text("Hide");
            }

        });*/

    }

    paymentMethodListTable();


    $(document).on("click", ".__btn__add__new__payment__method", function () {
        $('#addCardModal').modal('show');
    });

    $('#addCardModal').on('shown.bs.modal', function () {
        mountStripeElements();
    });

    $('#addCardModal').on('hidden.bs.modal', function () {
        $("#paymentMethodAddForm")[0].reset();
        if (typeof cardNumber !== 'undefined' && cardNumber) cardNumber.clear();
        if (typeof cardExpiry !== 'undefined' && cardExpiry) cardExpiry.clear();
        if (typeof cardCvc !== 'undefined' && cardCvc) cardCvc.clear();
    });


    $(document).on("submit", "#paymentMethodAddForm", async function (e) {
        e.preventDefault();

        // Validate input fields
        let firstname = $("#firstname").val().trim();
        let email = $("#email_address3").val().trim();
        let phone = $("#phone").val().trim();
        let address = $("#address").val().trim();
        let zip = $("#zip").val().trim();


        // Validate required fields
        if (!firstname || !email || !phone || !address || !zip) {
            showNotification("All fields are required.", "error");
            return;
        }

        // ✅ 2. Validate Email Format
        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailPattern.test(email)) {
            showNotification("Invalid email format. Please enter a valid email.", "error");
            return false;
        }

        // ✅ 3. Validate Phone Number (Digits Only, Min 7, Max 15)
        let phonePattern = /^[0-9]{7,15}$/;
        if (!phonePattern.test(phone)) {
            showNotification("Invalid phone number. Enter 7 to 15 digits only.", "error");
            return false;
        }

        // ✅ 4. Validate ZIP Code (Digits Only)
        let zipPattern = /^[0-9]{4,6}$/;
        if (!zipPattern.test(zip)) {
            showNotification("Invalid ZIP code. Only digits allowed (5-6 digits).", "error");
            return false;
        }

        if (cardNumberValidate == 0) {
            showNotification("Card number must be 16 digits.", "error");
            return false;
        }

        if (cardExpireDateValidate == 0) {
            showNotification("Invalid expiration date.", "error");
            return false;
        }

        if (cardCVCValidate == 0) {
            showNotification("CVV must be 3 digits.", "error");
            return false;
        }


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

            // 2️⃣ Create SetupIntent
            const setup = await create_setup_intent(customer_id);
            if (!setup.success) {
                showNotification(setup.message, "error");
                return;
            }

            // 3️⃣ Confirm card with Stripe Elements
            const result = await stripe.confirmCardSetup(setup.client_secret, {
                payment_method: {
                    card: cardNumber,
                    billing_details: {
                        name: `${firstname}`,
                        email: email,
                        phone: phone,
                        address: {
                            line1: address,
                            postal_code: zip
                        }
                    }
                }
            });

            if (result.error) {

                hideSpinner();

                showNotification("Card confirmation failed: " + result.error.message, "error");
                return;
            }

            const payment_method_id = result.setupIntent.payment_method;

            const attach = await attach_payment_method(customer_id, payment_method_id);

            if (attach.success) {
                showNotification(attach.message, "success");

                $("#paymentMethodAddForm")[0].reset();
                cardNumber.clear();
                cardExpiry.clear();
                cardCvc.clear();


                $('#addCardModal').modal('hide');
                $("#paymentMethodAddForm")[0].reset();
                if (typeof cardNumber !== 'undefined' && cardNumber) cardNumber.clear();
                if (typeof cardExpiry !== 'undefined' && cardExpiry) cardExpiry.clear();
                if (typeof cardCvc !== 'undefined' && cardCvc) cardCvc.clear();


                paymentMethodListTable();

            } else {

                showNotification("Failed to attach card: " + attach.message, "error");
            }

            hideSpinner();


        } catch (err) {

            hideSpinner();

            showNotification("Something went wrong: " + err.message, "error");

        }
    });



    /*
        // Submit Form with AJAX
        $(document).on("submit", "#paymentMethodAddForm", function (e) {
            e.preventDefault();

            // Validate input fields
            let firstname = $("#firstname").val().trim();
            let lastname = $("#lastname").val().trim();
            let email = $("#email_address").val().trim();
            let phone = $("#phone").val().trim();
            let address = $("#address").val().trim();
            let zip = $("#zip").val().trim();
            let card_type = $("#card_type").val().trim();
            let card_number = $("#card_number").val().trim();
            let card_security_code = $("#card_security_code").val().trim();
            let card_expiration_month = $("#card_expiration_month").val().trim();
            let card_expiration_year = $("#card_expiration_year").val().trim();


            // ✅ 1. Required Fields Check
            if (!firstname || !lastname || !email || !phone || !address || !zip || !card_type || !card_number || !card_security_code || !card_expiration_month || !card_expiration_year) {
                showNotification("All fields are required. Please fill in the missing details.", "error");
                return false;
            }

            // ✅ 2. Validate Email Format
            let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailPattern.test(email)) {
                showNotification("Invalid email format. Please enter a valid email.", "error");
                return false;
            }

            // ✅ 3. Validate Phone Number (Digits Only, Min 7, Max 15)
            let phonePattern = /^[0-9]{7,15}$/;
            if (!phonePattern.test(phone)) {
                showNotification("Invalid phone number. Enter 7 to 15 digits only.", "error");
                return false;
            }

            // ✅ 4. Validate ZIP Code (Digits Only)
            let zipPattern = /^[0-9]{5,6}$/;
            if (!zipPattern.test(zip)) {
                showNotification("Invalid ZIP code. Only digits allowed (5-6 digits).", "error");
                return false;
            }

            // ✅ Validate Card Number (Only Digits, Exactly 20 digits)
            let cardNumberPattern = /^[0-9]{20}$/;  // Only allows exactly 20 digits
            if (!cardNumberPattern.test(card_number)) {
                showNotification("Invalid card number. It must be exactly 20 digits.", "error");
                return false;
            }


            // ✅ 6. Validate CVV (Only Digits, 3 or 4 digits)
            let cvvPattern = /^[0-9]{3}$/;
            if (!cvvPattern.test(card_security_code)) {
                showNotification("Invalid CVV. Enter a valid 3 digit CVV.", "error");
                return false;
            }

            // ✅ 7. Validate Expiration Date (Must be in the Future)
            let currentYear = new Date().getFullYear();
            let currentMonth = new Date().getMonth() + 1; // JavaScript months start from 0 (Jan = 0)

            let expMonth = parseInt(card_expiration_month, 10);
            let expYear = parseInt(card_expiration_year, 10);

            if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
                showNotification("Invalid expiration date. The card must expire in the future.", "error");
                return false;
            }


            // ✅ Prepare Data for JSON POST Request
            let formData = {
                firstname: firstname,
                lastname: lastname,
                email: email,
                phone: phone,
                address: address,
                zip: zip,
                card_type: card_type,
                card_number: card_number,
                card_security_code: card_security_code,
                card_expiration_month: card_expiration_month,
                card_expiration_year: card_expiration_year
            };

            $.ajax({
                url: SITE_URL + "api/users/storage-facility/payment-method/add",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(formData),
                dataType: "json",
                beforeSend: function () {
                    // Show loading button
                    $(".btn-add-new-payment-method").html('<span class="spinner-border spinner-border-sm"></span> Saving...').attr("disabled", true);
                },
                success: function (response) {
                    $(".btn-add-new-payment-method").text("Save Card Information").prop("disabled", false);

                    if (response.status === "success") {

                        showNotification(response.message, "success");

                        $("#paymentMethodAddForm")[0].reset();
                        $(".__new__payment_method_add__form").slideUp(300, function () {
                            $(this).remove();
                            $(".__btn__add__new__payment__method__text").text("Add New");
                            $(".__btn__update__payment__method__text").text("Edit Card");
                        });

                        paymentMethodListTable();
                    } else {

                        showNotification(response.message, "error");
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {

                    $(".btn-add-new-payment-method").text("Save Card Information").prop("disabled", false);

                    let errorMessage = "Something went wrong. Try again.";

                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMessage = jqXHR.responseJSON.message;
                    }

                    showNotification(errorMessage, "error");
                }
            });
        });

        */

    // Submit Form with AJAX
    $(document).on("submit", "#paymentMethodUpdateForm", function (e) {
        e.preventDefault();

        // Validate input fields
        let payment_method_id = $("#payment_method_id").val().trim();
        let firstname = $("#firstname").val().trim();
        let lastname = $("#lastname").val().trim();
        let email = $("#email_address3").val().trim();
        let phone = $("#phone").val().trim();
        let address = $("#address").val().trim();
        let zip = $("#zip").val().trim();
        let card_type = $("#card_type").val().trim();
        let card_number = $("#card_number").val().trim();
        let card_security_code = $("#card_security_code").val().trim();
        let card_expiration_month = $("#card_expiration_month").val().trim();
        let card_expiration_year = $("#card_expiration_year").val().trim();

        // ✅ 1. Required Fields Check
        if (!firstname || !lastname || !email || !phone || !address || !zip || !card_type || !card_number || !card_security_code || !card_expiration_month || !card_expiration_year) {
            showNotification("All fields are required. Please fill in the missing details.", "error");
            return false;
        }

        // ✅ 2. Validate Email Format
        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailPattern.test(email)) {
            showNotification("Invalid email format. Please enter a valid email.", "error");
            return false;
        }

        // ✅ 3. Validate Phone Number (Digits Only, Min 7, Max 15)
        let phonePattern = /^[0-9]{7,15}$/;
        if (!phonePattern.test(phone)) {
            showNotification("Invalid phone number. Enter 7 to 15 digits only.", "error");
            return false;
        }

        // ✅ 4. Validate ZIP Code (Digits Only)
        let zipPattern = /^[0-9]{4,6}$/;
        if (!zipPattern.test(zip)) {
            showNotification("Invalid ZIP code. Only digits allowed (5-6 digits).", "error");
            return false;
        }

        // ✅ Validate Card Number (Only Digits, Exactly 20 digits)
        let cardNumberPattern = /^[0-9]{20}$/;  // Only allows exactly 20 digits
        if (!cardNumberPattern.test(card_number)) {
            showNotification("Invalid card number. It must be exactly 20 digits.", "error");
            return false;
        }


        // ✅ 6. Validate CVV (Only Digits, 3 or 4 digits)
        let cvvPattern = /^[0-9]{3}$/;
        if (!cvvPattern.test(card_security_code)) {
            showNotification("Invalid CVV. Enter a valid 3 digit CVV.", "error");
            return false;
        }

        // ✅ 7. Validate Expiration Date (Must be in the Future)
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth() + 1; // JavaScript months start from 0 (Jan = 0)

        let expMonth = parseInt(card_expiration_month, 10);
        let expYear = parseInt(card_expiration_year, 10);

        if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
            showNotification("Invalid expiration date. The card must expire in the future.", "error");
            return false;
        }


        // ✅ Prepare Data for JSON POST Request
        let formData = {
            payment_method_id: payment_method_id,
            firstname: firstname,
            lastname: lastname,
            email: email,
            phone: phone,
            address: address,
            zip: zip,
            card_type: card_type,
            card_number: card_number,
            card_security_code: card_security_code,
            card_expiration_month: card_expiration_month,
            card_expiration_year: card_expiration_year
        };

        showSpinner();

        $.ajax({
            url: SITE_URL + "api/users/bidder/payment-method/update",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(formData),
            dataType: "json",
            beforeSend: function () {
                // Show loading button
                $(".btn-update-payment-method").html('<span class="spinner-border spinner-border-sm"></span> Saving...').attr("disabled", true);
            },
            success: function (response) {
                $(".btn-update-payment-method").text("Save Card Information").prop("disabled", false);

                if (response.status === "success") {

                    showNotification(response.message, "success");


                    $(".__update__payment_method__form").slideUp(300, function () {
                        $(this).remove();
                        $(".__btn__add__new__payment__method__text").text("Add New");
                        $(".__btn__update__payment__method__text").text("Edit Card");
                    });

                    paymentMethodListTable();
                } else {

                    showNotification(response.message, "error");
                }

                hideSpinner();
            },
            error: function (jqXHR, textStatus, errorThrown) {

                $(".btn-update-payment-method").text("Update Card Information").prop("disabled", false);

                let errorMessage = "Something went wrong. Try again.";

                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }

                showNotification(errorMessage, "error");

                hideSpinner();
            }
        });
    });

    // Function to Show Bootstrap Alert Message
    function showAlert(type, message) {
        let alertBox = `
            <div class="alert alert-${type === "success" ? "success" : "danger"} alert-dismissible fade show text-center" role="alert"
                style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;

        $("body").append(alertBox);
        setTimeout(() => $(".alert").alert("close"), 3000);
    }
});

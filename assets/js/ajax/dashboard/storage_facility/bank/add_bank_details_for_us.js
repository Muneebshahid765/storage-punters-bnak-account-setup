$(document).on("submit", "#bank-setup-add-form", async function (e) {
    e.preventDefault();

    $(".error-message").remove(); // Remove existing errors
    let isValid = true;

    let formData = {
        account_holder_name: $("#account_holder_name").val().trim(),
        routing_number: $("#routing_number").val().trim(),
        account_number: $("#account_number").val().trim(),
        account_type: $("#account_type").val(),
        country: $("#country").val().trim().toUpperCase(),
        currency: $("#currency").val().trim().toLowerCase()
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
    if (formData.country === "AU" && !/^\d{6}$/.test(formData.routing_number)) {
        showError("routing_number", "Routing number must be 6 digits (BSB).");
    }

    // ✅ Account Number: 6–9 digits
    if (!/^\d{6,9}$/.test(formData.account_number)) {
        showError("account_number", "Account number must be 6–9 digits.");
    }

    if (!isValid) return; // ⛔ Stop if validation fails


    // showNotification("Thanks for your interest! We're actively working on this feature and will release it soon.", "success");


    try {
        console.log("Creating token with:", formData);

        // 1️⃣ Create Stripe token for bank account
        const result = await stripe.createToken("bank_account", {
            country: formData.country,
            currency: formData.currency,
            routing_number: formData.routing_number,
            account_number: formData.account_number,
            account_holder_name: formData.account_holder_name,
            account_holder_type: ["individual", "company"].includes(formData.account_holder_type)
                ? formData.account_holder_type
                : "individual"
        });

        console.log("Stripe result:", result);

        if (result.error) {
            showNotification("Stripe error: " + result.error.message, "error");
            return;
        }

        const token = result.token.id;

        // 2️⃣ Create or retrieve Stripe customer
        const { exists, customer } = await search_customer_by_email();
        let customer_id = exists ? customer.id : null;

        if (!customer_id) {
            const created = await create_stripe_customer();
            if (!created.success) {
                showNotification(created.message, "error");
                return;
            }
            customer_id = created.customer.id;
        }

        // 3️⃣ Attach bank to customer
        const attach = await attach_bank_account(customer_id, token);
        if (attach.success) {
            showNotification("Bank account added successfully!", "success");
            $("#bank-setup-add-form")[0].reset();
        } else {
            showNotification("Attach failed: " + attach.message, "error");
        }

    } catch (err) {
        showNotification("Unexpected error: " + err.message, "error");
    }


});

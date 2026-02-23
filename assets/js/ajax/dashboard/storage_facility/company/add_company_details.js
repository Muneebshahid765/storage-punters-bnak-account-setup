$('#company-setup-add-form').submit(function (event) {
    event.preventDefault(); // Prevent default form submission
    $('.error-message').remove();
    let isValid = true;

    // Helper: Show error message
    function showError(inputId, message) {
        $(`#${inputId}`).after(`<p class="error-message text-yellow text-error">${message}</p>`);
        isValid = false;
    }

    // Get form data
    const company_name = $("#company_name").val().trim();
    const abn = $("#abn").val().trim();
    const company_email = $("#company_email").val().trim();
    const company_website = $("#company_website").val().trim();
    const company_cell_phone = $("#cell_phone").val().trim();
    const company_street_address = $("#address").val().trim();
    const company_apt_suite = $("#apt_suite").val().trim(); // optional
    const company_city = $("#city").val().trim();
    const company_zip = $("#zip").val().trim();
    const company_country = $("#country").val();
    const company_state = $("#state").val();

    // Basic validations
    if (!company_name) showError("company_name", "Company name is required.");
    if (!abn) showError("abn", "ABN is required.");
    if (!company_email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(company_email)) {
        showError("company_email", "A valid email address is required.");
    }
    // Simplified URL validation (allow no protocol)
    const urlRegex = /^([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/;
    const protocolRegex = /^https?:\/\//i;

    let finalWebsite = company_website;
    if (company_website) {
        if (!protocolRegex.test(company_website)) {
            if (urlRegex.test(company_website)) {
                finalWebsite = "https://" + company_website;
            } else {
                showError("company_website", "Enter a valid website URL.");
            }
        } else if (!/^https?:\/\/([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/i.test(company_website)) {
            showError("company_website", "Enter a valid website URL.");
        }
    }
    if (!company_cell_phone || !/^[\d\s()+-]{6,}$/.test(company_cell_phone)) {
        showError("cell_phone", "Enter a valid phone number.");
    }
    if (!company_street_address) showError("address", "Street address is required.");
    if (!company_city) showError("city", "City is required.");
    if (!company_zip || !/^\d{4,10}$/.test(company_zip)) {
        showError("zip", "Enter a valid ZIP/postal code.");
    }
    if (!company_country) showError("country", "Country is required.");
    if (!company_state) showError("state", "State is required.");

    if (!isValid) return;

    $("#save-company-add-btn").html('<span class="spinner-border spinner-border-sm"></span> Setting up your company...').attr("disabled", true);

    // Prepare data
    const formData = {
        company_name,
        abn,
        company_email,
        company_website: finalWebsite,
        company_cell_phone,
        company_street_address,
        company_apt_suite,
        company_city,
        company_zip,
        company_country,
        company_state
    };

    // Submit data
    $.ajax({
        url: SITE_URL + 'api/companies/storage-facility/add',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        beforeSend: function () {
            console.log('Adding company details...');
        },
        success: function (response) {



            if (response.status === "success") {
                showNotification(response.message, "success");

                // Delay briefly to let user see the message, then redirect
                setTimeout(function () {
                    const userId = response.data.user_id;
                    const roleId = response.data.user_role_id;

                    // Retrieve stored data (if any)
                    const storedData = JSON.parse(localStorage.getItem('hasVisitedStorageFacility')) || {};

                    // ✅ Only go to "company/edit" if BOTH are true:
                    // 1. hasVisitedStorageFacility === true
                    // 2. userId matches current logged-in user
                    if (storedData.hasVisitedStorageFacility === true && storedData.userId === userId) {
                        // Returning user → go to company edit page
                        window.location.href = SITE_URL + 'company/edit/' + companyId;
                    } else {
                        // First time or different user → go to add storage facility
                        const visitData = {
                            hasVisitedStorageFacility: true,
                            userId: userId,
                            roleId: roleId
                        };

                        // Save visit info in localStorage
                        localStorage.setItem('hasVisitedStorageFacility', JSON.stringify(visitData));

                        // Redirect to storage facility add page
                        window.location.href = SITE_URL + 'storage-facility/add';
                    }
                }, 1000);



            } else {

                $("#save-company-add-btn").html("Add Company").attr("disabled", false);

                showNotification(response.message, "error");
            }
        },
        error: function (jqXHR) {

            $("#save-company-add-btn").html("Add Company").attr("disabled", false);

            const errorMessage = jqXHR.responseJSON?.message || "Something went wrong. Try again.";
            showNotification(errorMessage, "error");
        }
    });
});

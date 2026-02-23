let companyId = window.location.pathname.split("/").pop(); // Extract Auction ID from URL


$(document).ready(function () {
    checkUserLogin();
});


// Function to check if the user is logged in
function checkUserLogin() {
    $.ajax({
        url: SITE_URL + "api/user/checkUserLogin", // Adjust API endpoint
        type: "GET",
        dataType: "json",
        success: function (response) {
            if (response.status === true && response.user_data.isLoggedIn) {
                isUserLoggedIn = true; // User is logged in
                hasRoleId = response.user_data.roleId;
            } else {
                isUserLoggedIn = false; // User is not logged in
            }

            fetchCompanyDetails(companyId); // Proceed to fetch auction details after login check
        },
        error: function () {
            isUserLoggedIn = false; // Assume not logged in on error
            fetchCompanyDetails(companyId); // Proceed anyway
        }
    });
}

// Fetch auction details from API
function fetchCompanyDetails(companyId) {


    $.ajax({
        url: SITE_URL + "api/companies/storage-facility/get_by_company_id",
        type: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({ companyId: companyId }),
        success: function (response) {


            if (response.status === "success" && response.data) {
                let company = response.data;


                // Trim and clean data if needed
                $("#company_name").val(company.company_name);
                $("#abn").val(company.company_abn);
                $("#company_website").val(company.company_website);
                $("#company_email").val(company.company_email.replace(/[\r\n]+/g, '').trim());
                $("#address").val(company.company_street_address);
                $("#apt_suite").val(company.company_apt_suite);
                $("#city").val(company.company_city);
                $("#zip").val(company.company_zip);
                $("#country").val(company.company_country);
                $("#state").val(company.company_state);
                $("#cell_phone").val(company.company_cell_phone);

                // Set company ID in hidden field for location discovery
                $("#companyId").val(company.id);

                showNotification(response.message, "success");
            }


        },
        error: function (jqXHR, textStatus, errorThrown) {
            let errorMessage = "Something went wrong. Try again.";

            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage = jqXHR.responseJSON.message;
            }

            showNotification(errorMessage, "error");
        }
    });
}



$('#company-update-form').submit(function (event) {
    event.preventDefault(); // Prevent default form submission

    $('.error-message').remove();

    let isValid = true; // Flag to check form validity

    // Function to show validation errors
    function showError(inputId, message) {
        $(`#${inputId}`).after(`<p class="error-message text-yellow text-error">${message}</p>`);
        isValid = false;
    }


    // Get form data
    const company_name = ($("#company_name").val() || "").trim();
    const abn = ($("#abn").val() || "").trim();
    const company_email = ($("#company_email").val() || "").trim();
    const company_website = ($("#company_website").val() || "").trim();
    const company_cell_phone = ($("#cell_phone").val() || "").trim();
    const company_street_address = ($("#address").val() || "").trim();
    const company_apt_suite = ($("#apt_suite").val() || "").trim();
    const company_city = ($("#city").val() || "").trim();
    const company_zip = ($("#zip").val() || "").trim();
    const company_country = $("#country").val();
    const company_state = $("#state").val();

    // Validations
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


    $("#save-company-updates-btn").html('<span class="spinner-border spinner-border-sm"></span> Updating company...').attr("disabled", true);

    let formData = {
        companyId: companyId,
        company_name: company_name,
        abn: abn,
        company_email: company_email,
        company_website: finalWebsite,
        company_cell_phone: company_cell_phone,
        company_street_address: company_street_address,
        company_apt_suite: company_apt_suite,
        company_city: company_city,
        company_zip: company_zip,
        company_country: company_country,
        company_state: company_state
    };


    $.ajax({
        url: SITE_URL + 'api/companies/storage-facility/update', // Update with actual API endpoint
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        beforeSend: function () {
            console.log('Updating company details...');
        },
        success: function (response) {

            $("#save-company-updates-btn").html("Save Updates").attr("disabled", false);

            if (response.status === "success") {
                showNotification(response.message, "success");
            } else {
                showNotification(response.message, "error");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {

            $("#save-company-updates-btn").html("Save Updates").attr("disabled", false);


            let errorMessage = "Something went wrong. Try again.";

            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage = jqXHR.responseJSON.message;
            }

            showNotification(errorMessage, "error");
        }
    });
});


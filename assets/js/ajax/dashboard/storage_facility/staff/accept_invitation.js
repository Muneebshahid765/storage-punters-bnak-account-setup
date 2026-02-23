$(document).ready(function () {

    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const companyId = $("#company_id").val();
    const userId = $("#user_id").val();

    const $spinner = $('#spinner-overlay');

    function showSpinner() { $spinner.show(); }
    function hideSpinner() { $spinner.hide(); }


    // Fetch auction details from API
    function fetchCompanyDetails(company_id, user_id) {

        showSpinner(); // Show global spinner

        $.ajax({
            url: SITE_URL + "companies/get_by_company_id",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ companyId: company_id, userId: user_id }),
            success: function (response) {
                hideSpinner();


                if (response.status === "success" && response.data) {
                    $(".company_name").text(response.data.company_name);
                } else {
                    $(".company_name").text("Unknown Company");
                }

            },
            error: function (jqXHR, textStatus, errorThrown) {
                let errorMessage = "Something went wrong. Try again.";

                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                $(".company_name").text("Error loading company");
                showNotification(errorMessage, "error");
                hideSpinner();

            }
        });
    }

    // Call on page load
    fetchCompanyDetails(companyId, userId);

    // Accept button
    $("#btnAccept").on("click", function () {

        showSpinner(); // Show spinner when button clicked

        const password = $("#password").val();
        const confirmPassword = $("#confirm_password").val();

        if (!password || !confirmPassword) {
            hideSpinner();
            showNotification("Please set and confirm your password.", "error");
            return;
        }

        if (password !== confirmPassword) {
            hideSpinner();
            showNotification("Passwords do not match.", "error");
            return;
        }

        $.ajax({
            url: SITE_URL + "api/users/invitation/accept",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ userId: userId, companyId: companyId, token: token, password: password }),
            success: function (response) {


                if (response.status === "success") {
                    showNotification("Invitation accepted and account activated!", "success");

                    setTimeout(function () {
                        window.location.href = SITE_URL + "storage-facility/dashboard";
                    }, 1500); //

                } else {

                    hideSpinner(); // Hide spinner after success

                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {

                hideSpinner(); // Hide spinner on error

                let errorMessage = "Something went wrong while accepting invitation. Try again.";

                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }

                showNotification(errorMessage, "error");
            }
        });
    });


    // Accept button
    $("#btnCancel").on("click", function () {

        showSpinner(); // Show spinner when button clicked

        $.ajax({
            url: SITE_URL + "api/users/invitation/cancel",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ userId: userId, companyId: companyId, token: token }),
            success: function (response) {

                hideSpinner(); // Hide spinner after success

                if (response.status === "success") {

                    showNotification(response.message, "success");

                    setTimeout(function () {
                        window.location.href = SITE_URL;
                    }, 2000); //
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {

                hideSpinner(); // Hide spinner after success

                let errorMessage = "Something went wrong while accepting invitation. Try again.";

                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }

                showNotification(errorMessage, "error");
            }
        });
    });


});
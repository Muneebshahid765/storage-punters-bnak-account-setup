$(document).ready(function () {
    $("#forgot_password_form").submit(function (event) {
        event.preventDefault(); // Prevent form submission
        // Validate inputs
        let email = $("#email_address").val().trim();

        if (email === "") {
            showNotification("Email address is required.", "error");
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showNotification("Invalid email format.", "error");
            return;
        }

        // Show loading button
        $(".btn-forgot-password").html('<span class="spinner-border spinner-border-sm"></span> Sending...').attr("disabled", true);

        // Prepare data
        let formData = {
            email: email,
        };

        // AJAX Request
        $.ajax({
            url: SITE_URL + "email/process-forgot-password", // Replace with actual API endpoint
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(formData),
            dataType: "json",
            success: function (response) {
                $(".btn-forgot-password").html("Reset Password and Send Link").attr("disabled", false);

                if (response.success == true) {
                    showNotification("Password reset link sent! Please check your email.", "success");
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(".btn-forgot-password").html("Reset Password and Send Link").attr("disabled", false);

                let errorMessage = "Something went wrong. Try again.";

                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.responseText) {
                    try {
                        let response = JSON.parse(jqXHR.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        console.error("Error parsing response:", e);
                    }
                }

                showNotification(errorMessage, "error");
                console.error("AJAX Error:", textStatus, errorThrown);
            }
        });
    });
});

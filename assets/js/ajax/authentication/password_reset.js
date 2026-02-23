$(document).ready(function () {
    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const $spinner = $('#spinner-overlay');

    function showSpinner() { $spinner.show(); }
    function hideSpinner() { $spinner.hide(); }

    if (!token) {
        showNotification("Invalid or missing token. Please use the password reset link from your email.", "error");
        return;
    }

    $("#reset-password-form").submit(function (event) {
        event.preventDefault(); // Prevent form submission

        $('.error-message').remove();
        let valid = true;

        // Get input values
        let newPassword = $("#new_password").val().trim();
        let passwordConfirmation = $("#password_confirmation").val().trim();

        const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=])[A-Za-z\d!@#$%^&*()_\-+=]{8,}$/;

        if (!passwordRegex.test(newPassword)) {
            $('#new_password').after(
                `<p class="error-message text-yellow text-error">
                    Password must be at least 8 characters long and include at least one uppercase letter, one number, and one special character.
                </p>`
            );
            valid = false;
        }

        if (newPassword !== passwordConfirmation) {
            $('#password_confirmation').after(
                `<p class="error-message text-yellow text-error">Passwords do not match. Please try again.</p>`
            );
            valid = false;
        }

        if (!valid) {
            return;
        }

        $(".passsword-reset-btn").html('<span class="spinner-border spinner-border-sm"></span> Updating...').attr("disabled", true);

        let formData = {
            new_password: newPassword,
            confirm_password: passwordConfirmation,
            token: token
        };

        showSpinner(); // Show global spinner

        // First AJAX call: reset password
        $.ajax({
            url: SITE_URL + "api/users/account/process-reset-password",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(formData),
            dataType: "json",
            success: function (response) {
                $(".passsword-reset-btn").html("Reset Password").attr("disabled", false);

                if (response.success === true) {

                    // showNotification("Your password has been successfully reset.", "success");

                    // Second AJAX call: send confirmation email
                    if (response.data && response.data.email && response.data.name) {
                        $.ajax({
                            url: SITE_URL + "email/process-password-reset-success",
                            type: "POST",
                            contentType: "application/json",
                            data: JSON.stringify({
                                email: response.data.email,
                            }),
                            success: function (emailResponse) {

                                hideSpinner();

                                if (emailResponse.success) {
                                    showNotification("Your password has been successfully reset. " + emailResponse.message, "success");

                                    // Redirect after short delay
                                    setTimeout(() => {
                                        window.location.href = SITE_URL;
                                    }, 3000); // 2-second delay to let the user see the notification
                                } else {
                                    showNotification(emailResponse.message, "error");
                                }
                            },
                            error: function () {
                                console.error("Email sending failed.");

                            }
                        });
                    }
                } else {
                    showNotification(response.message || "Reset failed. Try again.", "error");
                    hideSpinner();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(".passsword-reset-btn").html("Reset Password").attr("disabled", false);

                let errorMessage = "Something went wrong. Please try again.";

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
                hideSpinner();
            }
        });
    });
});

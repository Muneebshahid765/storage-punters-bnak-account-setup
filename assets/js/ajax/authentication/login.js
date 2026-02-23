$(document).ready(function () {
    $("#login_form2").submit(function (event) {
        event.preventDefault(); // Prevent form submission
        // Validate inputs
        let email = $("#email2").val().trim();
        let password = $("#password2").val().trim();

        if (email === "" || password === "") {
            showNotification("All fields are required. Please fill in the missing details.", "error");
            return;
        }

        // Show loading button
        $(".btn-login").html('<span class="spinner-border spinner-border-sm"></span> Logging in...').attr("disabled", true);

        // Prepare data
        let formData = {
            email: email,
            password: password
        };

        // AJAX Request
        $.ajax({
            url: SITE_URL + "auth/login", // Replace with actual API endpoint
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(formData),
            dataType: "json",
            success: function (response) {
                $(".btn-login").html("Sign In").attr("disabled", false);

                if (response.status == true) {
                    showNotification("Login successful!", "success");
                    setTimeout(() => {
                        window.location.href = response.redirect_url;
                    }, 300);
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(".btn-login").html("Sign In").attr("disabled", false);

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


    $("#login_form").submit(function (event) {
        event.preventDefault(); // Prevent form submission
        // Validate inputs
        let email = $("#email").val().trim();
        let password = $("#password").val().trim();

        if (email === "" || password === "") {
            showNotification("All fields are required. Please fill in the missing details.", "error");
            return;
        }

        // Show loading button
        $(".btn-login").html('<span class="spinner-border spinner-border-sm"></span> Logging in...').attr("disabled", true);

        // Prepare data
        let formData = {
            email: email,
            password: password
        };

        // AJAX Request
        $.ajax({
            url: SITE_URL + "auth/login", // Replace with actual API endpoint
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(formData),
            dataType: "json",
            success: function (response) {
                $(".btn-login").html("Sign In").attr("disabled", false);

                if (response.status == true) {

                    showNotification("Login successful!", "success");
                    setTimeout(() => {
                        window.location.href = response.redirect_url;
                    }, 300);
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(".btn-login").html("Sign In").attr("disabled", false);

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

$(document).ready(function () {
    function fetchUserProfile() {
        $.ajax({
            url: SITE_URL + "api/users/bidder/profile/get", // Change this to your backend URL
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            beforeSend: function () {
            },
            success: function (response) {
                if (response.status === "success") {

                    let data = response.data;

                    // Populate form fields

                    $('#firstname').prop('disabled', false).val(data.firstname).prop('disabled', true);
                    $('#lastname').prop('disabled', false).val(data.lastname).prop('disabled', true);
                    $('#dob').val(data.dob);
                    // Fix: Enable email field, set value, and disable again
                    $('#standard_email_address').prop('disabled', false).val(data.email).prop('disabled', true);

                    $('#current_password').val(data.password);
                    $('#password_confirmation').val(data.password);
                    $('#cell_phone').prop('disabled', false).val(data.cell_phone).prop('disabled', true);
                    $('#address').val(data.street_address);
                    $('#state').val(data.state);
                    $('#apt_suite').val(data.apt_suite);
                    $('#country').val(data.country);
                    $('#zip').val(data.zip);
                    $('#city').val(data.city);
                    $('#timezone').val(data.timezone);
                    $('#hear_us').val(data.hear_us);

                    showNotification(response.message, "success");

                } else {
                    showNotification(response.message, "error");
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

    // Call function to load user data on page load
    fetchUserProfile();


    $('#user-profile-form').submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        $('.error-message').remove();

        let isValid = true; // Flag to check form validity

        let formData = {
            current_password: $('#current_password').val(),
            password_confirmation: $('#password_confirmation').val()
        };

        // Function to show validation errors
        function showError(inputId, message) {
            $(`#${inputId}`).after(`<p class="error-message text-yellow text-error">${message}</p>`);
            isValid = false;
        }

        // Validate required fields
        $.each(formData, function (key, value) {
            if (value === "" && key !== "password" && key !== "password_confirmation") {
                showError(key, "This field is required.");
            }
        });


        // Validate passwords match if provided
        if (formData.current_password !== "" || formData.password_confirmation !== "") {
            if (formData.current_password.length < 6) {
                showError("current_password", "Password must be at least 6 characters.");
            }
            if (formData.current_password !== formData.password_confirmation) {
                showError("password_confirmation", "Passwords do not match.");
            }
        }

        // If form is invalid, stop AJAX call
        if (!isValid) {
            return;
        }


        $.ajax({
            url: SITE_URL + 'api/users/bidder/profile/update', // Update with actual API endpoint
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            beforeSend: function () {
                console.log('Updating user profile...');
            },
            success: function (response) {
                if (response.status === "success") {
                    showNotification(response.message, "success");

                    // Delay calling fetchUserProfile() by 3 seconds (3000 milliseconds)
                    setTimeout(function () {
                        fetchUserProfile();
                    }, 3000);

                } else {
                    showNotification(response.message, "error");
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
    });
});
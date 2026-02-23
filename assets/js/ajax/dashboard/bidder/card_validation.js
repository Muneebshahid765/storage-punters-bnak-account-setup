function cardNumberValidation() {

    cardNumberValidate = 0;

    var cardNumber = $(this).val().replace(/\D/g, ''); // Remove non-numeric characters
    cardNumber = cardNumber.substr(0, 16); // Limit to maximum of 3 digits

    if (cardNumber.length <= 16) {
        // Format card number with spaces after every 4 digits
        var formattedCardNumber = '';
        for (var i = 0; i < cardNumber.length; i++) {
            if (i < 16) { // Allow only 16 digits
                if (i > 0 && i % 4 === 0) {
                    formattedCardNumber += ' ';
                }
                formattedCardNumber += cardNumber.charAt(i);
            }
        }
        $(this).val(formattedCardNumber);

        // Show error/success messages
        if (cardNumber.length === 16) {

            cardNumberValidate = 1;

        } else {

            showNotification("Card number must be 16 digits.", "error");
            cardNumberValidate = 0;
        }
    } else {
        $(this).val(cardNumber);
    }

}


// Expiration Date Validation
function cardExpireDateValidation() {

    cardExpireDateValidate = 0;

    var expireDate = $(this).val().replace(/\D/g, ''); // Remove non-numeric characters

    // Format expiration date as MM / YY
    if (expireDate.length > 2) {
        var month = expireDate.substr(0, 2);
        var year = expireDate.substr(2, 2);

        // Validate month and year
        var currentDate = new Date();
        var currentMonth = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Convert to MM format
        var currentYear = currentDate.getFullYear().toString().substr(2, 2); // Convert to YY format

        if (month >= '01' && month <= '12' && year >= currentYear && year <= '99' && !(month < currentMonth && year == currentYear)) {
            expireDate = month + ' / ' + year;

            cardExpireDateValidate = 1;
        } else {
            expireDate = month + ' / ' + year;

            showNotification("Invalid expiration date.", "error");

            cardExpireDateValidate = 0;
        }
    }

    $(this).val(expireDate);
}

// CVV Validation
function cardCVCValidation() {

    cardCVCValidate = 0;

    var cvv = $(this).val().replace(/\D/g, ''); // Remove non-numeric characters
    cvv = cvv.substr(0, 3); // Limit to maximum of 3 digits

    // Show error/success messages
    if (cvv.length === 3) {

        cardCVCValidate = 1;

    } else {

        showNotification("CVV must be 3 digits.", "error");


        cardCVCValidate = 0;
    }

    $(this).val(cvv); // Update the input value with the limited CVV
}


function cardValidation(type, status, message) {

    if (type == "cardNumber" && status == "success") {
    } else if (type == "cardNumber" && status == "failed") {
        showNotification(message, "error");
    } else if (type == "cardExpiry" && status == "success") {
    } else if (type == "cardExpiry" && status == "failed") {
        showNotification(message, "error");
    } else if (type == "cardCvc" && status == "success") {
    } else if (type == "cardCvc" && status == "failed") {
        showNotification(message, "error");
    }
}

// Handle real-time validation errors from the card elements.
function handleCardValidationChange(event) {

    if (event.error) {
        //displayError.textContent = event.error.message;
        if (event.elementType === 'cardNumber') {
            cardNumberValidate = 0;
            cardValidation("cardNumber", "failed", event.error.message);
        } else if (event.elementType === 'cardExpiry') {
            cardExpireDateValidate = 0;
            cardValidation("cardExpiry", "failed", event.error.message);
        } else if (event.elementType === 'cardCvc') {
            cardCVCValidate = 0;
            cardValidation("cardCvc", "failed", event.error.message);
        }
    } else {
        //displayError.textContent = '';
        if (event.elementType === 'cardNumber') {
            cardNumberValidate = 1;
            cardValidation("cardNumber", "success", "");
        } else if (event.elementType === 'cardExpiry') {
            cardExpireDateValidate = 1;
            cardValidation("cardExpiry", "success", "");
        } else if (event.elementType === 'cardCvc') {
            cardCVCValidate = 1;
            cardValidation("cardCvc", "success");
        }
    }
}
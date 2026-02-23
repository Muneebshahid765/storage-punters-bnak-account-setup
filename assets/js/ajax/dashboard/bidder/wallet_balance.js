$(document).ready(function () {
    function fetchUserWalletBalance() {
        $.ajax({
            url: SITE_URL + "api/users/biddent/wallet_balance/get", // Change this to your backend URL
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            beforeSend: function () {
            },
            success: function (response) {
                if (response.status === "success") {

                    let data = response.data;
                    var newBalance = "$" + data.balance + " AUD";
                    // Populate form fields

                    $(".wallet_balance").text(newBalance);

                } else {

                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }

    // Call function to load user data on page load
    fetchUserWalletBalance();
});
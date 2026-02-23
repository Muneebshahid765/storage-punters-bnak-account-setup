function addToWatchList(auctionId) {
    // Ensure auction_id exists before making the AJAX request
    if (!auctionId) {
        console.error("Auction ID is missing.");
        return;
    }

    $.ajax({
        url: SITE_URL + "api/auctions/add_auction_to_watchlist", // Change this to your backend URL
        type: "POST",
        data: JSON.stringify({ auctionId: auctionId }), // Sending data as JSON
        contentType: "application/json",
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
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
        },
        complete: function () {
        }
    });
}
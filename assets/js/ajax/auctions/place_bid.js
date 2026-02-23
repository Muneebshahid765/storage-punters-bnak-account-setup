function addAuctionToBid(auctionId, bidAmount) {
    // Ensure auction_id exists before making the AJAX request
    if (!auctionId) {
        console.error("Auction ID is missing.");
        return;
    }

    Swal.fire({
        title: `Are you sure you want to bid $${bidAmount.toFixed(2)}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#007550',
        cancelButtonColor: '#8E4D49',
        confirmButtonText: 'Yes, place bid!'
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                url: SITE_URL + "api/auctions/add_bid_to_auction", // Change this to your backend URL
                type: "POST",
                data: JSON.stringify({ auctionId: auctionId, bidAmount: bidAmount }), // Sending data as JSON
                contentType: "application/json",
                dataType: "json",
                beforeSend: function () {
                },
                success: function (response) {
                    if (response.status === "success") {
                        showNotification(response.message, "success");

                        initialBid = bidAmount;
                        let bidInput = $("input[name='highBid']");
                        bidInput.val(`$${bidAmount.toFixed(2)}`);
                        let auctionCurrentPriceContainer = $(".auction__current_price__section");
                        auctionCurrentPriceContainer.text(bidAmount.toFixed(2));

                        // ✅ Trigger cron job after bid success using sendBeacon
                        const cronUrl = SITE_URL + "email/process-auction-outbid-by-id/" + auctionId;
                        navigator.sendBeacon(cronUrl);

                    } else {
                        showNotification(response.message, "error");

                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    let errorMessage = "Something went wrong. Try again.";

                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMessage = jqXHR.responseJSON.message;
                    }

                    if (errorMessage === "No active payment methods found.") {

                        const errorMessage = `Set up your bank details. <strong> <a href="` + SITE_URL + `show-dashboard/payment-method" style="text-decoration: underline; color: #007550;">Click Here to Setup Your Payment Method</a> </strong>`;
                        showNotification(errorMessage, "error", 10000);

                    } else {

                        showNotification(errorMessage, "error");
                    }
                }
            });

        }
    });
}
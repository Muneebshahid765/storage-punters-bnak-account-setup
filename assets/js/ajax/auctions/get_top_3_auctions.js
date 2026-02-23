function focusOnAuctionSection() {
    $("html, body").animate({
        scrollTop: $(".feature_live_storage_auction").offset().top - 120
    }, 800); // 800ms for smooth scrolling
}

$(document).ready(function () {
    setTimeout(() => {
        fetchAuctions(3); // Load auctions after 2 seconds
    }, 300);

});

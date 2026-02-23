function focusOnAuctionSection() {
    $("html, body").animate({
        scrollTop: $(".auction__all__live__section__container__grid").offset().top - 120
    }, 800); // 800ms for smooth scrolling
}

$(document).ready(function () {
    //focusOnAuctionSection(); // Instantly move to auction section

    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search_term');

    if (searchTerm) {
        $('#search_term').val(searchTerm);
    }

    setTimeout(() => {
        fetchAllAuctions(0, 16, 1); // Load auctions after 2 seconds
    }, 300);

});

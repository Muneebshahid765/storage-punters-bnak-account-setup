let auctionId = window.location.pathname.split("/").pop(); // Extract Auction ID from URL
// Get the input field
let bidInput = 0;
let platformFeeRate = 17.50; // default fallback, will be updated from API
var interval = "yearly"; // default

$(document).ready(function () {

    // Sync server time first
    syncServerTime().then(function() {
        checkUserLogin()
            .then((loggedIn) => {

                // Always fetch auction details
                return fetchAuctionDetails(auctionId).then(() => loggedIn);

            })
            .then((loggedIn) => {

                // Only fetch membership if logged in
                if (loggedIn) {
                    return fetchMembershipProduct();
                } else {
                    // Hide membership loading because user is not logged in
                    $("#bid-fee-loading").addClass("d-none");
                    return;
                }

            });
    });

});


function fetchMembershipProduct() {
    return new Promise((resolve) => {

        $.ajax({
            url: SITE_URL + "api/membership/plan/get",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ interval: interval }),

            beforeSend: function () {
                // Keep spinner visible
                $("#bid-fee-loading").removeClass("d-none");
                $("#bid-fee-content").addClass("d-none");
            },

            success: function (response) {

                if (response.status === "success") {
                    let data = response.data;

                    membershipData = data.membership;
                    userSub = data.subscriptions ? data.subscriptions[0] : null;

                    // Use dynamic platform commission from getAuctionById if available
                    // We store it in a higher scope or get it from the previous response if needed.
                    // Actually, let's update fetchAuctionDetails to set the global platformFeeRate
                    let membershipFeeRate = window.dynamicPlatformFee || 17.50;
                    let membershipName = null;
                    let messageHTML = "";


                    // -------------------------------------------
                    // ✔ Detect active membership
                    // -------------------------------------------
                    if (userSub && userSub.plan?.product_id) {

                        let subProductId = userSub.plan.product_id;

                        let matchedMembership = membershipData.find(m =>
                            m.membership.product_id === subProductId
                        );

                        if (matchedMembership) {
                            membershipName = matchedMembership.membership.name;
                            membershipFeeRate = parseFloat(matchedMembership.membership.commission_rate);

                            messageHTML = `
                                <span class="text-success fw-semibold">
                                    <i class="bi bi-check-circle-fill"></i>
                                    You are subscribed to <strong>${membershipName}</strong>
                                    (${membershipFeeRate}% fees)
                                </span>
                            `;
                        }

                    } else {
                        // ❌ NO membership
                        messageHTML = `
                            <span class="text-danger fw-semibold">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                You do not have an active membership.
                                Default platform fee applies (${membershipFeeRate}%).
                            </span>
                        `;
                    }


                    // -------------------------------------------
                    // ✔ Update Fee Everywhere
                    // -------------------------------------------
                    platformFeeRate = membershipFeeRate;
                    $(".platform-fee-rate").text(`(${platformFeeRate.toFixed(2)}%)`);


                    // -------------------------------------------
                    // ✔ Dynamic Title & Link Text
                    // -------------------------------------------

                    if (userSub && membershipFeeRate === 0) {

                        // 0% membership
                        $("#fee-title-text").html(`
                            Your Membership Platform Fee
                            <span class="text-success platform-fee-rate">
                                (${membershipFeeRate.toFixed(2)}%)
                            </span>
                        `);

                        $("#membership-link-text")
                            .text("Your fees are already reduced to 0%")
                            .css("color", "#198754"); // success green

                    } else if (userSub && membershipFeeRate > 0) {

                        // Reduced membership (10%)
                        $("#fee-title-text").html(`
                            Your Reduced Membership Platform Fee
                            <span class="text-success platform-fee-rate">
                                (${membershipFeeRate.toFixed(2)}%)
                            </span>
                        `);

                        $("#membership-link-text")
                            .text("Upgrade to remove all fees")
                            .css("color", "var(--border-active)");

                    } else {

                        // No membership
                        $("#fee-title-text").html(`
                            Bid Including Current Platform Fee
                            <span class="text-success platform-fee-rate">
                                (${membershipFeeRate.toFixed(2)}%)
                            </span>
                        `);

                        $("#membership-link-text")
                            .text("Subscribe here to reduce the fees")
                            .css("color", "var(--border-active)");
                    }


                    // Show membership message
                    $("#current-membership-label")
                        .html(messageHTML)
                        .removeClass("d-none");


                    // Hide loader → Show content
                    $("#bid-fee-loading").addClass("d-none");
                    $("#bid-fee-content").removeClass("d-none");


                    // Recalculate bid amount
                    updateBidWithFeeDisplay();
                }

                else {
                    $("#bid-fee-loading").addClass("d-none");
                }

                resolve();
            },

            error: function () {
                $("#bid-fee-loading").addClass("d-none");
                resolve();
            }
        });

    });
}





function updateBidWithFeeDisplay() {

    let bidInput = $("input[name='highBid']");

    if (!bidInput || bidInput.length === 0) return; // stop if missing

    const bidAmount = parseFloat(bidInput.val().replace(/[^0-9.]/g, "")) || 0;
    const totalWithFee = bidAmount + (bidAmount * platformFeeRate / 100);
    $("#bidTotalWithFee").text(totalWithFee.toFixed(2));
}

function checkUserLogin() {
    return new Promise((resolve) => {

        $.ajax({
            url: SITE_URL + "api/user/checkUserLogin",
            type: "GET",
            dataType: "json",

            success: function (response) {
                if (response.status === true && response.user_data.isLoggedIn) {
                    isUserLoggedIn = true;
                    hasRoleId = response.user_data.roleId;
                    resolve(true);   // USER LOGGED IN
                } else {
                    isUserLoggedIn = false;
                    resolve(false);  // USER NOT LOGGED IN
                }
            },

            error: function () {
                isUserLoggedIn = false;
                resolve(false); // also false on error
            }
        });

    });
}





// Function to format auction date
function formatAuctionDate(date, time) {
    if (!date || !time) return "N/A";

    let parsedDate = new Date(`${date} ${time}`);

    // Format options
    let options = { month: "short", day: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit", hour12: true };

    return parsedDate.toLocaleString("en-US", options);
}

// Fetch auction details from API
function fetchAuctionDetails(auctionId) {

    return new Promise((resolve) => {

        let auctionTitleContainer = $(".auction__title__section");
        let auctionContainer = $(".auction__details__section");
        let auctionDescriptionsContainer = $(".auction__descriptions__section");
        let auctionStartDateContainer = $(".auction__start_date_time__section");
        let auctionEndDateContainer = $(".auction__end_date_time__section");
        let auctionCurrentPriceContainer = $(".auction__current_price__section");
        let auctionDefaultTimeContainer = $(".auction__default__timezone");
        let auctionTagsContainer = $(".auction__tags__section");
        let auctionCountDownForSmallDevicesContainer = $(".auction__count_down__section_for_small_devices");
        let auctionCountDownForLargeDevicesContainer = $(".auction__count_down__section_for_large_devices");
        let auctionTotalViewsContainer = $(".auction__total_views__section");
        let userDynamicSectionContainer = $(".user-dynamic-section");
        let logginToBidContainer = $(".loggin-to-bid");
        let auctionNoticeToBuyersContainer = $(".bidder__notice");
        let auctionPaymentMethodsContainer = $(".auction__payment_methods__section");

        // Show Skeleton Loader
        appendSkeletonLoader(auctionContainer);

        $.ajax({
            url: SITE_URL + "api/auctions/getAuctionById",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ auctionId: auctionId, mode: "view" }),
            success: function (response) {
                $(".skeleton-loader").remove(); // Remove Skeleton Loader

                try {
                    if (response.status === "success" && response.data) {
                        let auction = response.data[0];
                        let userData = response.user_data;

                        // Set dynamic platform fee from response
                        if (response.platform_commission) {
                            window.dynamicPlatformFee = parseFloat(response.platform_commission);
                            platformFeeRate = window.dynamicPlatformFee;
                        }
                        // Get the initial bid amount directly from the auction variable
                        initialBid = parseFloat(auction.auction_max_bid_amount.toString().replace(/[^0-9.]/g, "")) || 0;
                        let isAuctionFavorite = userData.is_favorite;

                        // ===== 🖼️ Load Auction Images into Slider =====
                        const auctionImagesContainer = $("#auction-image-container");
                        const thumbnailContainer = $("#auction-thumbnail-container");

                        // Combine auction + facility images
                        let allImages = [];
                        if (auction.auction_cover_images && auction.auction_cover_images.length > 0) {
                            allImages = [...auction.auction_cover_images];
                        }
                        if (auction.storage_facility_cover_images && auction.storage_facility_cover_images.length > 0) {
                            allImages = [...allImages, ...auction.storage_facility_cover_images];
                        }

                        if (allImages.length > 0) {
                            let mainSlides = '';
                            let thumbnails = '';

                            allImages.forEach((img, index) => {
                                const imageUrl = img.path;
                                mainSlides += `
            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                <img src="${imageUrl}"
                     class="d-block w-100 rounded-3"
                     alt="Auction Image ${index + 1}"
                     style="height:400px;object-fit:cover;">
            </div>
        `;
                                thumbnails += `
            <img src="${imageUrl}"
                 class="thumbnail-img ${index === 0 ? 'active' : ''}"
                 data-bs-slide-to="${index}"
                 data-bs-target="#auctionImageCarousel">
        `;
                            });

                            auctionImagesContainer.html(mainSlides);
                            thumbnailContainer.html(thumbnails);

                            // Thumbnail interaction
                            $("#auction-thumbnail-container img").on("click", function () {
                                $("#auction-thumbnail-container img").removeClass("active");
                                $(this).addClass("active");
                            });
                        } else {
                            // Fallback image
                            auctionImagesContainer.html(`
        <div class="carousel-item active">
            <img src="${SITE_URL}assets/images/no-image.png"
                 class="d-block w-100 rounded-3"
                 alt="No Image"
                 style="height:400px;object-fit:cover;">
        </div>
    `);
                            thumbnailContainer.html("");
                        }


                        let auctionHtml = `
<div class="container-fluid px-4 py-4">

 <!-- 🔹 Row 1: Facility Info + Price -->
<div class="row align-items-center border-bottom pb-3 gy-3">

  <!-- Facility Info -->
<div class="col-12 col-md-8">
  <div class="d-flex flex-column">
    <!-- Facility Name + ID -->
    <div class="d-flex flex-wrap align-items-center mb-1">
      <h4 class="fw-bold text-dark mb-0 me-2">
        <i class="bi bi-building me-2 text-success fs-4"></i>
        ${auction.auction_storage_facility_name}
      </h4>
      <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 fs-6">
        Auction #${auction.auction_id}
      </span>
      ${auction.auction_unit_number ? `
      <span class="badge bg-success text-white border rounded-pill px-3 py-1 fs-6 ms-2">
        Unit: ${auction.auction_unit_number}
      </span>` : ''}
    </div>

    <!-- Address -->
    <p class="text-muted mb-0 fs-6">
      <i class="bi bi-geo-alt me-1 text-success"></i>
      ${auction.auction_storage_facility_street_address},
      ${auction.auction_storage_facility_city},
      ${auction.auction_storage_facility_state}
      ${auction.auction_storage_facility_postal_code}
    </p>
  </div>
</div>


  <!-- Current Price -->
  <div class="col-12 col-md-4 d-flex flex-column align-items-start align-items-md-end mt-3 mt-md-0">
    <p class="mb-1 fs-6">
      <i class="bi bi-cash-stack text-success me-1 fs-5"></i>
      <span class="fw-semibold text-secondary">Current Price</span>
    </p>
    <span class="fw-bold fs-3 text-dark">$${auction.auction_max_bid_amount}</span>
  </div>
</div>

  <!-- 🔹 Row 2: Expanded Auction Details -->
  <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-3 mt-3 text-center text-md-start">

    <div class="col">
      <div class="p-3 border rounded shadow-sm h-100">
        <p class="mb-2 text-secondary fw-semibold fs-6">
          <i class="bi bi-graph-up me-1 text-success fs-5"></i> Starting Bid
        </p>
        <p class="fw-bold text-dark fs-5 mb-0">$${auction.auction_start_bid_amount}</p>
      </div>
    </div>

    <div class="col">
      <div class="p-3 border rounded shadow-sm h-100">
        <p class="mb-2 text-secondary fw-semibold fs-6">
          <i class="bi bi-hash me-1 text-success fs-5"></i> Unit Number
        </p>
        <p class="fw-bold text-dark fs-5 mb-0">${auction.auction_unit_number || "N/A"}</p>
      </div>
    </div>

    <div class="col">
      <div class="p-3 border rounded shadow-sm h-100">
        <p class="mb-2 text-secondary fw-semibold fs-6">
          <i class="bi bi-box-seam me-1 text-success fs-5"></i> Unit Size
        </p>
        <p class="fw-bold text-dark fs-5 mb-0">${auction.auction_unit_size}</p>
      </div>
    </div>

    <div class="col">
      <div class="p-3 border rounded shadow-sm h-100">
        <p class="mb-2 text-secondary fw-semibold fs-6">
          <i class="bi bi-tags me-1 text-success fs-5"></i> Type
        </p>
        <p class="fw-bold text-dark fs-5 mb-0">${auction.auction_category_name}</p>
      </div>
    </div>

    <div class="col">
      <div class="p-3 border rounded shadow-sm h-100">
        <p class="mb-2 text-secondary fw-semibold fs-6">
          <i class="bi bi-shield-check me-1 text-success fs-5"></i> Cleaning Deposit
        </p>
        <p class="fw-bold text-dark fs-5 mb-0">
          ${auction.auction_cleaning_deposit_price ? `$${auction.auction_cleaning_deposit_price}` : "N/A"}
        </p>
      </div>
    </div>

    <div class="col">
      <div class="p-3 border rounded shadow-sm h-100">
        <p class="mb-2 text-secondary fw-semibold fs-6">
          <i class="bi bi-credit-card-2-front me-1 text-success fs-5"></i> Seller Accepts
        </p>
        <span class="badge bg-success text-white fs-6 px-3 py-2">
          ${auction.accept_card == 1 ? "Card" : (auction.accept_cash == 1 ? "Cash" : (auction.auction_cleaning_deposit_payment_type || "N/A"))}
        </span>
      </div>
    </div>

    <div class="col">
      <div class="p-3 border rounded shadow-sm h-100">
        <p class="mb-2 text-secondary fw-semibold fs-6">
          <i class="bi bi-exclamation-triangle me-1 text-success fs-5"></i> Outstanding Debt
        </p>
        <p class="fw-bold text-dark fs-5 mb-0">
          ${auction.auction_outstanding_dept ? `$${auction.auction_outstanding_dept}` : "N/A"}
        </p>
      </div>
    </div>

  </div>
</div>
`;
                        $(".auction__details__section").html(auctionHtml);


                        // Update Auction Details in UI
                        /*let auctionHtml = `
                            <div class="h-100 w-100 ps-1 ps-md-5">
                                        <div class="d-flex gap-2">
                                            <p class="mb-0 text-grey title__detail__auction" style="font-weight:bold">Current Price:</p>
                                            <p class=" text-grey title__detail__auction d-flex" style="margin-top:0;margin-bottom:0.5rem">$${auction.auction_max_bid_amount}</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <p class="mb-0 text-grey title__detail__auction" style="font-weight:bold">Unit Size:</p>
                                            <p class=" text-grey title__detail__auction d-flex" style="margin-top:0;margin-bottom:0.5rem">${auction.auction_unit_size}</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <p class="mb-0 text-grey title__detail__auction" style="font-weight:bold">Facility:</p>
                                            <p class=" text-grey title__detail__auction d-flex" style="margin-top:0;margin-bottom:0.5rem">
                                            <div class="d-flex flex-wrap "><span>${auction.auction_storage_facility_name}</span><a class="ms-1 d-flex justify-content-start align-items-center" style="font-size: 13px; color: var(--border-active); cursor: pointer; text-decoration: none;" href="${SITE_URL + "auctions/" + auction.auction_id}"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="15" width="15" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M946.5 505L560.1 118.8l-25.9-25.9a31.5 31.5 0 0 0-44.4 0L77.5 505a63.9 63.9 0 0 0-18.8 46c.4 35.2 29.7 63.3 64.9 63.3h42.5V940h691.8V614.3h43.4c17.1 0 33.2-6.7 45.3-18.8a63.6 63.6 0 0 0 18.7-45.3c0-17-6.7-33.1-18.8-45.2zM568 868H456V664h112v204zm217.9-325.7V868H632V640c0-22.1-17.9-40-40-40H432c-22.1 0-40 17.9-40 40v228H238.1V542.3h-96l370-369.7 23.1 23.1L882 542.3h-96.1z"></path>
                                                    </svg>View Facility Page</a></div>
                                            </p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <p class="mb-0 text-grey title__detail__auction" style="font-weight:bold">Location:</p>
                                            <p class=" text-grey title__detail__auction d-flex" style="margin-top:0;margin-bottom:0.5rem">${auction.auction_storage_facility_city}, ${auction.auction_storage_facility_state}</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <p class="mb-0 text-grey title__detail__auction" style="font-weight:bold">Starting Bid:</p>
                                            <p class=" text-grey title__detail__auction d-flex" style="margin-top:0;margin-bottom:0.5rem">$${auction.auction_start_bid_amount}</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <p class="mb-0 text-grey title__detail__auction" style="font-weight:bold">Type:</p>
                                            <p class=" text-grey title__detail__auction d-flex" style="margin-top:0;margin-bottom:0.5rem">${auction.auction_category_name}</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <p class="mb-0 text-grey title__detail__auction" style="font-weight:bold">Cleaning Deposit:</p>
                                                <p class="text-grey title__detail__auction d-flex" style="margin-top:0;margin-bottom:0.5rem">
                                                    ${auction.auction_cleaning_deposit_price && auction.auction_cleaning_deposit_payment_type
                                ? `$${auction.auction_cleaning_deposit_price}`
                                : "N/A"}
                                                </p>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <p class="mb-0 text-grey title__detail__auction" style="font-weight:bold">Seller Accepts:</p><span class="px-2 py-1" style="background-color:#41fff3;border-radius:32px;width:max-content">${auction.auction_cleaning_deposit_price && auction.auction_cleaning_deposit_payment_type
                                ? `${auction.auction_cleaning_deposit_payment_type}`
                                : "N/A"}</span>
                                        </div>



                                    </div>
                        `;

                        auctionContainer.html(auctionHtml); // Replace content with auction details*/


                        let auctionCountDownForSmallDevicesHtml = `

                 <div class="d-flex px-0 justify-content-center flex-column align-items-center ps-md-0 ">
                            <div class="__timing__box__ __Days days-${auction.auction_id}"  style="visibility: visible;">--</div>
                            <p class="text-center mt-2 ">Days</p>
                        </div>
                        <div class="d-flex justify-content-center flex-column align-items-center">
                            <div class="__timing__box__ __Hours hours-${auction.auction_id}" style="visibility: visible;">--</div>
                            <p class="text-center mt-2 ">Hours</p>
                        </div>
                        <div class="d-flex justify-content-center flex-column align-items-center ">
                            <div class="__timing__box__ __Minutes minutes-${auction.auction_id}" style="visibility: visible;">--</div>
                            <p class="text-center mt-2 ">Mins</p>
                        </div>
                        <div class="d-flex justify-content-center flex-column align-items-center ">
                            <div class="__timing__box__ __Seconds seconds-${auction.auction_id}" style="visibility: visible;">--</div>
                            <p class="text-center mt-2 ">Secs</p>
                        </div>
                `;

                        auctionCountDownForSmallDevicesContainer.html(auctionCountDownForSmallDevicesHtml); // Replace content with auction details


                        let auctionCountDownForLargeDevicesHtml = `

                  <div class="ps-0  col-3">
                                <div class="__timing__box__ __Days days-${auction.auction_id}"  style="visibility: visible;">--</div>
                                <p class="text-center mt-2 ">Days</p>
                            </div>
                            <div class="col-3">
                                <div class="__timing__box__ __Hours hours-${auction.auction_id}" style="visibility: visible;">--</div>
                                <p class="text-center mt-2 ">Hours</p>
                            </div>
                            <div class="col-3">
                                <div class="__timing__box__ __Minutes minutes-${auction.auction_id}" style="visibility: visible;">--</div>
                                <p class="text-center mt-2 ">Minutes</p>
                            </div>
                            <div class="col-3">
                                <div class="__timing__box__ __Seconds seconds-${auction.auction_id}" style="visibility: visible;">--</div>
                                <p class="text-center mt-2 ">Seconds</p>
                            </div>
               `;

                        auctionCountDownForLargeDevicesContainer.html(auctionCountDownForLargeDevicesHtml); // Replace content with auction details


                        let userDynamicSectionHtml = "";


                        if (isUserLoggedIn == false) {
                            userDynamicSectionHtml = `<p class="text-center w-100 p-3 border-warning-subtle mt-0 mt-md-3 bg-warning-subtle fw-bold fs-5">You must be logged in to bid</p>`;

                            logginToBidContainer.addClass("w-100");

                        } else {

                            if (hasRoleId != 2) {

                                userDynamicSectionHtml = `
                        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
                            <strong>Access Denied:</strong> You do not have permission to place bids on this auction due to your account role.
                        </div>
                    `;

                                logginToBidContainer.addClass("w-100");

                            } else if (auction.auction_status_id == "2") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Auction Paused:</strong> Bidding is temporarily suspended. Please check back soon.
        </div>
    `;

                                logginToBidContainer.addClass("w-100");

                            } else if (auction.auction_status_id == "3") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Scheduled Auction:</strong> This auction hasn’t started yet. Stay tuned for the live event.
        </div>
    `;

                                logginToBidContainer.addClass("w-100");

                            } else if (auction.auction_status_id == "4") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Auction Closed:</strong> This auction has ended. No further bids can be placed.
        </div>
    `;

                                logginToBidContainer.addClass("w-100");
                            } else if (auction.auction_status_id == "5") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Auction Cancelled:</strong> This auction has been cancelled by the administrator.
        </div>
    `;
                                logginToBidContainer.addClass("w-100");

                            } else if (auction.auction_status_id == "6") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Draft Auction:</strong> This auction is still being prepared and is not yet visible to bidders.
        </div>
    `;
                                logginToBidContainer.addClass("w-100");
                            } else if (auction.auction_status_id == "7") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Under Review:</strong> This auction is under administrative review and not currently available.
        </div>
    `;
                                logginToBidContainer.addClass("w-100");
                            } else if (auction.auction_status_id == "8") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Item Sold:</strong> The auction has concluded and the item has been sold to a winning bidder.
        </div>
    `;
                                logginToBidContainer.addClass("w-100");
                            } else if (auction.auction_status_id == "9") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Auction Won:</strong> A winner has been declared for this auction.
        </div>
    `;
                                logginToBidContainer.addClass("w-100");
                            } else if (auction.auction_status_id == "10") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Auction Expired:</strong> This auction has expired and is no longer active.
        </div>
    `;
                                logginToBidContainer.addClass("w-100");
                            } else if (auction.auction_status_id == "11") {
                                userDynamicSectionHtml = `
        <div class="alert alert-warning text-center w-100 p-3 mt-0 mt-md-3">
            <strong>Auction Deleted:</strong> This auction has been removed from the system.
        </div>
    `;
                                logginToBidContainer.addClass("w-100");

                            } else if (auction.auction_status_id == "1") {
                                if (isAuctionFavorite == false) {

                                    userDynamicSectionHtml = `

                   <form>
                        <div class="d-flex justify-content-start align-items-center flex-column flex-md-row gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-3">
                                <span class="d-inline-block me-auto">
                                    <div class="me-auto d-flex justify-content-centent align-items-center bid__input__container">
                                        <div class="me-auto ">
                                        <input class="bid__input bid__amount me-auto ps-5 text-200 fs-5" type="text"
                                                name="highBid" inputclassnames="text-300" required="" value="$${(parseFloat(auction.auction_max_bid_amount) + 10).toFixed(2)}"
                                                data-gtm-form-interact-field-id="0" error="add bid amount">
                                        </div>
                                        <div class="d-flex flex-column bid__btn_container">
                                                <button type="button" class="bid__input__btn btn__add_amount" style="background: var(--color-high-bid);">+10</button>

                                                <button type="button" class="bid__input__btn btn__subtract_amount" style="background-color: var(--color-high-bid); color: rgb(0, 0, 0);">-10</button>
                                        </div>
                                    </div>

                                </span>
                                <button type="button" style="border: medium; background: var(--color-high-bid);"
                                    class="me-auto responsive-btn p-3 p-md-2 d-block btn btn-primary btn-place-bid">Bid Now!
                                </button>


                            </div>

                           <!-- FEE WRAPPER -->
<div id="bid-fee-wrapper" class="w-100">

    <!-- 🔄 LOADING SPINNER -->
    <div id="bid-fee-loading"
        class="w-100 mt-3 mb-3 py-4 px-4 border rounded-4 shadow-sm bg-white
               d-flex flex-column justify-content-center align-items-center
               text-center gap-2">

        <!-- Icon -->
        <div class="text-success" style="font-size: 1.7rem;">
            <i class="bi bi-credit-card-2-front"></i>
        </div>

        <!-- Spinner -->
        <div class="spinner-border text-success"
             style="width: 2.3rem; height: 2.3rem;" role="status">
        </div>

        <!-- Loading Text -->
        <span class="fw-semibold text-dark" style="font-size: 1rem;">
            Fetching membership details…
        </span>

        <span class="text-muted" style="font-size: 0.85rem;">
            Please wait a moment while we calculate your platform fee.
        </span>
    </div>


    <!-- ✅ REAL CONTENT (Initially Hidden) -->
    <div id="bid-fee-content"
        class="bid-fee-info w-100 mt-3 mb-3 py-3 px-4 border rounded-4 shadow-sm
               d-flex d-none justify-content-between align-items-center flex-wrap
               bg-white position-relative overflow-hidden">

        <!-- Gradient Bar -->
        <div class="position-absolute top-0 start-0 w-100"
            style="height: 4px; background: linear-gradient(90deg, #007550, #007550);
                   border-radius: 10px 10px 0 0;"></div>

        <!-- Dynamic Title -->
        <div class="d-flex flex-column flex-md-row align-items-md-center
                    gap-2 text-center text-md-start">

            <span id="fee-title-text" class="fw-semibold text-dark">
                <!-- JS will replace this text dynamically -->
                Loading...
                <span class="text-success platform-fee-rate">(0.00%)</span>
            </span>
        </div>

        <!-- Fee Amount -->
        <div class="fw-bold text-success fs-5">
            $<span id="bidTotalWithFee">0.00</span>
        </div>

        <!-- Dynamic Membership Link -->
        <div class="mt-2 mt-md-0">
            <a id="membership-link-text"
               href="${SITE_URL + 'show-dashboard/membership'}"
               class="fw-medium text-decoration-underline"
               style="font-size: 0.9rem;">
                <!-- JS will replace this text -->
                Loading...
            </a>
        </div>

        <!-- Membership Status Label -->
        <div id="current-membership-label"
            class="w-100 mt-2 text-center small d-none">
        </div>

    </div>

</div>



                            <div class="d-flex gap-3 flex-row-reverse align-items-center">
                                <button type="button"
                                    class="d-flex justify-content-left justify-content-md-center align-items-center gap-1 me-auto bg-transparent text-dark border-0 responsive-btn p-1 p-md-2 btn btn-primary">
                                    <span class="d-flex align-items-center"></span>
                                    <span style="border-bottom: 1px solid black;">
                                    </span>
                                </button>
                                <div>
                                    <div class="watchlist d-flex align-items-center" style="width: max-content;">
                                        <button type="button" style="border: 2px solid black;"
                                            class="btn-add-to-watch-list bg-transparent text-dark d-flex align-items-center gap-1 responsive-btn p-1 p-md-2 btn btn-primary"
                                            <span class="d-flex align-items-center">
                                                <span
                                                    style="box-sizing: border-box; display: inline-block; overflow: hidden; width: initial; height: initial; background: none; opacity: 1; border: 0px; margin: 0px; padding: 0px; position: relative; max-width: 100%;">
                                                    <span
                                                        style="box-sizing: border-box; display: block; width: initial; height: initial; background: none; opacity: 1; border: 0px; margin: 0px; padding: 0px; max-width: 100%;">
                                                        <img style="display: block; max-width: 100%; width: initial; height: initial; background: none; opacity: 1; border: 0px; margin: 0px; padding: 0px;"
                                                            alt="" aria-hidden="true"
                                                            src="data:image/svg+xml,%3csvg%20xmlns=%27http://www.w3.org/2000/svg%27%20version=%271.1%27%20width=%2720%27%20height=%2720%27/%3e">
                                                    </span>
                                                    <img alt="watch_list"
                                                        src="${SITE_URL + "assets/images/auction_details/watchlist.svg"}" decoding="async"
                                                        data-nimg="intrinsic"
                                                        style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: medium; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%;">
                                                </span>
                                            </span>
                                            <span class="d-flex align-items-center btn-text-watch-list">Add to Watch List
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                        `;

                                } else if (isAuctionFavorite == true) {

                                    userDynamicSectionHtml = `

                     <form>
                        <div class="d-flex justify-content-start align-items-center flex-column flex-md-row gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-3">
                                <span class="d-inline-block me-auto">
                                    <div class="me-auto d-flex justify-content-centent align-items-center bid__input__container">
                                        <div class="me-auto ">
                                        <input class="bid__input bid__amount me-auto ps-5 text-200 fs-5" type="text"
                                                name="highBid" inputclassnames="text-300" required="" value="$${auction.auction_max_bid_amount}"
                                                data-gtm-form-interact-field-id="0" error="add bid amount">
                                        </div>
                                        <div class="d-flex flex-column bid__btn_container">
                                                <button type="button" class="bid__input__btn btn__add_amount" style="background: var(--color-high-bid);">+10</button>

                                                <button type="button" class="bid__input__btn btn__subtract_amount" style="background-color: var(--color-high-bid); color: rgb(0, 0, 0);">-10</button>
                                        </div>
                                    </div>
                                </span>
                                <button type="button" style="border: medium; background: var(--color-high-bid);"
                                    class="me-auto responsive-btn p-3 p-md-2 d-block btn btn-primary btn-place-bid">Bid Now!
                                </button>
                            </div>

                            <!-- Bid Including Fee -->
                            <div class="bid-fee-info w-100 mt-3 mb-3 py-3 px-4 border rounded-4 shadow-sm d-flex justify-content-between align-items-center flex-wrap bg-white position-relative overflow-hidden">

                                <!-- Gradient Accent Bar -->
                                <div class="position-absolute top-0 start-0 w-100"
                                    style="height: 4px; background: linear-gradient(90deg, #007550, #007550); border-radius: 10px 10px 0 0;"></div>

                                <!-- Left Content -->
                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 text-center text-md-start">
                                    <span class="fw-semibold text-dark">
                                    Bid Including Current Platform Fee
                                    <span class="text-success">(${(parseFloat(platformFeeRate).toFixed(2))}%)</span>
                                    </span>
                                </div>

                                <!-- Center Content (Amount) -->
                                <div class="fw-bold text-success fs-5">
                                    $<span id="bidTotalWithFee">0.00</span>
                                </div>

                                <!-- Right Content (Link) -->
                                <div class="mt-2 mt-md-0">
                                    <a href="${SITE_URL + 'show-dashboard/membership'}"
                                    class="fw-medium text-decoration-underline"
                                    style="color: var(--border-active); font-size: 0.9rem;">
                                    Subscribe here to reduce the fees
                                    </a>
                                </div>
                            </div>

                            <div class="d-flex gap-3 flex-row-reverse align-items-center">
                                <button type="button"
                                    class="d-flex justify-content-left justify-content-md-center align-items-center gap-1 me-auto bg-transparent text-dark border-0 responsive-btn p-1 p-md-2 btn btn-primary">
                                    <span class="d-flex align-items-center"></span>
                                    <span style="border-bottom: 1px solid black;">
                                    </span>
                                </button>
                                <div>
                                    <div class="watchlist d-flex align-items-center" style="width: max-content;">
                                        <button type="button" style="border: 2px solid black;"
                                            class="btn-remove-from-watch-list bg-transparent text-dark d-flex align-items-center gap-1 responsive-btn p-1 p-md-2 btn btn-primary"
                                            <span class="d-flex align-items-center">
                                                <span
                                                    style="box-sizing: border-box; display: inline-block; overflow: hidden; width: initial; height: initial; background: none; opacity: 1; border: 0px; margin: 0px; padding: 0px; position: relative; max-width: 100%;">
                                                    <span
                                                        style="box-sizing: border-box; display: block; width: initial; height: initial; background: none; opacity: 1; border: 0px; margin: 0px; padding: 0px; max-width: 100%;">
                                                        <img style="display: block; max-width: 100%; width: initial; height: initial; background: none; opacity: 1; border: 0px; margin: 0px; padding: 0px;"
                                                            alt="" aria-hidden="true"
                                                            src="data:image/svg+xml,%3csvg%20xmlns=%27http://www.w3.org/2000/svg%27%20version=%271.1%27%20width=%2720%27%20height=%2720%27/%3e">
                                                    </span>
                                                    <img alt="watch_list"
                                                        src="${SITE_URL + "assets/images/auction_details/watchlist.svg"}" decoding="async"
                                                        data-nimg="intrinsic"
                                                        style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: medium; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%;">
                                                </span>
                                            </span>
                                            <span class="d-flex align-items-center btn-text-watch-list">Remove From Watch List
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                        `;
                                }
                            }


                        }

                        if (isUserLoggedIn == false) {
                            logginToBidContainer.html(userDynamicSectionHtml); // Replace content with auction detail
                        } else {
                            if (hasRoleId != 2 || parseInt(auction.auction_status_id) != 1) {

                                logginToBidContainer.html(userDynamicSectionHtml); // Replace content with auction detail
                            } else {
                                userDynamicSectionContainer.html(userDynamicSectionHtml); // Replace content with auction detail
                            }
                        }

                        let bidInput = $("input[name='highBid']");


                        function getBidAmount() {
                            return parseFloat(bidInput.val().replace(/[^0-9.]/g, "")) || 0;
                        }

                        // Increase bid by 10 and format to 2 decimal places
                        $(".btn__add_amount").click(function () {
                            let newBid = getBidAmount() + 10;
                            bidInput.val(`$${newBid.toFixed(2)}`); // Format to 2 decimal places
                        });

                        // Decrease bid by 10 but never below the initial bid amount
                        $(".btn__subtract_amount").click(function () {
                            let newBid = getBidAmount() - 10;
                            if (newBid < initialBid) newBid = initialBid; // Prevent going below initial value
                            bidInput.val(`$${newBid.toFixed(2)}`); // Format to 2 decimal places
                        });

                        // Validate input field on change (Only allow whole numbers and check min value)
                        bidInput.on("change", function () {
                            let value = getBidAmount();

                            // If input is not a valid number or below initial bid, reset to initial bid
                            if (isNaN(value) || value < initialBid) {
                                bidInput.val(`$${initialBid.toFixed(2)}`);
                            } else {
                                bidInput.val(`$${value.toFixed(2)}`);
                            }
                        });

                        // When input is clicked, clear the text
                        bidInput.on("focus", function () {
                            $(this).val(""); // Remove existing text
                        });

                        $(document).on("click", ".btn-add-to-watch-list", function () {
                            $(this).removeClass("btn-add-to-watch-list").addClass("btn-remove-from-watch-list");
                            $(this).find(".btn-text-watch-list").text("Remove from Watch List");
                            addToWatchList(auctionId);
                        });

                        $(document).on("click", ".btn-remove-from-watch-list", function () {
                            $(this).removeClass("btn-remove-from-watch-list").addClass("btn-add-to-watch-list");
                            $(this).find(".btn-text-watch-list").text("Add to Watch List");
                            addToWatchList(auctionId);
                        });


                        $(".btn-place-bid").click(function () {
                            let bidAmount = parseFloat($("input[name='highBid']").val().replace(/[^0-9.]/g, "")) || 0;
                            addAuctionToBid(auctionId, bidAmount);

                        });

                        // When user leaves the input field, validate the entry
                        bidInput.on("blur", function () {
                            let value = getBidAmount();

                            // If input is empty or invalid, reset to initial bid value
                            if (isNaN(value) || value < initialBid) {
                                $(this).val(`$${initialBid.toFixed(2)}`);
                            } else {
                                $(this).val(`$${value.toFixed(2)}`);
                            }
                        });



                        // Update fee display whenever bid changes
                        bidInput.on("input change blur", updateBidWithFeeDisplay);
                        $(".btn__add_amount, .btn__subtract_amount").on("click", updateBidWithFeeDisplay);

                        if (isUserLoggedIn == true) {

                            if (auction.auction_status_id == "1") {
                                // Initialize display when page loads
                                updateBidWithFeeDisplay();
                            }
                        }

                        // Restrict input to only whole numbers (No decimals, No special characters)
                        bidInput.on("keypress", function (e) {
                            let charCode = e.which ? e.which : e.keyCode;

                            // Allow only numbers and backspace
                            if (charCode < 48 || charCode > 57) { // ASCII codes for 0-9
                                e.preventDefault();
                            }
                        });

                        // Format Start Date & Time
                        let formattedStartDate = formatAuctionDate(auction.auction_start_date, auction.auction_start_time);
                        let formattedEndDate = formatAuctionDate(auction.auction_end_date, auction.auction_end_time);

                        // Set Text in UI
                        auctionStartDateContainer.text(formattedStartDate);
                        auctionEndDateContainer.text(formattedEndDate);

                        const { DateTime } = luxon;

                        // Assume formattedEndDate is in format: "Aug 06, 2025, 12:00 AM"
                        let endTimeStr = formattedEndDate;
                        let userTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

                        // Parse it as Brisbane time
                        let brisbaneTime = DateTime.fromFormat(endTimeStr, "MMM dd, yyyy, hh:mm a", { zone: "Australia/Brisbane" });

                        if (brisbaneTime.isValid) {
                            // Convert to user's local time zone
                            let localTime = brisbaneTime.setZone(userTimeZone);

                            // Format output like: "6:00 PM Asia/Riyadh"
                            let displayText = `(${localTime.toFormat("h:mm a")} ${userTimeZone})`;

                            // Display in the timezone container
                            auctionDefaultTimeContainer.text(displayText);
                        } else {
                            auctionDefaultTimeContainer.text("(Invalid End Time)");
                        }


                        // Set Auction Description
                        auctionTitleContainer.text(auction.auction_storage_facility_name + " Auction # " + auction.auction_id);
                        auctionDescriptionsContainer.text(auction.auction_description || "No description available.");
                        auctionCurrentPriceContainer.text(auction.auction_max_bid_amount);
                        auctionTotalViewsContainer.text(auction.auction_total_views);

                        const notice = auction.auction_storage_facility_notice_to_buyers;

                        if (notice && notice.trim() !== "") {
                            auctionNoticeToBuyersContainer.html(
                                notice
                                    .replace(/\n/g, "<br>") // keep line breaks
                                    .replace(/•/g, "&#8226;") // optional: make bullet points uniform
                            );
                        } else {
                            auctionNoticeToBuyersContainer.html(
                                `<div class="text-muted fst-italic">No notice available.</div>`
                            );
                        }

                        if (auction.auction_tags && auction.auction_tags.trim() !== "") {
                            let tagsArray = auction.auction_tags
                                .split(",")
                                .map(tag => tag.trim())
                                .filter(tag => !!tag); // ✅ remove empty tags

                            let tagsHtml = tagsArray
                                .map(tag => `
            <a href="#" style="text-decoration: none;">
                <div style="font-weight: 200;" class="px-md-4 px-2 py-2 tags">${tag}</div>
            </a>
        `)
                                .join("");
                            auctionTagsContainer.html(tagsHtml);
                        } else {
                            auctionTagsContainer.html('<p class="text-center text-muted">No tags available.</p>');
                        }


                        // Start the countdown timer
                        startCountdown(auction.auction_id, auction.auction_end_date, auction.auction_end_time, auction.auction_storage_facility_timezone);

                        showNotification(response.message, "success");
                    } else {
                        auctionContainer.html(`<p class="text-center text-danger">No auction details found.</p>`);
                    }
                    resolve(response); // ✅ SUCCESS RESOLVE

                } catch (e) {
                    console.error("Auction render error:", e);
                    resolve(response); // still resolve so next .then() runs
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(".skeleton-loader").remove();
                auctionContainer.html(`<p class="text-center text-danger">Failed to load auction details.</p>`);

                let errorMessage = "Something went wrong. Try again.";

                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }

                showNotification(errorMessage, "error");
                resolve(jqXHR); // ✅ ERROR RESOLVE — not reject
            }
        });
    });
}

// Append Skeleton Loader (Matching HTML Structure)
function appendSkeletonLoader(container) {
    let skeletonHtml = `
        <div class="skeleton-loader w-100">
            <h2 class="skeleton skeleton-heading mb-md-4"></h2>
            <div class="h-100 w-100 ps-1 ps-md-5">
                <div class="d-flex gap-2">
                    <p class="skeleton skeleton-text"></p>
                    <p class="skeleton skeleton-text"></p>
                </div>
                <div class="d-flex gap-2">
                    <p class="skeleton skeleton-text"></p>
                    <p class="skeleton skeleton-text"></p>
                </div>
                <div class="d-flex gap-2">
                    <p class="skeleton skeleton-text"></p>
                    <p class="skeleton skeleton-text"></p>
                </div>
                <div class="d-flex gap-2">
                    <p class="skeleton skeleton-text"></p>
                    <p class="skeleton skeleton-text"></p>
                </div>
                <div class="d-flex gap-2">
                    <p class="skeleton skeleton-text"></p>
                    <p class="skeleton skeleton-text"></p>
                </div>
                <div class="d-flex gap-2">
                    <p class="skeleton skeleton-text"></p>
                    <p class="skeleton skeleton-text"></p>
                </div>
            </div>
        </div>
    `;
    container.html(skeletonHtml);
}

// Countdown Timer for auction end time
function startCountdown2(auctionId, auctionEndDate, auctionEndTime, auctionTimezone) {
    try {
        new Intl.DateTimeFormat("en-US", { timeZone: auctionTimezone });
    } catch (error) {
        auctionTimezone = "UTC"; // Default to UTC if invalid timezone
    }

    function updateTimer() {
        const now = new Date(new Date().toLocaleString("en-US", { timeZone: auctionTimezone }));
        /*const endTime = new Date(`${auctionEndDate}T23:59:59`);
        const timeDiff = endTime - now;*/

        // Combine date and time, and parse in local timezone first
        const endDateTimeStr = `${auctionEndDate} ${auctionEndTime}`;
        const localEndTime = new Date(new Date(endDateTimeStr).toLocaleString("en-US", { timeZone: auctionTimezone }));

        const timeDiff = localEndTime - now;

        if (timeDiff <= 0) {
            $(`.days-${auctionId}`).text("0");
            $(`.hours-${auctionId}`).text("0");
            $(`.minutes-${auctionId}`).text("0");
            $(`.seconds-${auctionId}`).text("0");
            return;
        }

        let days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
        let hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);

        $(`.days-${auctionId}`).text(days);
        $(`.hours-${auctionId}`).text(hours);
        $(`.minutes-${auctionId}`).text(minutes);
        $(`.seconds-${auctionId}`).text(seconds);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

// Global server time offset (difference between server and client)
let serverTimeOffset = 0;
let serverTimeSynced = false;

// Sync with server time once on page load
function syncServerTime() {
    if (serverTimeSynced) return Promise.resolve();
    
    return $.ajax({
        url: SITE_URL + "api/auctions/getServerTime",
        type: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({}),
        success: function(response) {
            if (response.status === "success") {
                const serverTimestamp = response.timestamp * 1000;
                const clientTimestamp = Date.now();
                serverTimeOffset = serverTimestamp - clientTimestamp;
                serverTimeSynced = true;
                console.log("✅ Server time synced. Offset: " + serverTimeOffset + "ms");
            }
        },
        error: function() {
            console.warn("⚠️ Failed to sync server time. Using client time.");
            serverTimeSynced = true;
        }
    });
}

function startCountdown(auctionId, auctionEndDate, auctionEndTime, auctionTimezone) {
    const { DateTime } = luxon;

    const endDateTime = DateTime.fromFormat(
        `${auctionEndDate} ${auctionEndTime}`,
        "yyyy-MM-dd HH:mm:ss",
        { zone: auctionTimezone }
    );

    function updateTimer() {
        if (!endDateTime.isValid) {
            console.error(`Invalid date for auction ${auctionId}:`, endDateTime.invalidReason);
            $(`.days-${auctionId}`).text("00");
            $(`.hours-${auctionId}`).text("00");
            $(`.minutes-${auctionId}`).text("00");
            $(`.seconds-${auctionId}`).text("00");
            return;
        }

        // Use server-synchronized time
        const syncedClientTime = Date.now() + serverTimeOffset;
        const now = DateTime.fromMillis(syncedClientTime).setZone(auctionTimezone);
        const diff = endDateTime.diff(now, ["days", "hours", "minutes", "seconds"]);

        if (diff.toMillis() <= 0) {
            $(`.days-${auctionId}`).text("00");
            $(`.hours-${auctionId}`).text("00");
            $(`.minutes-${auctionId}`).text("00");
            $(`.seconds-${auctionId}`).text("00");
            return;
        }

        const days = Math.floor(diff.days);
        const hours = Math.floor(diff.hours);
        const minutes = Math.floor(diff.minutes);
        const seconds = Math.floor(diff.seconds);

        $(`.days-${auctionId}`).text(days < 10 ? "0" + days : days);
        $(`.hours-${auctionId}`).text(hours < 10 ? "0" + hours : hours);
        $(`.minutes-${auctionId}`).text(minutes < 10 ? "0" + minutes : minutes);
        $(`.seconds-${auctionId}`).text(seconds < 10 ? "0" + seconds : seconds);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

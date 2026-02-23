// Global settings
// Global auction data storage
let allAuctions = [];
let currentPage = 1;
const pageSize = 16;

function fetchAuctions(limit) {
    $(".feature__live__section__container__grid").html(""); // Clear existing content
    let loaderHtml = `
    <div id="auction-loader" class="d-flex justify-content-center align-items-center w-100 my-4">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
`;
    $(".feature__live__section__container__grid").append(loaderHtml);

    $.ajax({
        url: SITE_URL + "api/auctions/getTopThreeAuctions",
        type: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({ limit: limit }),
        success: function (response) {
            $("#auction-loader").remove();

            if (response.status === "success" && response.data.length > 0) {
                loadAuctionSequentially(response.data, 0); // Start loading one by one
            } else {
                $(".feature__live__section__container__grid").html(`
                    <div class=" bidding__cards__container  container">
                        <div style="border-radius: 15px;" class="bg-white mx-2 flex-wrap justify-content-center px-3 mt-3 row text-center py-4">
                            <div class="bid_card  border-0 px-1 col-lg-3 col-md-4 col-sm-6 col-10">
                                <div class="bid__card__wrapper">
                                    <div class="bid_card_img mb-3">
                                        <i class="bi bi-hourglass-split display-4 text-warning" style="z-index: 999 !important;"></i>
                                    </div>
                                    <h2 class="bid_card_title">No Auctions Live</h2>
                                    <p>There are currently no active auctions.<br>Check back soon or enable notifications to stay updated.</p>
                                </div>
                                <div class="bid_card_active"></div>
                            </div>
                        </div>
                    </div>
                `);


            }
        },
        error: function () {

            $("#auction-loader").remove();

            $(".feature__live__section__container__grid").html(`
                <div class=" bidding__cards__container  container">
                    <div style="border-radius: 15px;" class="bg-white mx-2 flex-wrap justify-content-center px-3 mt-3 row text-center py-4">
                        <div class="bid_card  border-0 px-1 col-lg-3 col-md-4 col-sm-6 col-10">
                            <div class="bid__card__wrapper">
                                <div class="bid_card_img mb-3">
                                    <i class="bi bi-hourglass-split display-4 text-warning" style="z-index: 999 !important;"></i>
                                </div>
                                <h2 class="bid_card_title">No Auctions Live</h2>
                                <p>There are currently no active auctions.<br>Check back soon or enable notifications to stay updated.</p>
                            </div>
                            <div class="bid_card_active"></div>
                        </div>
                    </div>
                </div>
            `);
        }
    });
}


function fetchAllAuctions(start = 0, end = 16, activePage = 1) {
    $(".auction__all__live__section__container__grid").html("");

    let loaderHtml = `
        <div id="auction-loader" class="d-flex justify-content-center align-items-center w-100 my-4">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    $(".auction__all__live__section__container__grid").append(loaderHtml);

    $.ajax({
        url: SITE_URL + "api/auctions/getAllAuctions",
        type: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({ start: start, end: end }),
        success: function (response) {
            $("#auction-loader").remove();

            if (response.status === "success" && response.data.length > 0) {
                loadAllAuctionSequentially(response.data, 0);
                const totalCount = response.totalCount || response.data.length; // Correct total
                renderPaginationFrontend(totalCount, activePage);

                // 🔥 Run advanced distance filter + sort
                if (typeof applyDistanceFiltersAndSort === "function") {
                    console.log("🧭 Running distance filter & sort...");
                    setTimeout(() => applyDistanceFiltersAndSort(), 1500);
                }
            } else {

                $(".auction__all__live__section__container__grid").html(`
                    <div class=" bidding__cards__container  container mb-3" style="max-width: 100% !important;">
                        <div style="border-radius: 15px;" class="bg-white mx-2 flex-wrap justify-content-center px-3 mt-3 row text-center py-4">
                            <div class="bid_card  border-0 px-1 col-lg-3 col-md-4 col-sm-6 col-10">
                                <div class="bid__card__wrapper">
                                    <div class="bid_card_img mb-3">
                                        <i class="bi bi-hourglass-split display-4 text-warning" style="z-index: 999 !important;"></i>
                                    </div>
                                    <h2 class="bid_card_title">No Auctions Live</h2>
                                    <p>There are currently no active auctions.<br>Check back soon or enable notifications to stay updated.</p>
                                </div>
                                <div class="bid_card_active"></div>
                            </div>
                        </div>
                    </div>
                `);


                $(".paginate-container").hide();
            }
        },
        error: function () {
            $("#auction-loader").remove();
            $(".auction__all__live__section__container__grid").html(`
                    <div class=" bidding__cards__container  container  mb-3" style="max-width: 100% !important;">
                        <div style="border-radius: 15px;" class="bg-white mx-2 flex-wrap justify-content-center px-3 mt-3 row text-center py-4">
                            <div class="bid_card  border-0 px-1 col-lg-3 col-md-4 col-sm-6 col-10">
                                <div class="bid__card__wrapper">
                                    <div class="bid_card_img mb-3">
                                        <i class="bi bi-hourglass-split display-4 text-warning" style="z-index: 999 !important;"></i>
                                    </div>
                                    <h2 class="bid_card_title">No Auctions Live</h2>
                                    <p>There are currently no active auctions.<br>Check back soon or enable notifications to stay updated.</p>
                                </div>
                                <div class="bid_card_active"></div>
                            </div>
                        </div>
                    </div>
                `);
        }
    });
}

function renderPaginationFrontend(totalCount, activePage) {
    const totalPages = Math.ceil(totalCount / pageSize);
    /*if (totalPages <= 1) {
        $(".paginate-container").hide();
        return;
    }*/

    let paginationHtml = `<ul class="container" role="navigation" aria-label="Pagination">`;

    // Previous Button
    paginationHtml += `<li class="previous ${activePage === 1 ? 'disabled' : ''}">
        <a class="page" data-page="${activePage - 1}" ${activePage === 1 ? 'aria-disabled="true"' : ''}>
            <svg stroke="currentColor" fill="currentColor" viewBox="0 0 24 24" height="1em" width="1em">
                <polygon fill="none" stroke="#000" stroke-width="2" points="6 2 18 12 6 22" transform="matrix(-1 0 0 1 24 0)"></polygon>
            </svg>
        </a>
    </li>`;

    // Page Numbers
    for (let i = 1; i <= totalPages; i++) {
        paginationHtml += `<li class="page ${i === activePage ? 'active1' : ''}">
            <a role="button" class="page-link" data-page="${i}">${i}</a>
        </li>`;
    }

    // Next Button
    paginationHtml += `<li class="next ${activePage === totalPages ? 'disabled' : ''}">
        <a class="page" data-page="${activePage + 1}" ${activePage === totalPages ? 'aria-disabled="true"' : ''}>
            <svg stroke="currentColor" fill="currentColor" viewBox="0 0 24 24" height="1em" width="1em">
                <polygon fill="none" stroke="#000" stroke-width="2" points="6 2 18 12 6 22"></polygon>
            </svg>
        </a>
    </li>`;

    paginationHtml += `</ul>`;
    $(".paginate-container").html(paginationHtml).show();
}


$(document).on("click", ".paginate-container .page a", function () {
    const page = parseInt($(this).data("page"));
    if (isNaN(page) || page < 1) return;

    const start = (page - 1) * pageSize;
    const end = start + pageSize;

    currentPage = page;
    fetchAllAuctions(start, end, page);
});


// Function to load auctions sequentially (one by one)
function loadAuctionSequentially(auctions, index) {
    if (index >= auctions.length) return; // Stop if all auctions are loaded

    appendSkeletonLoader(); // Show skeleton

    setTimeout(() => {
        $(".feature__live__section__container__grid").find(".skeleton-loader").first().remove(); // Remove skeleton
        appendAuctionCard(auctions[index]); // Append auction card
        loadAuctionSequentially(auctions, index + 1); // Load next auction
    }, 900); // Delay for smooth transition


}

// Function to load auctions sequentially (one by one)
function loadAllAuctionSequentially(auctions, index) {
    if (index >= auctions.length) return; // Stop if all auctions are loaded

    appendAllSkeletonLoader(); // Show skeleton

    setTimeout(() => {
        $(".auction__all__live__section__container__grid").find(".skeleton-loader").first().remove(); // Remove skeleton
        appendAllAuctionCard(auctions[index]); // Append auction card
        loadAllAuctionSequentially(auctions, index + 1); // Load next auction
    }, 450); // Delay for smooth transition


}


// Append skeleton loader (matching the auction card layout)
function appendSkeletonLoader() {
    let skeletonHtml = `
        <div class="mb-4 col-lg-4 col-md-6 skeleton-loader">
            <div class="bidding__card__grid">
                <!-- Skeleton for Image -->
                <div class="skeleton image"></div>

                <!-- Skeleton for Auction Number -->
                <div class="skeleton text auction-number"></div>

                <div class="d-flex card__info__container flex-column w-100">
                    <div class="card__info">
                        <table class="table table-bordered w-100 mt-md-3 mt-2 mb-2 mb-md-3">
                            <thead>
                                <tr class="p-0">
                                    <th scope="col">
                                        <div class="text-center text-xs text-light-black font-semibold"></div>
                                    </th>
                                    <th scope="col"><div class="skeleton small-box"></div></th>
                                    <th scope="col"><div class="skeleton small-box"></div></th>
                                    <th scope="col"><div class="skeleton small-box"></div></th>
                                    <th scope="col"><div class="skeleton small-box"></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-primary-dark">
                                        <div class="skeleton text high-bid"></div>
                                    </td>
                                    <td><div class="skeleton small-box"></div></td>
                                    <td><div class="skeleton small-box"></div></td>
                                    <td><div class="skeleton small-box"></div></td>
                                    <td><div class="skeleton small-box"></div></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Skeleton for Storage Facility Name -->
                        <div class="skeleton text storage-name"></div>

                        <!-- Skeleton for Address -->
                        <div class="skeleton text address"></div>
                        <div class="skeleton text address"></div>
                    </div>

                    <div class="bidding__card__button__section">
                        <!-- Skeleton for Button -->
                        <div class="skeleton button"></div>
                    </div>
                </div>
            </div>
        </div>
    `;
    $(".feature__live__section__container__grid").append(skeletonHtml);
}

// Append skeleton loader (matching the auction card layout)
function appendAllSkeletonLoader() {
    let skeletonHtml = `
        <div class="mb-4 col-lg-3 col-md-4 skeleton-loader">
            <div class="bidding__card__grid">
                <!-- Skeleton for Image -->
                <div class="skeleton image"></div>

                <!-- Skeleton for Auction Number -->
                <div class="skeleton text auction-number"></div>

                <div class="d-flex card__info__container flex-column w-100">
                    <div class="card__info">
                        <table class="table table-bordered w-100 mt-md-3 mt-2 mb-2 mb-md-3">
                            <thead>
                                <tr class="p-0">
                                    <th scope="col">
                                        <div class="text-center text-xs text-light-black font-semibold"></div>
                                    </th>
                                    <th scope="col"><div class="skeleton small-box"></div></th>
                                    <th scope="col"><div class="skeleton small-box"></div></th>
                                    <th scope="col"><div class="skeleton small-box"></div></th>
                                    <th scope="col"><div class="skeleton small-box"></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-primary-dark">
                                        <div class="skeleton text high-bid"></div>
                                    </td>
                                    <td><div class="skeleton small-box"></div></td>
                                    <td><div class="skeleton small-box"></div></td>
                                    <td><div class="skeleton small-box"></div></td>
                                    <td><div class="skeleton small-box"></div></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Skeleton for Storage Facility Name -->
                        <div class="skeleton text storage-name"></div>

                        <!-- Skeleton for Address -->
                        <div class="skeleton text address"></div>
                        <div class="skeleton text address"></div>
                    </div>

                    <div class="bidding__card__button__section">
                        <!-- Skeleton for Button -->
                        <div class="skeleton button"></div>
                    </div>
                </div>
            </div>
        </div>
    `;
    $(".auction__all__live__section__container__grid").append(skeletonHtml);
}


// Append auction card with smooth fade-in
function appendAuctionCard(auction) {
    let auctionStartDate = auction.auction_start_date;
    let auctionEndDate = auction.auction_end_date;
    let auctionEndTime = auction.auction_end_time;
    let auctionTimezone = auction.auction_storage_facility_timezone || "UTC";

    // Determine card style based on visual variety logic (since no specific API field)
    // ID % 3: 0 -> Green (HOT), 1 -> Grey (STANDARD), 2 -> Blue (ENDING SOON)

    let cardStyleStr = "standard";
    let badgeText = "STANDARD";
    let headerClass = "bg-secondary-light"; // Grey
    let badgeClass = "badge-standard";

    if (auction.auction_id % 3 === 0) {
        cardStyleStr = "hot";
        badgeText = "HOT";
        headerClass = "bg-success-modern"; // Green
        badgeClass = "badge-hot";
    } else if (auction.auction_id % 3 === 2) {
        cardStyleStr = "ending";
        badgeText = "ENDING SOON";
        headerClass = "bg-primary-modern"; // Blue
        badgeClass = "badge-ending";
    }

    let auctionHtml = `
        <div class="col-lg-4 col-md-6 mb-4 fade-in" data-auction-id="${auction.auction_id}">
             <a href="${SITE_URL}auctions/${auction.auction_id}" class="text-decoration-none text-reset d-block h-100">
                 <div class="auction-card-modern-v2 h-100">
                    <!-- Top Section with Image/Color -->
                    <div class="card-header-visual ${headerClass} d-flex flex-column justify-content-center align-items-center position-relative p-0 text-center overflow-hidden" style="height: 200px;">
                        <span class="status-badge-v2 ${badgeClass} position-absolute top-0 end-0 m-3 px-3 py-1 fw-bold text-white shadow-sm" style="border-radius: 20px; font-size: 0.75rem; z-index: 2;">${badgeText}</span>
                        
                        ${auction.auction_cover_images && auction.auction_cover_images.length > 0
            ? `<img src="${auction.auction_cover_images[0].path}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0" alt="Auction Image" style="z-index: 1;">`
            : `<div class="w-100 h-100 d-flex justify-content-center align-items-center" style="z-index: 1;">
                                <h3 class="text-white fw-bold mb-0 display-6 text-shadow-sm">${auction.auction_unit_number ? `Unit ${auction.auction_unit_number}` : `Unit ${auction.auction_id}`}</h3>
                               </div>`
        }
                        
                        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-start" style="z-index: 2; background: linear-gradient(transparent, rgba(0,0,0,0.7));">
                            <p class="text-white mb-0 small fw-bold">${auction.auction_storage_facility_name}</p>
                        </div>
                    </div>
                    
                    <!-- Bottom Content -->
                    <div class="card-body p-4 bg-white border-bottom border-start border-end rounded-bottom-4 shadow-sm">
                        <h5 class="card-title fw-bold mb-2 text-dark">Unit #${auction.auction_unit_number || auction.auction_id} - ${auction.auction_category_name || 'Storage Unit'}</h5>
                        <p class="card-text text-muted small mb-4">
                            Size: ${auction.auction_unit_size || 'N/A'} | Location: ${auction.auction_storage_facility_city}, ${auction.auction_storage_facility_state}
                        </p>
                        
                        <hr class="opacity-10 my-3">
                        
                        <div class="d-flex justify-content-between align-items-end mt-3">
                            <div>
                                <div class="text-secondary small fw-medium text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Current Bid</div>
                                <div class="text-success fw-bold fs-4">$${auction.auction_max_bid_amount}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-secondary small fw-medium text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Ends In</div>
                                <div class="fw-bold fs-5 text-danger-modern">
                                    <span id="days-${auction.auction_id}">00</span>d 
                                    <span id="hours-${auction.auction_id}">00</span>h
                                    <span id="minutes-${auction.auction_id}">00</span>m
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    `;

    $(".feature__live__section__container__grid").append(auctionHtml);

    // Start countdown for this auction
    startCountdown(auction.auction_id, auctionEndDate, auctionEndTime, auctionTimezone);
}

// Append auction card with smooth fade-in
function appendAllAuctionCard(auction) {
    let auctionStartDate = auction.auction_start_date;
    let auctionEndDate = auction.auction_end_date;
    let auctionEndTime = auction.auction_end_time;
    let auctionTimezone = auction.auction_storage_facility_timezone || "UTC";

    // Determine card style based on ID
    // Logic: Cycle through Blue, Purple, Green, Yellow, Grey

    let headerClass = "bg-primary-modern"; // Default Blue
    let headerColor = "#4285f4";
    let timerBg = "#e8f0fe";
    let timerText = "#1967d2";

    const mod = auction.auction_id % 5;

    if (mod === 0) { // Blue
        headerClass = "bg-primary-modern";
        timerBg = "#e8f0fe"; timerText = "#1967d2";
    } else if (mod === 1) { // Purple
        headerClass = "bg-purple-modern";
        timerBg = "#f3e8fd"; timerText = "#8430ce";
    } else if (mod === 2) { // Green
        headerClass = "bg-success-modern";
        timerBg = "#e6f4ea"; timerText = "#137333";
    } else if (mod === 3) { // Yellow
        headerClass = "bg-warning-modern";
        timerBg = "#fef7e0"; timerText = "#b06000";
    } else { // Grey
        headerClass = "bg-secondary-modern";
        timerBg = "#f1f3f4"; timerText = "#5f6368";
    }

    let auctionHtml = `
        <div class="mb-4 col-lg-3 col-md-4 fade-in" data-auction-id="${auction.auction_id}">
            <div class="auction-card-v3 h-100 shadow-sm rounded-3 overflow-hidden border bg-white d-flex flex-column">
                
                <!-- 1. Colored Header / Image -->
                <div class="${headerClass} text-center d-flex flex-column justify-content-center align-items-center position-relative overflow-hidden" style="height: 160px;">
                    ${auction.auction_cover_images && auction.auction_cover_images.length > 0
            ? `<img src="${auction.auction_cover_images[0].path}" class="w-100 h-100 object-fit-cover" alt="Auction Image">`
            : `<h4 class="text-white fw-bold mb-0 text-shadow-sm h5 px-3">
                            ${auction.auction_unit_number ? `Unit ${auction.auction_unit_number}` : `Auction #${auction.auction_id}`} - ${auction.auction_category_name || 'Goods'}
                           </h4>`
        }
                </div>

                <!-- 2. Timer Pill -->
                <div class="px-3 mt-2">
                    <div class="py-2 px-3 rounded-pill text-center fw-bold shadow-sm" style="background-color: ${timerBg}; color: ${timerText}; font-size: 0.95rem;">
                         <span id="days-${auction.auction_id}">00</span>d 
                         <span id="hours-${auction.auction_id}">00</span>h 
                         <span id="minutes-${auction.auction_id}">00</span>m 
                         <span id="seconds-${auction.auction_id}">00</span>s
                    </div>
                </div>

                <!-- 3. Body Content -->
                <div class="card-body p-4 pt-2 flex-grow-1 d-flex flex-column">
                    <h5 class="fw-bold text-dark mb-1">${auction.auction_storage_facility_name}</h5>
                    <p class="text-muted small mb-3">
                        ${auction.auction_storage_facility_city}, ${auction.auction_storage_facility_postal_code || ''}
                    </p>

                    <div class="mt-auto d-flex align-items-end justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Current Bid</div>
                            <div class="fs-5 fw-bold text-success">$${auction.auction_max_bid_amount} AUD</div>
						</div>
                        
                        <a href="${SITE_URL + "auctions/" + auction.auction_id}" class="btn btn-success fw-bold px-4 py-2 text-white" style="background-color: #28a745; border: none;">
                            Place Bid
                        </a>
                    </div>
                </div>

            </div>
        </div>
    `;

    $(".auction__all__live__section__container__grid").append(auctionHtml);

    // Start countdown for this auction
    startCountdown(auction.auction_id, auctionEndDate, auctionEndTime, auctionTimezone);
}

function startCountdown2(auctionId, auctionEndDate, auctionEndTime, auctionTimezone) {
    // ... (rest of startCountdown2 can be removed or kept if unused, but startCountdown is the main one used)
    startCountdown(auctionId, auctionEndDate, auctionEndTime, auctionTimezone);
}


function startCountdown(auctionId, auctionEndDate, auctionEndTime, auctionTimezone) {
    const { DateTime } = luxon;

    // Combine date and time and interpret it in the given timezone
    // Database returns date as YYYY-MM-DD and time as HH:mm:ss (24-hour)
    const endDateTime = DateTime.fromFormat(
        `${auctionEndDate} ${auctionEndTime}`,
        "yyyy-MM-dd HH:mm:ss",
        { zone: auctionTimezone }
    );

    function updateTimer() {
        if (!endDateTime.isValid) {
            // Invalid date handling
            $(`#days-${auctionId}`).text("00");
            $(`#hours-${auctionId}`).text("00");
            $(`#minutes-${auctionId}`).text("00");
            $(`#seconds-${auctionId}`).text("00");
            return;
        }

        const now = DateTime.now().setZone(auctionTimezone);
        const diff = endDateTime.diff(now, ["days", "hours", "minutes", "seconds"]);

        if (diff.toMillis() <= 0) {
            $(`#days-${auctionId}`).text("00");
            $(`#hours-${auctionId}`).text("00");
            $(`#minutes-${auctionId}`).text("00");
            $(`#seconds-${auctionId}`).text("00");
            return;
        }

        const days = Math.floor(diff.days);
        const hours = Math.floor(diff.hours);
        const minutes = Math.floor(diff.minutes);
        const seconds = Math.floor(diff.seconds);

        // Pad with zeros
        $(`#days-${auctionId}`).text(days < 10 ? "0" + days : days);
        $(`#hours-${auctionId}`).text(hours < 10 ? "0" + hours : hours);
        $(`#minutes-${auctionId}`).text(minutes < 10 ? "0" + minutes : minutes);
        $(`#seconds-${auctionId}`).text(seconds < 10 ? "0" + seconds : seconds);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}


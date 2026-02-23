let isUserLoggedIn = false; // Default login state
let hasRoleId = 0; // Default login state
let userEmail = "";

function capitalizeWords(str) {
    if (!str) return '';
    return str.toLowerCase().split(' ').map(function (word) {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }).join(' ');
}


function showUniversalLoaderForTable(tableId) {
    const $table = $('#' + tableId);
    const $loader = $('#table-loader');
    const $skeleton = $('#table-skeleton-overlay');

    const offset = $table.offset();
    const width = $table.outerWidth();
    const height = $table.outerHeight();

    // Position overlay and loader
    $skeleton.css({
        top: offset.top,
        left: offset.left,
        width: width,
        height: height
    }).show();

    $loader.css({
        top: offset.top + height / 2,
        left: offset.left + width / 2,
        transform: 'translate(-50%, -50%)'
    }).show();
}

function hideUniversalLoader() {
    $('#table-skeleton-overlay').hide();
    $('#table-loader').hide();
}

function showSpinner() {
    $("#spinner-overlay").fadeIn(200);
}
function hideSpinner() {
    $("#spinner-overlay").fadeOut(200);
}



// Function to show alert notification with icon
function showNotification(message, type, duration = 5000) {
    let icon;

    if (type === "success") {
        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="green" viewBox="0 0 16 16"> <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM7.97 11.03a.75.75 0 0 0 1.06 0l3.536-3.536a.75.75 0 1 0-1.06-1.06L8.5 9.44l-1.94-1.94a.75.75 0 1 0-1.06 1.06l2.47 2.47z"/> </svg>';
    } else {
        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="red" viewBox="0 0 16 16"> <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm.93 4.515a.5.5 0 0 0-.93 0l-.5 5.5a.5.5 0 0 0 .5.485h1a.5.5 0 0 0 .5-.485l-.5-5.5zM8 12a1.5 1.5 0 1 0 0 3A1.5 1.5 0 0 0 8 12z"/> </svg>';
    }

    $("#alertIcon").html(icon);
    $("#alertMessage").html(message);
    $(".alertNotification").removeClass("d-none");


    setTimeout(() => {
        $(".alertNotification").addClass("d-none");
        $(".alertNotification").fadeOut();

    }, duration);
}

function showNotificationWithLink(message, type) {
    let icon;

    if (type === "success") {
        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="green" viewBox="0 0 16 16"> <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM7.97 11.03a.75.75 0 0 0 1.06 0l3.536-3.536a.75.75 0 1 0-1.06-1.06L8.5 9.44l-1.94-1.94a.75.75 0 1 0-1.06 1.06l2.47 2.47z"/> </svg>';
    } else {
        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="red" viewBox="0 0 16 16"> <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm.93 4.515a.5.5 0 0 0-.93 0l-.5 5.5a.5.5 0 0 0 .5.485h1a.5.5 0 0 0 .5-.485l-.5-5.5zM8 12a1.5 1.5 0 1 0 0 3A1.5 1.5 0 0 0 8 12z"/> </svg>';
    }

    $("#alertIcon").html(icon);
    $("#alertMessage").html(message);
    $(".alertNotification").removeClass("d-none");


    setTimeout(() => {
        $(".alertNotification").addClass("d-none");
        $(".alertNotification").fadeOut();

    }, 30000);
}

$(document).ready(function () {


    $(".register__option").click(function () {
        let selectedOption = $(this).find("h2").text().trim().toLowerCase();


        if (selectedOption === "bidder") {
            window.location.href = SITE_URL + "register/standard-bidder";
        } else if (selectedOption === "storage facility") {
            window.location.href = SITE_URL + "register/standard-seller";
        }
    });



    $("#footerRegisterNow").click(function () {
        $("#registerModal").modal("show");
    });

    $("#footerRegisterNow").click(function () {
        $("#registerModal").modal("show");
    });

    // Open modal with slide down effect
    $(".__system__user__login__btn").click(function () {
        $(".forgot-password-popup").addClass("d-none");
        $(".login-popup").removeClass("d-none").css("top", "-100px").animate({ top: "100%" }, 300);
    });

    // Open modal with slide down effect
    $(".__system__user__forgot__password__btn").click(function () {
        $(".login-popup").addClass("d-none");
        $(".forgot-password-popup").removeClass("d-none").css("top", "-100px").animate({ top: "100%" }, 300);
    });

    // Open modal with slide down effect
    $(".__system__user__forgot__password__back__btn").click(function () {
        $(".forgot-password-popup").addClass("d-none");
        $(".login-popup").removeClass("d-none");
    });

    // Close modal when clicking outside the modal
    $(document).click(function (event) {
        if (!$(event.target).closest(".login-popup, .__system__user__login__btn").length) {
            $(".login-popup").animate({ top: "-100px" }, 300, function () {
                $(this).addClass("d-none");
            });

            /*$(".forgot-password-popup").animate({ top: "-100px" }, 300, function () {
                $(this).addClass("d-none");
            });*/
        }

        // Close profile menu only if clicking outside the button AND the menu
        if (!$(event.target).closest(".profile-btn, .profile_menu").length) {
            $(".profile_menu").remove();

            // Reset icon to DOWN when menu is closed
            $(".dropdown-icon").html(`
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M256 294.1L383 167c9.4-9.4 24.6-9.4 33.9 0s9.3 24.6 0 34L273 345c-9.1 9.1-23.7 9.3-33.1.7L95 201.1c-4.7-4.7-7-10.9-7-17s2.3-12.3 7-17c9.4-9.4 24.6-9.4 33.9 0l127.1 127z"></path>
                </svg>
            `);
        }
    });

    $(".header__nav__open_button__dropdown").click(function () {
        $(".header__nav__dropdown").toggleClass("header__nav__dropdown_active").removeClass("false");
    });



    $(".header__nav__close_button__dropdown").click(function () {
        $(".header__nav__dropdown").toggleClass("false").removeClass("header__nav__dropdown_active");
    });



    function adjustButton() {
        if ($(window).width() <= 768) { // Small screens (≤768px)
            $(".outline_search_input_postElem .me-2").removeClass("me-2").addClass("me-0"); // Change margin
            $(".outline_search_input_postElem button span").hide(); // Hide text
        } else { // Large screens (>768)
            $(".outline_search_input_postElem .me-0").removeClass("me-0").addClass("me-2"); // Restore margin
            $(".outline_search_input_postElem button span").show(); // Show text
        }
    }

    adjustButton(); // Run on page load

    $(window).resize(function () {
        adjustButton(); // Run when window is resized
    });

    // Get the current full URL
    var currentUrl = window.location.href;

    // First, remove active classes from all menu items
    $(".header__links ul li").removeClass("header__links__active text-white false").addClass("text-grey false");

    // Loop through each menu item and check if the link matches the current URL
    $(".header__links ul li a").each(function () {
        if (this.href === currentUrl) {

            $(this).parent().removeClass("text-grey false").addClass("header__links__active text-white false");
        }
    });



    const pathSegments = window.location.pathname.split('/');
    const auctionIdIndex = pathSegments.indexOf('auctions') + 1;
    const auctionId = parseInt(pathSegments[auctionIdIndex]);

    if (!auctionId || isNaN(auctionId)) {
        console.warn('Auction ID not found in URL.');
        return;
    }

    const addViewUrl = SITE_URL + 'api/auction/add_view';
    const removeViewUrl = SITE_URL + 'api/auction/remove_views';
    const tabKey = `auction_tab_${auctionId}_${Date.now()}_${Math.random()}`;

    // ✅ Track this tab as active for this auction
    let openTabs = JSON.parse(localStorage.getItem('openAuctionTabs') || '{}');
    openTabs[tabKey] = auctionId;
    localStorage.setItem('openAuctionTabs', JSON.stringify(openTabs));


    // ✅ On unload, remove just this tab's entry
    window.addEventListener('pagehide', function (event) {
        if (!event.persisted) {
            // Remove this tab from localStorage
            let tabs = JSON.parse(localStorage.getItem('openAuctionTabs') || '{}');
            delete tabs[tabKey];
            localStorage.setItem('openAuctionTabs', JSON.stringify(tabs));

            // Only remove the view if NO other tab for this auction remains open
            const stillOpen = Object.values(tabs).filter(id => id === auctionId);
            if (stillOpen.length === 0) {
                const data = JSON.stringify({ auctionId: auctionId });
                const blob = new Blob([data], { type: 'application/json' });
                navigator.sendBeacon(removeViewUrl, blob);
                console.log('Auction view removed:', auctionId);
            }
        }
    });

    // ✅ Cleanup in case tab crashes without triggering unload
    window.addEventListener('storage', function (e) {
        if (e.key === 'openAuctionTabs') {
            const allTabs = JSON.parse(localStorage.getItem('openAuctionTabs') || '{}');
            const values = Object.values(allTabs);
            const open = values.filter(id => id === auctionId);
            if (open.length === 0) {
                // This tab thinks it's the last one
                const data = JSON.stringify({ auctionId: auctionId });
                const blob = new Blob([data], { type: 'application/json' });
                navigator.sendBeacon(removeViewUrl, blob);
            }
        }
    });



});

function setDobField(minAge = 18) {
    const now = new Date();

    // Calculate date `minAge` years ago
    const adjustedDate = new Date(
        now.getFullYear() - minAge,
        now.getMonth(),
        now.getDate()
    );

    // Format dates as YYYY-MM-DD
    const todayStr = now.toISOString().split('T')[0];
    const minDobStr = adjustedDate.toISOString().split('T')[0];

    // Apply to DOB input
    $('#dob').attr('max', todayStr).val(minDobStr);
}


$(document).on('click', '.toggle-password', function () {
    const $icon = $(this);
    const $input = $icon.siblings('input[type="password"], input[type="text"]');

    if ($input.length) {
        const currentType = $input.attr('type');
        const newType = currentType === 'password' ? 'text' : 'password';
        $input.attr('type', newType);

        const msg = newType === 'text' ? 'Password is now visible.' : 'Password is now hidden for security.';
        showNotification(msg, 'success');
    }
});


function performSearch() {
    const query = $('.outline_search_input').val().trim();
    console.log("Search query:", query); // Debug log

    if (query.length > 0) {
        const fullUrl = SITE_URL + 'auctions?search_term=' + encodeURIComponent(query);
        console.log("Redirecting to:", fullUrl);
        window.location.href = fullUrl;
    }
}

// Trigger search on Enter key inside the input
$('.outline_search_input').on('keypress', function (e) {
    if (e.which === 13) {
        e.preventDefault();
        performSearch();
    }
});

// Trigger search on "Search Now" button click
$('.outline_search_input_postElem button').on('click', function () {
    performSearch();
});
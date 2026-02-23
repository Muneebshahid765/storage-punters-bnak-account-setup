
$(document).ready(function () {
    /*$(".register__option").click(function () {
        let selectedOption = $(this).find("h2").text().trim().toLowerCase();


        if (selectedOption === "bidder") {
            window.location.href = SITE_URL + "register/standard-bidder";
        } else if (selectedOption === "storage facility") {
            window.location.href = SITE_URL + "register/standard-seller";
        }
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
    });*/


    let container = $(".feature__live__section__container__grid");
    let cardWidth = $(".bidding__card__grid").outerWidth(true) + 50; // Get width of one card
    let leftArrow = $("<div class='left-arrow'>&lt;</div>"); // Create left arrow dynamically

    // Right Arrow Click: Slide Right
    $(".right-arrow").click(function () {
        container.animate({ scrollLeft: "+=" + cardWidth }, 500, function () {
            checkArrows();
        });
    });

    // Left Arrow Click: Slide Left
    $(document).on("click", ".left-arrow", function () {
        container.animate({ scrollLeft: "-=" + cardWidth }, 500, function () {
            checkArrows();
        });
    });

    // Function to check and add/remove arrows dynamically
    function checkArrows() {
        if (!container.length || !container[0]) return; // Exit if container doesn't exist

        let scrollLeft = container.scrollLeft();
        let maxScroll = container[0].scrollWidth - container.innerWidth();

        if (scrollLeft > 0) {
            if (!$(".left-arrow").length) {
                leftArrow.insertBefore(container); // Add left arrow if not exists
            }
        } else {
            $(".left-arrow").remove(); // Remove left arrow when at start
        }

        if (scrollLeft >= maxScroll) {
            $(".right-arrow").fadeOut(); // Hide right arrow at end
        } else {
            $(".right-arrow").fadeIn(); // Show right arrow if not at end
        }
    }

    // Check arrows on scroll manually
    container.scroll(function () {
        checkArrows();
    });

    // Initial Check
    if (container.length) {
        checkArrows();
    }
});

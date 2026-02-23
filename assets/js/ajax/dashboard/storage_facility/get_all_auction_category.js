// Mock API call functions – replace URLs with your actual endpoints
function getAllAuctionCategory() {
    return $.ajax({
        url: SITE_URL + "api/auction/categories",
        type: "POST",
        dataType: "json"
    });
}

function appendAuctionCategories(categories) {
    const $dropdown = $("#auctionCategoryDropdown");
    $dropdown.empty().append(`<option value="">Select Category</option>`);

    if (Array.isArray(categories)) {
        categories.forEach(category => {
            $dropdown.append(`<option selected value="${category.category_id}">${category.category_name}</option>`);
        });
    } else if (categories && typeof categories === "object") {
        // Handle single object fallback
        $dropdown.append(`<option selected value="${categories.category_id}">${categories.category_name}</option>`);
    }
}

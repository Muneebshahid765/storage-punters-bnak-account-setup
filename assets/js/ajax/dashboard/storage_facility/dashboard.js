// Master function to load auction types, facilities, and conditionally categories
function loadAllAuctionDataSequentially() {
    getAllAuctionTypes("all")
        .then(function (typeResponse) {
            if (typeResponse.status === "success") {
                appendAuctionTypes(typeResponse.data);
            }
            return getAllAuctionStorageFacilities();
        })
        .then(function (facilitiesResponse) {
            if (facilitiesResponse.status === "success") {
                appendAuctionFacilities(facilitiesResponse.data);
            }

            // Conditionally load categories if the dropdown exists
            if ($("#auctionCategoryDropdown").length) {
                return getAllAuctionCategory()
                    .then(function (categoryResponse) {
                        if (categoryResponse.status === "success") {
                            appendAuctionCategories(categoryResponse.data);
                        }
                    });
            }
        })
        .catch(function (error) {
            console.error("Error loading auction data:", error);
            showNotification("Failed to load auction data", "error");
        });
}





// Run on page ready
$(document).ready(function () {
    loadAllAuctionDataSequentially();
});
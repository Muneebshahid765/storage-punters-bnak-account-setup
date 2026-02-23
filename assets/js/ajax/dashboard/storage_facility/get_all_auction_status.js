// Mock API call functions – replace URLs with your actual endpoints
function getAllAuctionStatus() {
    return $.ajax({
        url: SITE_URL + "api/auction/statuses",
        type: "POST",
        dataType: "json"
    });
}

function appendAuctionStatuses(statuses) {
    const $dropdown = $("#auctionStatusDropdown");
    $dropdown.empty().append(`<option value="">Select Status</option>`);

    if (Array.isArray(statuses)) {
        statuses.forEach(status => {
            $dropdown.append(`<option value="${status.status_id}">${status.status_name}</option>`);
        });
    } else if (statuses && typeof statuses === "object") {
        // Handle single object fallback
        $dropdown.append(`<option value="${statuses.status_id}">${statuses.status_name}</option>`);
    }
}

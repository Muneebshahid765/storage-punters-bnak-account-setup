function getAllAuctionStorageFacilities() {
    return $.ajax({
        url: SITE_URL + "api/user/fetch_storage_facilities_by_user",
        type: "POST",
        dataType: "json"
    });
}


function appendAuctionFacilities(facilities) {
    const $dropdown = $("#storageFacilityDropdown");
    $dropdown.empty();

    if (Array.isArray(facilities)) {
        facilities.forEach(facility => {
            $dropdown.append(`<option value="${facility.storage_facility_id}">${facility.storage_facility_name}</option>`);
        });
    } else if (facilities && typeof facilities === "object") {
        // Handle single object fallback
        $dropdown.append(`<option value="${facilities.storage_facility_id}">${facilities.storage_facility_name}</option>`);
    }
}

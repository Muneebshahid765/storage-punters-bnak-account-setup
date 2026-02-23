function getAllAuctionTypes(filter_by) {
    return $.ajax({
        url: SITE_URL + "api/auction/types",
        type: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({ filter_by: filter_by }),
    });
}

function appendAuctionTypes(types) {
    const $dropdown = $("#auctionTypeDropdown");
    $dropdown.empty().append(`<option value="">Select Type</option>`);

    if (Array.isArray(types)) {
        types.forEach(type => {
            $dropdown.append(`<option value="${type.type_id}">${type.type_name}</option>`);
        });
    } else if (types && typeof types === "object") {
        // Handle single object fallback
        $dropdown.append(`<option value="${types.type_id}">${types.type_name}</option>`);
    }
}


function appendAuctionTypes2(types) {
    const $dropdown = $("#auctionTypeDropdown");
    $dropdown.empty().append(`<option value="">Select Type</option>`);

    const isCreateMode = window.location.pathname.includes("/create/new");

    if (Array.isArray(types)) {

        console.log("Create/New");
        types.forEach(type => {
            const isSelected = isCreateMode && type.type_id == 1 ? "selected" : "";
            $dropdown.append(`<option value="${type.type_id}" ${isSelected}>${type.type_name}</option>`);
        });
    } else if (types && typeof types === "object") {

        console.log("Edit");

        const isSelected = isCreateMode && types.type_id == 1 ? "selected" : "";
        $dropdown.append(`<option value="${types.type_id}" ${isSelected}>${types.type_name}</option>`);
    }
}

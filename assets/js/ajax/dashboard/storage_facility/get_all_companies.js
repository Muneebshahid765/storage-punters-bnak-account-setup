// Mock API call functions – replace URLs with your actual endpoints
function getAllCompanies() {
    return $.ajax({
        url: SITE_URL + "api/user/fetch_companies_by_user",
        type: "POST",
        dataType: "json"
    });
}

function appendCompanies(companies) {
    const $dropdown = $("#companiesDropdown");
    $dropdown.empty().append(`<option value="">Select Company</option>`);

    if (Array.isArray(companies)) {
        companies.forEach(company => {
            $dropdown.append(`<option value="${company.company_id}" selected>${company.company_name}</option>`);
        });
    } else if (companies && typeof companies === "object") {
        // Handle single object fallback
        $dropdown.append(`<option value="${companies.company_id}">${companies.company_name}</option>`);
    }
}

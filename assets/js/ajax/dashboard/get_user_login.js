// Low-level AJAX call
function getUserLogin() {
    return $.ajax({
        url: SITE_URL + "api/user/authentication/details",
        type: "POST",
        dataType: "json"
    });
}

// High-level helper: always returns the user object or throws error
async function getUser() {
    try {
        // 🔹 Show spinner before API call
        showSpinner();

        let response = await getUserLogin();

        if (response.status) {
            return response.user;
        } else {
            throw new Error(response.message || "Not logged in");
        }
    } catch (err) {
        throw err;
    } finally {
        // 🔹 Hide spinner after API call (success or error)
        hideSpinner();
    }
}


/*
// Example usage
$(document).ready(async function () {
    try {
        let user = await getUser();
        console.log("User Email:", user.email);
        console.log("Full Name:", user.first_name + " " + user.last_name);
        console.log("Role:", user.role_name);
    } catch (err) {
        console.warn("Auth error:", err.message);
    }
});
*/
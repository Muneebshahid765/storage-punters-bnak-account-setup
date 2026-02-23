$(document).ready(function () {

    function checkMembershipRedirect() {
        $.ajax({
            url: SITE_URL + "api/user/checkUserLogin",
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.status === true && response.user_data.isLoggedIn) {
                    const userId = parseInt(response.user_data.userId);
                    const roleId = parseInt(response.user_data.roleId);

                    // ✅ User is logged in — check localStorage
                    const storedData = JSON.parse(localStorage.getItem("membershipVisit")) || {};

                    // Only skip redirect if BOTH match:
                    // 1. firstVisitDone === true
                    // 2. userId matches current logged-in user
                    if (storedData.membershipVisit === true && storedData.userId === userId) {
                        // Already visited — allow normal access
                        console.log("Returning user — stay on dashboard.");
                        return;
                    } else {
                        // First time or different user → mark and redirect
                        const visitData = {
                            membershipVisit: true,
                            membershipModalOpened: true,
                            userId: userId,
                            roleId: roleId
                        };

                        localStorage.setItem("membershipVisit", JSON.stringify(visitData));
                        console.log("First visit detected — redirecting to membership setup...");
                        window.location.href = SITE_URL + "show-dashboard/membership";
                    }
                } else {
                    // 🚫 User not logged in — no localStorage checks, no redirect
                    console.log("User not logged in — skipping membership redirect check.");
                }
            },
            error: function () {
                console.log("Error checking user login — skipping redirect.");
            }
        });
    }

    // ✅ Call the function after defining it
    checkMembershipRedirect();
});

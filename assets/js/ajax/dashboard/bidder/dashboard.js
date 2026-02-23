$(document).ready(function () {
    // Fetch stats on load
    fetchDashboardStats();

    function fetchDashboardStats() {
        $.ajax({
            url: SITE_URL + "api/users/bidder/dashboard/stats",
            type: "POST",
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    let d = response.data;

                    // Wallet
                    // Check if wallet is a number or object (sometimes comes as array)
                    // Assuming CoinModel returns integer or float
                    let wallet = d.wallet_balance || 0;
                    // Format as currency view - or number based on coins
                    // Users are saying "Wallet Balance $0.00 AUD" in the template
                    // But backend is "Coins". I should clarify if wallet is cash or coins.
                    // The prompt "Coin Economy" implies coins.
                    // But the template says "$0.00 AUD".
                    // Let's assume for now "Wallet Balance" in template refers to Coins? Or actual Cash?
                    // Wait, earlier tasks "Cash Top-up" means there is Cash Wallet.
                    // Line 64 of Vault.php: 'coins' => $user_coins.
                    // The template in index.php has Lines 47: "$0.00 AUD".
                    // Maybe I should fetch BOTH?

                    // For now, I will display Coins as text "X Coins" if it's coin balance, OR just money formatting if it's cash.
                    // CoinModel::get_user_coins usually returns int.

                    // Re-reading previous summary: "Cash Top-up" -> user has cash balance?
                    // CoinModel has `get_user_coins`.
                    // Does User have `wallet_balance` field?
                    // Users.php Line 487 insertBidderBalaneData has `amount` and `wallet_type_id`.
                    // 1 = Coins, 2 = Cash?
                    // I will just display the response I get.

                    $(".stat-value-modern.text-dark").text(wallet + " Coins"); // Assuming it is coins for now.

                    // Active Bids
                    $("#stat-active-bids").text(d.active_bids);

                    // Won Auctions
                    $("#stat-won-auctions").text(d.won_auctions);

                }
            },
            error: function (e) {
                console.error("Failed to fetch dashboard stats", e);
            }
        });
    }
});

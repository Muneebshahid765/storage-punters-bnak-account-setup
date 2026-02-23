<?php
$host = '127.0.0.1';
$user = 'homestead';
$pass = 'secret';
$db   = 'storagepunters';
$port = 8889;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "--- Signup Coin Bonus Verification ---\n\n";

// 1. Check signup bonus settings
$res = $conn->query("SELECT * FROM tbl_signup_bonus_settings WHERE id = 1");
$settings = $res->fetch_assoc();
echo "✓ Signup Bonus Settings:\n";
echo "  - Enabled: " . $settings['enabled'] . "\n";
echo "  - Wallet Amount: $" . $settings['amount'] . "\n";
echo "  - Signup Coins: " . $settings['signup_coins'] . "\n";
echo "  - Expiration Days: " . $settings['expiration_days'] . "\n\n";

// 2. Check a test user's coin balance
$testUserId = 17; // Using existing test user
$res = $conn->query("SELECT coin_balance FROM tbl_users WHERE id = $testUserId");
if ($res && $res->num_rows > 0) {
    $user = $res->fetch_assoc();
    echo "✓ Test User (ID: $testUserId) Coin Balance: " . $user['coin_balance'] . " coins\n\n";
}

// 3. Check coin transactions for signup bonuses
$res = $conn->query("SELECT * FROM tbl_coin_transactions WHERE description = 'Signup bonus' ORDER BY created_at DESC LIMIT 5");
echo "✓ Recent Signup Coin Transactions:\n";
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo "  - User ID: " . $row['user_id'] . ", Coins: " . $row['coins_delta'] . ", Date: " . $row['created_at'] . "\n";
    }
} else {
    echo "  - No signup coin transactions found yet\n";
}

echo "\n--- Verification Complete ---\n";

$conn->close();
?>

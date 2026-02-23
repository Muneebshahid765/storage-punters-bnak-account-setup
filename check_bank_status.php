<?php
// Quick script to check bank account status
define('BASEPATH', TRUE);

$db_config = include 'application/config/database.php';
$db = $db_config['default'];

$conn = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h3>Bank Accounts Status</h3>";
$result = $conn->query("SELECT id, user_id, account_name, bsb, stripe_bank_account_id, status_type_id, is_default FROM tbl_bank ORDER BY id DESC LIMIT 10");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>User ID</th><th>Account Name</th><th>BSB</th><th>Stripe ID</th><th>Status</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['user_id']}</td>";
    echo "<td>{$row['account_name']}</td>";
    echo "<td>{$row['bsb']}</td>";
    echo "<td>" . substr($row['stripe_bank_account_id'], 0, 20) . "...</td>";
    echo "<td><strong>" . ($row['status_type_id'] == 1 ? 'ACTIVE' : 'INACTIVE') . "</strong></td>";
    echo "<td>{$row['is_default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>User Stripe Connect IDs</h3>";
$result2 = $conn->query("SELECT id, email, stripe_connect_account_id FROM tbl_users WHERE stripe_connect_account_id IS NOT NULL LIMIT 10");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>User ID</th><th>Email</th><th>Stripe Connect ID</th></tr>";
while ($row = $result2->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['email']}</td>";
    echo "<td>" . substr($row['stripe_connect_account_id'], 0, 30) . "...</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>

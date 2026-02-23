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

$queries = [
    "CREATE TABLE IF NOT EXISTS tbl_bidder_subscription_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plan_name VARCHAR(100) NOT NULL,
        fee_reduction DECIMAL(5,2) NOT NULL,
        price_28_days DECIMAL(10,2) NOT NULL,
        price_year DECIMAL(10,2) NOT NULL,
        created_at DATETIME,
        updated_at DATETIME
    );",
    "CREATE TABLE IF NOT EXISTS tbl_facility_subscription_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plan_name VARCHAR(100) NOT NULL,
        monthly_price DECIMAL(10,2) NOT NULL,
        annual_price DECIMAL(10,2) NOT NULL,
        created_at DATETIME,
        updated_at DATETIME
    );",
    "CREATE TABLE IF NOT EXISTS tbl_facility_plan_features (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plan_id INT NOT NULL,
        feature_name VARCHAR(255) NOT NULL,
        created_at DATETIME
    );",
    "CREATE TABLE IF NOT EXISTS tbl_signup_bonus_settings (
        id INT PRIMARY KEY,
        enabled TINYINT(1) DEFAULT 0,
        amount DECIMAL(10,2) DEFAULT 0.00,
        expiration_days INT DEFAULT 90,
        updated_at DATETIME
    );",
    "INSERT IGNORE INTO tbl_signup_bonus_settings (id, enabled, amount, expiration_days, updated_at) VALUES (1, 1, 40.00, 90, NOW());",
    "ALTER TABLE tbl_platform_settings ADD COLUMN IF NOT EXISTS platform_fee DECIMAL(5,2) DEFAULT 5.00"
];

foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "Executed: " . substr($sql, 0, 50) . "...\n";
    } else {
        echo "Failed: " . $conn->error . "\n";
    }
}

$conn->close();
unlink(__FILE__);
?>

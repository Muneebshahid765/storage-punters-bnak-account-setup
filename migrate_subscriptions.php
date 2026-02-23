<?php
define('BASEPATH', 'TRUE');
require_once('index.php');
$CI =& get_instance();
$CI->load->database();

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
    "INSERT IGNORE INTO tbl_signup_bonus_settings (id, enabled, amount, expiration_days, updated_at) VALUES (1, 1, 40.00, 90, NOW());"
];

foreach ($queries as $sql) {
    if ($CI->db->query($sql)) {
        echo "Executed: " . substr($sql, 0, 50) . "...\n";
    } else {
        echo "Failed: " . $CI->db->error()['message'] . "\n";
    }
}

// Add platform_fee column if missing
$check = $CI->db->query("SHOW COLUMNS FROM tbl_platform_settings LIKE 'platform_fee'");
if ($check->num_rows() == 0) {
    $CI->db->query("ALTER TABLE tbl_platform_settings ADD COLUMN platform_fee DECIMAL(5,2) DEFAULT 5.00");
    echo "Added platform_fee column.\n";
}

unlink(__FILE__);
?>

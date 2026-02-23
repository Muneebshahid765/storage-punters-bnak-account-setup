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

// Check if platform_fee column exists in tbl_platform_settings
$result = $conn->query("SHOW COLUMNS FROM tbl_platform_settings LIKE 'platform_fee'");
if ($result->num_rows == 0) {
    if ($conn->query("ALTER TABLE tbl_platform_settings ADD COLUMN platform_fee DECIMAL(5,2) DEFAULT 5.00")) {
        echo "Added platform_fee column.\n";
    } else {
        echo "Failed to add platform_fee column: " . $conn->error . "\n";
    }
} else {
    echo "platform_fee column already exists.\n";
}

$conn->close();
unlink(__FILE__);
?>

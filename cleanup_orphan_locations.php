<?php
// Script to delete orphan locations with no data
$conn = new mysqli('localhost', 'root', '', 'storagepunters');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Cleaning Up Orphan Locations</h2>";

// Delete completely empty orphan locations
$orphan_ids = [54, 60, 114, 115, 116, 117, 118, 119, 120];

foreach ($orphan_ids as $id) {
    $sql = "DELETE FROM tbl_locations WHERE id = $id";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Deleted orphan location ID: $id</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to delete location ID $id: " . $conn->error . "</p>";
    }
}

echo "<br><h3>Cleanup Complete!</h3>";
echo "<p>Run <a href='fix_missing_locations.php'>fix_missing_locations.php</a> again to verify all issues are resolved.</p>";

$conn->close();
?>

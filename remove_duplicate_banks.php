<?php
// Script to remove duplicate bank accounts (keep the oldest one)
$conn = new mysqli('localhost', 'root', '', 'storagepunters');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h3>Finding Duplicate Bank Accounts...</h3>";

// Find duplicates based on user_id, bsb, and account_number
$sql = "
SELECT 
    user_id, 
    bsb, 
    account_number,
    COUNT(*) as count,
    GROUP_CONCAT(id ORDER BY id ASC) as ids
FROM tbl_bank 
WHERE status_type_id = 1
GROUP BY user_id, bsb, account_number
HAVING COUNT(*) > 1
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>User ID</th><th>BSB</th><th>Account</th><th>Count</th><th>IDs</th><th>Action</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $ids = explode(',', $row['ids']);
        $keepId = array_shift($ids); // Keep the first (oldest) one
        $deleteIds = $ids; // Delete the rest
        
        echo "<tr>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['bsb']}</td>";
        echo "<td>{$row['account_number']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "<td>{$row['ids']}</td>";
        echo "<td>";
        
        if (!empty($deleteIds)) {
            foreach ($deleteIds as $deleteId) {
                // Deactivate instead of delete to maintain data integrity
                $updateSql = "UPDATE tbl_bank SET status_type_id = 2, updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("i", $deleteId);
                
                if ($stmt->execute()) {
                    echo "Deactivated ID {$deleteId}<br>";
                    
                    // Also update relationship table
                    $relSql = "UPDATE tbl_user_bank_informations_relationship SET status_type_id = 2, updated_at = NOW() WHERE bank_id = ?";
                    $relStmt = $conn->prepare($relSql);
                    $relStmt->bind_param("i", $deleteId);
                    $relStmt->execute();
                    $relStmt->close();
                } else {
                    echo "Failed to deactivate ID {$deleteId}<br>";
                }
                $stmt->close();
            }
            echo "Kept ID {$keepId}";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No duplicate bank accounts found.</p>";
}

echo "<h3>Current Active Bank Accounts After Cleanup</h3>";
$result2 = $conn->query("SELECT id, user_id, account_name, bsb, account_number, status_type_id FROM tbl_bank WHERE status_type_id = 1 ORDER BY user_id, id");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>User ID</th><th>Account Name</th><th>BSB</th><th>Account Number</th><th>Status</th></tr>";
while ($row = $result2->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['user_id']}</td>";
    echo "<td>{$row['account_name']}</td>";
    echo "<td>{$row['bsb']}</td>";
    echo "<td>{$row['account_number']}</td>";
    echo "<td>" . ($row['status_type_id'] == 1 ? 'Active' : 'Inactive') . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
echo "<p><strong>Done! Refresh your bank accounts page.</strong></p>";
?>

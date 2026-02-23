<?php
// Script to identify and fix missing storage facility location data
$conn = new mysqli('localhost', 'root', '', 'storagepunters');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Storage Facility Location Data Analysis</h2>";

// 1. Check storage facilities without location relationships
echo "<h3>1. Storage Facilities Without Location Relationships</h3>";
$sql = "
SELECT sf.id, sf.name 
FROM tbl_storage_facilities sf
LEFT JOIN tbl_storage_facility_location_relationship sflr ON sf.id = sflr.storage_facility_id
WHERE sflr.location_id IS NULL
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Facility ID</th><th>Facility Name</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td style='color: red;'>Missing Location Link</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✓ All facilities have location relationships</p>";
}

// 2. Check locations with NULL city or state
echo "<h3>2. Locations With Missing City/State Data</h3>";
$sql = "
SELECT l.id, l.street_address, l.city, l.state, sflr.storage_facility_id, sf.name as facility_name
FROM tbl_locations l
LEFT JOIN tbl_storage_facility_location_relationship sflr ON l.id = sflr.location_id
LEFT JOIN tbl_storage_facilities sf ON sflr.storage_facility_id = sf.id
WHERE l.city IS NULL OR l.state IS NULL OR l.city = '' OR l.state = ''
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Location ID</th><th>Facility</th><th>Street</th><th>City</th><th>State</th><th>Issue</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $issues = [];
        if (empty($row['city'])) $issues[] = 'City missing';
        if (empty($row['state'])) $issues[] = 'State missing';
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['facility_name']}</td>";
        echo "<td>{$row['street_address']}</td>";
        echo "<td>" . ($row['city'] ?: '<span style="color:red;">NULL</span>') . "</td>";
        echo "<td>" . ($row['state'] ?: '<span style="color:red;">NULL</span>') . "</td>";
        echo "<td style='color: red;'>" . implode(', ', $issues) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✓ All locations have city and state data</p>";
}

// 3. Check auctions without storage facility mapping
echo "<h3>3. Auctions Without Storage Facility Mapping</h3>";
$sql = "
SELECT a.id, a.description, a.unit_number
FROM tbl_auctions a
LEFT JOIN tbl_auction_storage_facility_relationship asfr ON a.id = asfr.auction_id
WHERE asfr.storage_facility_id IS NULL AND a.is_staged = 0
LIMIT 20
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Auction ID</th><th>Description</th><th>Unit Number</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . substr($row['description'], 0, 50) . "...</td>";
        echo "<td>{$row['unit_number']}</td>";
        echo "<td style='color: red;'>No Facility Linked</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✓ All auctions have storage facility mappings</p>";
}

// 4. Complete data chain verification
echo "<h3>4. Complete Auction → Facility → Location Chain</h3>";
$sql = "
SELECT 
    a.id as auction_id,
    sf.id as facility_id,
    sf.name as facility_name,
    l.id as location_id,
    l.city,
    l.state
FROM tbl_auctions a
LEFT JOIN tbl_auction_storage_facility_relationship asfr ON a.id = asfr.auction_id
LEFT JOIN tbl_storage_facilities sf ON asfr.storage_facility_id = sf.id
LEFT JOIN tbl_storage_facility_location_relationship sflr ON sf.id = sflr.storage_facility_id
LEFT JOIN tbl_locations l ON sflr.location_id = l.id
WHERE a.is_staged = 0
AND (sf.id IS NULL OR l.id IS NULL OR l.city IS NULL OR l.state IS NULL)
LIMIT 20
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Auction ID</th><th>Facility ID</th><th>Facility Name</th><th>Location ID</th><th>City</th><th>State</th><th>Issue</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $issues = [];
        if (!$row['facility_id']) $issues[] = 'No facility';
        if (!$row['location_id']) $issues[] = 'No location';
        if (empty($row['city'])) $issues[] = 'No city';
        if (empty($row['state'])) $issues[] = 'No state';
        
        echo "<tr>";
        echo "<td>{$row['auction_id']}</td>";
        echo "<td>" . ($row['facility_id'] ?: '<span style="color:red;">NULL</span>') . "</td>";
        echo "<td>" . ($row['facility_name'] ?: '<span style="color:red;">NULL</span>') . "</td>";
        echo "<td>" . ($row['location_id'] ?: '<span style="color:red;">NULL</span>') . "</td>";
        echo "<td>" . ($row['city'] ?: '<span style="color:red;">NULL</span>') . "</td>";
        echo "<td>" . ($row['state'] ?: '<span style="color:red;">NULL</span>') . "</td>";
        echo "<td style='color: red;'>" . implode(', ', $issues) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p><strong>⚠️ Action Required:</strong> Fix the issues above by:</p>";
    echo "<ol>";
    echo "<li>Ensure all storage facilities have location data in admin panel</li>";
    echo "<li>Link auctions to correct storage facilities</li>";
    echo "<li>Fill in city and state for all locations</li>";
    echo "</ol>";
} else {
    echo "<p style='color: green; font-size: 18px;'>✓ All auction → facility → location chains are complete!</p>";
}

// 5. Sample of working data
echo "<h3>5. Sample of Correctly Configured Auctions (First 5)</h3>";
$sql = "
SELECT 
    a.id as auction_id,
    a.description,
    sf.name as facility_name,
    l.city,
    l.state
FROM tbl_auctions a
INNER JOIN tbl_auction_storage_facility_relationship asfr ON a.id = asfr.auction_id
INNER JOIN tbl_storage_facilities sf ON asfr.storage_facility_id = sf.id
INNER JOIN tbl_storage_facility_location_relationship sflr ON sf.id = sflr.storage_facility_id
INNER JOIN tbl_locations l ON sflr.location_id = l.id
WHERE a.is_staged = 0 
AND l.city IS NOT NULL 
AND l.state IS NOT NULL
LIMIT 5
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Auction ID</th><th>Description</th><th>Facility</th><th>City</th><th>State</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr style='background-color: #e8f5e9;'>";
        echo "<td>{$row['auction_id']}</td>";
        echo "<td>" . substr($row['description'], 0, 40) . "...</td>";
        echo "<td>{$row['facility_name']}</td>";
        echo "<td>{$row['city']}</td>";
        echo "<td>{$row['state']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No correctly configured auctions found. Please add location data.</p>";
}

$conn->close();
echo "<br><p><strong>Next Step:</strong> Open this file in browser to see the analysis, then fix issues in admin panel or run SQL updates.</p>";
?>

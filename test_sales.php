<?php
include 'db connect.php';

echo "<h2>Sales Report Test</h2>";

// Check if there are any sold bikes
$sold_bikes = $conn->query("SELECT COUNT(*) as count FROM bikes WHERE status = 'sold'");
$sold_count = $sold_bikes->fetch_assoc()['count'];

echo "<p><strong>Total sold bikes:</strong> $sold_count</p>";

if ($sold_count > 0) {
    echo "<h3>Sold Bikes Details:</h3>";
    $sold_details = $conn->query("
        SELECT b.*, u.username as seller_name, buyer.username as buyer_name
        FROM bikes b 
        JOIN users u ON b.user_id = u.id 
        LEFT JOIN users buyer ON b.buyer_id = buyer.id
        WHERE b.status = 'sold'
        ORDER BY b.created_at DESC
    ");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Price</th><th>Seller</th><th>Buyer</th><th>Buyer ID</th></tr>";
    
    while ($bike = $sold_details->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $bike['id'] . "</td>";
        echo "<td>" . htmlspecialchars($bike['title']) . "</td>";
        echo "<td>₹" . number_format($bike['price']) . "</td>";
        echo "<td>" . htmlspecialchars($bike['seller_name']) . "</td>";
        echo "<td>" . htmlspecialchars($bike['buyer_name'] ?? 'NULL') . "</td>";
        echo "<td>" . ($bike['buyer_id'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No sold bikes found in the database.</p>";
    echo "<p>To test the sales report, you need to:</p>";
    echo "<ol>";
    echo "<li>Add some bikes to the database</li>";
    echo "<li>Mark some bikes as 'sold' by updating their status</li>";
    echo "<li>Set a buyer_id for sold bikes</li>";
    echo "</ol>";
}

// Test the sales report query
echo "<h3>Testing Sales Report Query:</h3>";
try {
    $test_query = $conn->query("
        SELECT b.*, u.username as seller_name, buyer.username as buyer_name
        FROM bikes b 
        JOIN users u ON b.user_id = u.id 
        LEFT JOIN users buyer ON b.buyer_id = buyer.id
        WHERE b.status = 'sold'
        ORDER BY b.created_at DESC
    ");
    
    if ($test_query) {
        echo "<p style='color: green;'>✅ Sales report query executed successfully</p>";
        echo "<p>Query returned " . $test_query->num_rows . " rows</p>";
    } else {
        echo "<p style='color: red;'>❌ Sales report query failed: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
}

echo "<h3>Database Tables Check:</h3>";
$tables = ['bikes', 'users'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
        echo "<p>✅ Table '$table' exists with $count records</p>";
    } else {
        echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
    }
}

echo "<h3>Sample Data for Testing:</h3>";
echo "<p>If you want to test the sales report, you can add sample data:</p>";
echo "<pre>";
echo "-- Add a test user
INSERT INTO users (username, email, password) VALUES ('testuser', 'test@example.com', 'password');

-- Add a test bike
INSERT INTO bikes (user_id, title, description, price, status) 
VALUES (1, 'Test Bike', 'Test Description', 50000, 'available');

-- Mark bike as sold (replace 1 with actual user ID)
UPDATE bikes SET status = 'sold', buyer_id = 1 WHERE id = 1;
</pre>";

echo "<p><a href='admin_reports.php'>Back to Reports</a></p>";
?> 
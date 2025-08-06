<?php
include 'db connect.php';

echo "<h2>Adding Test Data for Sales Report</h2>";

// Check current data
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$bikes_count = $conn->query("SELECT COUNT(*) as count FROM bikes")->fetch_assoc()['count'];
$sold_count = $conn->query("SELECT COUNT(*) as count FROM bikes WHERE status = 'sold'")->fetch_assoc()['count'];

echo "<p><strong>Current data:</strong></p>";
echo "<ul>";
echo "<li>Users: $users_count</li>";
echo "<li>Bikes: $bikes_count</li>";
echo "<li>Sold bikes: $sold_count</li>";
echo "</ul>";

// Add test users if none exist
if ($users_count == 0) {
    echo "<h3>Adding test users...</h3>";
    
    $test_users = [
        ['seller1', 'seller1@test.com', 'password123'],
        ['seller2', 'seller2@test.com', 'password123'],
        ['buyer1', 'buyer1@test.com', 'password123'],
        ['buyer2', 'buyer2@test.com', 'password123']
    ];
    
    foreach ($test_users as $user) {
        $username = $user[0];
        $email = $user[1];
        $password = password_hash($user[2], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Added user: $username</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add user: $username - " . $conn->error . "</p>";
        }
        $stmt->close();
    }
}

// Add test bikes if none exist
if ($bikes_count == 0) {
    echo "<h3>Adding test bikes...</h3>";
    
    $test_bikes = [
        ['Honda Activa', 'Well maintained scooter', 45000, 1],
        ['Bajaj Pulsar', 'Good condition bike', 65000, 1],
        ['TVS Jupiter', 'Excellent condition', 35000, 2],
        ['Hero Splendor', 'Reliable commuter bike', 40000, 2]
    ];
    
    foreach ($test_bikes as $bike) {
        $title = $bike[0];
        $description = $bike[1];
        $price = $bike[2];
        $user_id = $bike[3];
        
        $stmt = $conn->prepare("INSERT INTO bikes (user_id, title, description, price, status) VALUES (?, ?, ?, ?, 'available')");
        $stmt->bind_param("issd", $user_id, $title, $description, $price);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Added bike: $title</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add bike: $title - " . $conn->error . "</p>";
        }
        $stmt->close();
    }
}

// Mark some bikes as sold
echo "<h3>Marking bikes as sold...</h3>";

// Get available bikes
$available_bikes = $conn->query("SELECT id, user_id FROM bikes WHERE status = 'available' LIMIT 2");
$buyer_ids = [3, 4]; // buyer1 and buyer2

$sold_count = 0;
while ($bike = $available_bikes->fetch_assoc()) {
    $bike_id = $bike['id'];
    $buyer_id = $buyer_ids[$sold_count % count($buyer_ids)];
    
    $stmt = $conn->prepare("UPDATE bikes SET status = 'sold', buyer_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $buyer_id, $bike_id);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Marked bike ID $bike_id as sold to buyer ID $buyer_id</p>";
        $sold_count++;
    } else {
        echo "<p style='color: red;'>❌ Failed to mark bike ID $bike_id as sold - " . $conn->error . "</p>";
    }
    $stmt->close();
}

// Show final data
echo "<h3>Final Data Summary:</h3>";
$final_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$final_bikes = $conn->query("SELECT COUNT(*) as count FROM bikes")->fetch_assoc()['count'];
$final_sold = $conn->query("SELECT COUNT(*) as count FROM bikes WHERE status = 'sold'")->fetch_assoc()['count'];

echo "<ul>";
echo "<li>Users: $final_users</li>";
echo "<li>Bikes: $final_bikes</li>";
echo "<li>Sold bikes: $final_sold</li>";
echo "</ul>";

// Show sold bikes details
if ($final_sold > 0) {
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
    echo "<tr><th>ID</th><th>Title</th><th>Price</th><th>Seller</th><th>Buyer</th><th>Status</th></tr>";
    
    while ($bike = $sold_details->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $bike['id'] . "</td>";
        echo "<td>" . htmlspecialchars($bike['title']) . "</td>";
        echo "<td>₹" . number_format($bike['price']) . "</td>";
        echo "<td>" . htmlspecialchars($bike['seller_name']) . "</td>";
        echo "<td>" . htmlspecialchars($bike['buyer_name'] ?? 'Unknown') . "</td>";
        echo "<td>" . $bike['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='admin_reports.php'>Go to Reports Page</a></li>";
echo "<li>Click 'Download CSV' for the Sales Report</li>";
echo "<li>The report should now work with the test data</li>";
echo "</ol>";

echo "<p><a href='debug_sales.php'>Check Sales Report Debug</a></p>";
?> 
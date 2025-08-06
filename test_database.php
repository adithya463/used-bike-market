<?php
include 'db connect.php';

echo "<h2>Database Connection Test</h2>";

// Test database connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Test required tables
$tables = ['users', 'bikes', 'wishlist', 'messages', 'cart'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        
        // Count records
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $count_result->fetch_assoc()['count'];
        echo "<p>📊 Records in $table: $count</p>";
    } else {
        echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
    }
}

// Test admin dashboard queries
echo "<h3>Testing Admin Dashboard Queries</h3>";

try {
    $user_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 0")->fetch_assoc()['total'] ?? 0;
    echo "<p>✅ Users count: $user_count</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Users query failed: " . $e->getMessage() . "</p>";
}

try {
    $bike_count = $conn->query("SELECT COUNT(*) as total FROM bikes")->fetch_assoc()['total'] ?? 0;
    echo "<p>✅ Bikes count: $bike_count</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Bikes query failed: " . $e->getMessage() . "</p>";
}

try {
    $sold_count = $conn->query("SELECT COUNT(*) as sold FROM bikes WHERE status = 'sold'")->fetch_assoc()['sold'] ?? 0;
    echo "<p>✅ Sold bikes count: $sold_count</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Sold bikes query failed: " . $e->getMessage() . "</p>";
}

try {
    $message_count = $conn->query("SELECT COUNT(*) as total FROM messages")->fetch_assoc()['total'] ?? 0;
    echo "<p>✅ Messages count: $message_count</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Messages query failed: " . $e->getMessage() . "</p>";
}

try {
    $wishlist_count = $conn->query("SELECT COUNT(*) as total FROM wishlist")->fetch_assoc()['total'] ?? 0;
    echo "<p>✅ Wishlist count: $wishlist_count</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Wishlist query failed: " . $e->getMessage() . "</p>";
}

try {
    $cart_count = $conn->query("SELECT COUNT(*) as total FROM cart")->fetch_assoc()['total'] ?? 0;
    echo "<p>✅ Cart count: $cart_count</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Cart query failed: " . $e->getMessage() . "</p>";
}

echo "<h3>Test Complete</h3>";
echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
?> 
<?php
include 'db connect.php';

echo "<h2>Sales Report Debug</h2>";

// Test database connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Check sold bikes
$sold_count = $conn->query("SELECT COUNT(*) as count FROM bikes WHERE status = 'sold'")->fetch_assoc()['count'];
echo "<p><strong>Sold bikes count:</strong> $sold_count</p>";

// Check if there are any bikes at all
$total_bikes = $conn->query("SELECT COUNT(*) as count FROM bikes")->fetch_assoc()['count'];
echo "<p><strong>Total bikes:</strong> $total_bikes</p>";

// Check if there are any users
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
echo "<p><strong>Total users:</strong> $total_users</p>";

// Test the sales report query
echo "<h3>Testing Sales Report Query:</h3>";
$test_query = $conn->query("
    SELECT b.*, u.username as seller_name, buyer.username as buyer_name
    FROM bikes b 
    JOIN users u ON b.user_id = u.id 
    LEFT JOIN users buyer ON b.buyer_id = buyer.id
    WHERE b.status = 'sold'
    ORDER BY b.created_at DESC
");

if ($test_query) {
    echo "<p style='color: green;'>✅ Query executed successfully</p>";
    echo "<p>Query returned " . $test_query->num_rows . " rows</p>";
    
    if ($test_query->num_rows > 0) {
        echo "<h4>Sample Data:</h4>";
        $sample = $test_query->fetch_assoc();
        echo "<pre>";
        print_r($sample);
        echo "</pre>";
    }
} else {
    echo "<p style='color: red;'>❌ Query failed: " . $conn->error . "</p>";
}

// Test CSV generation
echo "<h3>Testing CSV Generation:</h3>";
try {
    ob_start();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="test_sales.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Bike ID', 'Title', 'Price', 'Seller', 'Buyer', 'Sale Date']);
    
    if ($sold_count > 0) {
        $sales_data = $conn->query("
            SELECT b.*, u.username as seller_name, buyer.username as buyer_name
            FROM bikes b 
            JOIN users u ON b.user_id = u.id 
            LEFT JOIN users buyer ON b.buyer_id = buyer.id
            WHERE b.status = 'sold'
            ORDER BY b.created_at DESC
        ");
        
        while ($sale = $sales_data->fetch_assoc()) {
            fputcsv($output, [
                $sale['id'],
                $sale['title'],
                $sale['price'],
                $sale['seller_name'],
                $sale['buyer_name'] ?? 'Unknown',
                $sale['created_at']
            ]);
        }
    } else {
        fputcsv($output, ['No Data', 'No Sales Found', '0', 'N/A', 'N/A', 'N/A']);
    }
    
    fclose($output);
    $csv_content = ob_get_clean();
    
    echo "<p style='color: green;'>✅ CSV generation successful</p>";
    echo "<p>CSV content length: " . strlen($csv_content) . " bytes</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CSV generation failed: " . $e->getMessage() . "</p>";
}

echo "<h3>Recommendations:</h3>";
if ($sold_count == 0) {
    echo "<p>To test the sales report, you need to:</p>";
    echo "<ol>";
    echo "<li>Add some bikes to the database</li>";
    echo "<li>Mark some bikes as 'sold' by updating their status</li>";
    echo "<li>Set a buyer_id for sold bikes</li>";
    echo "</ol>";
    
    echo "<h4>Sample SQL to add test data:</h4>";
    echo "<pre>";
    echo "-- Add a test user if none exist
INSERT INTO users (username, email, password) VALUES ('testuser', 'test@example.com', 'password');

-- Add a test bike
INSERT INTO bikes (user_id, title, description, price, status) 
VALUES (1, 'Test Bike', 'Test Description', 50000, 'available');

-- Mark bike as sold (replace 1 with actual user ID)
UPDATE bikes SET status = 'sold', buyer_id = 1 WHERE id = 1;
</pre>";
}

echo "<p><a href='admin_reports.php'>Back to Reports</a></p>";
?> 
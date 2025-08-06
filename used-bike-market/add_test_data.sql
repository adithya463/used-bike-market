-- Add test data for sales report testing
-- Run this in your MySQL database (phpMyAdmin or command line)

USE bike_market;

-- Add test users
INSERT INTO users (username, email, password, is_admin) VALUES 
('seller1', 'seller1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('seller2', 'seller2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('buyer1', 'buyer1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('buyer2', 'buyer2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0)
ON DUPLICATE KEY UPDATE username = username;

-- Add test bikes
INSERT INTO bikes (user_id, title, description, price, status, year, mileage, fuel_type, transmission, color, location) VALUES 
(1, 'Honda Activa 6G', 'Well maintained scooter with good mileage', 45000.00, 'available', 2019, 15000, 'Petrol', 'Automatic', 'White', 'Mumbai'),
(1, 'Bajaj Pulsar 150', 'Good condition bike with recent service', 65000.00, 'available', 2018, 25000, 'Petrol', 'Manual', 'Black', 'Delhi'),
(2, 'TVS Jupiter', 'Excellent condition scooter', 35000.00, 'available', 2020, 12000, 'Petrol', 'Automatic', 'Red', 'Bangalore'),
(2, 'Hero Splendor Plus', 'Reliable commuter bike', 40000.00, 'available', 2019, 18000, 'Petrol', 'Manual', 'Blue', 'Chennai')
ON DUPLICATE KEY UPDATE title = title;

-- Mark some bikes as sold
UPDATE bikes SET status = 'sold', buyer_id = 3 WHERE id = 1;
UPDATE bikes SET status = 'sold', buyer_id = 4 WHERE id = 2;

-- Verify the data
SELECT 'Users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'Bikes' as table_name, COUNT(*) as count FROM bikes
UNION ALL
SELECT 'Sold Bikes' as table_name, COUNT(*) as count FROM bikes WHERE status = 'sold';

-- Show sold bikes with seller and buyer info
SELECT 
    b.id,
    b.title,
    b.price,
    seller.username as seller_name,
    buyer.username as buyer_name,
    b.status,
    b.created_at
FROM bikes b 
JOIN users seller ON b.user_id = seller.id 
LEFT JOIN users buyer ON b.buyer_id = buyer.id
WHERE b.status = 'sold'
ORDER BY b.created_at DESC; 
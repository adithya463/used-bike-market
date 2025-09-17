-- Update existing bikes table with new fields
USE bike_market;

-- Add new columns to bikes table if they don't exist
ALTER TABLE bikes 
ADD COLUMN IF NOT EXISTS year INT,
ADD COLUMN IF NOT EXISTS mileage INT,
ADD COLUMN IF NOT EXISTS engine_capacity VARCHAR(50),
ADD COLUMN IF NOT EXISTS fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'Hybrid'),
ADD COLUMN IF NOT EXISTS transmission ENUM('Manual', 'Automatic'),
ADD COLUMN IF NOT EXISTS color VARCHAR(50),
ADD COLUMN IF NOT EXISTS location VARCHAR(255),
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS sold_at TIMESTAMP NULL DEFAULT NULL;

-- Create wishlist table if it doesn't exist
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bike_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, bike_id)
);

-- Add some sample data for testing (optional)
-- INSERT INTO bikes (user_id, title, description, price, image, year, mileage, fuel_type, transmission, color, location) 
-- VALUES 
-- (1, 'Honda Activa 6G', 'Well maintained scooter with low mileage', 45000, 'activa.jpg', 2020, 15000, 'Petrol', 'Automatic', 'White', 'Mumbai, Maharashtra'),
-- (1, 'Bajaj Pulsar 150', 'Sporty bike in excellent condition', 65000, 'pulsar.jpg', 2019, 25000, 'Petrol', 'Manual', 'Red', 'Delhi, NCR'),
-- (1, 'TVS Jupiter', 'Family scooter with good mileage', 35000, 'jupiter.jpg', 2021, 8000, 'Petrol', 'Automatic', 'Blue', 'Bangalore, Karnataka'); 
<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'config/Security.php';

session_start();

// Initialize database
$db = Database::getInstance();
$conn = $db->getConnection();

// Get featured bikes
$featuredBikes = $conn->query("
    SELECT b.*, u.username, u.city as seller_city,
           (SELECT image_path FROM bike_images WHERE bike_id = b.id AND is_primary = 1 LIMIT 1) as primary_image,
           (SELECT AVG(rating) FROM reviews WHERE reviewee_id = b.user_id) as seller_rating
    FROM bikes b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.status = 'available' AND b.featured = 1
    ORDER BY b.created_at DESC 
    LIMIT 6
")->fetchAll();

// Get latest bikes
$latestBikes = $conn->query("
    SELECT b.*, u.username, u.city as seller_city,
           (SELECT image_path FROM bike_images WHERE bike_id = b.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM bikes b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.status = 'available'
    ORDER BY b.created_at DESC 
    LIMIT 8
")->fetchAll();

// Get statistics
$stats = [
    'total_bikes' => $conn->query("SELECT COUNT(*) as count FROM bikes WHERE status = 'available'")->fetch()['count'],
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch()['count'],
    'total_sales' => $conn->query("SELECT COUNT(*) as count FROM bikes WHERE status = 'sold'")->fetch()['count'],
    'cities' => $conn->query("SELECT COUNT(DISTINCT city) as count FROM bikes WHERE city IS NOT NULL")->fetch()['count']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Professional Used Bike Marketplace</title>
    <meta name="description" content="Find the best deals on used bikes. Professional marketplace with verified sellers, advanced search, and secure transactions.">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Styles -->
    <link href="assets/css/main.css" rel="stylesheet">
    
    <!-- Meta Tags -->
    <meta property="og:title" content="<?php echo APP_NAME; ?> - Professional Used Bike Marketplace">
    <meta property="og:description" content="Find the best deals on used bikes with verified sellers and secure transactions.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo APP_URL; ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo APP_NAME; ?>">
    <meta name="twitter:description" content="Professional used bike marketplace">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="flex justify-between items-center py-4">
                <a href="index.php" class="navbar-brand">
                    <i class="fas fa-motorcycle mr-2"></i>
                    <?php echo APP_NAME; ?>
                </a>
                
                <div class="flex items-center gap-6">
                    <a href="view_bikes.php" class="text-gray-600 hover:text-primary">
                        <i class="fas fa-search mr-1"></i> Browse Bikes
                    </a>
                    <a href="add_bike.php" class="text-gray-600 hover:text-primary">
                        <i class="fas fa-plus mr-1"></i> Sell Bike
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="relative">
                            <button class="flex items-center gap-2 text-gray-600 hover:text-primary">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden">
                                <a href="dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                </a>
                                <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> Profile
                                </a>
                                <a href="messages.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-envelope mr-2"></i> Messages
                                </a>
                                <a href="wishlist_view.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-heart mr-2"></i> Wishlist
                                </a>
                                <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login</a>
                        <a href="register.php" class="btn btn-secondary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-primary-dark text-white py-20">
        <div class="container">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Find Your Perfect Ride
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100">
                    Professional marketplace for buying and selling used bikes
                </p>
                
                <!-- Advanced Search Bar -->
                <div class="max-w-4xl mx-auto">
                    <form action="view_bikes.php" method="GET" class="bg-white rounded-lg p-6 shadow-xl">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <input type="text" 
                                       name="q" 
                                       placeholder="Search bikes, brands, models..."
                                       class="form-input"
                                       value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                            </div>
                            <div>
                                <select name="city" class="form-select">
                                    <option value="">All Cities</option>
                                    <?php
                                    $cities = $conn->query("SELECT DISTINCT city FROM bikes WHERE city IS NOT NULL ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
                                    foreach ($cities as $city): ?>
                                        <option value="<?php echo htmlspecialchars($city); ?>" 
                                                <?php echo ($_GET['city'] ?? '') === $city ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($city); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <select name="fuel_type" class="form-select">
                                    <option value="">All Fuel Types</option>
                                    <option value="Petrol" <?php echo ($_GET['fuel_type'] ?? '') === 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                                    <option value="Diesel" <?php echo ($_GET['fuel_type'] ?? '') === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                                    <option value="Electric" <?php echo ($_GET['fuel_type'] ?? '') === 'Electric' ? 'selected' : ''; ?>>Electric</option>
                                    <option value="Hybrid" <?php echo ($_GET['fuel_type'] ?? '') === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary w-full">
                                    <i class="fas fa-search mr-2"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-16 bg-white">
        <div class="container">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl font-bold text-primary mb-2"><?php echo number_format($stats['total_bikes']); ?></div>
                    <div class="text-gray-600">Bikes Available</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-primary mb-2"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="text-gray-600">Verified Users</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-primary mb-2"><?php echo number_format($stats['total_sales']); ?></div>
                    <div class="text-gray-600">Successful Sales</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-primary mb-2"><?php echo number_format($stats['cities']); ?></div>
                    <div class="text-gray-600">Cities Covered</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Bikes Section -->
    <?php if (!empty($featuredBikes)): ?>
    <section class="py-16 bg-gray-50">
        <div class="container">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Bikes</h2>
                <p class="text-gray-600">Handpicked bikes from verified sellers</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($featuredBikes as $bike): ?>
                <div class="bike-card">
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($bike['primary_image'] ?? 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($bike['title']); ?>" 
                             class="bike-image">
                        <div class="absolute top-2 right-2">
                            <span class="bg-primary text-white px-2 py-1 rounded text-xs font-semibold">
                                Featured
                            </span>
                        </div>
                        <div class="absolute top-2 left-2">
                            <span class="bg-white text-gray-700 px-2 py-1 rounded text-xs">
                                <?php echo htmlspecialchars($bike['condition_rating'] ?? 'Good'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="bike-info">
                        <h3 class="bike-title"><?php echo htmlspecialchars($bike['title']); ?></h3>
                        <div class="bike-price">₹<?php echo number_format($bike['price']); ?></div>
                        <div class="bike-details">
                            <span><i class="fas fa-calendar"></i> <?php echo $bike['year'] ?? 'N/A'; ?></span>
                            <span><i class="fas fa-tachometer-alt"></i> <?php echo $bike['mileage'] ? number_format($bike['mileage']) . ' km' : 'N/A'; ?></span>
                            <span><i class="fas fa-gas-pump"></i> <?php echo $bike['fuel_type'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="bike-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($bike['city'] ?? 'Unknown Location'); ?>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <a href="view_bike.php?id=<?php echo $bike['id']; ?>" class="btn btn-primary btn-sm">
                                View Details
                            </a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="btn btn-secondary btn-sm wishlist-btn" data-bike-id="<?php echo $bike['id']; ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="view_bikes.php" class="btn btn-primary btn-lg">
                    View All Bikes
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Latest Bikes Section -->
    <section class="py-16 bg-white">
        <div class="container">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Latest Bikes</h2>
                <p class="text-gray-600">Recently added bikes from our community</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach (array_slice($latestBikes, 0, 8) as $bike): ?>
                <div class="bike-card">
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($bike['primary_image'] ?? 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($bike['title']); ?>" 
                             class="bike-image">
                        <div class="absolute top-2 right-2">
                            <span class="bg-success text-white px-2 py-1 rounded text-xs">
                                New
                            </span>
                        </div>
                    </div>
                    <div class="bike-info">
                        <h3 class="bike-title"><?php echo htmlspecialchars($bike['title']); ?></h3>
                        <div class="bike-price">₹<?php echo number_format($bike['price']); ?></div>
                        <div class="bike-details">
                            <span><i class="fas fa-calendar"></i> <?php echo $bike['year'] ?? 'N/A'; ?></span>
                            <span><i class="fas fa-tachometer-alt"></i> <?php echo $bike['mileage'] ? number_format($bike['mileage']) . ' km' : 'N/A'; ?></span>
                        </div>
                        <div class="bike-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($bike['city'] ?? 'Unknown Location'); ?>
                        </div>
                        <div class="mt-4">
                            <a href="view_bike.php?id=<?php echo $bike['id']; ?>" class="btn btn-primary btn-sm w-full">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-gray-50">
        <div class="container">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose BikeMart Pro?</h2>
                <p class="text-gray-600">Professional features for a better buying and selling experience</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Verified Sellers</h3>
                    <p class="text-gray-600">All sellers are verified with proper documentation and background checks.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Advanced Search</h3>
                    <p class="text-gray-600">Find exactly what you're looking for with our powerful search and filter system.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Secure Transactions</h3>
                    <p class="text-gray-600">Safe and secure payment processing with buyer protection.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4"><?php echo APP_NAME; ?></h3>
                    <p class="text-gray-400 mb-4">Professional marketplace for buying and selling used bikes.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="view_bikes.php" class="text-gray-400 hover:text-white">Browse Bikes</a></li>
                        <li><a href="add_bike.php" class="text-gray-400 hover:text-white">Sell Your Bike</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="faq.php" class="text-gray-400 hover:text-white">FAQ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="terms.php" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="help.php" class="text-gray-400 hover:text-white">Help Center</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <div class="space-y-2 text-gray-400">
                        <p><i class="fas fa-envelope mr-2"></i> support@bikemart.com</p>
                        <p><i class="fas fa-phone mr-2"></i> +91-9876543210</p>
                        <p><i class="fas fa-map-marker-alt mr-2"></i> Mumbai, India</p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>

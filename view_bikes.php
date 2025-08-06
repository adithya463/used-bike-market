<?php
session_start();
include 'db connect.php';

// Handle advanced filters
$where = ["b.status = 'available'"];
$params = [];

// Basic search
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where[] = "(b.title LIKE '%$search%' OR b.description LIKE '%$search%')";
}

// Brand/Model filter
if (!empty($_GET['brand'])) {
    $brand = $conn->real_escape_string($_GET['brand']);
    $where[] = "b.title LIKE '%$brand%'";
}

// Price range
if (!empty($_GET['min_price'])) {
    $min_price = (float)$_GET['min_price'];
    $where[] = "b.price >= $min_price";
}
if (!empty($_GET['max_price'])) {
    $max_price = (float)$_GET['max_price'];
    $where[] = "b.price <= $max_price";
}

// Year range
if (!empty($_GET['min_year'])) {
    $min_year = (int)$_GET['min_year'];
    $where[] = "b.year >= $min_year";
}
if (!empty($_GET['max_year'])) {
    $max_year = (int)$_GET['max_year'];
    $where[] = "b.year <= $max_year";
}

// Mileage range
if (!empty($_GET['max_mileage'])) {
    $max_mileage = (int)$_GET['max_mileage'];
    $where[] = "b.mileage <= $max_mileage";
}

// Fuel type
if (!empty($_GET['fuel_type'])) {
    $fuel_type = $conn->real_escape_string($_GET['fuel_type']);
    $where[] = "b.fuel_type = '$fuel_type'";
}

// Transmission
if (!empty($_GET['transmission'])) {
    $transmission = $conn->real_escape_string($_GET['transmission']);
    $where[] = "b.transmission = '$transmission'";
}

// Location
if (!empty($_GET['location'])) {
    $location = $conn->real_escape_string($_GET['location']);
    $where[] = "b.location LIKE '%$location%'";
}

// My Listings filter
if (isset($_SESSION['user_id']) && !empty($_GET['my_listings'])) {
    $user_id = (int)$_SESSION['user_id'];
    $where[] = "b.user_id = $user_id";
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Sorting
$order = '';
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc': $order = 'ORDER BY b.price ASC'; break;
        case 'price_desc': $order = 'ORDER BY b.price DESC'; break;
        case 'newest': $order = 'ORDER BY b.created_at DESC'; break;
        case 'oldest': $order = 'ORDER BY b.created_at ASC'; break;
        case 'mileage_low': $order = 'ORDER BY b.mileage ASC'; break;
        case 'mileage_high': $order = 'ORDER BY b.mileage DESC'; break;
        case 'year_new': $order = 'ORDER BY b.year DESC'; break;
        case 'year_old': $order = 'ORDER BY b.year ASC'; break;
    }
} else {
    $order = 'ORDER BY b.created_at DESC'; // Default sort
}

// Get wishlist status for logged-in users
$wishlist_join = '';
$wishlist_select = '';
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $wishlist_join = "LEFT JOIN wishlist w ON b.id = w.bike_id AND w.user_id = $user_id";
    $wishlist_select = ", CASE WHEN w.id IS NOT NULL THEN 1 ELSE 0 END as in_wishlist";
}

$sql = "SELECT b.*, u.username as seller_name$wishlist_select 
        FROM bikes b 
        JOIN users u ON b.user_id = u.id 
        $wishlist_join 
        $where_sql 
        $order";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Available Bikes | BikeMart</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary:rgb(24, 53, 216);
      --primary-light: #dbeafe;
      --secondary: #1e40af;
      --dark: #1e293b;
      --light: #f8fafc;
      --success: #10b981;
      --danger: #ef4444;
      --warning: #f59e0b;
      --border-radius: 12px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
      --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
      --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f1f5f9;
      color: var(--dark);
      line-height: 1.6;
    }

    .navbar {
      background: white;
      box-shadow: var(--shadow-sm);
      padding: 1rem 2rem;
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .navbar-brand i {
      font-size: 1.8rem;
    }

    .container {
      max-width: 1400px;
      padding: 2rem;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2.5rem;
    }

    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--dark);
      position: relative;
      display: inline-block;
    }

    .page-title:after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 60px;
      height: 4px;
      background: var(--primary);
      border-radius: 2px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      border-radius: var(--border-radius);
      font-weight: 500;
      text-decoration: none;
      transition: var(--transition);
      border: none;
      cursor: pointer;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background-color: var(--secondary);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--primary);
      color: var(--primary);
    }

    .btn-outline:hover {
      background: var(--primary-light);
    }

    .btn-success {
      background-color: var(--success);
      color: white;
    }

    .btn-success:hover {
      opacity: 0.9;
      transform: translateY(-2px);
    }

    .bike-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 2rem;
    }

    .bike-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .bike-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-lg);
    }

    .bike-image {
      height: 240px;
      width: 100%;
      object-fit: cover;
      border-bottom: 1px solid #e2e8f0;
    }

    .bike-body {
      padding: 1.5rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .bike-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }

    .bike-description {
      color: #64748b;
      margin-bottom: 1rem;
      flex: 1;
    }

    .bike-price {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 1.5rem;
    }

    .bike-actions {
      display: grid;
      gap: 0.75rem;
    }

    .login-message {
      color: #64748b;
      font-style: italic;
      text-align: center;
      margin-top: 1rem;
      padding: 1rem;
      background: #f8fafc;
      border-radius: var(--border-radius);
    }

    .login-message a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      grid-column: 1 / -1;
    }

    .empty-state i {
      font-size: 3rem;
      color: #cbd5e1;
      margin-bottom: 1rem;
    }

    .empty-state p {
      color: #64748b;
      font-size: 1.1rem;
    }

    /* Badge for featured bikes */
    .featured-badge {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: var(--warning);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .container {
        padding: 1.5rem;
      }
      
      .bike-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      }
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }

      .filter-row {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-form input,
      .filter-form select {
        min-width: auto;
        width: 100%;
      }

      .bike-card {
        margin: 0;
      }

      .bike-actions {
        flex-direction: column;
        gap: 0.5rem;
      }

      .bike-actions .btn {
        width: 100%;
        justify-content: center;
      }

      .search-container {
        flex-direction: column;
        gap: 1rem;
      }

      .search-input {
        width: 100%;
      }

      .sort-select {
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      .navbar {
        padding: 1rem;
      }
      
      .bike-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }

      .container {
        padding: 1rem;
      }

      .page-title {
        font-size: 1.5rem;
      }

      .bike-card {
        padding: 1rem;
      }

      .bike-title {
        font-size: 1.1rem;
      }

      .bike-price {
        font-size: 1.25rem;
      }

      .filter-form {
        padding: 1rem;
      }

      .filter-row {
        gap: 0.5rem;
      }

      .filter-form input,
      .filter-form select {
        padding: 0.625rem 0.875rem;
        font-size: 0.95rem;
      }

      .btn {
        padding: 0.625rem 1rem;
        font-size: 0.95rem;
      }
    }

    @media (max-width: 360px) {
      .bike-card {
        margin: 0 0.5rem;
      }

      .bike-image {
        height: 180px;
      }

      .bike-actions .btn {
        padding: 0.5rem 0.875rem;
        font-size: 0.9rem;
      }
    }

    .filter-container {
      background: #fff;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-sm);
      margin-bottom: 2rem;
      overflow: hidden;
    }

    .filter-form {
      padding: 1.5rem;
    }

    .filter-row {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
      flex-wrap: wrap;
      align-items: center;
    }

    .filter-row:last-of-type {
      margin-bottom: 0;
    }

    .filter-form input,
    .filter-form select {
      padding: 0.75rem 1rem;
      border-radius: 6px;
      border: 1px solid #cbd5e1;
      font-size: 1rem;
      background: #f8fafc;
      min-width: 150px;
      flex: 1;
    }

    .price-range,
    .year-range {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex: 1;
    }

    .price-range input,
    .year-range input {
      flex: 1;
      min-width: 120px;
    }

    .price-range span,
    .year-range span {
      color: #64748b;
      font-weight: 500;
    }

    .filter-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 1rem;
      border-top: 1px solid #e2e8f0;
      margin-top: 1rem;
    }

    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1rem;
      color: var(--dark);
      cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
      width: auto;
      min-width: auto;
      margin: 0;
    }

    .filter-buttons {
      display: flex;
      gap: 0.75rem;
    }

    .wishlist-btn {
      position: absolute;
      top: 1rem;
      left: 1rem;
      background: rgba(255, 255, 255, 0.9);
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      color: #64748b;
      z-index: 10;
    }

    .wishlist-btn:hover {
      background: white;
      transform: scale(1.1);
    }

    .wishlist-btn.active {
      color: var(--danger);
    }

    .wishlist-btn.active i {
      animation: heartBeat 0.3s ease-in-out;
    }

    @keyframes heartBeat {
      0% { transform: scale(1); }
      50% { transform: scale(1.2); }
      100% { transform: scale(1); }
    }

    .bike-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      font-size: 0.875rem;
      color: #64748b;
    }

    .bike-specs {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }

    .bike-spec {
      background: var(--primary-light);
      color: var(--primary);
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    @media (max-width: 768px) {
      .filter-row { 
        flex-direction: column; 
        gap: 0.75rem; 
      }
      
      .filter-form input, 
      .filter-form select { 
        width: 100%; 
        min-width: auto;
      }
      
      .price-range,
      .year-range {
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .filter-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }
      
      .filter-buttons {
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <a href="index.php" class="navbar-brand">
      <i class="fas fa-motorcycle"></i>
      BikeMart
    </a>
  </nav>

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Available Bikes</h1>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="cart_view.php" class="btn btn-primary">
          <i class="fas fa-shopping-cart"></i>
          View Cart
        </a>
      <?php endif; ?>
    </div>

    <!-- Advanced Filter Form -->
    <div class="filter-container">
      <form method="GET" class="filter-form">
        <div class="filter-row">
          <input type="text" name="search" placeholder="Search bikes..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          <input type="text" name="brand" placeholder="Brand/Model" value="<?= htmlspecialchars($_GET['brand'] ?? '') ?>">
          <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
        </div>
        
        <div class="filter-row">
          <div class="price-range">
            <input type="number" name="min_price" placeholder="Min Price" min="0" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
            <span>-</span>
            <input type="number" name="max_price" placeholder="Max Price" min="0" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
          </div>
          
          <div class="year-range">
            <input type="number" name="min_year" placeholder="Min Year" min="1900" max="2024" value="<?= htmlspecialchars($_GET['min_year'] ?? '') ?>">
            <span>-</span>
            <input type="number" name="max_year" placeholder="Max Year" min="1900" max="2024" value="<?= htmlspecialchars($_GET['max_year'] ?? '') ?>">
          </div>
          
          <input type="number" name="max_mileage" placeholder="Max Mileage" min="0" value="<?= htmlspecialchars($_GET['max_mileage'] ?? '') ?>">
        </div>
        
        <div class="filter-row">
          <select name="fuel_type">
            <option value="">Fuel Type</option>
            <option value="Petrol" <?= (($_GET['fuel_type'] ?? '') == 'Petrol') ? 'selected' : '' ?>>Petrol</option>
            <option value="Diesel" <?= (($_GET['fuel_type'] ?? '') == 'Diesel') ? 'selected' : '' ?>>Diesel</option>
            <option value="Electric" <?= (($_GET['fuel_type'] ?? '') == 'Electric') ? 'selected' : '' ?>>Electric</option>
            <option value="Hybrid" <?= (($_GET['fuel_type'] ?? '') == 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
          </select>
          
          <select name="transmission">
            <option value="">Transmission</option>
            <option value="Manual" <?= (($_GET['transmission'] ?? '') == 'Manual') ? 'selected' : '' ?>>Manual</option>
            <option value="Automatic" <?= (($_GET['transmission'] ?? '') == 'Automatic') ? 'selected' : '' ?>>Automatic</option>
          </select>
          
          <select name="sort">
            <option value="">Sort By</option>
            <option value="newest" <?= (($_GET['sort'] ?? '') == 'newest') ? 'selected' : '' ?>>Newest First</option>
            <option value="oldest" <?= (($_GET['sort'] ?? '') == 'oldest') ? 'selected' : '' ?>>Oldest First</option>
            <option value="price_asc" <?= (($_GET['sort'] ?? '') == 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= (($_GET['sort'] ?? '') == 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="mileage_low" <?= (($_GET['sort'] ?? '') == 'mileage_low') ? 'selected' : '' ?>>Lowest Mileage</option>
            <option value="mileage_high" <?= (($_GET['sort'] ?? '') == 'mileage_high') ? 'selected' : '' ?>>Highest Mileage</option>
            <option value="year_new" <?= (($_GET['sort'] ?? '') == 'year_new') ? 'selected' : '' ?>>Newest Year</option>
            <option value="year_old" <?= (($_GET['sort'] ?? '') == 'year_old') ? 'selected' : '' ?>>Oldest Year</option>
          </select>
        </div>
        
        <div class="filter-actions">
          <?php if (isset($_SESSION['user_id'])): ?>
            <label class="checkbox-label">
              <input type="checkbox" name="my_listings" value="1" <?= !empty($_GET['my_listings']) ? 'checked' : '' ?>>
              My Listings
            </label>
          <?php endif; ?>
          
          <div class="filter-buttons">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-filter"></i> Apply Filters
            </button>
            <a href="view_bikes.php" class="btn btn-outline">
              <i class="fas fa-times"></i> Clear All
            </a>
          </div>
        </div>
      </form>
    </div>
    <!-- End Advanced Filter Form -->

    <div class="bike-grid">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($bike = $result->fetch_assoc()): ?>
          <div class="bike-card">
            <?php if (isset($_SESSION['user_id'])): ?>
              <button class="wishlist-btn <?php echo ($bike['in_wishlist'] ?? 0) ? 'active' : ''; ?>" 
                      onclick="toggleWishlist(<?php echo $bike['id']; ?>, this)">
                <i class="fas fa-heart"></i>
              </button>
            <?php endif; ?>
            
            <?php if (rand(0, 1)): ?>
              <span class="featured-badge">Featured</span>
            <?php endif; ?>
            
            <img src="uploads/<?php echo htmlspecialchars($bike['image']); ?>" class="bike-image" alt="<?php echo htmlspecialchars($bike['title']); ?>">
            <div class="bike-body">
              <h3 class="bike-title"><?php echo htmlspecialchars($bike['title']); ?></h3>
              
              <div class="bike-meta">
                <span>Seller: <?php echo htmlspecialchars($bike['seller_name']); ?></span>
                <span>₹<?php echo number_format($bike['price']); ?></span>
              </div>
              
              <?php if (!empty($bike['year']) || !empty($bike['mileage']) || !empty($bike['fuel_type'])): ?>
                <div class="bike-specs">
                  <?php if (!empty($bike['year'])): ?>
                    <span class="bike-spec"><?php echo $bike['year']; ?></span>
                  <?php endif; ?>
                  <?php if (!empty($bike['mileage'])): ?>
                    <span class="bike-spec"><?php echo number_format($bike['mileage']); ?> km</span>
                  <?php endif; ?>
                  <?php if (!empty($bike['fuel_type'])): ?>
                    <span class="bike-spec"><?php echo $bike['fuel_type']; ?></span>
                  <?php endif; ?>
                  <?php if (!empty($bike['transmission'])): ?>
                    <span class="bike-spec"><?php echo $bike['transmission']; ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              
              <p class="bike-description"><?php echo htmlspecialchars($bike['description']); ?></p>

              <?php if (isset($_SESSION['user_id'])): ?>
                <div class="bike-actions">
                  <!-- Buy Now -->
                  <form method="GET" action="buy_bike.php">
                    <input type="hidden" name="id" value="<?php echo $bike['id']; ?>">
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-bolt"></i> Buy Now
                    </button>
                  </form>

                  <!-- Add to Cart -->
                  <form method="POST" action="cart_add.php">
                    <input type="hidden" name="bike_id" value="<?php echo $bike['id']; ?>">
                    <button type="submit" name="submit" class="btn btn-outline">
                      <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                  </form>

                  <!-- Contact Seller -->
                  <a href="message_form.php?bike_id=<?php echo $bike['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-envelope"></i> Contact Seller
                  </a>
                  
                  <!-- Generate Invoice -->
                  <a href="generate_invoice.php?type=quote&bike_id=<?php echo $bike['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-receipt"></i> Get Quote
                  </a>
                </div>
              <?php else: ?>
                <div class="login-message">
                  Please <a href="login.php">login</a> to purchase or contact seller
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-bicycle"></i>
          <p>No bikes available at the moment. Please check back later.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    // Add simple animation to cards when they come into view
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.bike-card');
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = 1;
            entry.target.style.transform = 'translateY(0)';
          }
        });
      }, { threshold: 0.1 });

      cards.forEach(card => {
        card.style.opacity = 0;
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
      });
    });

    // Wishlist functionality
    function toggleWishlist(bikeId, button) {
      const isInWishlist = button.classList.contains('active');
      const action = isInWishlist ? 'wishlist_remove.php' : 'wishlist_add.php';
      
      fetch(action, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'bike_id=' + bikeId
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (isInWishlist) {
            button.classList.remove('active');
            button.querySelector('i').classList.remove('fas');
            button.querySelector('i').classList.add('far');
          } else {
            button.classList.add('active');
            button.querySelector('i').classList.remove('far');
            button.querySelector('i').classList.add('fas');
          }
          
          // Show success message
          showNotification(data.message, 'success');
        } else {
          showNotification(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating wishlist', 'error');
      });
    }

    // Notification system
    function showNotification(message, type) {
      const notification = document.createElement('div');
      notification.className = `notification notification-${type}`;
      notification.textContent = message;
      
      // Add styles
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
      `;
      
      if (type === 'success') {
        notification.style.backgroundColor = '#10b981';
      } else {
        notification.style.backgroundColor = '#ef4444';
      }
      
      document.body.appendChild(notification);
      
      // Animate in
      setTimeout(() => {
        notification.style.transform = 'translateX(0)';
      }, 100);
      
      // Remove after 3 seconds
      setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
          document.body.removeChild(notification);
        }, 300);
      }, 3000);
    }
  </script>
</body>
</html>
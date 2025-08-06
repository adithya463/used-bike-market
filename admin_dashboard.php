<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Fetch enhanced statistics with error handling
try {
    $user_count    = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 0")->fetch_assoc()['total'] ?? 0;
    $bike_count    = $conn->query("SELECT COUNT(*) as total FROM bikes")->fetch_assoc()['total'] ?? 0;
    $sold_count    = $conn->query("SELECT COUNT(*) as sold FROM bikes WHERE status = 'sold'")->fetch_assoc()['sold'] ?? 0;
    $available_count = $conn->query("SELECT COUNT(*) as available FROM bikes WHERE status = 'available'")->fetch_assoc()['available'] ?? 0;
    $message_count = $conn->query("SELECT COUNT(*) as total FROM messages")->fetch_assoc()['total'] ?? 0;
    $wishlist_count = $conn->query("SELECT COUNT(*) as total FROM wishlist")->fetch_assoc()['total'] ?? 0;

    // Get recent activity
    $recent_bikes = $conn->query("SELECT b.*, u.username FROM bikes b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC LIMIT 5");
    $recent_users = $conn->query("SELECT * FROM users WHERE is_admin = 0 ORDER BY id DESC LIMIT 5");

    // Get bike statistics by fuel type
    $fuel_stats = $conn->query("SELECT fuel_type, COUNT(*) as count FROM bikes WHERE fuel_type IS NOT NULL GROUP BY fuel_type");

    // Get monthly sales data (last 6 months)
    $monthly_sales = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
        FROM bikes 
        WHERE status = 'sold' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY month 
        ORDER BY month DESC
    ");

    // Calculate total revenue
    $total_revenue = $conn->query("SELECT SUM(price) as total FROM bikes WHERE status = 'sold'")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    // Handle database errors gracefully
    $user_count = $bike_count = $sold_count = $available_count = $message_count = $wishlist_count = 0;
    $total_revenue = 0;
    $recent_bikes = $recent_users = $fuel_stats = $monthly_sales = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - BikeMart</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    /* Admin Dashboard Styles */
    :root {
      --primary: #4361ee;
      --primary-light: #e6f0ff;
      --secondary: #3a0ca3;
      --dark: #1a1a2e;
      --light: #f8f9fa;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --info: #4895ef;
      --border-radius: 10px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
      --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
    }

    /* Base Styles */
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background-color: #f5f7ff;
      color: var(--dark);
      line-height: 1.6;
      padding: 0;
      margin: 0;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    /* Header Styles */
    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2.5rem;
    }

    .dashboard-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--dark);
      margin: 0;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.25rem;
      border-radius: var(--border-radius);
      font-weight: 500;
      text-decoration: none;
      transition: var(--transition);
      cursor: pointer;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
      border: none;
    }

    .btn-primary:hover {
      background-color: var(--secondary);
      transform: translateY(-2px);
    }

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    .stat-card {
      background: white;
      padding: 1.5rem;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
    }

    .stat-value {
      font-size: 2.2rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 0.5rem;
    }

    .stat-title {
      color: #64748b;
      font-size: 0.95rem;
      font-weight: 500;
    }

    /* Navigation Grid */
    .section-title {
      font-size: 1.4rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: var(--dark);
    }

    .nav-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1.25rem;
    }

    .nav-card {
      background: white;
      padding: 1.75rem 1rem;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-sm);
      text-align: center;
      transition: var(--transition);
      text-decoration: none;
      color: var(--dark);
    }

    .nav-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      background-color: var(--primary-light);
    }

    .nav-icon {
      font-size: 1.75rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }

    .nav-title {
      font-weight: 500;
      font-size: 1rem;
    }

    /* Analytics Cards */
    .analytics-card, .activity-card {
      background: white;
      padding: 1.5rem;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-sm);
    }

    .analytics-title, .activity-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin: 0 0 1rem 0;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .analytics-content, .activity-content {
      max-height: 300px;
      overflow-y: auto;
    }

    .fuel-item, .month-item, .activity-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .fuel-item:last-child, .month-item:last-child, .activity-item:last-child {
      border-bottom: none;
    }

    .fuel-type, .month-name {
      font-weight: 500;
      color: var(--dark);
    }

    .fuel-count, .month-count {
      color: var(--primary);
      font-weight: 600;
    }

    .activity-info {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .activity-meta {
      font-size: 0.875rem;
      color: #64748b;
    }

    .activity-status {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 0.25rem;
    }

    .activity-price {
      font-weight: 600;
      color: var(--success);
    }

    .activity-date {
      font-size: 0.875rem;
      color: #64748b;
    }

    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .status-available {
      background: var(--success);
      color: white;
    }

    .status-sold {
      background: var(--danger);
      color: white;
    }

    .no-data {
      color: #64748b;
      font-style: italic;
      text-align: center;
      padding: 1rem;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
      .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      }
      
      .nav-grid {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      }
    }

    @media (max-width: 768px) {
      .container {
        padding: 1.5rem;
      }
      
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }

      .nav-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }

      .analytics-card, .activity-card {
        margin-bottom: 1rem;
      }

      .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }

      .dashboard-title {
        font-size: 1.5rem;
      }

      .stat-value {
        font-size: 1.8rem;
      }

      .nav-icon {
        font-size: 1.5rem;
      }

      .nav-title {
        font-size: 0.9rem;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 1rem;
      }
      
      .stats-grid, .nav-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
      }
      
      .stat-card, .nav-card {
        padding: 1rem;
      }

      .stat-value {
        font-size: 1.5rem;
      }

      .stat-title {
        font-size: 0.85rem;
      }

      .nav-icon {
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
      }

      .nav-title {
        font-size: 0.85rem;
      }

      .analytics-title, .activity-title {
        font-size: 1rem;
      }

      .analytics-content, .activity-content {
        max-height: 250px;
      }

      .fuel-item, .month-item, .activity-item {
        padding: 0.5rem 0;
      }

      .fuel-type, .month-name {
        font-size: 0.9rem;
      }

      .fuel-count, .month-count {
        font-size: 0.9rem;
      }
    }

    @media (max-width: 360px) {
      .container {
        padding: 0.75rem;
      }

      .stat-card, .nav-card {
        padding: 0.875rem;
      }

      .stat-value {
        font-size: 1.25rem;
      }

      .nav-icon {
        font-size: 1rem;
        margin-bottom: 0.5rem;
      }

      .nav-title {
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header class="dashboard-header">
      <h1 class="dashboard-title">Admin Dashboard</h1>
      <a href="logout.php" class="btn btn-primary">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </a>
    </header>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-value"><?php echo $user_count; ?></div>
        <div class="stat-title">Total Users</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $bike_count; ?></div>
        <div class="stat-title">Total Bikes</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $available_count; ?></div>
        <div class="stat-title">Available Bikes</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $sold_count; ?></div>
        <div class="stat-title">Sold Bikes</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">₹<?php echo number_format($total_revenue); ?></div>
        <div class="stat-title">Total Revenue</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $wishlist_count; ?></div>
        <div class="stat-title">Wishlist Items</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $message_count; ?></div>
        <div class="stat-title">Messages</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $sold_count > 0 ? round(($sold_count / $bike_count) * 100, 1) : 0; ?>%</div>
        <div class="stat-title">Conversion Rate</div>
      </div>
    </div>

    <!-- Analytics Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
      <!-- Fuel Type Distribution -->
      <div class="analytics-card">
        <h3 class="analytics-title"><i class="fas fa-chart-pie"></i> Fuel Type Distribution</h3>
        <div class="analytics-content">
          <?php if ($fuel_stats && $fuel_stats->num_rows > 0): ?>
            <?php while ($fuel = $fuel_stats->fetch_assoc()): ?>
              <div class="fuel-item">
                <span class="fuel-type"><?= htmlspecialchars($fuel['fuel_type']) ?></span>
                <span class="fuel-count"><?= $fuel['count'] ?> bikes</span>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="no-data">No fuel type data available</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Monthly Sales -->
      <div class="analytics-card">
        <h3 class="analytics-title"><i class="fas fa-chart-line"></i> Monthly Sales (Last 6 Months)</h3>
        <div class="analytics-content">
          <?php if ($monthly_sales && $monthly_sales->num_rows > 0): ?>
            <?php while ($month = $monthly_sales->fetch_assoc()): ?>
              <div class="month-item">
                <span class="month-name"><?= date('M Y', strtotime($month['month'] . '-01')) ?></span>
                <span class="month-count"><?= $month['count'] ?> sold</span>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="no-data">No sales data available</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
      <!-- Recent Bikes -->
      <div class="activity-card">
        <h3 class="activity-title"><i class="fas fa-clock"></i> Recent Bike Listings</h3>
        <div class="activity-content">
          <?php if ($recent_bikes && $recent_bikes->num_rows > 0): ?>
            <?php while ($bike = $recent_bikes->fetch_assoc()): ?>
              <div class="activity-item">
                <div class="activity-info">
                  <strong><?= htmlspecialchars($bike['title']) ?></strong>
                  <span class="activity-meta">by <?= htmlspecialchars($bike['username']) ?></span>
                </div>
                <div class="activity-status">
                  <span class="status-badge status-<?= $bike['status'] ?>"><?= ucfirst($bike['status']) ?></span>
                  <span class="activity-price">₹<?= number_format($bike['price']) ?></span>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="no-data">No recent bike listings</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent Users -->
      <div class="activity-card">
        <h3 class="activity-title"><i class="fas fa-user-plus"></i> Recent User Registrations</h3>
        <div class="activity-content">
          <?php if ($recent_users && $recent_users->num_rows > 0): ?>
            <?php while ($user = $recent_users->fetch_assoc()): ?>
              <div class="activity-item">
                <div class="activity-info">
                  <strong><?= htmlspecialchars($user['username']) ?></strong>
                  <span class="activity-meta"><?= htmlspecialchars($user['email']) ?></span>
                </div>
                <div class="activity-date">
                  <?= date('M j, Y', strtotime($user['id'])) ?>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="no-data">No recent user registrations</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <h2 class="section-title">Admin Actions</h2>
    <div class="nav-grid">
      <a href="admin_users.php" class="nav-card">
        <div class="nav-icon"><i class="fas fa-users"></i></div>
        <div class="nav-title">Manage Users</div>
      </a>
      <a href="admin_bikes.php" class="nav-card">
        <div class="nav-icon"><i class="fas fa-motorcycle"></i></div>
        <div class="nav-title">Manage Bikes</div>
      </a>
      <a href="admin_messages.php" class="nav-card">
        <div class="nav-icon"><i class="fas fa-envelope"></i></div>
        <div class="nav-title">View Messages</div>
      </a>
      <a href="admin_wishlist.php" class="nav-card">
        <div class="nav-icon"><i class="fas fa-heart"></i></div>
        <div class="nav-title">Wishlist Analytics</div>
      </a>
      <a href="admin_analytics.php" class="nav-card">
        <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="nav-title">Advanced Analytics</div>
      </a>
      <a href="admin_reports.php" class="nav-card">
        <div class="nav-icon"><i class="fas fa-file-alt"></i></div>
        <div class="nav-title">Generate Reports</div>
      </a>
    </div>
  </div>
</body>
</html>
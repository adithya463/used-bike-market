<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Fetch wishlist statistics
$total_wishlist_items = $conn->query("SELECT COUNT(*) as total FROM wishlist")->fetch_assoc()['total'];
$unique_users_with_wishlist = $conn->query("SELECT COUNT(DISTINCT user_id) as total FROM wishlist")->fetch_assoc()['total'];
$most_wishlisted_bikes = $conn->query("
    SELECT b.title, COUNT(w.id) as wishlist_count 
    FROM bikes b 
    JOIN wishlist w ON b.id = w.bike_id 
    GROUP BY b.id 
    ORDER BY wishlist_count DESC 
    LIMIT 10
");

// Get recent wishlist activity
$recent_wishlist = $conn->query("
    SELECT w.*, b.title as bike_title, b.price, u.username 
    FROM wishlist w 
    JOIN bikes b ON w.bike_id = b.id 
    JOIN users u ON w.user_id = u.id 
    ORDER BY w.created_at DESC 
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Analytics - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-title {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .wishlist-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .wishlist-item:last-child {
            border-bottom: none;
        }

        .bike-info {
            flex: 1;
        }

        .bike-title {
            font-weight: 600;
            color: var(--dark);
        }

        .user-info {
            font-size: 0.875rem;
            color: #64748b;
        }

        .wishlist-count {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .price {
            font-weight: 600;
            color: var(--success);
        }

        .date {
            font-size: 0.875rem;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heart"></i> Wishlist Analytics</h1>
            <a href="admin_dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $total_wishlist_items ?></div>
                <div class="stat-title">Total Wishlist Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $unique_users_with_wishlist ?></div>
                <div class="stat-title">Users with Wishlists</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $total_wishlist_items > 0 ? round($total_wishlist_items / $unique_users_with_wishlist, 1) : 0 ?></div>
                <div class="stat-title">Avg Items per User</div>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Most Wishlisted Bikes
                </h3>
                <?php if ($most_wishlisted_bikes->num_rows > 0): ?>
                    <?php while ($bike = $most_wishlisted_bikes->fetch_assoc()): ?>
                        <div class="wishlist-item">
                            <div class="bike-info">
                                <div class="bike-title"><?= htmlspecialchars($bike['title']) ?></div>
                            </div>
                            <div class="wishlist-count"><?= $bike['wishlist_count'] ?> wishes</div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #64748b; text-align: center; padding: 1rem;">No wishlist data available</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i> Recent Wishlist Activity
                </h3>
                <?php if ($recent_wishlist->num_rows > 0): ?>
                    <?php while ($item = $recent_wishlist->fetch_assoc()): ?>
                        <div class="wishlist-item">
                            <div class="bike-info">
                                <div class="bike-title"><?= htmlspecialchars($item['bike_title']) ?></div>
                                <div class="user-info">by <?= htmlspecialchars($item['username']) ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div class="price">₹<?= number_format($item['price']) ?></div>
                                <div class="date"><?= date('M j, Y', strtotime($item['created_at'])) ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #64748b; text-align: center; padding: 1rem;">No recent wishlist activity</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 
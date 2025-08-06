<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Fetch comprehensive analytics data
$total_revenue = $conn->query("SELECT SUM(price) as total FROM bikes WHERE status = 'sold'")->fetch_assoc()['total'] ?? 0;
$avg_bike_price = $conn->query("SELECT AVG(price) as avg_price FROM bikes")->fetch_assoc()['avg_price'] ?? 0;
$avg_sold_price = $conn->query("SELECT AVG(price) as avg_price FROM bikes WHERE status = 'sold'")->fetch_assoc()['avg_price'] ?? 0;

// Price range distribution
$price_ranges = $conn->query("
    SELECT 
        CASE 
            WHEN price < 50000 THEN 'Under ₹50k'
            WHEN price < 100000 THEN '₹50k - ₹100k'
            WHEN price < 200000 THEN '₹100k - ₹200k'
            ELSE 'Above ₹200k'
        END as range_name,
        COUNT(*) as count
    FROM bikes 
    GROUP BY range_name 
    ORDER BY MIN(price)
");

// Fuel type distribution
$fuel_distribution = $conn->query("
    SELECT fuel_type, COUNT(*) as count, AVG(price) as avg_price
    FROM bikes 
    WHERE fuel_type IS NOT NULL 
    GROUP BY fuel_type
");

// Monthly trends
$monthly_trends = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total_listings,
        COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_count,
        AVG(price) as avg_price
    FROM bikes 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month 
    ORDER BY month DESC
");

// Top performing sellers
$top_sellers = $conn->query("
    SELECT 
        u.username,
        COUNT(b.id) as total_listings,
        COUNT(CASE WHEN b.status = 'sold' THEN 1 END) as sold_count,
        SUM(CASE WHEN b.status = 'sold' THEN b.price ELSE 0 END) as total_revenue
    FROM users u 
    LEFT JOIN bikes b ON u.id = b.user_id 
    WHERE u.is_admin = 0 
    GROUP BY u.id 
    HAVING total_listings > 0
    ORDER BY total_revenue DESC 
    LIMIT 10
");

// Location analysis
$location_stats = $conn->query("
    SELECT 
        location,
        COUNT(*) as total_bikes,
        COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_count,
        AVG(price) as avg_price
    FROM bikes 
    WHERE location IS NOT NULL AND location != ''
    GROUP BY location 
    ORDER BY total_bikes DESC 
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Analytics - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            max-width: 1400px;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .data-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .data-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .data-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-item:last-child {
            border-bottom: none;
        }

        .item-label {
            font-weight: 500;
            color: var(--dark);
        }

        .item-value {
            font-weight: 600;
            color: var(--primary);
        }

        .seller-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .seller-info {
            flex: 1;
        }

        .seller-name {
            font-weight: 600;
            color: var(--dark);
        }

        .seller-stats {
            font-size: 0.875rem;
            color: #64748b;
        }

        .seller-revenue {
            text-align: right;
        }

        .revenue-amount {
            font-weight: 600;
            color: var(--success);
        }

        .revenue-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .charts-grid, .data-grid {
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
            <h1><i class="fas fa-chart-line"></i> Advanced Analytics</h1>
            <a href="admin_dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">₹<?= number_format($total_revenue) ?></div>
                <div class="stat-title">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₹<?= number_format($avg_bike_price, 0) ?></div>
                <div class="stat-title">Average Bike Price</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₹<?= number_format($avg_sold_price, 0) ?></div>
                <div class="stat-title">Average Sold Price</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $avg_sold_price > 0 ? round((($avg_sold_price - $avg_bike_price) / $avg_bike_price) * 100, 1) : 0 ?>%</div>
                <div class="stat-title">Price Premium</div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-chart-pie"></i> Price Range Distribution
                </h3>
                <canvas id="priceChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-chart-bar"></i> Fuel Type Analysis
                </h3>
                <canvas id="fuelChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="data-grid">
            <div class="data-card">
                <h3 class="data-title">
                    <i class="fas fa-trophy"></i> Top Performing Sellers
                </h3>
                <?php if ($top_sellers->num_rows > 0): ?>
                    <?php while ($seller = $top_sellers->fetch_assoc()): ?>
                        <div class="seller-item">
                            <div class="seller-info">
                                <div class="seller-name"><?= htmlspecialchars($seller['username']) ?></div>
                                <div class="seller-stats">
                                    <?= $seller['total_listings'] ?> listings, 
                                    <?= $seller['sold_count'] ?> sold
                                </div>
                            </div>
                            <div class="seller-revenue">
                                <div class="revenue-amount">₹<?= number_format($seller['total_revenue']) ?></div>
                                <div class="revenue-label">Total Revenue</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #64748b; text-align: center; padding: 1rem;">No seller data available</p>
                <?php endif; ?>
            </div>

            <div class="data-card">
                <h3 class="data-title">
                    <i class="fas fa-map-marker-alt"></i> Location Analysis
                </h3>
                <?php if ($location_stats->num_rows > 0): ?>
                    <?php while ($location = $location_stats->fetch_assoc()): ?>
                        <div class="data-item">
                            <div class="item-label"><?= htmlspecialchars($location['location']) ?></div>
                            <div class="item-value">
                                <?= $location['total_bikes'] ?> bikes
                                (<?= $location['sold_count'] ?> sold)
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #64748b; text-align: center; padding: 1rem;">No location data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Price Range Chart
        const priceCtx = document.getElementById('priceChart').getContext('2d');
        new Chart(priceCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    $price_ranges->data_seek(0);
                    while ($range = $price_ranges->fetch_assoc()) {
                        echo "'" . $range['range_name'] . "',";
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        $price_ranges->data_seek(0);
                        while ($range = $price_ranges->fetch_assoc()) {
                            echo $range['count'] . ",";
                        }
                        ?>
                    ],
                    backgroundColor: ['#4361ee', '#3a0ca3', '#f72585', '#4cc9f0']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Fuel Type Chart
        const fuelCtx = document.getElementById('fuelChart').getContext('2d');
        new Chart(fuelCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    $fuel_distribution->data_seek(0);
                    while ($fuel = $fuel_distribution->fetch_assoc()) {
                        echo "'" . $fuel['fuel_type'] . "',";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Number of Bikes',
                    data: [
                        <?php 
                        $fuel_distribution->data_seek(0);
                        while ($fuel = $fuel_distribution->fetch_assoc()) {
                            echo $fuel['count'] . ",";
                        }
                        ?>
                    ],
                    backgroundColor: '#4361ee'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 
<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// No CSV generation needed - reports now link to admin pages

// Fetch summary statistics for reports
$total_bikes = $conn->query("SELECT COUNT(*) as total FROM bikes")->fetch_assoc()['total'];
$sold_bikes = $conn->query("SELECT COUNT(*) as total FROM bikes WHERE status = 'sold'")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 0")->fetch_assoc()['total'];
$total_revenue = $conn->query("SELECT SUM(price) as total FROM bikes WHERE status = 'sold'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - Admin Dashboard</title>
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
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            opacity: 0.9;
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

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .report-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .report-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
            text-align: center;
        }

        .report-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            text-align: center;
        }

        .report-description {
            color: #64748b;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .report-form {
            text-align: center;
        }

        .report-form button {
            width: 100%;
        }

        @media (max-width: 1024px) {
            .reports-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .reports-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .report-card {
                padding: 1.5rem;
            }

            .report-icon {
                font-size: 2.5rem;
            }

            .report-title {
                font-size: 1.1rem;
            }

            .report-description {
                font-size: 0.85rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-title {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 1rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .reports-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .report-card {
                padding: 1.25rem;
            }

            .report-icon {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }

            .report-title {
                font-size: 1rem;
            }

            .report-description {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.25rem;
            }

            .stat-title {
                font-size: 0.75rem;
            }

            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 360px) {
            .container {
                padding: 0.75rem;
            }

            .report-card {
                padding: 1rem;
            }

            .report-icon {
                font-size: 1.75rem;
            }

            .report-title {
                font-size: 0.9rem;
            }

            .report-description {
                font-size: 0.75rem;
            }

            .stat-value {
                font-size: 1.1rem;
            }

            .btn {
                padding: 0.625rem 0.875rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Generate Reports</h1>
            <a href="admin_dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>



        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $total_bikes ?></div>
                <div class="stat-title">Total Bikes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $sold_bikes ?></div>
                <div class="stat-title">Sold Bikes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $total_users ?></div>
                <div class="stat-title">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₹<?= number_format($total_revenue) ?></div>
                <div class="stat-title">Total Revenue</div>
            </div>
        </div>

        <div class="reports-grid">
            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <h3 class="report-title">Bikes Report</h3>
                <p class="report-description">
                    View all bike listings with detailed information including specifications, 
                    seller details, and status. Perfect for inventory management.
                </p>
                <a href="admin_bikes.php" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-eye"></i> View Bikes
                </a>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="report-title">Users Report</h3>
                <p class="report-description">
                    View user data with their listing statistics, sales performance, 
                    and revenue generation. Useful for user analysis.
                </p>
                <a href="admin_users.php" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-eye"></i> View Users
                </a>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="report-title">Sales Report</h3>
                <p class="report-description">
                    View all completed sales with buyer and seller information. 
                    Ideal for financial analysis and sales tracking.
                </p>
                <a href="admin_dashboard.php" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-eye"></i> View Dashboard
                </a>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="report-title">Wishlist Report</h3>
                <p class="report-description">
                    View wishlist data to understand user preferences and 
                    popular bike models. Great for market analysis.
                </p>
                <a href="admin_wishlist.php" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-eye"></i> View Wishlist
                </a>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="report-title">Messages Report</h3>
                <p class="report-description">
                    View message data to analyze communication patterns 
                    between buyers and sellers. Useful for engagement metrics.
                </p>
                <a href="admin_messages.php" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-eye"></i> View Messages
                </a>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="report-title">Analytics Summary</h3>
                <p class="report-description">
                    Generate a comprehensive analytics report with charts, 
                    trends, and key performance indicators.
                </p>
                <a href="admin_analytics.php" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-chart-bar"></i> View Analytics
                </a>
            </div>
        </div>
    </div>
</body>
</html> 
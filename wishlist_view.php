<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Fetch wishlist items
$wishlist_query = "
    SELECT b.*, w.created_at as added_date, u.username as seller_name
    FROM wishlist w
    JOIN bikes b ON w.bike_id = b.id
    JOIN users u ON b.user_id = u.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
";

$stmt = $conn->prepare($wishlist_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - BikeMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
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
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: white;
            box-shadow: var(--shadow-sm);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .wishlist-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
        }

        .wishlist-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .wishlist-image {
            height: 240px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid #e2e8f0;
        }

        .wishlist-body {
            padding: 1.5rem;
        }

        .wishlist-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .wishlist-description {
            color: #64748b;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .wishlist-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .wishlist-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .wishlist-actions {
            display: grid;
            gap: 0.75rem;
        }

        .empty-wishlist {
            text-align: center;
            padding: 4rem 2rem;
            grid-column: 1 / -1;
        }

        .empty-wishlist i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-wishlist h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-wishlist p {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .remove-wishlist {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .remove-wishlist:hover {
            background: var(--danger);
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .wishlist-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
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
        <div style="display: flex; gap: 1rem; align-items: center;">
            <a href="view_bikes.php" class="btn btn-outline">Browse Bikes</a>
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">My Wishlist</h1>
            <a href="view_bikes.php" class="btn btn-primary">
                <i class="fas fa-search"></i>
                Browse More Bikes
            </a>
        </div>

        <div class="wishlist-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($bike = $result->fetch_assoc()): ?>
                    <div class="wishlist-card" data-bike-id="<?php echo $bike['id']; ?>">
                        <button class="remove-wishlist" onclick="removeFromWishlist(<?php echo $bike['id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                        
                        <img src="uploads/<?php echo htmlspecialchars($bike['image']); ?>" 
                             class="wishlist-image" 
                             alt="<?php echo htmlspecialchars($bike['title']); ?>">
                        
                        <div class="wishlist-body">
                            <h3 class="wishlist-title"><?php echo htmlspecialchars($bike['title']); ?></h3>
                            <p class="wishlist-description"><?php echo htmlspecialchars($bike['description']); ?></p>
                            
                            <div class="wishlist-price">₹<?php echo number_format($bike['price']); ?></div>
                            
                            <div class="wishlist-meta">
                                <span>Seller: <?php echo htmlspecialchars($bike['seller_name']); ?></span>
                                <span>Added: <?php echo date('M j, Y', strtotime($bike['added_date'])); ?></span>
                            </div>

                            <div class="wishlist-actions">
                                <a href="buy_bike.php?id=<?php echo $bike['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-bolt"></i> Buy Now
                                </a>
                                <a href="generate_invoice.php?type=wishlist&bike_id=<?php echo $bike['id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-receipt"></i> Generate Invoice
                                </a>
                                <a href="message_form.php?bike_id=<?php echo $bike['id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-envelope"></i> Contact Seller
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-wishlist">
                    <i class="fas fa-heart"></i>
                    <h3>Your wishlist is empty</h3>
                    <p>Start browsing bikes and add your favorites to your wishlist!</p>
                    <a href="view_bikes.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Bikes
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function removeFromWishlist(bikeId) {
            if (confirm('Are you sure you want to remove this bike from your wishlist?')) {
                fetch('wishlist_remove.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'bike_id=' + bikeId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the card from DOM
                        const card = document.querySelector(`[data-bike-id="${bikeId}"]`);
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            card.remove();
                            
                            // Check if no more items
                            const remainingCards = document.querySelectorAll('.wishlist-card');
                            if (remainingCards.length === 0) {
                                location.reload(); // Reload to show empty state
                            }
                        }, 300);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing from wishlist');
                });
            }
        }
    </script>
</body>
</html> 
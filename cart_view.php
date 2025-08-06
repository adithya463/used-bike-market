<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT b.* FROM cart c JOIN bikes b ON c.bike_id = b.id WHERE c.user_id = $user_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart - BikeMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .btn-remove {
            background-color: #e74c3c;
            color: white;
        }
        .btn-remove:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2 class="mb-4">🛒 Your Cart Items</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($bike = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="uploads/<?php echo htmlspecialchars($bike['image']); ?>" class="card-img-top" alt="Bike Image">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($bike['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($bike['description']); ?></p>
                            <p class="fw-bold text-primary">₹<?php echo number_format($bike['price']); ?></p>

                            <div class="mt-auto d-grid gap-2">
                                <!-- Buy Now button -->
                                <form method="GET" action="buy_bike.php">
                                    <input type="hidden" name="id" value="<?php echo $bike['id']; ?>">
                                    <button type="submit" class="btn btn-primary">Buy Now</button>
                                </form>

                                <!-- Remove from cart -->
                                <form method="POST" action="cart_remove.php">
                                    <input type="hidden" name="bike_id" value="<?php echo $bike['id']; ?>">
                                    <button type="submit" name="remove" class="btn btn-remove">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            Your cart is empty. Go to <a href="view_bikes.php" class="alert-link">View Bikes</a> to add some.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

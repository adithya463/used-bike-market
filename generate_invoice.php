<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$invoice_type = $_GET['type'] ?? 'purchase';
$bike_id = isset($_GET['bike_id']) ? intval($_GET['bike_id']) : 0;

if ($bike_id <= 0) {
    echo "<p style='color:red;text-align:center;'>❌ Invalid bike ID.</p>";
    exit;
}

// Fetch bike details with seller information
$sql = "SELECT b.*, u.username AS seller_name, u.email AS seller_email, u.phone AS seller_phone
        FROM bikes b 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bike_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<p style='color:red;text-align:center;'>❌ Bike not found.</p>";
    exit;
}

$bike = $result->fetch_assoc();
$buyer = $conn->query("SELECT * FROM users WHERE id = " . $_SESSION['user_id'])->fetch_assoc();

// Generate invoice details
$invoice_number = 'BIKE-' . date('Ymd') . '-' . rand(1000, 9999);
$invoice_date = date('Y-m-d H:i:s');

// If this is a purchase, update the bike status
if ($invoice_type === 'purchase' && $bike['status'] === 'available') {
    $conn->query("UPDATE bikes SET status = 'sold', buyer_id = " . $_SESSION['user_id'] . " WHERE id = $bike_id");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= ucfirst($invoice_type) ?> Invoice - <?= htmlspecialchars($bike['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #475569;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --border-radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            margin: 0;
            min-height: 100vh;
        }

        .invoice-container {
            background: #fff;
            max-width: 900px;
            margin: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .invoice-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .invoice-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .invoice-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .invoice-body {
            padding: 40px;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--light);
            border-radius: var(--border-radius);
        }

        .info-section h3 {
            margin: 0 0 15px 0;
            color: var(--dark);
            font-size: 1.2rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--secondary);
        }

        .info-value {
            color: var(--dark);
        }

        .bike-image {
            text-align: center;
            margin: 30px 0;
        }

        .bike-image img {
            max-width: 400px;
            height: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            border: 3px solid var(--light);
        }

        .bike-specs {
            background: var(--light);
            padding: 25px;
            border-radius: var(--border-radius);
            margin: 30px 0;
        }

        .specs-title {
            margin: 0 0 20px 0;
            color: var(--dark);
            font-size: 1.3rem;
            font-weight: 600;
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .spec-item:last-child {
            border-bottom: none;
        }

        .spec-label {
            font-weight: 600;
            color: var(--secondary);
        }

        .spec-value {
            color: var(--dark);
        }

        .price-section {
            background: var(--success);
            color: white;
            padding: 25px;
            border-radius: var(--border-radius);
            text-align: center;
            margin: 30px 0;
        }

        .price-amount {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
        }

        .price-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .contact-info {
            background: #e8f5e8;
            padding: 25px;
            border-radius: var(--border-radius);
            margin: 30px 0;
            border-left: 4px solid var(--success);
        }

        .contact-title {
            margin: 0 0 15px 0;
            color: var(--success);
            font-size: 1.2rem;
            font-weight: 600;
        }

        .contact-item {
            margin: 8px 0;
            color: var(--dark);
        }

        .invoice-actions {
            text-align: center;
            padding: 30px;
            background: var(--light);
            border-top: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 10px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.875rem;
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

        @media print {
            body { background: white; }
            .invoice-actions { display: none; }
            .invoice-container { box-shadow: none; }
        }

        @media (max-width: 768px) {
            .invoice-info {
                grid-template-columns: 1fr;
            }
            
            .specs-grid {
                grid-template-columns: 1fr;
            }
            
            .invoice-header h1 {
                font-size: 2rem;
            }
            
            .price-amount {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1><i class="fas fa-receipt"></i> <?= ucfirst($invoice_type) ?> Invoice</h1>
            <p>Invoice #: <?= $invoice_number ?> | Date: <?= date('F j, Y g:i a', strtotime($invoice_date)) ?></p>
        </div>

        <div class="invoice-body">
            <div class="invoice-info">
                <div class="info-section">
                    <h3><i class="fas fa-motorcycle"></i> Bike Information</h3>
                    <div class="info-item">
                        <span class="info-label">Model:</span>
                        <span class="info-value"><?= htmlspecialchars($bike['title']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge status-<?= $bike['status'] ?>">
                                <?= ucfirst($bike['status']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Price:</span>
                        <span class="info-value">₹<?= number_format($bike['price'], 2) ?></span>
                    </div>
                    <?php if (!empty($bike['location'])): ?>
                    <div class="info-item">
                        <span class="info-label">Location:</span>
                        <span class="info-value"><?= htmlspecialchars($bike['location']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="info-section">
                    <h3><i class="fas fa-users"></i> Contact Information</h3>
                    <div class="info-item">
                        <span class="info-label">Seller:</span>
                        <span class="info-value"><?= htmlspecialchars($bike['seller_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Buyer:</span>
                        <span class="info-value"><?= htmlspecialchars($buyer['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Buyer Email:</span>
                        <span class="info-value"><?= htmlspecialchars($buyer['email']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Buyer Phone:</span>
                        <span class="info-value"><?= htmlspecialchars($buyer['phone'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>

            <div class="bike-image">
                <img src="uploads/<?= htmlspecialchars($bike['image']) ?>" 
                     alt="<?= htmlspecialchars($bike['title']) ?>">
            </div>

            <?php if (!empty($bike['year']) || !empty($bike['mileage']) || !empty($bike['fuel_type'])): ?>
            <div class="bike-specs">
                <h3 class="specs-title"><i class="fas fa-cogs"></i> Technical Specifications</h3>
                <div class="specs-grid">
                    <?php if (!empty($bike['year'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Manufacturing Year:</span>
                        <span class="spec-value"><?= $bike['year'] ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($bike['mileage'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Mileage:</span>
                        <span class="spec-value"><?= number_format($bike['mileage']) ?> km</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($bike['engine_capacity'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Engine Capacity:</span>
                        <span class="spec-value"><?= htmlspecialchars($bike['engine_capacity']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($bike['fuel_type'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Fuel Type:</span>
                        <span class="spec-value"><?= htmlspecialchars($bike['fuel_type']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($bike['transmission'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Transmission:</span>
                        <span class="spec-value"><?= htmlspecialchars($bike['transmission']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($bike['color'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Color:</span>
                        <span class="spec-value"><?= htmlspecialchars($bike['color']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="price-section">
                <div class="price-label">Total Amount</div>
                <div class="price-amount">₹<?= number_format($bike['price'], 2) ?></div>
                <div class="price-label">
                    <?= $invoice_type === 'purchase' ? 'Paid' : 'Listed Price' ?>
                </div>
            </div>

            <div class="contact-info">
                <h4 class="contact-title"><i class="fas fa-address-card"></i> Seller Contact Details</h4>
                <div class="contact-item"><strong>Name:</strong> <?= htmlspecialchars($bike['seller_name']) ?></div>
                <div class="contact-item"><strong>Email:</strong> <?= htmlspecialchars($bike['seller_email'] ?? 'N/A') ?></div>
                <div class="contact-item"><strong>Phone:</strong> <?= htmlspecialchars($bike['seller_phone'] ?? 'N/A') ?></div>
            </div>
        </div>

        <div class="invoice-actions">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            
            <?php if ($invoice_type === 'wishlist'): ?>
            <a href="buy_bike.php?id=<?= $bike_id ?>" class="btn btn-success">
                <i class="fas fa-shopping-cart"></i> Buy Now
            </a>
            <?php endif; ?>
            
            <a href="view_bikes.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Bikes
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="wishlist_view.php" class="btn btn-outline">
                <i class="fas fa-heart"></i> My Wishlist
            </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
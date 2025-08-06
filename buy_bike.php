<?php
session_start();
include 'db connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!isset($_SESSION['user_id']) || $id <= 0) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT b.*, u.username AS seller_name, u.email AS seller_email, u.phone AS seller_phone
        FROM bikes b 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = $id";

$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    echo "<p style='color:red;text-align:center;'>❌ Bike not found.</p>";
    exit;
}

$bike = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payment_success = true;

    if ($payment_success) {
        $conn->query("UPDATE bikes SET status = 'sold', buyer_id = " . $_SESSION['user_id'] . " WHERE id = $id");
        $bill_number = 'BIKE-' . date('Ymd') . '-' . rand(1000, 9999);
        $purchase_date = date('Y-m-d H:i:s');
        $buyer_id = $_SESSION['user_id'];
        $buyer = $conn->query("SELECT * FROM users WHERE id = $buyer_id")->fetch_assoc();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice - <?= htmlspecialchars($bike['title']) ?></title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Poppins', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 20px;
                    margin: 0;
                }
                .invoice-box {
                    background: #fff;
                    padding: 40px;
                    max-width: 800px;
                    margin: auto;
                    border-radius: 12px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                }
                .invoice-header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #f0f0f0;
                    padding-bottom: 20px;
                }
                .invoice-header h1 {
                    margin: 0;
                    color: #2c3e50;
                    font-size: 2rem;
                }
                .invoice-header p {
                    color: #7f8c8d;
                    margin: 10px 0 0 0;
                }
                .invoice-details {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 30px;
                    margin-bottom: 30px;
                }
                .invoice-details div {
                    line-height: 1.8;
                }
                .bike-img {
                    display: block;
                    margin: 20px auto;
                    max-width: 350px;
                    height: auto;
                    border-radius: 12px;
                    border: 2px solid #ecf0f1;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                }
                .bike-specs {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                .spec-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin-top: 15px;
                }
                .spec-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px solid #e9ecef;
                }
                .spec-item:last-child {
                    border-bottom: none;
                }
                .spec-label {
                    font-weight: 600;
                    color: #495057;
                }
                .spec-value {
                    color: #6c757d;
                }
                .total {
                    text-align: right;
                    font-size: 1.5rem;
                    font-weight: bold;
                    margin-top: 30px;
                    padding: 20px;
                    background: #27ae60;
                    color: white;
                    border-radius: 8px;
                }
                .buttons {
                    text-align: center;
                    margin-top: 30px;
                    display: flex;
                    gap: 15px;
                    justify-content: center;
                }
                .btn {
                    padding: 12px 24px;
                    background: #27ae60;
                    color: white;
                    border: none;
                    border-radius: 8px;
                    text-decoration: none;
                    cursor: pointer;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
                }
                .btn-outline {
                    background: white;
                    color: #27ae60;
                    border: 2px solid #27ae60;
                }
                .btn-outline:hover {
                    background: #27ae60;
                    color: white;
                }
                .contact-info {
                    background: #e8f5e8;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                    border-left: 4px solid #27ae60;
                }
                @media print {
                    body { background: white; }
                    .buttons { display: none; }
                }
            </style>
        </head>
        <body>
        <div class="invoice-box">
            <div class="invoice-header">
                <h1>Purchase Invoice</h1>
                <p>Invoice #: <?= $bill_number ?> <br> Date: <?= date('F j, Y g:i a', strtotime($purchase_date)) ?></p>
            </div>
            <img src="uploads/<?= htmlspecialchars($bike['image']) ?>" alt="<?= htmlspecialchars($bike['title']) ?>" class="bike-img">
            
            <div class="invoice-details">
                <div>
                    <strong>Bike Model:</strong> <?= htmlspecialchars($bike['title']) ?><br>
                    <strong>Seller:</strong> <?= htmlspecialchars($bike['seller_name']) ?><br>
                    <strong>Buyer:</strong> <?= htmlspecialchars($buyer['username']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($buyer['email']) ?><br>
                    <strong>Phone:</strong> <?= htmlspecialchars($buyer['phone'] ?? 'N/A') ?>
                </div>
                <div>
                    <strong>Status:</strong> Sold<br>
                    <strong>Purchase Date:</strong> <?= date('F j, Y', strtotime($purchase_date)) ?><br>
                    <strong>Invoice #:</strong> <?= $bill_number ?><br>
                    <strong>Amount Paid:</strong> ₹<?= number_format($bike['price'], 2) ?>
                </div>
            </div>

            <?php if (!empty($bike['year']) || !empty($bike['mileage']) || !empty($bike['fuel_type'])): ?>
            <div class="bike-specs">
                <h3 style="margin: 0 0 15px 0; color: #2c3e50;">Bike Specifications</h3>
                <div class="spec-grid">
                    <?php if (!empty($bike['year'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Year:</span>
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
                    
                    <?php if (!empty($bike['location'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Location:</span>
                        <span class="spec-value"><?= htmlspecialchars($bike['location']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="contact-info">
                <h4 style="margin: 0 0 10px 0; color: #27ae60;">Seller Contact Information</h4>
                <p style="margin: 5px 0;"><strong>Name:</strong> <?= htmlspecialchars($bike['seller_name']) ?></p>
                <p style="margin: 5px 0;"><strong>Email:</strong> <?= htmlspecialchars($bike['seller_email'] ?? 'N/A') ?></p>
                <p style="margin: 5px 0;"><strong>Phone:</strong> <?= htmlspecialchars($bike['seller_phone'] ?? 'N/A') ?></p>
            </div>

            <div class="total">Total Paid: ₹<?= number_format($bike['price'], 2) ?></div>
            <div class="buttons">
                <a href="view_bikes.php" class="btn-outline">← Back to Bikes</a>
                <button onclick="window.print()" class="btn">🖨 Print Invoice</button>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit;
    } else {
        echo "<p style='color:red;text-align:center;'>❌ Payment failed.</p>";
        exit;
    }
}
?>

<!-- Payment Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Buy <?= htmlspecialchars($bike['title']) ?> - BikeMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #0b1c2c;
            margin: 0;
            padding: 0;
            color: white;
        }
        .container {
            padding: 40px 20px;
            max-width: 500px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            margin-top: 30px;
            color: #2d3436;
        }
        .bike-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .bike-header img {
            width: 100%;
            max-height: 240px;
            object-fit: contain;
            border-radius: 8px;
        }
        .bike-header h2 {
            margin: 10px 0 5px;
        }
        .payment-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .payment-form .form-row {
            display: flex;
            gap: 10px;
        }
        .btn {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background: #27ae60;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #0984e3;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="bike-header">
    <img src="uploads/<?= htmlspecialchars($bike['image']) ?>" alt="<?= htmlspecialchars($bike['title']) ?>">
    <h2><?= htmlspecialchars($bike['title']) ?></h2>
    <p>Sold by: <?= htmlspecialchars($bike['seller_name']) ?></p>
    <h3>₹<?= number_format($bike['price']) ?></h3>
    
    <?php if (!empty($bike['year']) || !empty($bike['mileage']) || !empty($bike['fuel_type'])): ?>
    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; margin-top: 15px; text-align: left;">
        <h4 style="margin: 0 0 10px 0; color: #fff;">Bike Details</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 0.9rem;">
            <?php if (!empty($bike['year'])): ?>
            <div><strong>Year:</strong> <?= $bike['year'] ?></div>
            <?php endif; ?>
            <?php if (!empty($bike['mileage'])): ?>
            <div><strong>Mileage:</strong> <?= number_format($bike['mileage']) ?> km</div>
            <?php endif; ?>
            <?php if (!empty($bike['fuel_type'])): ?>
            <div><strong>Fuel:</strong> <?= htmlspecialchars($bike['fuel_type']) ?></div>
            <?php endif; ?>
            <?php if (!empty($bike['transmission'])): ?>
            <div><strong>Transmission:</strong> <?= htmlspecialchars($bike['transmission']) ?></div>
            <?php endif; ?>
            <?php if (!empty($bike['location'])): ?>
            <div><strong>Location:</strong> <?= htmlspecialchars($bike['location']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="container">
    <h3 style="text-align:center;">Payment Details</h3>
    <form method="POST" class="payment-form" id="paymentForm">
        <label>Name on Card</label>
        <input type="text" name="card_name" required placeholder="John Doe">

        <label>Card Number</label>
        <input type="text" name="card_number" pattern="[0-9\s]{16,19}" placeholder="4242 4242 4242 4242" required>

        <div class="form-row">
            <div style="flex:1">
                <label>Expiry Date</label>
                <input type="text" name="expiry" placeholder="MM/YY" pattern="\d{2}/\d{2}" required>
            </div>
            <div style="flex:1">
                <label>CVC</label>
                <input type="text" name="cvc" placeholder="123" pattern="\d{3}" required>
            </div>
        </div>

        <button type="submit" class="btn">Pay ₹<?= number_format($bike['price']) ?></button>
    </form>
    <a href="view_bikes.php" class="back-link">← Back to Available Bikes</a>
</div>

<script>
    document.getElementById("paymentForm").addEventListener("submit", function(e){
        if (!confirm("Do you want to confirm the payment for ₹<?= number_format($bike['price']) ?>?")) {
            e.preventDefault();
        }
    });
</script>

</body>
</html>

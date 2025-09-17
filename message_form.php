<?php
session_start();
include 'db connect.php'; // Fixed filename to match standard

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$bike_id = intval($_GET['bike_id'] ?? 0);

// Get bike and seller info
$sql = "SELECT bikes.*, users.id AS seller_id FROM bikes 
        JOIN users ON bikes.user_id = users.id WHERE bikes.id = $bike_id";
$result = $conn->query($sql);
$bike = $result->fetch_assoc();

// Self-contact guard
$isOwnListing = isset($bike['seller_id']) && ($bike['seller_id'] == $_SESSION['user_id']);

// Get current user's phone to prefill
$sender_id = $_SESSION['user_id'];
$sender = $conn->query("SELECT phone FROM users WHERE id = $sender_id")->fetch_assoc();
$prefillPhone = $sender['phone'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($isOwnListing) {
        $error = "You cannot contact yourself about your own listing.";
    } else {
        $message = trim($_POST['message'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $sender_id = $_SESSION['user_id'];
        $receiver_id = $bike['seller_id'];

        // Basic phone validation (digits and + - spaces allowed, length 7-20)
        if ($phone === '' || !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
            $error = "Please enter a valid phone number.";
        } elseif ($message === '') {
            $error = "Message cannot be empty.";
        } else {
            // Include phone in message body
            $composed = "Phone: " . $phone . "\n\n" . $message;
            $safeMessage = htmlspecialchars($composed);

            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, bike_id, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $sender_id, $receiver_id, $bike_id, $safeMessage);
            
            if ($stmt->execute()) {
                $success = "Message sent successfully!";
                $prefillPhone = $phone;
            } else {
                $error = "Failed to send message: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Seller</title>
    <style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #1e1e1e;
            --primary: #bb86fc;
            --text: #e1e1e1;
            --text-secondary: #a0a0a0;
            --success: #4caf50;
            --error: #cf6679;
            --warning: #f39c12;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: var(--darker-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        h3 {
            color: var(--primary);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .bike-info {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
        }
        
        .bike-title {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        
        .bike-price {
            color: var(--primary);
            font-weight: bold;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px;
            background-color: #2d2d2d;
            border: 1px solid #444;
            border-radius: 6px;
            color: var(--text);
            font-size: 16px;
            margin-bottom: 16px;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            background-color: #2d2d2d;
            border: 1px solid #444;
            border-radius: 6px;
            color: var(--text);
            font-size: 16px;
            min-height: 150px;
            resize: vertical;
            margin-bottom: 20px;
        }
        
        textarea:focus, input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        button {
            background-color: var(--primary);
            color: #000;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #a370d9;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        
        .success {
            background-color: rgba(76, 175, 80, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
        }
        
        .error {
            background-color: rgba(207, 102, 121, 0.2);
            color: var(--error);
            border: 1px solid var(--error);
        }
        .warning {
            background-color: rgba(243, 156, 18, 0.2);
            color: var(--warning);
            border: 1px solid var(--warning);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <h3>Contact Seller</h3>
        
        <div class="bike-info">
            <div class="bike-title">About: <?php echo htmlspecialchars($bike['title']); ?></div>
            <div class="bike-price">Price: ₹<?php echo number_format($bike['price']); ?></div>
        </div>
        
<?php if ($isOwnListing): ?>
        <div class="message warning">You are viewing your own listing. You can’t send a message to yourself.</div>
<?php else: ?>
        <form method="POST">
            <input type="text" name="phone" placeholder="Your phone number" value="<?php echo htmlspecialchars($prefillPhone); ?>" required>
            <textarea name="message" placeholder="Write your message to the seller..." required></textarea>
            <button type="submit">Send Message</button>
        </form>
<?php endif; ?>
    </div>
</body>
</html>
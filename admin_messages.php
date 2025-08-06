<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

$messages = $conn->query("SELECT m.*, u1.username AS sender, u2.username AS receiver, b.title AS bike_title
                           FROM messages m
                           JOIN users u1 ON m.sender_id = u1.id
                           JOIN users u2 ON m.receiver_id = u2.id
                           JOIN bikes b ON m.bike_id = b.id
                           ORDER BY m.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Messages - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
        }
        .container {
            max-width: 960px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #6c5ce7;
            color: white;
        }
        .back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #6c5ce7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .message-content {
            max-width: 400px;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Messages</h2>
    <table>
        <tr>
            <th>Message ID</th>
            <th>Sender</th>
            <th>Receiver</th>
            <th>Bike</th>
            <th>Message</th>
            <th>Time</th>
        </tr>
        <?php while ($msg = $messages->fetch_assoc()): ?>
            <tr>
                <td><?php echo $msg['id']; ?></td>
                <td><?php echo htmlspecialchars($msg['sender']); ?></td>
                <td><?php echo htmlspecialchars($msg['receiver']); ?></td>
                <td><?php echo htmlspecialchars($msg['bike_title']); ?></td>
                <td class="message-content"><?php echo htmlspecialchars($msg['message']); ?></td>
                <td><?php echo date('M d, Y g:i A', strtotime($msg['created_at'])); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <a href="admin_dashboard.php" class="back">Back to Dashboard</a>
</div>
</body>
</html>

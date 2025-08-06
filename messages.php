<?php
session_start();
include 'db connect.php'; 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all messages related to this user
$sql = "SELECT m.*, b.title, 
        u1.username as sender_name, 
        u2.username as receiver_name 
        FROM messages m 
        JOIN bikes b ON m.bike_id = b.id 
        JOIN users u1 ON m.sender_id = u1.id
        JOIN users u2 ON m.receiver_id = u2.id
        WHERE m.sender_id = $user_id OR m.receiver_id = $user_id
        ORDER BY m.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Messages</title>
    <style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #1e1e1e;
            --primary: #bb86fc;
            --text: #e1e1e1;
            --text-secondary: #a0a0a0;
            --success: #4caf50;
            --error: #cf6679;
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
            max-width: 800px;
            margin: 40px auto;
            background-color: var(--darker-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        h2 {
            color: var(--primary);
            margin-bottom: 25px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
        }
        
        .message-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .message-card {
            background-color: #2d2d2d;
            border-radius: 6px;
            padding: 20px;
            border-left: 4px solid var(--primary);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .bike-title {
            color: var(--primary);
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .message-time {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .message-content {
            margin: 15px 0;
            padding: 15px;
            background-color: rgba(0,0,0,0.2);
            border-radius: 4px;
        }
        
        .message-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .sender {
            color: var(--primary);
        }
        
        .receiver {
            color: #03dac6;
        }
        
        .no-messages {
            text-align: center;
            padding: 30px;
            color: var(--text-secondary);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px auto;
            }
            
            .message-header {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Messages</h2>
        
        <div class="message-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <span class="bike-title"><?php echo htmlspecialchars($row['title']); ?></span>
                            <span class="message-time"><?php echo date('M j, Y g:i a', strtotime($row['created_at'])); ?></span>
                        </div>
                        
                        <div class="message-content">
                            <?php echo htmlspecialchars($row['message']); ?>
                        </div>
                        
                        <div class="message-meta">
                            <span class="sender">From: <?php echo htmlspecialchars($row['sender_name']); ?></span>
                            <span class="receiver">To: <?php echo htmlspecialchars($row['receiver_name']); ?></span>
                        </div>

                        <!-- Reply Form -->
                        <?php if ($row['receiver_id'] == $user_id): ?>
                            <form action="send_reply.php" method="POST" style="margin-top: 15px;">
                                <input type="hidden" name="bike_id" value="<?php echo $row['bike_id']; ?>">
                                <input type="hidden" name="receiver_id" value="<?php echo $row['sender_id']; ?>">
                                <input type="hidden" name="reply_to" value="<?php echo $row['id']; ?>">
                                <textarea name="message" rows="3" placeholder="Write your reply..." required 
                                        style="width:100%; padding:8px; border-radius:4px; border:none; resize:vertical; background:#1e1e1e; color:#fff;"></textarea>
                                <button type="submit" 
                                        style="margin-top:10px; background:var(--primary); color:white; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">
                                    Reply
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-messages">
                    No messages found. Start a conversation about a bike!
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
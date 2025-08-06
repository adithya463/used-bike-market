<?php
include 'db connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = intval($_POST['receiver_id']);
    $bike_id = intval($_POST['bike_id']);
    $message = $_POST['message'];
    $reply_to = isset($_POST['reply_to']) ? intval($_POST['reply_to']) : NULL;

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, bike_id, message, reply_to) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $sender_id, $receiver_id, $bike_id, $message, $reply_to);

    if ($stmt->execute()) {
        header("Location: messages.php");
    } else {
        echo "❌ Error sending reply.";
    }
}
?>
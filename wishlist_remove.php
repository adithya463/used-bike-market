<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $bike_id = (int)$_POST['bike_id'];
    
    // Remove from wishlist
    $remove_wishlist = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND bike_id = ?");
    $remove_wishlist->bind_param("ii", $user_id, $bike_id);
    
    if ($remove_wishlist->execute()) {
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 
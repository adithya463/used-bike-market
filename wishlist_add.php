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
    
    // Check if bike exists and is available
    $bike_check = $conn->prepare("SELECT id FROM bikes WHERE id = ? AND status = 'available'");
    $bike_check->bind_param("i", $bike_id);
    $bike_check->execute();
    $bike_result = $bike_check->get_result();
    
    if ($bike_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Bike not found or not available']);
        exit;
    }
    
    // Check if already in wishlist
    $check_wishlist = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND bike_id = ?");
    $check_wishlist->bind_param("ii", $user_id, $bike_id);
    $check_wishlist->execute();
    $wishlist_result = $check_wishlist->get_result();
    
    if ($wishlist_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Bike already in wishlist']);
        exit;
    }
    
    // Add to wishlist
    $add_wishlist = $conn->prepare("INSERT INTO wishlist (user_id, bike_id) VALUES (?, ?)");
    $add_wishlist->bind_param("ii", $user_id, $bike_id);
    
    if ($add_wishlist->execute()) {
        echo json_encode(['success' => true, 'message' => 'Added to wishlist successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 
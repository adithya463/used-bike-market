<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['remove'])) {
    $user_id = $_SESSION['user_id'];
    $bike_id = intval($_POST['bike_id']);
    $conn->query("DELETE FROM cart WHERE user_id = $user_id AND bike_id = $bike_id");
}

header("Location: cart_view.php");
?>

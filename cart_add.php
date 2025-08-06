<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $bike_id = intval($_POST['bike_id']);

    $check = $conn->query("SELECT * FROM cart WHERE user_id = $user_id AND bike_id = $bike_id");
    if ($check->num_rows === 0) {
        $conn->query("INSERT INTO cart (user_id, bike_id) VALUES ($user_id, $bike_id)");
    }

    header("Location: view_bikes.php");
}
?>

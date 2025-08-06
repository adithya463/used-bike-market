<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "bike_market";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
} else if ($conn->connect_errno == 0) {}
  else {
    echo "⚠️ Unknown connection tissue!";
}
?>

<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM bikes WHERE id = $delete_id");
    header("Location: admin_bikes.php");
    exit;
}

$bikes = $conn->query("SELECT b.*, u.username FROM bikes b JOIN users u ON b.user_id = u.id ORDER BY b.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Bikes - Admin</title>
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
            background: #00b894;
            color: white;
        }
        img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }
        .delete-btn {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
        .back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #0984e3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Bike Listings</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Price</th>
            <th>Status</th>
            <th>Image</th>
            <th>Posted By</th>
            <th>Action</th>
        </tr>
        <?php while ($bike = $bikes->fetch_assoc()): ?>
            <tr>
                <td><?php echo $bike['id']; ?></td>
                <td><?php echo htmlspecialchars($bike['title']); ?></td>
                <td><?php echo htmlspecialchars($bike['description']); ?></td>
                <td>₹<?php echo number_format($bike['price']); ?></td>
                <td><?php echo $bike['status']; ?></td>
                <td><img src="uploads/<?php echo htmlspecialchars($bike['image']); ?>" alt="Bike Image"></td>
                <td><?php echo htmlspecialchars($bike['username']); ?></td>
                <td><a class="delete-btn" href="admin_bikes.php?delete=<?php echo $bike['id']; ?>" onclick="return confirm('Delete this bike?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <a href="admin_dashboard.php" class="back">Back to Dashboard</a>
</div>
</body>
</html>

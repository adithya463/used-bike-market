<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $delete_id AND is_admin = 0");
    header("Location: admin_users.php");
    exit;
}

$users = $conn->query("SELECT * FROM users WHERE is_admin = 0 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            padding: 30px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #3498db;
            color: white;
        }
        a.delete {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
        a.back {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
        <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></td>
                <td><?php echo htmlspecialchars($user['address'] ?? 'Not set'); ?></td>
                <td><a href="admin_users.php?delete=<?php echo $user['id']; ?>" class="delete" onclick="return confirm('Delete this user?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <a class="back" href="admin_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>

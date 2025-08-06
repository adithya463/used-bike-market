<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - BikeMart</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
    }

    .dashboard-container {
      width: 90%;
      max-width: 900px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 15px;
      padding: 40px 50px;
      text-align: center;
      backdrop-filter: blur(12px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .welcome-header h2 {
      font-size: 32px;
      margin-bottom: 8px;
      color: #00ffff;
    }

    .welcome-header p {
      font-size: 16px;
      color: #ccc;
    }

    .dashboard-menu {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 25px;
      margin-top: 35px;
    }

    .menu-item {
      background: rgba(255, 255, 255, 0.07);
      border-radius: 12px;
      padding: 25px 15px;
      text-decoration: none;
      color: #ffffff;
      font-weight: 600;
      border: 1px solid rgba(255, 255, 255, 0.15);
      transition: all 0.4s ease;
      box-shadow: 0 4px 15px rgba(0, 255, 255, 0.05);
    }

    .menu-item i {
      display: block;
      font-size: 36px;
      margin-bottom: 12px;
      color: #00ffff;
    }

    .menu-item:hover {
      transform: translateY(-5px) scale(1.03);
      background: rgba(0, 255, 255, 0.1);
      box-shadow: 0 0 15px #00ffff80;
      border-color: #00ffff;
    }

    .logout-btn {
      display: inline-block;
      margin-top: 40px;
      padding: 12px 30px;
      background: #e74c3c;
      color: white;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
    }

    .logout-btn:hover {
      background: #c0392b;
      box-shadow: 0 0 10px #e74c3c88;
    }

    @media (max-width: 600px) {
      .dashboard-menu {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
      header("Location: login.php");
      exit;
    }

    echo "<div class='dashboard-container'>";
    echo "<div class='welcome-header'>";
    echo "<h2>Welcome back, " . htmlspecialchars($_SESSION['username']) . "!</h2>";
    echo "<p>Manage your BikeMart account</p>";
    echo "</div>";

    


    echo "<div class='dashboard-menu'>";
    echo "<a href='view_bikes.php' class='menu-item'>";
    echo "<i class='fas fa-bicycle'></i>";
    echo "View Bikes";
    echo "</a>";

    echo "<a href='profile.php' class='menu-item'>";
    echo "<i class='fas fa-user'></i> ";
    echo "My Profile";
    echo "</a>";


    echo "<a href='add_bike.php' class='menu-item'>";
    echo "<i class='fas fa-plus-circle'></i>";
    echo "Add Bike";
    echo "</a>";

    echo "<a href='messages.php' class='menu-item'>";
    echo "<i class='fas fa-envelope'></i>";
    echo "Messages";
    echo "</a>";

    echo "<a href='wishlist_view.php' class='menu-item'>";
    echo "<i class='fas fa-heart'></i>";
    echo "My Wishlist";
    echo "</a>";
    echo "</div>";

    echo "<a href='logout.php' class='logout-btn'>Logout</a>";
    echo "</div>";
  ?>

</body>
</html>

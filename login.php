<?php
session_start();
include 'db connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['is_admin'] = $row['is_admin']; 

            if ($row['is_admin'] == 1) {
                header("Location: admin_dashboard.php"); 
            } else {
                header("Location: dashboard.php"); 
            }
            exit;
        } else {
            $error = "❌ Wrong password.";
        }
    } else {
        $error = "❌ No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - BikeMart</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Orbitron:wght@500&display=swap" rel="stylesheet">
  <style>
    body {
      background: url('uploads/harley-davidson-eeTJKC_wz34-unsplash.jpg') center/cover no-repeat fixed;
      font-family: 'Roboto', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      backdrop-filter: blur(4px);
    }

    .login-card {
      background: rgba(0, 0, 0, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 50px 40px;
      width: 100%;
      max-width: 400px;
      color: #fff;
      box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    }

    .login-card h2 {
      font-family: 'Orbitron', sans-serif;
      text-align: center;
      margin-bottom: 30px;
      font-size: 28px;
      letter-spacing: 2px;
    }

    .login-card input {
      width: 100%;
      padding: 12px 15px;
      margin: 12px 0;
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 5px;
      font-size: 16px;
      background: rgba(255,255,255,0.1);
      color: #fff;
      outline: none;
      transition: border-color 0.3s, background 0.3s;
    }

    .login-card input:focus {
      border-color: #00ffff;
      background: rgba(255,255,255,0.2);
    }

    .login-card button {
      width: 100%;
      padding: 12px 15px;
      background: #00ffff;
      border: none;
      color: #000;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 20px;
      transition: transform 0.2s, background 0.3s;
      font-weight: bold;
    }

    .login-card button:hover {
      background: #00e0e0;
      transform: translateY(-2px);
    }

    .login-card p {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }

    .login-card a {
      color: #00ffff;
      text-decoration: none;
    }

    .login-card a:hover {
      text-decoration: underline;
    }

    .error {
      color: #ff4c4c;
      font-size: 14px;
      text-align: center;
      margin-bottom: 15px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .login-card {
        margin: 20px;
        padding: 40px 30px;
        max-width: 100%;
      }

      .login-card h2 {
        font-size: 24px;
        margin-bottom: 25px;
      }

      .login-card input,
      .login-card button {
        padding: 10px 12px;
        font-size: 15px;
      }
    }

    @media (max-width: 480px) {
      .login-card {
        margin: 15px;
        padding: 30px 20px;
      }

      .login-card h2 {
        font-size: 20px;
        margin-bottom: 20px;
      }

      .login-card input,
      .login-card button {
        padding: 8px 10px;
        font-size: 14px;
        margin: 8px 0;
      }

      .login-card button {
        margin-top: 15px;
      }

      .login-card p {
        font-size: 13px;
        margin-top: 15px;
      }

      .error {
        font-size: 13px;
        margin-bottom: 12px;
      }
    }

    @media (max-width: 360px) {
      .login-card {
        margin: 10px;
        padding: 25px 15px;
      }

      .login-card h2 {
        font-size: 18px;
        margin-bottom: 15px;
      }

      .login-card input,
      .login-card button {
        padding: 6px 8px;
        font-size: 13px;
        margin: 6px 0;
      }

      .login-card button {
        margin-top: 12px;
      }
    }
  </style>
</head>
<body>

  <div class="login-card">
    <h2>🚲 BikeMart Login</h2>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
      <input name="email" type="email" placeholder="Email" required>
      <input name="password" type="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
  </div>

</body>
</html>

<?php
include 'db connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, phone, address, password) 
            VALUES ('$username', '$email', '$phone', '$address', '$password')";

    if ($conn->query($sql) === TRUE) {
        $message = "✅ Registered successfully! <a href='login.php'>Login now</a>";
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register - BikeMart</title>
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

    .register-card {
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

    .register-card h2 {
      font-family: 'Orbitron', sans-serif;
      text-align: center;
      margin-bottom: 30px;
      font-size: 28px;
      letter-spacing: 2px;
    }

    .register-card input {
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

    .register-card input:focus {
      border-color: #00ffff;
      background: rgba(255,255,255,0.2);
    }

    .register-card button {
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

    .register-card button:hover {
      background: #00e0e0;
      transform: translateY(-2px);
    }

    .register-card p {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }

    .register-card a {
      color: #00ffff;
      text-decoration: none;
    }

    .register-card a:hover {
      text-decoration: underline;
    }

    .message {
      text-align: center;
      margin-bottom: 15px;
      font-size: 14px;
    }

    .message a {
      color: #00ffff;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .register-card {
        margin: 20px;
        padding: 40px 30px;
        max-width: 100%;
      }

      .register-card h2 {
        font-size: 24px;
        margin-bottom: 25px;
      }

      .register-card input,
      .register-card button {
        padding: 10px 12px;
        font-size: 15px;
      }
    }

    @media (max-width: 480px) {
      .register-card {
        margin: 15px;
        padding: 30px 20px;
      }

      .register-card h2 {
        font-size: 20px;
        margin-bottom: 20px;
      }

      .register-card input,
      .register-card button {
        padding: 8px 10px;
        font-size: 14px;
        margin: 8px 0;
      }

      .register-card button {
        margin-top: 15px;
      }

      .register-card p {
        font-size: 13px;
        margin-top: 15px;
      }

      .message {
        font-size: 13px;
        margin-bottom: 12px;
      }
    }

    @media (max-width: 360px) {
      .register-card {
        margin: 10px;
        padding: 25px 15px;
      }

      .register-card h2 {
        font-size: 18px;
        margin-bottom: 15px;
      }

      .register-card input,
      .register-card button {
        padding: 6px 8px;
        font-size: 13px;
        margin: 6px 0;
      }

      .register-card button {
        margin-top: 12px;
      }
    }
  </style>
</head>
<body>

  <div class="register-card">
    <h2>🚲 Register at BikeMart</h2>

    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
      <input name="username" placeholder="Username" required>
      <input name="email" type="email" placeholder="Email" required>
      <input name="phone" placeholder="Phone Number" required>
      <input name="address" placeholder="Address" required>
      <input name="password" type="password" placeholder="Password" required>
      <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us - BikeMart</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #f1f5f9; margin: 0; }
    .container { max-width: 500px; margin: 60px auto; background: #fff; padding: 32px; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    h1 { text-align: center; color: #2563eb; margin-bottom: 24px; }
    label { font-weight: 600; color: #222; }
    input, textarea { width: 100%; padding: 12px; margin: 10px 0 20px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1em; background: #f8fafc; }
    button { width: 100%; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; }
    button:hover { background: #1d4ed8; }
    .success { color: #22c55e; text-align: center; margin-bottom: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Contact Us</h1>
    <?php if (!empty($_POST)) echo '<div class="success">Thank you for contacting us! We will get back to you soon.</div>'; ?>
    <form method="POST">
      <label for="name">Your Name</label>
      <input name="name" id="name" required>
      <label for="email">Your Email</label>
      <input name="email" id="email" type="email" required>
      <label for="message">Your Message</label>
      <textarea name="message" id="message" rows="5" required></textarea>
      <button type="submit">Send</button>
    </form>
  </div>
</body>
</html> 
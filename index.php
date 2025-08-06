<?php
session_start();
include 'db connect.php';

// Fetch 4 latest bikes for featured section
$featured_bikes = $conn->query("SELECT * FROM bikes WHERE status = 'available' ORDER BY id DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BikeMart - Buy and Sell Used Bikes</title>
  <meta name="description" content="BikeMart - The best place to buy and sell used bikes. Find great deals on used motorcycles or sell your bike quickly.">
  <link rel="icon" href="uploads/pulsar.jpg" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --secondary: #475569;
      --accent: #f97316;
      --light: #f8fafc;
      --dark: #0f172a;
      --success: #22c55e;
      --gray-100: #f1f5f9;
      --gray-200: #e2e8f0;
      --gray-300: #cbd5e1;
      --gray-600: #475569;
      --gray-800: #1e293b;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      line-height: 1.6;
      color: var(--gray-800);
    }

    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      text-decoration: none;
    }

    .nav-links {
      display: flex;
      gap: 2rem;
      align-items: center;
    }

    .nav-links a {
      text-decoration: none;
      color: var(--gray-600);
      font-weight: 500;
      transition: color 0.3s;
    }

    .nav-links a:hover {
      color: var(--primary);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      border-radius: 0.5rem;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }

    .btn-outline {
      border: 2px solid var(--primary);
      color: var(--primary);
    }

    .btn-outline:hover {
      background: var(--primary);
      color: white;
    }

    .hero {
      min-height: 100vh;
      background: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3)), 
                  url(uploads/harley-davidson-eeTJKC_wz34-unsplash.jpg) no-repeat center center / cover;
      display: flex;
      align-items: center;
      padding: 6rem 2rem 2rem;
      position: relative;
    }

    .hero-content {
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
      color: white;
    }

    .hero h1 {
      font-size: 3.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      line-height: 1.2;
    }

    .hero p {
      font-size: 1.25rem;
      margin-bottom: 2rem;
      opacity: 0.9;
    }

    .featured {
      padding: 5rem 2rem;
      background: var(--gray-100);
    }

    .section-title {
      text-align: center;
      margin-bottom: 3rem;
    }

    .section-title h2 {
      font-size: 2.5rem;
      color: var(--dark);
      margin-bottom: 1rem;
    }

    .section-title p {
      color: var(--gray-600);
      font-size: 1.1rem;
    }

    .bikes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    .bike-card {
      background: white;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }

    .bike-card:hover {
      transform: translateY(-5px);
    }

    .bike-image {
      height: 200px;
      width: 100%;
      object-fit: cover;
    }

    .bike-details {
      padding: 1.5rem;
    }

    .bike-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }

    .bike-price {
      color: var(--primary);
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .features {
      padding: 5rem 2rem;
      background: white;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 3rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .feature-card {
      text-align: center;
      padding: 2rem;
    }

    .feature-icon {
      width: 80px;
      height: 80px;
      background: var(--primary);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      margin: 0 auto 1.5rem;
    }

    .feature-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--dark);
    }

    .feature-desc {
      color: var(--gray-600);
    }

    footer {
      background: var(--gray-800);
      color: white;
      padding: 4rem 2rem 2rem;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      gap: 3rem;
      justify-content: space-between;
      align-items: flex-start;
    }

    .footer-brand {
      flex: 1 1 260px;
      min-width: 220px;
      margin-bottom: 2rem;
    }

    .footer-logo {
      font-size: 1.7rem;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .footer-tagline {
      color: var(--gray-300);
      margin: 1rem 0 1.5rem 0;
      font-size: 1.05rem;
    }

    .footer-social a {
      color: var(--gray-300);
      margin-right: 1rem;
      font-size: 1.3rem;
      transition: color 0.2s;
      text-decoration: none;
    }

    .footer-social a:hover {
      color: var(--primary);
    }

    .footer-links-section {
      display: flex;
      gap: 3rem;
      flex: 2 1 400px;
      min-width: 220px;
      justify-content: flex-end;
    }

    .footer-section h3 {
      font-size: 1.15rem;
      margin-bottom: 1.2rem;
      color: #fff;
    }

    .footer-links {
      list-style: none;
      padding: 0;
    }

    .footer-links li {
      margin-bottom: 0.75rem;
    }

    .footer-links a {
      color: var(--gray-300);
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer-links a:hover {
      color: var(--primary);
    }

    .copyright {
      text-align: center;
      padding-top: 2rem;
      margin-top: 2rem;
      border-top: 1px solid var(--gray-600);
      color: var(--gray-300);
      font-size: 0.98rem;
    }

    @media (max-width: 900px) {
      .footer-content {
        flex-direction: column;
        gap: 2.5rem;
        align-items: stretch;
      }
      .footer-links-section {
        flex-direction: column;
        gap: 2rem;
        justify-content: flex-start;
      }
    }

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2.5rem;
      }
      
      .hero p {
        font-size: 1.1rem;
      }

      .navbar {
        padding: 1rem;
      }

      .nav-links {
        display: none;
      }

      .hero {
        padding: 6rem 1rem 4rem;
      }

      .hero-content {
        text-align: center;
        max-width: 100%;
      }

      .hero-buttons {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
      }

      .hero-buttons .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
      }

      .featured {
        padding: 3rem 1rem;
      }

      .bikes-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      .bike-card {
        max-width: 400px;
        margin: 0 auto;
      }

      .features {
        padding: 3rem 1rem;
      }

      .features-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
      }

      .feature-card {
        padding: 1.5rem;
      }

      .section-title {
        text-align: center;
        margin-bottom: 2rem;
      }

      .section-title h2 {
        font-size: 1.8rem;
      }

      .section-title p {
        font-size: 1rem;
      }

      footer {
        padding: 3rem 1rem 2rem;
      }
    }

    @media (max-width: 480px) {
      .hero h1 {
        font-size: 2rem;
      }
      
      .hero p {
        font-size: 1rem;
      }

      .hero-buttons .btn {
        padding: 0.875rem 1.25rem;
        font-size: 0.95rem;
      }

      .bike-card {
        margin: 0 0.5rem;
      }

      .bike-image {
        height: 200px;
      }

      .feature-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
      }

      .feature-title {
        font-size: 1.1rem;
      }

      .footer-logo {
        font-size: 1.5rem;
      }

      .footer-tagline {
        font-size: 1rem;
      }
    }

    /* Mobile menu toggle */
    .mobile-menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: var(--gray-600);
      cursor: pointer;
    }

    @media (max-width: 768px) {
      .mobile-menu-toggle {
        display: block;
      }

      .nav-links {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background: white;
        flex-direction: column;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transform: translateY(-100%);
        opacity: 0;
        transition: all 0.3s ease;
      }

      .nav-links.active {
        transform: translateY(0);
        opacity: 1;
      }

      .nav-links a {
        padding: 1rem 0;
        border-bottom: 1px solid var(--gray-200);
        width: 100%;
        text-align: center;
      }

      .nav-links a:last-child {
        border-bottom: none;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar">
    <a href="index.php" class="logo">
      <i class="fas fa-motorcycle"></i>
      BikeMart
    </a>
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
      <i class="fas fa-bars"></i>
    </button>
    <div class="nav-links" id="navLinks">
      <a href="view_bikes.php">Browse Bikes</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="cart_view.php">Cart</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="btn btn-outline">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline">Login</a>
        <a href="register.php" class="btn btn-primary">Register</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>Find Your Perfect Ride</h1>
      <p>Buy and sell used bikes with confidence. Join thousands of satisfied riders in the largest bike marketplace.</p>
      <div class="hero-buttons">
        <a href="view_bikes.php" class="btn btn-primary">Browse Bikes</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="register.php" class="btn btn-outline">Join Now</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Featured Bikes -->
  <section class="featured">
    <div class="section-title">
      <h2>Featured Bikes</h2>
      <p>Discover our latest and most popular listings</p>
    </div>
    <div class="bikes-grid">
      <?php while ($bike = $featured_bikes->fetch_assoc()): ?>
        <div class="bike-card">
          <img src="uploads/<?= htmlspecialchars($bike['image']) ?>" alt="<?= htmlspecialchars($bike['title']) ?>" class="bike-image">
          <div class="bike-details">
            <h3 class="bike-title"><?= htmlspecialchars($bike['title']) ?></h3>
            <div class="bike-price">₹<?= number_format($bike['price']) ?></div>
            <a href="view_bikes.php" class="btn btn-primary">View Details</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features">
    <div class="section-title">
      <h2>Why Choose BikeMart?</h2>
      <p>We make buying and selling bikes simple and secure</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h3 class="feature-title">Secure Transactions</h3>
        <p class="feature-desc">Safe and secure payment processing for peace of mind.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-search"></i>
        </div>
        <h3 class="feature-title">Easy to Search</h3>
        <p class="feature-desc">Find exactly what you're looking for with our powerful search filters.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-comments"></i>
        </div>
        <h3 class="feature-title">Direct Communication</h3>
        <p class="feature-desc">Connect directly with sellers through our messaging system.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-brand">
        <span class="footer-logo"><i class="fas fa-motorcycle"></i> <strong>BikeMart</strong></span>
        <p class="footer-tagline">Your trusted marketplace for used bikes.</p>
        <div class="footer-social">
          <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
      <div class="footer-links-section">
        <div class="footer-section">
          <h3>Quick Links</h3>
          <ul class="footer-links">
            <li><a href="view_bikes.php">Browse Bikes</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="login.php">Login</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h3>Support</h3>
          <ul class="footer-links">
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="faq.php">FAQ</a></li>
            <li><a href="terms.php">Terms of Service</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="copyright">
      &copy; <?php echo date("Y"); ?> <strong>BikeMart</strong>. All rights reserved. |
      Designed & Developed by <strong>Adithya Kumar Mishra</strong>
    </div>
  </footer>

  <script>
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.getElementById('navLinks');

    mobileMenuToggle.addEventListener('click', function() {
      navLinks.classList.toggle('active');
    });

    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', function() {
        navLinks.classList.remove('active');
      });
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
      if (!event.target.closest('.navbar')) {
        navLinks.classList.remove('active');
      }
    });

    // Add scroll animation for navbar
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
      } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
      }
    });

    // Add animation to features on scroll
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = 1;
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.feature-card').forEach(card => {
      card.style.opacity = 0;
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      observer.observe(card);
    });
  </script>
</body>
</html>

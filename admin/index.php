<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
    body {
      background: url('images/tenant1.jpg') no-repeat center center/cover;
      color: #fff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background: rgba(0, 0, 0, 0.7);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header h1 {
      color: gold;
      font-size: 1.8rem;
    }
    nav a {
      color: #fff;
      margin-left: 20px;
      text-decoration: none;
      font-weight: bold;
    }
    .main {
      flex: 1;
      padding: 2rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background-color: rgba(0,0,0,0.5);
    }
    .welcome-text {
      text-align: center;
      margin-bottom: 2rem;
    }
    .welcome-text h2 {
      font-size: 2.2rem;
      color: gold;
    }
    .welcome-text p {
      font-size: 1.1rem;
      color: #ddd;
      max-width: 600px;
      margin: 0 auto;
    }
    .about {
      background-color: rgba(0, 0, 0, 0.6);
      color: #eee;
      padding: 2rem;
      text-align: center;
    }
    .about h4 {
      font-size: 1.5rem;
      color: gold;
      margin-bottom: 1rem;
    }
    footer {
      background: #111;
      color: #ccc;
      padding: 1rem;
      text-align: center;
      font-size: 0.9rem;
    }
    @media (max-width: 768px) {
      .welcome-text h2 { font-size: 1.5rem; }
      .main { padding: 1rem; }
    }
  </style>
</head>
<body>

  <header>
    <h1>TenantManager</h1>
    <nav>
      <a href="login.php">Login</a>
      <a href="#about">About Us</a>
    </nav>
  </header>

  <section class="main">
    <div class="welcome-text">
      <h2>Smart Tenant Management System</h2>
      <p>Track tenant payments, bills, and communication—all in one powerful, automated platform.</p>
    </div>
  </section>

  <section class="about" id="about">
    <h4>About This System</h4>
    <p>This system helps property managers and landlords manage tenants, track M-Pesa payments, add bills for water, electricity, garbage, and get real-time insights on rent collection.</p>
  </section>

  <footer>
    &copy; <?php echo date("Y"); ?> TenantManager System. Built with ❤️ in Kenya.
  </footer>

</body>
</html>

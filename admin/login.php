<?php
session_start();
require 'includes/db.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header("Location: admin/dashboard.php");
            exit();
        }
    }

    $error = "Invalid email or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - TenantManager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
    body {
      background: url('images/tenant1.jpg') no-repeat center center/cover;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: #fff;
    }
    .login-container {
      background-color: rgba(255, 255, 255, 0.95);
      padding: 2rem;
      border-radius: 10px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }
    h2 {
      text-align: center;
      color: gold;
      margin-bottom: 1.5rem;
    }
    input {
      width: 100%;
      padding: 10px;
      margin-bottom: 1rem;
      border: 1px solid #aaa;
      border-radius: 5px;
    }
    button {
      width: 100%;
      background-color: gold;
      border: none;
      color: #000;
      padding: 10px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 1rem;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <h2>Admin Login</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="text" name="email" placeholder="Enter email" required>
      <input type="password" name="password" placeholder="Enter password" required>
      <button type="submit">Login</button>
    </form>
  
  <a href="createaccount.php" style="color: blue; text-decoration: underline;">Don't have an account? Create one</a>
</div>

</body>
</html>

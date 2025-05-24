<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials";
        }
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Login</title>
  <style>
    body {
      background: #f4f4f4;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      font-family: Arial, sans-serif;
    }
    .login-form {
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
    }
    .login-form h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #333;
    }
    .login-form input {
      width: 100%;
      padding: 12px;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .login-form button {
      background-color: #007bff;
      border: none;
      color: #fff;
      padding: 12px;
      width: 100%;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

  <form method="POST" class="login-form">
    <h2>Admin Login</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <input type="text" name="email" placeholder="Enter Email" required>
    <input type="password" name="password" placeholder="Enter Password" required>
    <button type="submit">Login</button>
  </form>

</body>
</html>

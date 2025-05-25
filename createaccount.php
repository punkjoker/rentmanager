<?php
require 'includes/db.php';

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $role = "admin";

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $mysqli->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Insert new admin
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO admins (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $role);
            if ($stmt->execute()) {
                $success = "Account created successfully! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin Account</title>
    <style>
        body {
            background: url('images/tenant1.jpg') no-repeat center center/cover;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.97);
            padding: 2rem;
            border-radius: 10px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        h2 {
            text-align: center;
            color: gold;
            margin-bottom: 1rem;
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
        .error, .success {
            text-align: center;
            margin-bottom: 1rem;
            font-weight: bold;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Create Admin Account</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Create Account</button>
    </form>
</div>

</body>
</html>

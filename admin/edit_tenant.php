<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch tenant details
    $stmt = $mysqli->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $tenant = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $stmt = $mysqli->prepare("UPDATE tenants SET full_name=?, national_id=?, phone_number=?, email=?, house_number=?, deposit=? WHERE tenant_id=?");
   $stmt->bind_param(
    "ssssddi",
    $_POST['full_name'],
    $_POST['national_id'],
    $_POST['phone_number'],
    $_POST['email'],
    $_POST['house_number'],
    $_POST['deposit'],
    $_POST['id']
);

    $stmt->execute();
    $stmt->close();

    header("Location: tenants.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Tenant</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/tenant2.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            color: #fff;
            backdrop-filter: brightness(0.3);
        }

        h2 {
            color: #d4af37;
            text-align: center;
        }

        .edit-container {
            background: #1f1f1f;
            width: 50%;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #d4af37;
        }

        form input[type=text], form input[type=email] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #555;
            background-color: #ffffff;
            color: #000;
        }

        form input[type=submit] {
            background-color: #d4af37;
            border: none;
            padding: 10px;
            width: 100%;
            color: #000;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #d4af37;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="edit-container">
        <h2>Edit Tenant</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['tenant_id']) ?>">
            <input type="text" name="full_name" value="<?= htmlspecialchars($tenant['full_name']) ?>" required>
            <input type="text" name="national_id" value="<?= htmlspecialchars($tenant['national_id']) ?>" required>
            <input type="text" name="phone_number" value="<?= htmlspecialchars($tenant['phone_number']) ?>" required>
            <input type="email" name="email" value="<?= htmlspecialchars($tenant['email']) ?>">
            <input type="text" name="house_number" value="<?= htmlspecialchars($tenant['house_number']) ?>" required>
            <input type="number" step="50" name="deposit" value="<?= htmlspecialchars($tenant['deposit'] ?? 0) ?>" placeholder="Deposit Amount" required>
            <input type="submit" name="update" value="Update Tenant">
        </form>
        <a href="add_tenant.php" class="back-btn">← Back to Tenant List</a>
    </div>
</body>
</html>

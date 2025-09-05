<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $mysqli->prepare("INSERT INTO tenants (full_name, national_id, phone_number, email, house_number, deposit) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssd", 
        $_POST['full_name'], 
        $_POST['national_id'], 
        $_POST['phone_number'], 
        $_POST['email'], 
        $_POST['house_number'], 
        $_POST['deposit']
    );
    $stmt->execute();
    echo "<div class='success'>Tenant added successfully.</div>";
}


// Fetch existing tenants
$result = $mysqli->query("SELECT * FROM tenants WHERE status='active' ORDER BY house_number ASC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add & View Tenants</title>
    <style>
    body {
    font-family: Arial, sans-serif;
    background-image: url('images/tenant2.jpg'); /* Updated image path */
    background-size: cover;
    background-repeat: no-repeat;
    color: #fff;
    backdrop-filter: brightness(0.3);
}


    h2 {
        color: #d4af37;
        text-align: center;
    }

    .container {
        display: flex;
        gap: 20px;
        margin-top: 30px;
        margin-left: 220px; /* Add this to prevent overlap with left navbar */
    }

    .form-section, .list-section {
        background: #1f1f1f;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 8px #d4af37;
        flex: 1;
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

    .success {
        color: #d4af37;
        text-align: center;
        margin-top: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        border: 1px solid #444;
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #3b3b3b;
        color: #d4af37;
    }

    .edit-btn {
        background-color: #d4af37;
        color: #000;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 5px;
        text-decoration: none;
    }
</style>

</head>
<body>
<?php include 'navbar.php'; ?>
    <h2>Tenant Management</h2>
    <div class="container">
        <div class="form-section">
            <h3>Add New Tenant</h3>
            <form method="post">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="text" name="national_id" placeholder="National ID" required>
                <input type="text" name="phone_number" placeholder="Phone Number" required>
                <input type="email" name="email" placeholder="Email">
                <input type="text" name="house_number" placeholder="House Number" required>
                <input type="text" name="deposit" placeholder="Deposit Amount" required>
                <input type="submit" value="Add Tenant">
            </form>
        </div>

        <div class="list-section">
            <h3>Tenants List</h3>
            <table>
                <tr>
                    <th>Full Name</th>
                    <th>House No</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Deposit</th>
                    <th>Edit</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['house_number']) ?></td>
                    <td><?= htmlspecialchars($row['phone_number']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= number_format((float)$row['deposit'], 2) ?></td>
                    <td>
  <a href="edit_tenant.php?id=<?= htmlspecialchars($row['tenant_id']) ?>" class="edit-btn">Edit</a>
</td>

                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>

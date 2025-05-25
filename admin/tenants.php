<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

$result = $mysqli->query("SELECT * FROM tenants ORDER BY house_number ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tenants List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/tenant2.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            color: #fff;
            backdrop-filter: brightness(0.3);
        }

        .container {
        display: flex;
        gap: 20px;
        margin-top: 30px;
        margin-left: 220px; /* Add this to prevent overlap with left navbar */
    }

        .dashboard {
            background: rgba(0, 0, 0, 0.75);
            padding: 25px;
            border-radius: 15px;
            max-width: 1000px;
            margin: auto;
        }

        h2 {
            color: gold;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #1f1f1f;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border: 1px solid #444;
            text-align: left;
        }

        th {
            background-color: #333;
            color: gold;
        }

        tr:nth-child(even) {
            background-color: #2b2b2b;
        }

        .edit-btn {
            background-color: gold;
            color: #000;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: gold;
            text-decoration: underline;
        }

    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container">
        <div class="dashboard">
            <h2>All Tenants</h2>
            <table>
                <tr>
                    <th>Full Name</th>
                    <th>National ID</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>House No</th>
                    <th>Edit</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['national_id']) ?></td>
                    <td><?= htmlspecialchars($row['phone_number']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['house_number']) ?></td>
                    <td>
                        <a href="edit_tenant.php?id=<?= htmlspecialchars($row['tenant_id']) ?>" class="edit-btn">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

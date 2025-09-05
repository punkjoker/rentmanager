<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "rentmanager");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch all vacated tenants
$result = $mysqli->query("SELECT * FROM vacated_tenants ORDER BY vacated_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vacated Tenants</title>
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
            padding: 30px;
            margin-left: 220px;
        }

        h2 {
            color: gold;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .btn-history {
            background-color: gold;
            color: black;
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
    <h2>Vacated Tenants</h2>
    <table>
        <tr>
            <th>Full Name</th>
            <th>National ID</th>
            <th>Phone</th>
            <th>Email</th>
            <th>House No</th>
            <th>Deposit</th>
            <th>Vacated Date</th>
            <th>History</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['national_id']) ?></td>
            <td><?= htmlspecialchars($row['phone_number']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['house_number']) ?></td>
            <td><?= number_format($row['deposit'], 2) ?></td>
            <td><?= date("d M Y", strtotime($row['vacated_date'])) ?></td>
            <td>
                <a href="client_history.php?tenant_id=<?= $row['tenant_id'] ?>" class="btn-history">
                    View History
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</div>
</body>
</html>

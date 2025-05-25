<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
// Use direct DB connection like in login.php
$dbuser = "root";
$dbpass = "";
$host = "localhost";
$db = "rentmanager";
$mysqli = new mysqli($host, $dbuser, $dbpass, $db);

// Check for DB connection error
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Sample queries using $mysqli
$totalTenants = $mysqli->query("SELECT COUNT(*) AS total FROM tenants")->fetch_assoc()['total'];
$rentCollected = $mysqli->query("SELECT SUM(amount_paid) AS total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE())")->fetch_assoc()['total'];
$pendingPayments = $mysqli->query("SELECT COUNT(*) AS total FROM balances WHERE status != 'paid'")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/tenants1.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            color: #fff;
            backdrop-filter: brightness(0.3);
        }
        .container {
            padding: 30px;
        }
        .dashboard {
            background: rgba(0, 0, 0, 0.7);
            padding: 25px;
            border-radius: 15px;
            max-width: 1000px;
            margin-left: 220px;
        }
        h1 {
            color: gold;
            margin-bottom: 20px;
            text-align: center;
        }
        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .card {
            flex: 1 1 30%;
            background: #222;
            margin: 10px;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid gold;
        }
        .card h3 {
            color: gold;
            margin-bottom: 10px;
        }
        .quick-links {
    position: absolute;
    top: 0;
    left: 0;
    height: 100vh;
    width: 200px;
    background: rgba(0, 0, 0, 0.8);
    padding-top: 100px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding-left: 20px;
}

.quick-links a {
    margin: 10px 0;
    padding: 10px 20px;
    background: gold;
    color: #000;
    text-decoration: none;
    border-radius: 5px;
    width: 160px;
    font-weight: bold;
}

        @media(max-width: 768px) {
            .stats { flex-direction: column; }
            .card { flex: 1 1 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <h1>Welcome, <?php echo $_SESSION['admin_name']; ?></h1>
            <div class="stats">
                <div class="card">
                    <h3>Total Tenants</h3>
                    <p><?php echo $totalTenants; ?></p>
                </div>
                <div class="card">
                    <h3>Rent Collected (This Month)</h3>
                    <p>KES <?php echo number_format($rentCollected ?? 0); ?></p>
                </div>
                <div class="card">
                    <h3>Pending Payments</h3>
                    <p><?php echo $pendingPayments; ?></p>
                </div>
            </div>

            <div class="quick-links">
                <a href="add_tenant.php">Add Tenant</a>
                <a href="monthly_report.php">Monthly Report</a>
                <a href="add_charges.php">Add Rent & Bills</a>
                <a href="view_payments.php">Latest Payments</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>

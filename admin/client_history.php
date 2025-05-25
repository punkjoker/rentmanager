<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

$tenant_id = $_GET['tenant_id'] ?? 0;

// Fetch tenant details
$tenant = $mysqli->query("SELECT full_name FROM tenants WHERE tenant_id = $tenant_id")->fetch_assoc();

// Fetch rent charges
$rentCharges = $mysqli->query("
    SELECT month_year, total_due 
    FROM rent_charges 
    WHERE tenant_id = $tenant_id 
    ORDER BY month_year DESC
");

// Fetch payment history
$payments = $mysqli->query("
    SELECT amount_paid, payment_date 
    FROM payments 
    WHERE tenant_id = $tenant_id 
    ORDER BY payment_date DESC
");

// Get total due and total paid
$summary = $mysqli->query("
    SELECT 
        (SELECT SUM(total_due) FROM rent_charges WHERE tenant_id = $tenant_id) AS total_due,
        (SELECT SUM(amount_paid) FROM payments WHERE tenant_id = $tenant_id) AS total_paid
")->fetch_assoc();

$total_due = $summary['total_due'] ?? 0;
$total_paid = $summary['total_paid'] ?? 0;
$balance = $total_due - $total_paid;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $tenant['full_name'] ?> - History</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 20px;
            color: #fff;
        }
        .container {
            max-width: 1000px;
            margin-left: 220px;
            background: #222;
            padding: 20px;
            border-radius: 10px;
        }
        h2 {
            color: gold;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #333;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #555;
            text-align: center;
        }
        th {
            background: gold;
            color: #000;
        }
        .summary {
            text-align: right;
            font-weight: bold;
            color: gold;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            padding: 8px 12px;
            background: gold;
            color: black;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
    <a class="back-link" href="monthly_report.php">&larr; Back to Report</a>
    <h2><?= $tenant['full_name'] ?> - Rent History</h2>

    <div class="summary">
        Total Due: <?= number_format($total_due, 2) ?> |
        Total Paid: <?= number_format($total_paid, 2) ?> |
        Balance: <?= number_format($balance, 2) ?>
    </div>

    <h3 style="color:gold;">Rent Charges</h3>
    <table>
        <tr>
            <th>Month</th>
            <th>Amount Due</th>
        </tr>
        <?php while ($row = $rentCharges->fetch_assoc()) { ?>
        <tr>
            <td><?= date("F Y", strtotime($row['month_year'])) ?></td>
            <td><?= number_format($row['total_due'], 2) ?></td>
        </tr>
        <?php } ?>
    </table>

    <h3 style="color:gold;">Payment History</h3>
    <table>
        <tr>
            <th>Date Paid</th>
            <th>Amount Paid</th>
        </tr>
        <?php while ($row = $payments->fetch_assoc()) { ?>
        <tr>
            <td><?= date("d M Y", strtotime($row['payment_date'])) ?></td>
            <td><?= number_format($row['amount_paid'], 2) ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>

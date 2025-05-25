<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

$result = $mysqli->query("
    SELECT p.payment_date, t.full_name, p.amount_paid, p.payment_method, p.transaction_code 
    FROM payments p 
    JOIN tenants t ON p.tenant_id = t.tenant_id 
    ORDER BY p.payment_date DESC 
    LIMIT 20
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Latest Payments</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-image: url('images/tenant2.jpg'); /* Updated image path */
    background-size: cover;
    background-repeat: no-repeat;
    color: #fff;
    backdrop-filter: brightness(0.3);
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    margin: 20px auto; /* Center horizontally */
    color: #000;
    border-radius: 8px;
    overflow: hidden;
}
.container {
    max-width: 1000px;
    margin-left: 220px;;
    padding: 20px;
    background: rgba(0, 0, 0, 0.7);
    border-radius: 10px;
}

        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background: #17a2b8; color: white; }
        h2 { text-align: center; }
    
    </style>
</head>
<body>
<div class="container">
<?php include 'navbar.php'; ?>

    <h2>Latest Payments</h2>
    <table>
        <tr><th>Date</th><th>Tenant</th><th>Amount</th><th>Method</th><th>Transaction</th></tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['payment_date'] ?></td>
            <td><?= $row['full_name'] ?></td>
            <td><?= number_format($row['amount_paid'], 2) ?></td>
            <td><?= ucfirst($row['payment_method']) ?></td>
            <td><?= $row['transaction_code'] ?></td>
        </tr>
        <?php } ?>
    </table>
    </div>
</body>
</html>

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
        body { font-family: Arial; padding: 20px; background: #f4f6f9; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background: #17a2b8; color: white; }
        h2 { text-align: center; }
    </style>
</head>
<body>
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
</body>
</html>

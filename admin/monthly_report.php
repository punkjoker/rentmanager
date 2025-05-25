<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");
$month_year = date('Y-m');

$result = $mysqli->query("
    SELECT t.full_name, r.total_due, p.amount_paid, b.balance, b.status 
    FROM tenants t
    LEFT JOIN rent_charges r ON t.tenant_id = r.tenant_id AND r.month_year = '$month_year'
    LEFT JOIN (
        SELECT tenant_id, SUM(amount_paid) AS amount_paid 
        FROM payments 
        WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month_year' 
        GROUP BY tenant_id
    ) p ON t.tenant_id = p.tenant_id
    LEFT JOIN balances b ON t.tenant_id = b.tenant_id AND b.month_year = '$month_year'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monthly Report</title>
    <style>
        body { font-family: Arial; background: #eef2f7; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        th { background: #007bff; color: white; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Monthly Report - <?php echo $month_year; ?></h2>
    <table>
        <tr><th>Tenant</th><th>Total Due</th><th>Paid</th><th>Balance</th><th>Status</th></tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['full_name'] ?></td>
            <td><?= number_format($row['total_due'], 2) ?></td>
            <td><?= number_format($row['amount_paid'], 2) ?></td>
            <td><?= number_format($row['balance'], 2) ?></td>
            <td><?= ucfirst($row['status']) ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>

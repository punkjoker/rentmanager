<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

// Default filter
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-t');
$filter = $_GET['filter'] ?? 'due';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Main Query
$query = "
    SELECT t.tenant_id, t.full_name, 
           COALESCE(SUM(r.total_due), 0) as total_due,
           COALESCE(SUM(p.amount_paid), 0) as amount_paid,
           COALESCE(SUM(r.total_due - p.amount_paid), 0) as balance
    FROM tenants t
    LEFT JOIN rent_charges r ON t.tenant_id = r.tenant_id AND r.month_year BETWEEN '$from' AND '$to'
    LEFT JOIN (
        SELECT tenant_id, SUM(amount_paid) AS amount_paid, payment_date
        FROM payments
        WHERE payment_date BETWEEN '$from' AND '$to'
        GROUP BY tenant_id
    ) p ON t.tenant_id = p.tenant_id
    GROUP BY t.tenant_id, t.full_name
    LIMIT $limit OFFSET $offset
";

$result = $mysqli->query($query);

// For total calculations
$totalsQuery = "
    SELECT 
        SUM(r.total_due) AS total_due, 
        SUM(p.amount_paid) AS amount_paid 
    FROM tenants t
    LEFT JOIN rent_charges r ON t.tenant_id = r.tenant_id AND r.month_year BETWEEN '$from' AND '$to'
    LEFT JOIN (
        SELECT tenant_id, SUM(amount_paid) AS amount_paid 
        FROM payments 
        WHERE payment_date BETWEEN '$from' AND '$to' 
        GROUP BY tenant_id
    ) p ON t.tenant_id = p.tenant_id
";
$totals = $mysqli->query($totalsQuery)->fetch_assoc();
$total_due = $totals['total_due'] ?? 0;
$total_paid = $totals['amount_paid'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Monthly Report</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-image: url('images/tenant2.jpg'); /* Updated image path */
    background-size: cover;
    background-repeat: no-repeat;
    color: #fff;
    backdrop-filter: brightness(0.3);
}

        .container {
            max-width: 1200px;
            margin-left: 220px;
            background: #222;
            padding: 20px;
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            color: gold;
        }
        form {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        form input, form select {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #333;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #333;
        }
        th, td {
            padding: 12px;
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
            margin-top: 20px;
            color: gold;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            color: gold;
            margin: 0 5px;
            text-decoration: none;
        }
        .btn-history {
            background: gold;
            color: black;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        button.btn-history {
    margin-top: 5px;
    background: gold;
    color: black;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
}
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
<form method="post" action="export_excel.php" style="text-align: right; margin-bottom: 15px;">
    <input type="hidden" name="from" value="<?= $from ?>">
    <input type="hidden" name="to" value="<?= $to ?>">
    <input type="hidden" name="filter" value="<?= $filter ?>">
    <button type="submit" class="btn-history">Download report</button>
</form>
    <h2>Monthly Report</h2>
    <form method="get">
        <input type="date" name="from" value="<?= $from ?>" required>
        <input type="date" name="to" value="<?= $to ?>" required>
        <select name="filter">
            <option value="due" <?= $filter == 'due' ? 'selected' : '' ?>>Total Due</option>
            <option value="paid" <?= $filter == 'paid' ? 'selected' : '' ?>>Total Paid</option>
        </select>
        <input type="submit" value="Filter">
    </form>

    <table>
        <tr>
            <th>Tenant</th>
            <th>Total Due</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['full_name'] ?></td>
            <td><?= number_format($row['total_due'], 2) ?></td>
            <td><?= number_format($row['amount_paid'], 2) ?></td>
            <td><?= number_format($row['balance'], 2) ?></td>
            <td><a href="client_history.php?tenant_id=<?= $row['tenant_id'] ?>" class="btn-history">View History</a></td>
        </tr>
        <?php } ?>
    </table>

    <div class="summary">
        <?= $filter == 'due' ? "Total Due: " . number_format($total_due, 2) : "Total Paid: " . number_format($total_paid, 2) ?>
    </div>

    <div class="pagination">
        <a href="?from=<?= $from ?>&to=<?= $to ?>&filter=<?= $filter ?>&page=<?= max(1, $page - 1) ?>">&laquo; Prev</a>
        <a href="?from=<?= $from ?>&to=<?= $to ?>&filter=<?= $filter ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
    </div>
</div>
</body>
</html>

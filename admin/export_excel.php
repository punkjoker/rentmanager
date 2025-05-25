<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

$from = $_POST['from'] ?? date('Y-m-01');
$to = $_POST['to'] ?? date('Y-m-t');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=monthly_report.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Tenant', 'Total Due', 'Amount Paid', 'Balance']);

$query = "
    SELECT t.full_name,
           COALESCE(SUM(r.total_due), 0) as total_due,
           COALESCE(SUM(p.amount_paid), 0) as amount_paid,
           COALESCE(SUM(r.total_due - p.amount_paid), 0) as balance
    FROM tenants t
    LEFT JOIN rent_charges r ON t.tenant_id = r.tenant_id AND r.month_year BETWEEN '$from' AND '$to'
    LEFT JOIN (
        SELECT tenant_id, SUM(amount_paid) AS amount_paid
        FROM payments
        WHERE payment_date BETWEEN '$from' AND '$to'
        GROUP BY tenant_id
    ) p ON t.tenant_id = p.tenant_id
    GROUP BY t.tenant_id, t.full_name
";

$result = $mysqli->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['full_name'],
        number_format($row['total_due'], 2),
        number_format($row['amount_paid'], 2),
        number_format($row['balance'], 2)
    ]);
}
fclose($output);
?>

<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");
if ($mysqli->connect_error) {
    die("DB connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// Inputs & defaults
$from   = $_GET['from']  ?? date('Y-m-01');
$to     = $_GET['to']    ?? date('Y-m-t');
$filter = $_GET['filter'] ?? 'due';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

// Fetch distinct house numbers for filter dropdown
$houses = $mysqli->query("SELECT DISTINCT house_number FROM tenants WHERE status='active' ORDER BY CAST(SUBSTRING(house_number,4) AS UNSIGNED)");
$selected_house = $_GET['house_number'] ?? ''; 

// Fetch tenants for the page
$whereHouse = '';
if ($selected_house) {
    $houseEscaped = $mysqli->real_escape_string($selected_house);
    $whereHouse = " AND house_number = '$houseEscaped'";
}

// --- Search by name ---
$search_name = $_GET['search_name'] ?? '';
$whereName = '';
if ($search_name) {
    $nameEscaped = $mysqli->real_escape_string($search_name);
    $whereName = " AND full_name LIKE '%$nameEscaped%'";
}

$isDownload = isset($_GET['download_pdf']);
if ($isDownload) {
    $limitClause = "";
} else {
    $limitClause = "LIMIT $limit OFFSET $offset";
}

$tenants = $mysqli->query("
    SELECT tenant_id, full_name, house_number 
    FROM tenants 
    WHERE status='active' $whereHouse $whereName 
    ORDER BY CAST(SUBSTRING(house_number,4) AS UNSIGNED) ASC 
    $limitClause
");


// Prepare array for per-tenant data
$tenantData = [];

while ($tenant = $tenants->fetch_assoc()) {
    $tenant_id = $tenant['tenant_id'];

    // Fetch rent charges for this tenant
    $month = date('Y-m', strtotime($from)); // current month
$rentCharges = $mysqli->query("
    SELECT month_year, total_due 
    FROM rent_charges 
    WHERE tenant_id = $tenant_id AND month_year = '$month'
");


    // Fetch payment history for this tenant
   $payments = $mysqli->query("
    SELECT amount_paid, payment_date 
    FROM payments 
    WHERE tenant_id = $tenant_id AND DATE_FORMAT(payment_date, '%Y-%m') = '$month'
");

    // Get total due and total paid
   // Get total due and total paid
$summary = $mysqli->query("
    SELECT 
        (SELECT SUM(total_due) FROM rent_charges WHERE tenant_id = $tenant_id AND month_year = '$month') AS total_due,
        (SELECT SUM(amount_paid) FROM payments WHERE tenant_id = $tenant_id AND DATE_FORMAT(payment_date, '%Y-%m') = '$month') AS total_paid
")->fetch_assoc();


$total_due = $summary['total_due'] ?? 0;
$total_paid = $summary['total_paid'] ?? 0;
$balance = $total_due - $total_paid;

    $tenantData[] = [
        'tenant_id'   => $tenant_id,
        'full_name'   => $tenant['full_name'],
        'house_number'    => $tenant['house_number'],
        'total_due'   => $total_due,
        'total_paid'  => $total_paid,
        'balance'     => $balance,
        'rentCharges' => $rentCharges,
        'payments'    => $payments
    ];
}
if (isset($_GET['download_pdf'])) {
    require('fpdf.php');

    $pdf = new FPDF();
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial','B',16);
    $pdf->SetTextColor(0,0,128); // Dark blue title
    $pdf->Cell(0,10,'Monthly Rent Report ('.date('M Y', strtotime($from)).')',0,1,'C');
    $pdf->Ln(5);

    // Apartment name and Date Printed
$apartmentName = "NELLY PLAZA, MLOLONGO"; // <-- change to your real apartment name
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,8,"Apartment: $apartmentName",0,1,'L');
$pdf->Cell(0,8,"Date Printed: ".date('d M Y H:i'),0,1,'L');
$pdf->Ln(5);


    // Table Header
    $pdf->SetFont('Arial','B',12);
    $pdf->SetFillColor(255,215,0); // Gold background
    $pdf->SetTextColor(0,0,0);      // Black text
    $pdf->Cell(10,10,'#',1,0,'C',true);
    $pdf->Cell(50,10,'Tenant Name',1,0,'C',true);
    $pdf->Cell(30,10,'House No',1,0,'C',true);
    $pdf->Cell(40,10,'Amount Due',1,0,'C',true);
    $pdf->Cell(40,10,'Paid',1,0,'C',true);
    $pdf->Cell(30,10,'Balance',1,1,'C',true); // smaller to fit

    // Set font and alternating row color
    $pdf->SetFont('Arial','',12);
    $fill = false; // for alternating row color

  $totalDueSum = 0;
$totalPaidSum = 0;
$totalBalanceSum = 0;

$counter = 1;
foreach ($tenantData as $tenant) {
    $total_due   = $tenant['total_due'];
    $total_paid  = $tenant['total_paid'];
    $balance     = $tenant['balance'];

    $totalDueSum += $total_due;
    $totalPaidSum += $total_paid;
    $totalBalanceSum += $balance;

    // Existing row drawing…
    $pdf->Cell(10,10,$counter++,1,0,'C',$fill);
    $pdf->Cell(50,10,$tenant['full_name'],1,0,'L',$fill);
    $pdf->Cell(30,10,$tenant['house_number'],1,0,'C',$fill);
    $pdf->Cell(40,10,number_format($total_due,2),1,0,'R',$fill);
    $pdf->Cell(40,10,number_format($total_paid,2),1,0,'R',$fill);
    $pdf->Cell(30,10,number_format($balance,2),1,1,'R',$fill);

    $fill = !$fill;
}

// After the loop, draw totals row:
$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,10,'TOTALS',1,0,'R',true);
$pdf->Cell(40,10,number_format($totalDueSum,2),1,0,'R',true);
$pdf->Cell(40,10,number_format($totalPaidSum,2),1,0,'R',true);
$pdf->Cell(30,10,number_format($totalBalanceSum,2),1,1,'R',true);



    $pdf->Output('D', 'monthly_report_'.date('Y_m', strtotime($from)).'.pdf');
    exit();
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>Monthly Report</title>
    <style>
        body { font-family: Arial, sans-serif; background-image: url('images/tenant2.jpg'); background-size: cover; background-repeat: no-repeat; color: #fff; backdrop-filter: brightness(0.3); }
        .container { max-width: 1200px; margin-left: 220px; background: #222; padding: 20px; border-radius: 10px; }
        h2 { text-align: center; color: gold; }
        form { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        form input, form select { padding: 10px; border: none; border-radius: 5px; background: #333; color: white; }
        table { width: 100%; border-collapse: collapse; background: #333; }
        th, td { padding: 12px; border: 1px solid #555; text-align: center; }
        th { background: gold; color: #000; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a { color: gold; margin: 0 5px; text-decoration: none; }
        .btn-history { background: gold; color: black; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">

<h2>Monthly Report</h2>
<form method="get" style="text-align:center; margin-bottom:10px;">
    <input type="text" name="search_name" placeholder="Search tenant by name"
           value="<?= htmlspecialchars($search_name) ?>"
           style="padding:8px; width:250px; border-radius:5px; border:1px solid #ccc;">
    <button type="submit" style="padding:8px 15px; background:gold; border:none; border-radius:5px; cursor:pointer;">
        Search
    </button>
    <!-- preserve other filters when searching -->
    <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
    <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">
    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
</form>

<form method="get" style="text-align:center; margin-bottom:15px;">
    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" required>
    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" required>
    <select name="filter">
        <option value="due"  <?= $filter === 'due'  ? 'selected' : '' ?>>Total Due</option>
        <option value="paid" <?= $filter === 'paid' ? 'selected' : '' ?>>Total Paid</option>
    </select>
    <input type="submit" value="Filter">
    <!-- Download PDF button inside the form -->
    <button type="submit" name="download_pdf" class="btn-history">Download PDF</button>
</form>
<table>
    <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>House No</th>
        <th>Total Due</th>
        <th>Paid</th>
        <th>Balance</th>
        <th>Actions</th>
    </tr>
   <?php 
$counter = ($page - 1) * $limit + 1; // ✅ continues numbering across pages
foreach ($tenantData as $row) { ?>
<tr>
    <td><?= $counter++; ?></td> <!-- ✅ display row number -->
    <td><?= htmlspecialchars($row['full_name']) ?></td>
    <td><?= htmlspecialchars($row['house_number']) ?></td>

    <!-- Total Due column: list monthly charges without dates -->
    <td>
        <?php
        if ($row['rentCharges']) {
            while ($charge = $row['rentCharges']->fetch_assoc()) {
                echo number_format((float)$charge['total_due'], 2) . "<br>";
            }
        }
        ?>
    </td>

    <!-- Paid column: list payments without dates -->
    <td>
        <?php
        if ($row['payments']) {
            while ($payment = $row['payments']->fetch_assoc()) {
                echo number_format((float)$payment['amount_paid'], 2) . "<br>";
            }
        }
        ?>
    </td>

    <!-- Balance -->
    <td><?= number_format((float)$row['balance'], 2) ?></td>

    <!-- Actions -->
    <td><a href="client_history.php?tenant_id=<?= (int)$row['tenant_id'] ?>" class="btn-history">View History</a></td>
</tr>
<?php } ?>


<div class="pagination">
    <a href="?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&filter=<?= urlencode($filter) ?>&page=<?= max(1, $page - 1) ?>">&laquo; Prev</a>
    <a href="?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&filter=<?= urlencode($filter) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
</div>

</div>
</body>
</html>

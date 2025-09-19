<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

$tenant_id = $_GET['tenant_id'] ?? 0;

// Fetch tenant details
$tenant = $mysqli->query("
    SELECT full_name, house_number 
    FROM tenants 
    WHERE tenant_id = $tenant_id
")->fetch_assoc();


// Fetch rent charges
$rentCharges = $mysqli->query("
    SELECT month_year, total_due, base_rent, water_bill, electricity_bill, garbage_bill
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

if (isset($_POST['download_history'])) {
    require('fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();

// Colors for title
$pdf->SetTextColor(0, 51, 102); // dark blue
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'NELLY PLAZA MLOLONGO',0,1,'C');

$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0); // black
$pdf->Cell(0,10,'Date Printed: '.date('d M Y H:i'),0,1,'C');
$pdf->Ln(5);

// Tenant header
$pdf->SetFont('Arial','B',16);
$pdf->SetFillColor(255, 215, 0); // gold fill
$pdf->SetTextColor(0);           // black text
$pdf->Cell(0,10,$tenant['full_name'].' - House '.$tenant['house_number'].' - Rent History',0,1,'C',true);
$pdf->Ln(5);

// After tenant header
$pdf->Ln(3);
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0); // black text

$pdf->Cell(0,10,'Total Due: KES '.number_format($total_due,2).' | Total Paid: KES '.number_format($total_paid,2).' | Balance: KES '.number_format($balance,2),0,1,'C');
$pdf->Ln(5);

/* ========== RENT CHARGES TABLE ========== */
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(0,51,102);
$pdf->Cell(0,10,'Rent Charges',0,1);

$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(255, 215, 0); // gold header
$pdf->SetTextColor(0);           // black text
$pdf->Cell(60,10,'Month',1,0,'C',true);
$pdf->Cell(60,10,'Amount Due',1,0,'C',true);
$pdf->Cell(60,10,'Breakdown',1,1,'C',true);

// Data rows
$pdf->SetFont('Arial','',11);
$fill = false;
while ($row = $rentCharges->fetch_assoc()) {
    // Alternate row color
    $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
    $fill = !$fill;

    $pdf->Cell(60,10,date("F Y", strtotime($row['month_year'])),1,0,'C',true);
    $pdf->Cell(60,10,number_format($row['total_due'],2),1,0,'C',true);

    // breakdown as string
    $breakdown = 
        "Base: ".number_format($row['base_rent'],2)."\n".
        "Water: ".number_format($row['water_bill'],2)."\n".
        "Other Charges: ".number_format($row['electricity_bill'],2)."\n".
        "Garbage: ".number_format($row['garbage_bill'],2);

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(60,5,$breakdown,1,'L',$fill);
    $pdf->SetXY($x + 60, $y);
    $pdf->Ln(max(10, $pdf->GetY() - $y));
}

/* ========== PAYMENTS TABLE ========== */
$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(0,51,102);
$pdf->Cell(0,10,'Payment History',0,1);

$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(255, 215, 0);
$pdf->SetTextColor(0);
$pdf->Cell(60,10,'Date Paid',1,0,'C',true);
$pdf->Cell(60,10,'Amount Paid',1,1,'C',true);

// Data
$pdf->SetFont('Arial','',11);
$fill = false;
while ($row = $payments->fetch_assoc()) {
    $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
    $fill = !$fill;

    $pdf->Cell(60,10,date("d M Y", strtotime($row['payment_date'])),1,0,'C',true);
    $pdf->Cell(60,10,number_format($row['amount_paid'],2),1,1,'C',true);
}

$pdf->Output('D', 'tenant_history_'.$tenant_id.'.pdf');
exit();

}

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
<form method="post">
    <input type="submit" name="download_history" value="Download History" style="background: gold; color:black; padding: 10px 15px; border:none; border-radius:5px; cursor:pointer;">
</form>

    <h3 style="color:gold;">Rent Charges</h3>

    <table>
        <tr>
            <th>Month</th>
            <th>Amount Due</th>
        <th>Breakdown</th>
    </tr>
    <?php while ($row = $rentCharges->fetch_assoc()) { ?>
    <tr>
        <td><?= date("F Y", strtotime($row['month_year'])) ?></td>
        <td><?= number_format($row['total_due'], 2) ?></td>
        <td>
            Base Rent: <?= number_format($row['base_rent'], 2) ?><br>
            Water Bill: <?= number_format($row['water_bill'], 2) ?><br>
            Other charges: <?= number_format($row['electricity_bill'], 2) ?><br>
            Garbage Bill: <?= number_format($row['garbage_bill'], 2) ?>
        </td>
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

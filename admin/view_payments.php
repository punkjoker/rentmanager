<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$dbuser = "root";
$dbpass = "";
$host = "localhost";
$db = "rentmanager";
$mysqli = new mysqli($host, $dbuser, $dbpass, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle MPESA payment notification or manual payment form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Is this an MPESA notification (simulate fields like phone, amount, transaction code)
    if (isset($_POST['mpesa_phone'], $_POST['mpesa_amount'], $_POST['mpesa_transaction'])) {
        $phone = $mysqli->real_escape_string($_POST['mpesa_phone']);
        $amount = floatval($_POST['mpesa_amount']);
        $trans_code = $mysqli->real_escape_string($_POST['mpesa_transaction']);

        // Find tenant by phone
        $tenantResult = $mysqli->query("SELECT tenant_id FROM tenants WHERE phone_number = '$phone' AND status='active' LIMIT 1");
        if ($tenantResult->num_rows > 0) {
            $tenant = $tenantResult->fetch_assoc();
            $tenant_id = $tenant['tenant_id'];

            // Insert payment
            $stmt = $mysqli->prepare("INSERT INTO payments (tenant_id, amount_paid, payment_method, transaction_code) VALUES (?, ?, 'mpesa', ?)");
            $stmt->bind_param("ids", $tenant_id, $amount, $trans_code);
            $stmt->execute();

            // Update rent_charges for tenant if amount covers total_due
            // Here, update all unpaid charges with total_due <= amount_paid (you might adjust to your logic)
            $update = $mysqli->prepare("UPDATE rent_charges SET is_paid = 1 WHERE tenant_id = ? AND is_paid = 0 AND total_due <= ?");
            $update->bind_param("id", $tenant_id, $amount);
            $update->execute();

            $message = "MPESA payment recorded and rent charges updated for tenant ID: $tenant_id.";
        } else {
            $message = "Tenant with phone $phone not found or inactive.";
        }
    }
    // Manual payment form
    // Manual payment form
elseif (isset($_POST['manual_tenant'], $_POST['manual_amount'], $_POST['manual_method'])) {
    $tenant_id = intval($_POST['manual_tenant']);
    $amount = floatval($_POST['manual_amount']);
    $method = $mysqli->real_escape_string($_POST['manual_method']);
    $transaction_code = "MANUAL-" . time();
    $manual_date = $_POST['manual_date'] ?? date('Y-m-d');

    $stmt = $mysqli->prepare("
        INSERT INTO payments (tenant_id, amount_paid, payment_method, transaction_code, payment_date)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idsss", $tenant_id, $amount, $method, $transaction_code, $manual_date);
    $stmt->execute();

    // Update rent charges
    $update = $mysqli->prepare("UPDATE rent_charges SET is_paid = 1 WHERE tenant_id = ? AND is_paid = 0 AND total_due <= ?");
    $update->bind_param("id", $tenant_id, $amount);
    $update->execute();

    $message = "Manual payment recorded for tenant ID: $tenant_id.";
}


}

// Fetch all payments to display
$payments = $mysqli->query("
    SELECT p.payment_id, t.full_name, t.house_number, p.amount_paid, p.payment_method, p.transaction_code, p.payment_date
    FROM payments p
    JOIN tenants t ON p.tenant_id = t.tenant_id
    ORDER BY p.payment_date DESC
");

// Fetch tenants for dropdown in manual payment
// Example: building the WHERE and LIMIT clauses dynamically
$whereHouse = ""; // example: "AND house_number LIKE 'HSE-%'"
$limitClause = ""; // example: "LIMIT 0, 20"

// Build the query
$query = "
    SELECT tenant_id, full_name, house_number
    FROM tenants
    WHERE status='active' $whereHouse
    ORDER BY CAST(SUBSTRING(house_number, 4) AS UNSIGNED) ASC
    $limitClause
";

// Execute the query
$tenants = $mysqli->query($query);



?>

<!DOCTYPE html>
<html>
<head>
    <title>View Payments</title>
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
            max-width: 1000px;
            margin-left: 220px;
            background: #222;
            padding: 20px;
            border-radius: 10px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #555; padding: 10px; text-align: left; }
        th { background: gold; color: #000; }
        h1, h2 { color: gold; }
        .message { background: green; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        form { background: #333; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        label { display: block; margin: 10px 0 5px; }
        input, select { width: 100%; padding: 8px; border-radius: 5px; border: none; }
        button { background: gold; color: #000; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
    <h1>View Payments</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <h2>Manual Rent Payment</h2>
    <form method="POST">
        <label>Select Tenant:</label>
        <select name="manual_tenant" required>
            <option value="">-- Select Tenant --</option>
            <?php while ($tenant = $tenants->fetch_assoc()): ?>
                <option value="<?php echo $tenant['tenant_id']; ?>">
                    <?php echo htmlspecialchars($tenant['full_name'] . " - House " . $tenant['house_number']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <label>Amount Paid:</label>
        <input type="number" name="manual_amount" step="0.01" required>
        <label>Payment Method:</label>
        <select name="manual_method" required>
            <option value="cash">Cash</option>
            <option value="mpesa">MPESA</option>
            <option value="bank">Bank Transfer</option>
            <option value="other">Other</option>
        </select>
        <!-- New field: pick a date -->
<label>Payment Date:</label>
<input type="date" name="manual_date" required>

        <button type="submit">Add Manual Payment</button>
    </form>

    <h2>Payment History</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tenant</th>
                <th>House No</th>
                <th>Amount Paid</th>
                <th>Payment Method</th>
                <th>Transaction Code</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($payment = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $payment['payment_id']; ?></td>
                    <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($payment['house_number']); ?></td>
                    <td><?php echo number_format($payment['amount_paid'], 2); ?></td>
                    <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                    <td><?php echo htmlspecialchars($payment['transaction_code']); ?></td>
                    <td><?php echo $payment['payment_date']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

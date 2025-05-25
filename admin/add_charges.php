<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $total = $_POST['base_rent'] + $_POST['water'] + $_POST['electricity'] + $_POST['garbage'];
    $stmt = $mysqli->prepare("INSERT INTO rent_charges (tenant_id, month_year, base_rent, water_bill, electricity_bill, garbage_bill, total_due) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdddd", $_POST['tenant_id'], $_POST['month_year'], $_POST['base_rent'], $_POST['water'], $_POST['electricity'], $_POST['garbage'], $total);
    if ($stmt->execute()) {
        $success = true;
    }
    $stmt->close();
}

// Get tenants for dropdown
$tenants_result = $mysqli->query("SELECT tenant_id, full_name FROM tenants ORDER BY full_name");

// Get charges for current month
$current_month = date('Y-m');
$charges_sql = "SELECT rc.*, t.full_name, t.phone_number, t.email 
                FROM rent_charges rc 
                JOIN tenants t ON rc.tenant_id = t.tenant_id 
                WHERE rc.month_year = '$current_month'
                ORDER BY t.full_name";
$charges_result = $mysqli->query($charges_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Rent & Bills</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-image: url('images/tenant2.jpg'); /* Updated image path */
    background-size: cover;
    background-repeat: no-repeat;
    color: #fff;
    backdrop-filter: brightness(0.3);
}
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: gold;
        }
        form {
            max-width: 500px;
            margin: 0 auto 40px auto;
            background-color: #111;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px gold;
        }
        select, input[type=month], input[type=number] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background-color: #222;
            border: 1px solid gold;
            color: gold;
            border-radius: 5px;
        }
        input[type=submit] {
            background-color: gold;
            color: #111;
            border: none;
            padding: 12px;
            width: 100%;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        input[type=submit]:hover {
            background-color: #b38f00;
        }
        .success {
            text-align: center;
            color: #b8b800;
            margin-bottom: 20px;
            font-weight: bold;
        }
        table {
            width: 90%;
            margin: 0 auto 40px auto;
            border-collapse: collapse;
            background-color: #111;
            box-shadow: 0 0 10px gold;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid gold;
            padding: 10px;
            text-align: left;
            color: gold;
        }
        th {
            background-color: #333;
        }
        tbody tr:nth-child(even) {
            background-color: #222;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>Add Rent & Bills</h2>

<?php if ($success): ?>
    <div class="success">Charges added successfully.</div>
<?php endif; ?>

<form method="post" action="">
    <label for="tenant_id">Select Tenant</label>
    <select name="tenant_id" id="tenant_id" required>
        <option value="" disabled selected>Select Tenant</option>
        <?php while ($tenant = $tenants_result->fetch_assoc()): ?>
            <option value="<?php echo $tenant['tenant_id']; ?>">
                <?php echo htmlspecialchars($tenant['full_name']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="month_year">Month & Year</label>
    <input type="month" id="month_year" name="month_year" value="<?php echo date('Y-m'); ?>" required>

    <label for="base_rent">Base Rent</label>
    <input type="number" step="100" id="base_rent" name="base_rent" placeholder="Base Rent" required>

    <label for="water">Water Bill</label>
    <input type="number" step="10" id="water" name="water" placeholder="Water Bill" value="0">

    <label for="electricity">Electricity Bill</label>
    <input type="number" step="50" id="electricity" name="electricity" placeholder="Electricity Bill" value="0">

    <label for="garbage">Garbage Bill</label>
    <input type="number" step="50" id="garbage" name="garbage" placeholder="Garbage Bill" value="0">

    <input type="submit" value="Add Charges">
</form>

<h2>Current Month Charges (<?php echo date('F Y'); ?>)</h2>

<table>
    <thead>
        <tr>
            <th>Tenant Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Base Rent</th>
            <th>Water Bill</th>
            <th>Electricity Bill</th>
            <th>Garbage Bill</th>
            <th>Total Due</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($charges_result && $charges_result->num_rows > 0): ?>
            <?php while ($charge = $charges_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($charge['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($charge['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($charge['email']); ?></td>
                    <td><?php echo number_format($charge['base_rent'], 2); ?></td>
                    <td><?php echo number_format($charge['water_bill'], 2); ?></td>
                    <td><?php echo number_format($charge['electricity_bill'], 2); ?></td>
                    <td><?php echo number_format($charge['garbage_bill'], 2); ?></td>
                    <td><?php echo number_format($charge['total_due'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No charges for current month.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>

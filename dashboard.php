<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
// Use direct DB connection like in login.php
$dbuser = "root";
$dbpass = "";
$host = "localhost";
$db = "rentmanager";
$mysqli = new mysqli($host, $dbuser, $dbpass, $db);

// Check for DB connection error
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Total tenants
$totalTenants = $mysqli->query("
    SELECT COUNT(*) AS total 
    FROM tenants 
    WHERE status='active'
")->fetch_assoc()['total'] ?? 0;

// Total collected this month (payments table)
$totalCollected = $mysqli->query("
    SELECT SUM(amount_paid) AS total 
    FROM payments 
    WHERE MONTH(payment_date) = MONTH(CURDATE()) 
      AND YEAR(payment_date) = YEAR(CURDATE())
")->fetch_assoc()['total'] ?? 0;

// Total water bill (rent_charges table)
$totalWater = $mysqli->query("
    SELECT SUM(water_bill) AS total 
    FROM rent_charges 
    WHERE MONTH(month_year) = MONTH(CURDATE()) 
      AND YEAR(month_year) = YEAR(CURDATE())
")->fetch_assoc()['total'] ?? 0;

// Total due (rent_charges table)
$totalDue = $mysqli->query("
    SELECT SUM(total_due) AS total 
    FROM rent_charges 
    WHERE MONTH(month_year) = MONTH(CURDATE()) 
      AND YEAR(month_year) = YEAR(CURDATE())
")->fetch_assoc()['total'] ?? 0;

// Total balances = total_due - total_collected
$totalBalance = ($totalDue ?? 0) - ($totalCollected ?? 0);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
    font-family: Arial, sans-serif;
    background-image: url('images/tenant2.jpg'); /* Updated image path */
    background-size: cover;
    background-repeat: no-repeat;
    color: #fff;
    backdrop-filter: brightness(0.3);
}

        .container {
            padding: 30px;
        }
        .dashboard {
            background: rgba(0, 0, 0, 0.7);
            padding: 25px;
            border-radius: 15px;
            max-width: 1000px;
            margin-left: 220px;
        }
        h1 {
            color: gold;
            margin-bottom: 20px;
            text-align: center;
        }
        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .card {
            flex: 1 1 30%;
            background: #222;
            margin: 10px;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid gold;
        }
        .card h3 {
            color: gold;
            margin-bottom: 10px;
        }
        .quick-links {
    position: absolute;
    top: 0;
    left: 0;
    height: 100vh;
    width: 200px;
    background: rgba(0, 0, 0, 0.8);
    padding-top: 100px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding-left: 20px;
}

.quick-links a {
    margin: 10px 0;
    padding: 10px 20px;
    background: gold;
    color: #000;
    text-decoration: none;
    border-radius: 5px;
    width: 160px;
    font-weight: bold;
}

        @media(max-width: 768px) {
            .stats { flex-direction: column; }
            .card { flex: 1 1 100%; }
        }
    </style>
    <?php
// Get monthly income for the current year
$monthlyIncome = [];
for ($i = 1; $i <= 12; $i++) {
    $stmt = $mysqli->prepare("SELECT SUM(amount_paid) AS total FROM payments WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = YEAR(CURDATE())");
    $stmt->bind_param("i", $i);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $monthlyIncome[] = $result['total'] ?? 0;
}
?>

</head>
<body>
    <div class="container">
        <div class="dashboard">
            <h1>Welcome, <?php echo $_SESSION['admin_name']; ?></h1>
            <div class="stats">
    <div class="card">
        <h3>Total Tenants</h3>
        <p><?php echo number_format($totalTenants); ?></p>
    </div>

    <div class="card">
        <h3>Total Collected (This Month)</h3>
        <p>KES <?php echo number_format($totalCollected, 2); ?></p>
    </div>

    <div class="card">
        <h3>Total Water Bill (This Month)</h3>
        <p>KES <?php echo number_format($totalWater, 2); ?></p>
    </div>

    <div class="card">
        <h3>Total Balances (This Month)</h3>
        <p>KES <?php echo number_format($totalBalance, 2); ?></p>
    </div>
</div>

            <div style="margin-top: 40px;">
    <h2 style="color: gold; text-align:center;">Monthly Income Analytics</h2>
    <div style="text-align:center; margin-bottom: 20px;">
        <label for="monthSelect" style="color:#fff;">Generate Report for: </label>
        <select id="monthSelect" onchange="generateMonthlyReport()">
            <?php
            $months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            foreach ($months as $index => $month) {
                $selected = ($index + 1 == date('n')) ? 'selected' : '';
                echo "<option value='".($index + 1)."' $selected>$month</option>";
            }
            ?>
        </select>
    </div>
    <canvas id="incomeChart" height="100"></canvas>
</div>


            <div class="quick-links">
                <a href="add_tenant.php">Add Tenant</a>
                <a href="tenants.php">Tenants List</a>
                <a href="monthly_report.php">Monthly Report</a>
                <a href="add_charges.php">Add Rent & Bills</a>
                <a href="view_payments.php">Latest Payments</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
    <!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthlyData = <?php echo json_encode($monthlyIncome); ?>;
    const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const ctx = document.getElementById('incomeChart').getContext('2d');
    const incomeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'KES Collected',
                data: monthlyData,
                backgroundColor: 'gold',
                borderColor: '#444',
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: 'white' },
                    title: {
                        display: true,
                        text: 'KES',
                        color: 'white'
                    }
                },
                x: {
                    ticks: { color: 'white' }
                }
            }
        }
    });

    function generateMonthlyReport() {
        const selectedMonth = document.getElementById("monthSelect").value;
        window.location.href = `monthly_report.php?month=${selectedMonth}`;
    }
</script>

</body>
</html>
<!-- navbar.php -->
<style>
    .quick-links {
        position: fixed;
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
        z-index: 1000;
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
</style>

<div class="quick-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="add_tenant.php">Add Tenant</a>
    <a href="tenants.php">Tenants List</a>
    <a href="monthly_report.php">Monthly Report</a>
    <a href="add_charges.php">Add Rent & Bills</a>
    <a href="view_payments.php">Payments</a>
    <a href="vacated_list.php">Vacated Tenants</a>
    <a href="logout.php">Logout</a>
</div>

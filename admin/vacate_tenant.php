<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "rentmanager");

$tenant_id = $_GET['id'] ?? 0;
if (!$tenant_id) die("Invalid tenant ID.");

// Fetch tenant info
$tenant = $mysqli->query("SELECT * FROM tenants WHERE tenant_id = $tenant_id")->fetch_assoc();
if (!$tenant) die("Tenant not found.");

// Move tenant info to vacated_tenants
$stmt = $mysqli->prepare("
    INSERT INTO vacated_tenants 
    (tenant_id, full_name, national_id, phone_number, email, house_number, deposit, vacated_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())
");
$deposit = $tenant['deposit'] ?? 0;
$stmt->bind_param(
    "isssssd",
    $tenant['tenant_id'],
    $tenant['full_name'],
    $tenant['national_id'],
    $tenant['phone_number'],
    $tenant['email'],
    $tenant['house_number'],
    $deposit
);
$stmt->execute();

// Soft vacate: mark tenant as vacated
$mysqli->query("UPDATE tenants SET status='vacated' WHERE tenant_id=$tenant_id");

header("Location: tenants.php?msg=Tenant vacated successfully");
exit();
?>

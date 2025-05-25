<?php
$mysqli = new mysqli("localhost", "root", "", "rentmanager");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>

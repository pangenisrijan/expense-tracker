<?php
// ============================================
// db_connect.php
// This file's only job: open one connection to MySQL
// that every other PHP file can reuse by writing: require 'db_connect.php';
// ============================================

$host = "localhost";     // XAMPP's MySQL always runs on your own machine
$db_user = "root";       // XAMPP's default MySQL username
$db_pass = "";           // XAMPP's default MySQL password is blank
$db_name = "expense_tracker";

// mysqli_connect() is a built-in PHP function that opens the connection.
// It returns a "connection object" we can reuse, or false if it fails.
$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

// Always check the connection actually worked before continuing.
if (!$conn) {
    // die() stops the script immediately and prints a message.
    // In a real production app you would NOT show the raw error to users
    // (security risk) - but while learning, seeing it helps you debug.
    die("Database connection failed: " . mysqli_connect_error());
}
?>

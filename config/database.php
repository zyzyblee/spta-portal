<?php
/**
 * Database connection settings.
 *
 * If your MySQL username/password are different from the XAMPP
 * defaults below, change them here -- this is the ONLY file that
 * should need editing to get the system running.
 */

$host     = "localhost";
$db_name  = "spta_payment_monitoring";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db_name};charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die(
        "Database connection failed. Please make sure MySQL is running " .
        "in XAMPP and that the database 'spta_payment_monitoring' has " .
        "been imported. (Technical detail: " . $e->getMessage() . ")"
    );
}

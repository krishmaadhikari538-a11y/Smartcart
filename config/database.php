<?php
/**
 * Central database configuration file.
 * Update these values if your XAMPP MySQL credentials are different.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'smartcart_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please check config/database.php and make sure MySQL is running in XAMPP. (" . $e->getMessage() . ")");
}

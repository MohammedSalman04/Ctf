<?php
// =======================================================
// CTF Platform Config (PDO + Session + Helpers)
// =======================================================

// ⚠️ Development only — remove or disable in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// -------------------------------------------------------
// Start session safely (avoid duplicate session_start)
// -------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------
// Database config
// -------------------------------------------------------
$DB_HOST = 'localhost';      // DB host
$DB_USER = 'ctfuser';        // DB user
$DB_PASS = 'ctfpass';        // DB password
$DB_NAME = 'ctfdb';          // DB name
$charset = 'utf8mb4';

// DSN for PDO
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // fetch assoc arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared stmts
];

// -------------------------------------------------------
// Create PDO connection
// -------------------------------------------------------
try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// -------------------------------------------------------
// Helper Functions
// -------------------------------------------------------

// Check if logged in
function is_logged_in(): bool {
    return isset($_SESSION['uid']);
}

// Require login
function require_login(): void {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

// Get current user info
function user(): array {
    return [
        'id'   => $_SESSION['uid']   ?? null,
        'name' => $_SESSION['uname'] ?? '',
        'role' => $_SESSION['urole'] ?? 'user'
    ];
}

// Check admin role
function is_admin(): bool {
    return ($_SESSION['urole'] ?? '') === 'admin';
}

// Require admin
function require_admin(): void {
    if (!is_admin()) {
        header("HTTP/1.1 403 Forbidden");
        echo "❌ Access denied. Admins only.";
        exit;
    }
}
?>

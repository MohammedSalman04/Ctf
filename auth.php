<?php
// auth.php

// Ensure config.php is loaded only once
require_once __DIR__ . '/config.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define require_login() only if not already declared
if (!function_exists('require_login')) {
    function require_login() {
        if (!isset($_SESSION['uid'])) {
            header('Location: login.php');
            exit;
        }
    }
}

// ✅ Helper to get current logged-in user ID everywhere
if (!function_exists('current_user_id')) {
    function current_user_id() {
        return $_SESSION['uid'] ?? null;
    }
}

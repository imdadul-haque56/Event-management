<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Restrict page access to logged-in users only
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: " . get_base_url() . "login.php");
        exit();
    }
}

/**
 * Restrict page access to admin users only
 */
function require_admin() {
    if (!is_admin()) {
        header("HTTP/1.1 403 Forbidden");
        echo "403 - Access Denied. Administrator privileges required.";
        exit();
    }
}

/**
 * Get base path URL helper
 */
function get_base_url() {
    // Find depth to adjust redirection paths relative to root directory
    $script = $_SERVER['SCRIPT_NAME'];
    $depth = substr_count($script, '/') - 1;
    
    // In case of subfolders like admin/ or user/
    if (strpos($script, '/admin/') !== false || strpos($script, '/user/') !== false) {
        return '../';
    }
    return '';
}
?>

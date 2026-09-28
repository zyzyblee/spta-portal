<?php
/**
 * Session handling and admin-authentication helpers.
 * Include this on every page that needs to know whether an admin
 * is logged in, or that must be restricted to admins only.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * True if an administrator is currently logged in.
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Call at the top of any admin-only page. Redirects to the login
 * page (and stops execution) if nobody is logged in, so admin
 * pages can never be reached just by typing their URL.
 */
function requireAdminLogin($redirectTo = '../login.php') {
    if (!isAdminLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

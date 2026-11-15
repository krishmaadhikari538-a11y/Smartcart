<?php
/**
 * Authentication / authorization helper functions.
 * Requires includes/session.php to already be loaded.
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserRole() {
    return $_SESSION['role'] ?? null;
}

function currentUserName() {
    return $_SESSION['full_name'] ?? '';
}

/**
 * Redirects unauthenticated users to the login page.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

/**
 * Redirects users who do not have one of the allowed roles.
 * Usage: requireRole(['admin']); or requireRole(['customer','seller']);
 */
function requireRole($allowedRoles) {
    requireLogin();
    if (!in_array(currentUserRole(), $allowedRoles, true)) {
        header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
        exit;
    }
}

function requireCustomer() { requireRole(['customer']); }
function requireSeller()   { requireRole(['seller']); }
function requireAdmin()    { requireRole(['admin']); }

<?php
// ============================================================
// Authentication helpers
// ============================================================
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: ' . BASE_URL . '/index.php?error=unauthorized');
        exit;
    }
}

function currentUser() {
    return [
        'id'        => (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0),
        'username'  => (isset($_SESSION['username']) ? $_SESSION['username'] : ''),
        'full_name' => (isset($_SESSION['full_name']) ? $_SESSION['full_name'] : ''),
        'role'      => (isset($_SESSION['role']) ? $_SESSION['role'] : ''),
    ];
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/staff/dashboard.php');
        }
        exit;
    }
}

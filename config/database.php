<?php
// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bahay_pagasa');

define('SITE_NAME', 'Bahay Pag-asa');
define('SITE_SUBTITLE', 'Child Welfare & Resource Allocation Management System');
define('SITE_LOCATION', 'San Antonio, Biñan City Laguna');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="font-family:sans-serif;padding:2rem;color:#721c24;background:#f8d7da;border:1px solid #f5c6cb;border-radius:4px;">
                 <strong>Database connection failed:</strong> ' . htmlspecialchars($conn->connect_error) . '
                 <p>Please ensure MySQL is running and the database <em>' . DB_NAME . '</em> exists.</p></div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Log an activity
function logActivity($action, $module = '') {
    $userId = (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
    $ip     = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    $db     = getDB();
    $stmt   = $db->prepare("INSERT INTO activity_logs (user_id, action, module, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $userId, $action, $module, $ip);
    $stmt->execute();
    $stmt->close();
}

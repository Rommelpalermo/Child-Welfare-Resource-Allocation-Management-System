<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

logActivity('User logged out', 'Auth');
session_unset();
session_destroy();
header('Location: index.php');
exit;

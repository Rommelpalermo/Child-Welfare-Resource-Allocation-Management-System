<?php
// ============================================================
// Bootstrap / base config loaded on every page
// ============================================================
if (!defined('BASE_URL')) { define('BASE_URL', ''); }

// Force sessions to use strict mode
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);

require_once __DIR__ . '/../config/database.php';

// Sanitize output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Format date
function fmtDate($date, $format = 'M d, Y') {
    if (!$date) return '—';
    return date($format, strtotime($date));
}

// Truncate text
function truncate($text, $len = 60) {
    return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '…' : $text;
}

// Flash messages
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

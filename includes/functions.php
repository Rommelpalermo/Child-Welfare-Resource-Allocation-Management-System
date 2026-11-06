<?php
// ============================================================
// Bootstrap / base config loaded on every page
// ============================================================
if (!defined('BASE_URL')) { define('BASE_URL', ''); }

// Apply session settings before any page starts the session.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
}

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

        function donationDetailsJson(array $d) {
            $rows = [
                'Donor' => $d['donor_name'],
                'Donor Type' => !empty($d['donor_type']) ? $d['donor_type'] : '—',
                'Partner / Org' => !empty($d['partner_name']) ? $d['partner_name'] : '—',
                'Donation Type' => $d['donation_type'],
            ];
            if ($d['donation_type'] === 'Cash') $rows['Amount'] = '₱' . number_format($d['amount'], 2);
            $rows += [
                'Items Description' => !empty($d['items_desc']) ? $d['items_desc'] : '—',
                'Date' => fmtDate($d['donation_date']),
                'Received By' => !empty($d['receiver']) ? $d['receiver'] : '—',
                'Status' => $d['status'],
                'Acknowledgement No.' => !empty($d['acknowledgement_no']) ? $d['acknowledgement_no'] : '—',
                'Notes' => !empty($d['notes']) ? $d['notes'] : '—',
            ];
            return htmlspecialchars(json_encode($rows, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        }

<?php
header('Content-Type: text/html; charset=UTF-8');

define('DB_HOST', 'localhost');
define('DB_NAME', 'uXXXXX');
define('DB_USER', 'uXXXXX');
define('DB_PASS', 'your_pass');

function landing_get_db() {
    static $db = null;
    if ($db === null) {
        $db = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $db;
}

function landing_str_char_len($s) {
    if (function_exists('iconv_strlen')) return iconv_strlen($s, 'UTF-8');
    if (function_exists('mb_strlen')) return mb_strlen($s, 'UTF-8');
    return strlen($s);
}

function landing_set_temp_cookie($name, $value) { setcookie($name, $value, 0, '/'); }
function landing_set_perm_cookie($name, $value) { setcookie($name, $value, time() + 365 * 24 * 3600, '/'); }
function landing_set_error_cookie($name, $message) { setcookie($name, $message, 0, '/'); }
function landing_del_cookie($name) { setcookie($name, '', 100000, '/'); }

function landing_get_session_user() {
    if (empty($_COOKIE[session_name()])) return false;
    if (!session_start()) return false;
    if (empty($_SESSION['landing_user_login'])) return false;
    return [
        'uid' => (int)($_SESSION['landing_user_id'] ?? 0),
        'login' => (string)$_SESSION['landing_user_login'],
    ];
}

function landing_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['landing_csrf_token'])) {
        $_SESSION['landing_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['landing_csrf_token'];
}

function landing_csrf_verify() {
    $submitted = $_POST['csrf_token'] ?? '';
    if (session_status() === PHP_SESSION_NONE) {
        if (empty($_COOKIE[session_name()])) {
            http_response_code(403);
            exit('403 Forbidden');
        }
        session_start();
    }
    $expected = $_SESSION['landing_csrf_token'] ?? '';
    if (!$expected || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        exit('403 Forbidden: invalid CSRF token');
    }
    $_SESSION['landing_csrf_token'] = bin2hex(random_bytes(32));
}

function landing_h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

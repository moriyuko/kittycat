<?php
header('Content-Type: application/json; charset=UTF-8');

define('DB_HOST', 'localhost');
define('DB_NAME', 'uXXXXX');
define('DB_USER', 'uXXXXX');
define('DB_PASS', 'your_pass');

function get_db() {
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

function json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$comment = trim($_POST['comment'] ?? '');

if ($name === '' || strlen($name) > 100) {
    json_error('Некорректное имя');
}
if ($phone === '' || !preg_match('/^\+?[\d\s\-\(\)]{7,20}$/', $phone)) {
    json_error('Некорректный телефон');
}
if ($email === '' || !preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email) || strlen($email) > 255) {
    json_error('Некорректный email');
}
if (strlen($comment) > 2000) {
    json_error('Комментарий слишком длинный');
}

try {
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO landing_lead (name, phone, email, comment) VALUES (:n, :p, :e, :c)');
    $stmt->execute([
        ':n' => $name,
        ':p' => $phone,
        ':e' => $email,
        ':c' => $comment,
    ]);

    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log('[lead_submit.php] DB error: ' . $e->getMessage());
    json_error('Ошибка сервера. Попробуйте позже.', 500);
}

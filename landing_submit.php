<?php
require_once __DIR__ . '/landing_db.php';

header('Content-Type: application/json; charset=UTF-8');

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

$hasErr = false;

// cookie-based errors/values like in backend
if ($name === '' || landing_str_char_len($name) > 100) {
    landing_set_error_cookie('err_name', 'Укажите имя (до 100 символов).');
    landing_set_temp_cookie('val_name', $name);
    $hasErr = true;
} else {
    landing_set_perm_cookie('val_name', $name);
}

if ($phone === '') {
    landing_set_error_cookie('err_phone', 'Укажите телефон.');
    landing_set_temp_cookie('val_phone', $phone);
    $hasErr = true;
} elseif (!preg_match('/^\+?[\d\s\-\(\)]{7,20}$/', $phone)) {
    landing_set_error_cookie('err_phone', 'Телефон: допустимы цифры, +, (, ), пробел, дефис (7–20 знаков).');
    landing_set_temp_cookie('val_phone', $phone);
    $hasErr = true;
} else {
    landing_set_perm_cookie('val_phone', $phone);
}

if ($email === '') {
    landing_set_error_cookie('err_email', 'Укажите e-mail.');
    landing_set_temp_cookie('val_email', $email);
    $hasErr = true;
} elseif (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email) || landing_str_char_len($email) > 255) {
    landing_set_error_cookie('err_email', 'E-mail: введите адрес вида name@domain.ru.');
    landing_set_temp_cookie('val_email', $email);
    $hasErr = true;
} else {
    landing_set_perm_cookie('val_email', $email);
}

if (landing_str_char_len($comment) > 2000) {
    landing_set_error_cookie('err_comment', 'Комментарий слишком длинный (до 2000 символов).');
    landing_set_temp_cookie('val_comment', $comment);
    $hasErr = true;
} else {
    landing_set_perm_cookie('val_comment', $comment);
}

if ($hasErr) {
    landing_set_error_cookie('error_hint', 'Исправьте ошибки в форме.');
    json_error('Исправьте ошибки в форме.', 422);
}

function gen_login($db) {
    do {
        $login = 'user_' . substr(uniqid('', true), -7);
        $check = $db->prepare('SELECT id FROM landing_user WHERE login = :l');
        $check->execute([':l' => $login]);
    } while ($check->fetch());
    return $login;
}

function gen_password() {
    $plainPass = substr(str_shuffle('abcdefghjkmnpqrstuvwxyz23456789'), 0, 4)
        . substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 2)
        . substr(str_shuffle('23456789'), 0, 2);
    return str_shuffle($plainPass);
}

try {
    $db = landing_get_db();

    // create lead record
    $stmt = $db->prepare('INSERT INTO landing_lead (name, phone, email, comment) VALUES (:n, :p, :e, :c)');
    $stmt->execute([':n' => $name, ':p' => $phone, ':e' => $email, ':c' => $comment]);

    // create user account if not exists by email
    $stmtFind = $db->prepare('SELECT id, login FROM landing_user WHERE email = :e LIMIT 1');
    $stmtFind->execute([':e' => $email]);
    $existing = $stmtFind->fetch(PDO::FETCH_ASSOC);

    $credentials = null;
    if (!$existing) {
        $login = gen_login($db);
        $plainPass = gen_password();

        $stmtUser = $db->prepare('INSERT INTO landing_user (name, phone, email, comment, login, password_hash) VALUES (:n, :p, :e, :c, :l, :h)');
        $stmtUser->execute([
            ':n' => $name,
            ':p' => $phone,
            ':e' => $email,
            ':c' => $comment,
            ':l' => $login,
            ':h' => md5($plainPass),
        ]);

        $credentials = ['login' => $login, 'password' => $plainPass];
        landing_set_temp_cookie('new_login', $login);
        landing_set_temp_cookie('new_pass', $plainPass);
    }

    landing_set_temp_cookie('save', '1');
    landing_del_cookie('error_hint');

    echo json_encode(['ok' => true, 'credentials' => $credentials], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('[landing_submit.php] DB error: ' . $e->getMessage());
    landing_set_error_cookie('error_hint', 'Ошибка сервера. Попробуйте позже.');
    json_error('Ошибка сервера. Попробуйте позже.', 500);
}

<?php
require_once __DIR__ . '/landing_db.php';

$sessionUser = landing_get_session_user();
if (!$sessionUser) {
    header('Location: login.php');
    exit();
}

$messages = [];
$errors = [];
$values = [];

$db = landing_get_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // read from DB
    try {
        $stmt = $db->prepare('SELECT name, phone, email, comment FROM landing_user WHERE id = :id');
        $stmt->execute([':id' => $sessionUser['uid']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $values = [
                'name' => $row['name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'comment' => $row['comment'],
            ];
        }
    } catch (PDOException $e) {
        error_log('[profile GET] DB error: ' . $e->getMessage());
        $messages['error_hint'] = 'Ошибка загрузки данных. Попробуйте позже.';
    }

    // read cookies with errors/values
    $fieldMap = [
        'name' => ['err' => 'err_name', 'val' => 'val_name'],
        'phone' => ['err' => 'err_phone', 'val' => 'val_phone'],
        'email' => ['err' => 'err_email', 'val' => 'val_email'],
        'comment' => ['err' => 'err_comment', 'val' => 'val_comment'],
    ];

    $anyErr = false;
    foreach ($fieldMap as $field => $keys) {
        if (!empty($_COOKIE[$keys['err']])) {
            $errors[$field] = $_COOKIE[$keys['err']];
            landing_del_cookie($keys['err']);
            $anyErr = true;
            if ($keys['val'] && isset($_COOKIE[$keys['val']])) {
                $values[$field] = $_COOKIE[$keys['val']];
                landing_del_cookie($keys['val']);
            }
        } else {
            $errors[$field] = '';
        }
    }

    if (!empty($_COOKIE['save'])) {
        landing_del_cookie('save');
        $messages['success'] = 'Данные сохранены!';
    }

    if ($anyErr) $messages['error_hint'] = 'Исправьте ошибки в форме.';

    $csrfToken = landing_csrf_token();

} else {
    landing_csrf_verify();

    $hasErr = false;

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($name === '' || landing_str_char_len($name) > 100) {
        landing_set_error_cookie('err_name', 'Укажите имя (до 100 символов).');
        landing_set_temp_cookie('val_name', $name);
        $hasErr = true;
    }

    if ($phone === '') {
        landing_set_error_cookie('err_phone', 'Укажите телефон.');
        landing_set_temp_cookie('val_phone', $phone);
        $hasErr = true;
    } elseif (!preg_match('/^\+?[\d\s\-\(\)]{7,20}$/', $phone)) {
        landing_set_error_cookie('err_phone', 'Телефон: допустимы цифры, +, (, ), пробел, дефис (7–20 знаков).');
        landing_set_temp_cookie('val_phone', $phone);
        $hasErr = true;
    }

    if ($email === '') {
        landing_set_error_cookie('err_email', 'Укажите e-mail.');
        landing_set_temp_cookie('val_email', $email);
        $hasErr = true;
    } elseif (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email) || landing_str_char_len($email) > 255) {
        landing_set_error_cookie('err_email', 'E-mail: введите адрес вида name@domain.ru.');
        landing_set_temp_cookie('val_email', $email);
        $hasErr = true;
    }

    if (landing_str_char_len($comment) > 2000) {
        landing_set_error_cookie('err_comment', 'Комментарий слишком длинный (до 2000 символов).');
        landing_set_temp_cookie('val_comment', $comment);
        $hasErr = true;
    }

    if ($hasErr) {
        header('Location: profile.php');
        exit();
    }

    try {
        $stmt = $db->prepare('UPDATE landing_user SET name=:n, phone=:p, email=:e, comment=:c WHERE id=:id');
        $stmt->execute([
            ':n' => $name,
            ':p' => $phone,
            ':e' => $email,
            ':c' => $comment,
            ':id' => $sessionUser['uid'],
        ]);
    } catch (PDOException $e) {
        error_log('[profile POST] DB error: ' . $e->getMessage());
        landing_set_error_cookie('err_name', 'Ошибка сервера. Попробуйте позже.');
        header('Location: profile.php');
        exit();
    }

    setcookie('save', '1', 0, '/');
    header('Location: profile.php');
    exit();
}

function fieldVal($key) { global $values; return landing_h($values[$key] ?? ''); }
function hasErr($key) { global $errors; return !empty($errors[$key]); }
function errMsg($key) { global $errors; return landing_h($errors[$key] ?? ''); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Профиль</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f8f4ff; color:#333; padding: 24px 14px; }
    .card { max-width: 680px; margin: 0 auto; background:#fff; border-radius: 14px; box-shadow: 0 2px 10px rgba(147,112,219,.2); padding: 22px 22px; }
    .top { display:flex; justify-content: space-between; align-items:center; gap: 10px; margin-bottom: 16px; }
    h1 { font-size: 1.35rem; color:#7b68ee; margin: 0; }
    .auth { font-size: .9rem; color:#666; }
    .auth a { color:#7b68ee; text-decoration:none; margin-left: 10px; }
    .msg-success { background:#d4edda; color:#155724; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; }
    .msg-error { background:#f8d7da; color:#721c24; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; }
    .field { margin-bottom: 12px; display:flex; flex-direction:column; gap: 6px; }
    label { color:#666; font-size: .9rem; }
    input, textarea { padding: .65rem .8rem; border: 2px solid #ba9ff9; border-radius: 10px; font-size: 1rem; }
    input:focus, textarea:focus { outline:none; border-color:#ff69b4; }
    .field-error input, .field-error textarea { border-color:#c0392b; }
    .err { color:#c0392b; font-size: .85rem; }
    button { width:100%; padding: .9rem; background:#ff69b4; border:none; border-radius: 12px; color:#fff; font-weight: 700; cursor:pointer; font-size: 1rem; margin-top: 8px; }
    button:hover { background:#ff1493; }
  </style>
</head>
<body>
  <div class="card">
    <div class="top">
      <h1>Профиль</h1>
      <div class="auth">
        Вы вошли как <strong><?= landing_h($sessionUser['login']) ?></strong>
        <a href="login.php?logout=1">Выйти</a>
      </div>
    </div>

    <?php if (!empty($messages['success'])): ?>
      <div class="msg-success"><?= landing_h($messages['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($messages['error_hint'])): ?>
      <div class="msg-error"><?= landing_h($messages['error_hint']) ?></div>
    <?php endif; ?>

    <form action="profile.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= landing_h($csrfToken ?? landing_csrf_token()) ?>">

      <div class="field <?= hasErr('name') ? 'field-error' : '' ?>">
        <label for="name">Имя</label>
        <input type="text" id="name" name="name" value="<?= fieldVal('name') ?>" maxlength="100">
        <?php if (hasErr('name')): ?><div class="err"><?= errMsg('name') ?></div><?php endif; ?>
      </div>

      <div class="field <?= hasErr('phone') ? 'field-error' : '' ?>">
        <label for="phone">Телефон</label>
        <input type="tel" id="phone" name="phone" value="<?= fieldVal('phone') ?>">
        <?php if (hasErr('phone')): ?><div class="err"><?= errMsg('phone') ?></div><?php endif; ?>
      </div>

      <div class="field <?= hasErr('email') ? 'field-error' : '' ?>">
        <label for="email">Почта</label>
        <input type="email" id="email" name="email" value="<?= fieldVal('email') ?>" maxlength="255">
        <?php if (hasErr('email')): ?><div class="err"><?= errMsg('email') ?></div><?php endif; ?>
      </div>

      <div class="field <?= hasErr('comment') ? 'field-error' : '' ?>">
        <label for="comment">Комментарий</label>
        <textarea id="comment" name="comment" rows="4"><?= fieldVal('comment') ?></textarea>
        <?php if (hasErr('comment')): ?><div class="err"><?= errMsg('comment') ?></div><?php endif; ?>
      </div>

      <button type="submit">Сохранить</button>
    </form>
  </div>
</body>
</html>

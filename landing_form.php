<?php
require_once __DIR__ . '/landing_db.php';

$messages = [];
$errors = [];
$values = [
    'name' => '',
    'phone' => '',
    'email' => '',
    'comment' => '',
];

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
        if ($keys['val'] && isset($_COOKIE[$keys['val']])) {
            $values[$field] = $_COOKIE[$keys['val']];
        }
    }
}

if (!empty($_COOKIE['save'])) {
    landing_del_cookie('save');
    $messages['success'] = 'Спасибо! Данные отправлены.';

    if (!empty($_COOKIE['new_login']) && !empty($_COOKIE['new_pass'])) {
        $messages['credentials'] = sprintf(
            'Запомните: логин <strong>%s</strong>, пароль <strong>%s</strong>. Используйте их для <a href="login.php">входа</a> и изменения данных.',
            htmlspecialchars($_COOKIE['new_login'], ENT_QUOTES),
            htmlspecialchars($_COOKIE['new_pass'], ENT_QUOTES)
        );
        landing_del_cookie('new_login');
        landing_del_cookie('new_pass');
    }
}

if ($anyErr) $messages['error_hint'] = 'Исправьте ошибки в форме.';

function fieldVal($key) { global $values; return landing_h($values[$key] ?? ''); }
function hasErr($key) { global $errors; return !empty($errors[$key]); }
function errMsg($key) { global $errors; return landing_h($errors[$key] ?? ''); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Форма</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-page-wrap { max-width: 980px; margin: 110px auto 40px; padding: 0 20px; }
    .form-card { background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(147, 112, 219, 0.2); padding: 24px; }
    .top-actions { display:flex; justify-content: space-between; align-items:center; gap: 10px; margin-bottom: 14px; }
    .top-actions a { color: #7b68ee; text-decoration:none; font-weight: 600; }
    .msg-success { background:#d4edda; color:#155724; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; }
    .msg-credentials { background:#eef7ff; color:#1b3a57; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; }
    .msg-error { background:#f8d7da; color:#721c24; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; }
    .field-error input, .field-error textarea { border-color: #c0392b !important; }
    .err { color:#c0392b; font-size: .85rem; }
  </style>
</head>
<body>
  <header class="header" id="header">
    <div class="header-container">
      <div class="logo"><a href="index.html" style="text-decoration:none;color:inherit">KittyCAT</a></div>
      <nav class="nav" style="max-height:none;position:static;box-shadow:none">
        <ul class="nav-list">
          <li><a href="index.html#contacts" class="nav-link">Лендинг</a></li>
          <li><a href="login.php" class="nav-link">Вход</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <div class="form-page-wrap">
    <div class="form-card">
      <div class="top-actions">
        <h2 class="section-title" style="margin:0;text-align:left">Форма заявки</h2>
        <a href="login.php">Уже отправляли? Войти</a>
      </div>

      <?php if (!empty($messages['success'])): ?>
        <div class="msg-success"><?= landing_h($messages['success']) ?></div>
      <?php endif; ?>
      <?php if (!empty($messages['credentials'])): ?>
        <div class="msg-credentials"><?= $messages['credentials'] ?></div>
      <?php endif; ?>
      <?php if (!empty($messages['error_hint'])): ?>
        <div class="msg-error"><?= landing_h($messages['error_hint']) ?></div>
      <?php endif; ?>

      <form id="leadForm" action="landing_submit.php" method="POST">
        <div class="form-group field <?= hasErr('name') ? 'field-error' : '' ?>">
          <label for="name">Имя</label>
          <input type="text" id="name" name="name" required value="<?= fieldVal('name') ?>" maxlength="100">
          <?php if (hasErr('name')): ?><div class="err"><?= errMsg('name') ?></div><?php endif; ?>
        </div>
        <div class="form-group field <?= hasErr('phone') ? 'field-error' : '' ?>">
          <label for="phone">Телефон</label>
          <input type="tel" id="phone" name="phone" required value="<?= fieldVal('phone') ?>">
          <?php if (hasErr('phone')): ?><div class="err"><?= errMsg('phone') ?></div><?php endif; ?>
        </div>
        <div class="form-group field <?= hasErr('email') ? 'field-error' : '' ?>">
          <label for="email">Почта</label>
          <input type="email" id="email" name="email" required value="<?= fieldVal('email') ?>" maxlength="255">
          <?php if (hasErr('email')): ?><div class="err"><?= errMsg('email') ?></div><?php endif; ?>
        </div>
        <div class="form-group field <?= hasErr('comment') ? 'field-error' : '' ?>">
          <label for="comment">Комментарий</label>
          <textarea id="comment" name="comment" rows="4"><?= fieldVal('comment') ?></textarea>
          <?php if (hasErr('comment')): ?><div class="err"><?= errMsg('comment') ?></div><?php endif; ?>
        </div>
        <button type="submit" class="submit-btn">Отправить</button>
      </form>
    </div>
  </div>

  <script>
    const form = document.getElementById('leadForm');
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(form);

      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Отправка...';
      submitBtn.disabled = true;

      try {
        const res = await fetch('landing_submit.php', { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } });
        const json = await res.json().catch(() => null);

        if (res.ok && json && json.ok) {
          // after success we want the cookie-based messages on reload
          window.location.href = 'landing_form.php';
          return;
        }

        window.location.href = 'landing_form.php';
      } catch (e2) {
        window.location.href = 'landing_form.php';
      } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });
  </script>
</body>
</html>

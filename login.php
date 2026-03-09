<?php
require_once __DIR__ . '/landing_db.php';

// logout
if (isset($_GET['logout'])) {
    if (!empty($_COOKIE[session_name()]) && session_start()) {
        session_destroy();
        setcookie(session_name(), '', 100000, '/');
    }
    header('Location: profile.php');
    exit();
}

if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['landing_user_login'])) {
    header('Location: profile.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['login'] ?? '');
    $passInput = trim($_POST['pass'] ?? '');

    if ($loginInput === '' || $passInput === '') {
        $error = 'Введите логин и пароль.';
    } else {
        try {
            $db = landing_get_db();
            $stmt = $db->prepare('SELECT id, login FROM landing_user WHERE login = :l AND password_hash = MD5(:p) LIMIT 1');
            $stmt->execute([':l' => $loginInput, ':p' => $passInput]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (empty($_COOKIE[session_name()])) session_start();
                session_regenerate_id(true);
                $_SESSION['landing_user_login'] = $user['login'];
                $_SESSION['landing_user_id'] = (int)$user['id'];
                header('Location: profile.php');
                exit();
            }

            $error = 'Неверный логин или пароль.';
        } catch (PDOException $e) {
            error_log('[landing login] DB error: ' . $e->getMessage());
            $error = 'Ошибка сервера. Попробуйте позже.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Вход</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: #f8f4ff; color: #333; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 2rem 1rem; }
    .card { background:#fff; border-radius: 14px; box-shadow: 0 2px 10px rgba(147,112,219,.2); padding: 2rem 2.2rem; width: 100%; max-width: 420px; }
    h1 { font-size: 1.4rem; margin-bottom: 1.4rem; color: #7b68ee; text-align:center; }
    .field { margin-bottom: 1rem; display:flex; flex-direction:column; gap:.35rem; }
    label { font-size: .88rem; color:#666; }
    input { padding: .6rem .8rem; border: 2px solid #ba9ff9; border-radius: 8px; font-size: 1rem; }
    input:focus { outline:none; border-color:#ff69b4; }
    .msg-error { background:#f8d7da; color:#721c24; padding: .8rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
    button { width:100%; padding: .85rem; background:#ff69b4; border:none; border-radius: 10px; color:#fff; font-weight: 700; cursor:pointer; font-size: 1rem; }
    button:hover { background:#ff1493; }
    .back { display:block; margin-top: 1rem; text-align:center; color:#7b68ee; text-decoration:none; font-size:.9rem; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Вход</h1>

    <?php if ($error): ?>
      <div class="msg-error"><?= landing_h($error) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div class="field">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login" value="<?= landing_h($_POST['login'] ?? '') ?>" autocomplete="username">
      </div>
      <div class="field">
        <label for="pass">Пароль</label>
        <input type="password" id="pass" name="pass" autocomplete="current-password">
      </div>
      <button type="submit">Войти</button>
    </form>

    <a class="back" href="index.html">← На лендинг</a>
  </div>
</body>
</html>

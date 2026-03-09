<?php
error_reporting(0);
ini_set('display_errors','0');
header('Content-Type: text/html; charset=UTF-8');

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
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
        );
    }
    return $db;
}

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

function admin_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['landing_admin_csrf'])) {
        $_SESSION['landing_admin_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['landing_admin_csrf'];
}

function admin_csrf_verify() {
    $sub = $_POST['csrf_token'] ?? '';
    if (session_status() === PHP_SESSION_NONE) session_start();
    $exp = $_SESSION['landing_admin_csrf'] ?? '';
    if (!$exp || !hash_equals($exp, $sub)) { http_response_code(403); exit('403 Forbidden'); }
    $_SESSION['landing_admin_csrf'] = bin2hex(random_bytes(32));
}

$authOk = false;
if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    try {
        $stmt = get_db()->prepare('SELECT id FROM landing_admin WHERE login = :l AND password_hash = MD5(:p) LIMIT 1');
        $stmt->execute([':l' => $_SERVER['PHP_AUTH_USER'], ':p' => $_SERVER['PHP_AUTH_PW']]);
        $authOk = (bool)$stmt->fetch();
    } catch (PDOException $e) {
        error_log('[admin_leads] DB auth: ' . $e->getMessage());
    }
}

if (!$authOk) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Landing admin"');
    echo '<h1>401 Требуется авторизация</h1>';
    exit();
}

$actionMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            get_db()->prepare('DELETE FROM landing_lead WHERE id = :id')->execute([':id' => $id]);
            $actionMsg = 'Лид #' . $id . ' удалён.';
        }
    }
}

$leads = [];
try {
    $leads = get_db()->query('SELECT id, name, phone, email, comment, created_at FROM landing_lead ORDER BY id DESC')
                    ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('[admin_leads] DB list: ' . $e->getMessage());
}

$csrf = admin_csrf_token();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Лиды лендинга</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 24px; background: #f6f6fb; color: #222; }
    h1 { margin: 0 0 14px; }
    .flash { background: #eef7ee; border: 1px solid #b7e0b7; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
    thead { background: #7b68ee; color: #fff; }
    th, td { padding: 10px 12px; text-align: left; vertical-align: top; border-bottom: 1px solid #eee; }
    tbody tr:nth-child(even) { background: #fafaff; }
    .nowrap { white-space: nowrap; }
    .btn-del { background: #c0392b; color: #fff; border: none; border-radius: 8px; padding: 6px 10px; cursor: pointer; }
  </style>
</head>
<body>
  <h1>Лиды лендинга</h1>

  <?php if ($actionMsg): ?>
    <div class="flash"><?= h($actionMsg) ?></div>
  <?php endif; ?>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Имя</th>
          <th>Телефон</th>
          <th>Email</th>
          <th>Комментарий</th>
          <th>Дата</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($leads)): ?>
          <tr><td colspan="7" style="padding:16px;color:#666">Пока лидов нет.</td></tr>
        <?php else: ?>
          <?php foreach ($leads as $row): ?>
            <tr>
              <td class="nowrap"><?= (int)$row['id'] ?></td>
              <td><?= h($row['name']) ?></td>
              <td class="nowrap"><?= h($row['phone']) ?></td>
              <td><?= h($row['email']) ?></td>
              <td><?= h($row['comment'] ?: '—') ?></td>
              <td class="nowrap"><?= h($row['created_at']) ?></td>
              <td class="nowrap">
                <form action="admin_leads.php" method="POST" style="display:inline" onsubmit="return confirm('Удалить лид #<?= (int)$row['id'] ?>?')">
                  <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <button type="submit" class="btn-del">Удалить</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>

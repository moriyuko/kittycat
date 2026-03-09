<?php

declare(strict_types=1);

function admin_require_basic_auth(): void {
    $conf = app_config();
    $ac = $conf['admin'] ?? [];

    $login = null;
    $pass = null;

    if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        $login = (string)$_SERVER['PHP_AUTH_USER'];
        $pass = (string)$_SERVER['PHP_AUTH_PW'];
    } else {
        $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (is_string($hdr) && str_starts_with($hdr, 'Basic ')) {
            $decoded = base64_decode(substr($hdr, 6), true);
            if (is_string($decoded) && str_contains($decoded, ':')) {
                [$login, $pass] = explode(':', $decoded, 2);
            }
        }
    }

    $ok = false;
    if ($login !== null && $pass !== null) {
        if (!empty($ac['use_db'])) {
            $stmt = db()->prepare('SELECT id FROM admins WHERE login = :l AND password = :p LIMIT 1');
            $stmt->execute([':l' => $login, ':p' => $pass]);
            $ok = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $ok = ($login === (string)($ac['login'] ?? '') && $pass === (string)($ac['password'] ?? ''));
        }
    }

    if (!$ok) {
        header('WWW-Authenticate: Basic realm="Admin"');
        http_response_code(401);
        echo 'Unauthorized';
        exit;
    }
}

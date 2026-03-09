<?php

declare(strict_types=1);

function ensure_session_started(): void {
    $conf = app_config();
    ensure_storage_dirs();

    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', $conf['sessions_dir']);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function auth_current_user(): ?array {
    ensure_session_started();

    if (empty($_SESSION['auth'])) {
        return null;
    }

    $a = $_SESSION['auth'];
    if (!is_array($a)) {
        return null;
    }

    if (empty($a['profile_id']) || empty($a['login']) || empty($a['password'])) {
        return null;
    }

    return [
        'profile_id' => (int)$a['profile_id'],
        'login' => (string)$a['login'],
        'password' => (string)$a['password'],
    ];
}

function auth_login(int $profileId, string $login, string $password): void {
    ensure_session_started();
    session_regenerate_id(true);
    $_SESSION['auth'] = [
        'profile_id' => $profileId,
        'login' => $login,
        'password' => $password,
    ];
}

function auth_logout(): void {
    ensure_session_started();
    $_SESSION = [];
    session_destroy();
}

function auth_verify_credentials(string $login, string $password): ?array {
    $data = storage_load_profiles();
    foreach ($data['items'] as $item) {
        if (!is_array($item)) continue;
        if (($item['login'] ?? null) === $login && ($item['password'] ?? null) === $password) {
            return $item;
        }
    }
    return null;
}

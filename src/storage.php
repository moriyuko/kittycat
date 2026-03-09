<?php

declare(strict_types=1);

function app_config(): array {
    static $conf = null;
    if ($conf === null) {
        $conf = require __DIR__ . '/config.php';
    }
    return $conf;
}

function ensure_storage_dirs(): void {
    $conf = app_config();
    $dirs = [
        $conf['storage_dir'],
        $conf['sessions_dir'],
    ];
    foreach ($dirs as $d) {
        if (!is_dir($d)) {
            mkdir($d, 0777, true);
        }
    }
}

function storage_load_profiles(): array {
    $stmt = db()->query('SELECT id, name, phone, email, comment, login, password FROM profiles ORDER BY id ASC');
    $items = [];
    $maxId = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = (int)$row['id'];
        $maxId = max($maxId, $id);
        $items[(string)$id] = [
            'id' => $id,
            'name' => (string)$row['name'],
            'phone' => (string)$row['phone'],
            'email' => (string)$row['email'],
            'comment' => (string)$row['comment'],
            'login' => (string)$row['login'],
            'password' => (string)$row['password'],
        ];
    }

    return ['next_id' => $maxId + 1, 'items' => $items];
}

function storage_save_profiles(array $data): void {
    
}

function storage_create_profile(array $profile): array {
    $stmt = db()->prepare(
        'INSERT INTO profiles (name, phone, email, comment, login, password) VALUES (:name, :phone, :email, :comment, :login, :password)'
    );

    $stmt->execute([
        ':name' => (string)($profile['name'] ?? ''),
        ':phone' => (string)($profile['phone'] ?? ''),
        ':email' => (string)($profile['email'] ?? ''),
        ':comment' => (string)($profile['comment'] ?? ''),
        ':login' => (string)($profile['login'] ?? ''),
        ':password' => (string)($profile['password'] ?? ''),
    ]);

    $id = (int)db()->lastInsertId();
    $profile['id'] = $id;
    return $profile;
}

function storage_get_profile(int $id): ?array {
    $stmt = db()->prepare('SELECT id, name, phone, email, comment, login, password FROM profiles WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }

    return [
        'id' => (int)$row['id'],
        'name' => (string)$row['name'],
        'phone' => (string)$row['phone'],
        'email' => (string)$row['email'],
        'comment' => (string)$row['comment'],
        'login' => (string)$row['login'],
        'password' => (string)$row['password'],
    ];
}

function storage_update_profile(int $id, array $profile): ?array {
    $existing = storage_get_profile($id);
    if ($existing === null) {
        return null;
    }

    $stmt = db()->prepare(
        'UPDATE profiles SET name=:name, phone=:phone, email=:email, comment=:comment WHERE id=:id'
    );
    $stmt->execute([
        ':name' => (string)($profile['name'] ?? $existing['name']),
        ':phone' => (string)($profile['phone'] ?? $existing['phone']),
        ':email' => (string)($profile['email'] ?? $existing['email']),
        ':comment' => (string)($profile['comment'] ?? $existing['comment']),
        ':id' => $id,
    ]);

    return storage_get_profile($id);
}

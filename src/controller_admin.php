<?php

declare(strict_types=1);

function admin_list(array $request): array {
    admin_require_basic_auth();

    $stmt = db()->query('SELECT id, name, phone, email, comment, login, password, created_at, updated_at FROM profiles ORDER BY id DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = file_get_contents(__DIR__ . '/../assets/admin_list.html');
    if ($html === false) {
        return response_text('Missing assets', 500);
    }

    $items = '';
    foreach ($rows as $r) {
        $id = (int)$r['id'];
        $items .= '<tr>';
        $items .= '<td>' . $id . '</td>';
        $items .= '<td>' . h((string)$r['name']) . '</td>';
        $items .= '<td>' . h((string)$r['phone']) . '</td>';
        $items .= '<td>' . h((string)$r['email']) . '</td>';
        $items .= '<td>' . h((string)$r['login']) . '</td>';
        $items .= '<td><a href="/admin/user/' . $id . '">Открыть</a></td>';
        $items .= '</tr>';
    }

    $html = str_replace('{{rows}}', $items, $html);
    return response_html($html);
}

function admin_user_edit(array $request, int $id): array {
    admin_require_basic_auth();

    $profile = storage_get_profile($id);
    if ($profile === null) {
        return response_text('Not Found', 404);
    }

    $html = file_get_contents(__DIR__ . '/../assets/admin_edit.html');
    if ($html === false) {
        return response_text('Missing assets', 500);
    }

    $html = strtr($html, [
        '{{id}}' => (string)$id,
        '{{name}}' => h((string)($profile['name'] ?? '')),
        '{{phone}}' => h((string)($profile['phone'] ?? '')),
        '{{email}}' => h((string)($profile['email'] ?? '')),
        '{{comment}}' => h((string)($profile['comment'] ?? '')),
        '{{login}}' => h((string)($profile['login'] ?? '')),
        '{{password}}' => h((string)($profile['password'] ?? '')),
    ]);

    return response_html($html);
}

function admin_user_update(array $request, int $id): array {
    admin_require_basic_auth();

    $profile = storage_get_profile($id);
    if ($profile === null) {
        return response_text('Not Found', 404);
    }

    $payload = [
        'name' => (string)($request['post']['name'] ?? ''),
        'phone' => (string)($request['post']['phone'] ?? ''),
        'email' => (string)($request['post']['email'] ?? ''),
        'comment' => (string)($request['post']['comment'] ?? ''),
    ];

    $v = validate_profile_payload($payload);
    if (!$v['ok']) {
        $html = file_get_contents(__DIR__ . '/../assets/admin_edit.html') ?: '';
        $errHtml = '';
        foreach ($v['errors'] as $msg) {
            $errHtml .= '<div class="err">' . h((string)$msg) . '</div>';
        }
        $html = str_replace('{{errors}}', $errHtml, $html);
        $html = strtr($html, [
            '{{id}}' => (string)$id,
            '{{name}}' => h((string)($payload['name'] ?? '')),
            '{{phone}}' => h((string)($payload['phone'] ?? '')),
            '{{email}}' => h((string)($payload['email'] ?? '')),
            '{{comment}}' => h((string)($payload['comment'] ?? '')),
            '{{login}}' => h((string)($profile['login'] ?? '')),
            '{{password}}' => h((string)($profile['password'] ?? '')),
        ]);
        return response_html($html, 422);
    }

    $updated = $profile;
    $updated['name'] = $v['value']['name'];
    $updated['phone'] = $v['value']['phone'];
    $updated['email'] = $v['value']['email'];
    $updated['comment'] = $v['value']['comment'];

    storage_update_profile($id, $updated);

    return response_redirect('/admin');
}

function admin_user_delete(array $request, int $id): array {
    admin_require_basic_auth();

    $stmt = db()->prepare('DELETE FROM profiles WHERE id = :id');
    $stmt->execute([':id' => $id]);

    return response_redirect('/admin');
}

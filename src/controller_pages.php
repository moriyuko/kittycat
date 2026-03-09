<?php

declare(strict_types=1);

function page_home(array $request): array {
    $auth = auth_current_user();
    $profileId = $auth['profile_id'] ?? null;

    $index = file_get_contents(__DIR__ . '/../assets/index.html');
    if ($index === false) {
        return response_text('Missing assets', 500);
    }

    $boot = '<script>window.__APP__ = ' . json_encode([
        'auth' => $auth !== null,
        'profileId' => $profileId,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';</script>';

    $index = str_replace('</head>', $boot . "\n</head>", $index);

    return response_html($index);
}

function page_asset(array $request): array {
    $path = (string)($request['path'] ?? '');
    $rel = substr($path, strlen('/assets/'));
    $rel = ltrim($rel, '/');

    if ($rel === '' || str_contains($rel, '..') || str_contains($rel, "\\")) {
        return response_text('Not Found', 404);
    }

    $file = __DIR__ . '/../assets/' . $rel;
    if (!is_file($file)) {
        return response_text('Not Found', 404);
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $ct = match ($ext) {
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'mp4' => 'video/mp4',
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        default => 'application/octet-stream',
    };

    return [
        'status' => 200,
        'headers' => [
            'Content-Type' => $ct,
        ],
        'body' => file_get_contents($file) ?: '',
    ];
}

function page_profile(array $request): array {
    $id = (int)($request['query']['id'] ?? 0);
    if ($id <= 0) {
        return response_text('Bad Request', 400);
    }

    $profile = storage_get_profile($id);
    if ($profile === null) {
        return response_text('Not Found', 404);
    }

    $html = file_get_contents(__DIR__ . '/../assets/profile.html');
    if ($html === false) {
        return response_text('Missing assets', 500);
    }

    $replace = [
        '{{name}}' => h((string)($profile['name'] ?? '')),
        '{{phone}}' => h((string)($profile['phone'] ?? '')),
        '{{email}}' => h((string)($profile['email'] ?? '')),
        '{{comment}}' => h((string)($profile['comment'] ?? '')),
        '{{login}}' => h((string)($profile['login'] ?? '')),
        '{{password}}' => h((string)($profile['password'] ?? '')),
    ];

    $html = strtr($html, $replace);
    return response_html($html);
}

function page_login(array $request): array {
    $html = file_get_contents(__DIR__ . '/../assets/login.html');
    if ($html === false) {
        return response_text('Missing assets', 500);
    }
    return response_html($html);
}

function page_login_post(array $request): array {
    $login = trim((string)($request['post']['login'] ?? ''));
    $password = trim((string)($request['post']['password'] ?? ''));

    $profile = auth_verify_credentials($login, $password);
    if ($profile === null) {
        return response_html(file_get_contents(__DIR__ . '/../assets/login.html') ?: '', 401);
    }

    auth_login((int)$profile['id'], (string)$profile['login'], (string)$profile['password']);

    return response_redirect('/');
}

function page_logout_post(array $request): array {
    auth_logout();
    return response_redirect('/');
}

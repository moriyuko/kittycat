<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Moscow');

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/http.php';
require __DIR__ . '/db.php';
require __DIR__ . '/storage.php';
require __DIR__ . '/validation.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin_auth.php';
require __DIR__ . '/controller_pages.php';
require __DIR__ . '/controller_api.php';
require __DIR__ . '/controller_admin.php';

function dispatch(array $request): array {
    $method = strtoupper($request['method'] ?? 'GET');
    $path = $request['path'] ?? '/';

    if (str_starts_with($path, '/assets/')) {
        return page_asset($request);
    }

    if ($path === '/' && $method === 'GET') {
        return page_home($request);
    }

    if ($path === '/login' && $method === 'GET') {
        return page_login($request);
    }

    if ($path === '/login' && $method === 'POST') {
        return page_login_post($request);
    }

    if ($path === '/logout' && $method === 'POST') {
        return page_logout_post($request);
    }

    if ($path === '/profile' && $method === 'GET') {
        return page_profile($request);
    }

    if ($path === '/api/profile' && $method === 'POST') {
        return api_profile_create($request);
    }

    if (preg_match('#^/api/profile/(\d+)$#', $path, $m) && $method === 'PUT') {
        return api_profile_update($request, (int)$m[1]);
    }

    if ($path === '/admin' && $method === 'GET') {
        return admin_list($request);
    }

    if (preg_match('#^/admin/user/(\d+)$#', $path, $m) && $method === 'GET') {
        return admin_user_edit($request, (int)$m[1]);
    }

    if (preg_match('#^/admin/user/(\d+)$#', $path, $m) && $method === 'PUT') {
        return admin_user_update($request, (int)$m[1]);
    }

    if (preg_match('#^/admin/user/(\d+)$#', $path, $m) && $method === 'DELETE') {
        return admin_user_delete($request, (int)$m[1]);
    }

    return response_text('Not Found', 404, ['Content-Type' => 'text/plain; charset=UTF-8']);
}

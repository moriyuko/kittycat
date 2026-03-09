<?php

declare(strict_types=1);

function request_json(array $request): array {
    $raw = (string)($request['raw_body'] ?? '');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function api_profile_create(array $request): array {
    $payload = request_json($request);
    $v = validate_profile_payload($payload);

    if (!$v['ok']) {
        return json_response(['ok' => false, 'errors' => $v['errors']], 422);
    }

    $login = generate_login();
    $password = generate_password();

    $profile = $v['value'];
    $profile['login'] = $login;
    $profile['password'] = $password;

    $created = storage_create_profile($profile);

    return json_response([
        'ok' => true,
        'login' => $login,
        'password' => $password,
        'profileUrl' => '/profile?id=' . (int)$created['id'],
        'profileId' => (int)$created['id'],
    ], 201);
}

function api_profile_update(array $request, int $profileId): array {
    $auth = auth_current_user();
    if ($auth === null) {
        return json_response(['ok' => false, 'error' => 'unauthorized'], 401);
    }

    if ($auth['profile_id'] !== $profileId) {
        return json_response(['ok' => false, 'error' => 'forbidden'], 403);
    }

    $existing = storage_get_profile($profileId);
    if ($existing === null) {
        return json_response(['ok' => false, 'error' => 'not_found'], 404);
    }

    $payload = request_json($request);
    $v = validate_profile_payload($payload);

    if (!$v['ok']) {
        return json_response(['ok' => false, 'errors' => $v['errors']], 422);
    }

    $updated = $existing;
    $updated['name'] = $v['value']['name'];
    $updated['phone'] = $v['value']['phone'];
    $updated['email'] = $v['value']['email'];
    $updated['comment'] = $v['value']['comment'];

    $saved = storage_update_profile($profileId, $updated);

    return json_response(['ok' => true, 'profileId' => $profileId], 200);
}

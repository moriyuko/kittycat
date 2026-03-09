<?php

declare(strict_types=1);

function validate_profile_payload(array $payload): array {
    $errors = [];

    $name = trim((string)($payload['name'] ?? ''));
    $phone = trim((string)($payload['phone'] ?? ''));
    $email = trim((string)($payload['email'] ?? ''));
    $comment = trim((string)($payload['comment'] ?? ''));

    if ($name === '') {
        $errors['name'] = 'Укажите имя.';
    } elseif (mb_strlen($name) > 150) {
        $errors['name'] = 'Имя не должно превышать 150 символов.';
    }

    if ($phone === '') {
        $errors['phone'] = 'Укажите номер телефона.';
    } elseif (!preg_match('/^\+?[\d\s\-\(\)]{7,20}$/', $phone)) {
        $errors['phone'] = 'Телефон: допустимы цифры, +, (, ), пробел, дефис (7–20 знаков).';
    }

    if ($email === '') {
        $errors['email'] = 'Укажите e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'E-mail: введите корректный адрес.';
    } elseif (mb_strlen($email) > 255) {
        $errors['email'] = 'E-mail слишком длинный (максимум 255 символов).';
    }

    if (mb_strlen($comment) > 2000) {
        $errors['comment'] = 'Комментарий слишком длинный (максимум 2000 символов).';
    }

    return [
        'ok' => empty($errors),
        'errors' => $errors,
        'value' => [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'comment' => $comment,
        ],
    ];
}

function generate_login(): string {
    return 'user_' . substr(bin2hex(random_bytes(8)), 0, 8);
}

function generate_password(): string {
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i = 0; $i < 8; $i++) {
        $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $out;
}

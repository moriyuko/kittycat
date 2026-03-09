<?php

declare(strict_types=1);

function h(?string $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function json_response(array $data, int $status = 200, array $headers = []): array {
    $headers['Content-Type'] = 'application/json; charset=UTF-8';
    return [
        'status' => $status,
        'headers' => $headers,
        'body' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];
}

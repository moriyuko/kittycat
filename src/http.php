<?php

declare(strict_types=1);

function build_request(): array {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (strtoupper($method) === 'POST' && isset($_POST['_method'])) {
        $override = strtoupper((string)$_POST['_method']);
        if (in_array($override, ['PUT', 'DELETE'], true)) {
            $method = $override;
        }
    }
    $path = '/';

    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
        $qpos = strpos($uri, '?');
        $path = $qpos === false ? $uri : substr($uri, 0, $qpos);
    }

    $headers = [];
    foreach ($_SERVER as $k => $v) {
        if (str_starts_with($k, 'HTTP_')) {
            $name = str_replace('_', '-', strtolower(substr($k, 5)));
            $headers[$name] = $v;
        }
    }

    $rawBody = file_get_contents('php://input');

    return [
        'method' => $method,
        'path' => $path,
        'query' => $_GET,
        'post' => $_POST,
        'headers' => $headers,
        'raw_body' => $rawBody,
        'cookies' => $_COOKIE,
    ];
}

function response_text(string $text, int $status = 200, array $headers = []): array {
    $headers['Content-Type'] = $headers['Content-Type'] ?? 'text/plain; charset=UTF-8';
    return ['status' => $status, 'headers' => $headers, 'body' => $text];
}

function response_html(string $html, int $status = 200, array $headers = []): array {
    $headers['Content-Type'] = $headers['Content-Type'] ?? 'text/html; charset=UTF-8';
    return ['status' => $status, 'headers' => $headers, 'body' => $html];
}

function response_redirect(string $location, int $status = 302, array $headers = []): array {
    $headers['Location'] = $location;
    return ['status' => $status, 'headers' => $headers, 'body' => ''];
}

function send_response(array $response): void {
    http_response_code($response['status'] ?? 200);

    $headers = $response['headers'] ?? [];
    foreach ($headers as $k => $v) {
        if (is_int($k)) {
            header($v);
        } else {
            header($k . ': ' . $v);
        }
    }

    echo (string)($response['body'] ?? '');
}

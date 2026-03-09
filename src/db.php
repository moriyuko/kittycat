<?php

declare(strict_types=1);

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $conf = app_config();
    $dbc = $conf['db'] ?? [];

    $host = (string)($dbc['host'] ?? 'localhost');
    $name = (string)($dbc['name'] ?? 'web_project');
    $user = (string)($dbc['user'] ?? 'root');
    $pass = (string)($dbc['pass'] ?? '');

    $dsn = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8mb4';

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}

<?php

declare(strict_types=1);

return [
    'storage_dir' => __DIR__ . '/../var',
    'profiles_file' => __DIR__ . '/../var/profiles.json',
    'sessions_dir' => __DIR__ . '/../var/sessions',
    'db' => [
        'host' => 'localhost',
        'name' => 'web_project',
        'user' => 'root',
        'pass' => '',
    ],
    'admin' => [
        'use_db' => true,
        'login' => 'admin',
        'password' => 'admin',
    ],
];

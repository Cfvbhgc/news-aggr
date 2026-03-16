<?php

declare(strict_types=1);

// Database connection configuration

return [
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'port'     => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'news_aggr',
    'username' => $_ENV['DB_USERNAME'] ?? 'news_aggr',
    'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
    'charset'  => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];

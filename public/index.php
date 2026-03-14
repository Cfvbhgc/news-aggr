<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Build DI container
$container = require __DIR__ . '/../config/container.php';

// Create Slim app with PHP-DI
$app = Bridge::create($container);

// Register middleware
$app->addBodyParsingMiddleware();

// CORS middleware
$app->add(new App\Middleware\CorsMiddleware());

// Rate limiting middleware
$app->add(new App\Middleware\RateLimitMiddleware());

// JSON response middleware
$app->add(new App\Middleware\JsonResponseMiddleware());

// Slim error middleware
$app->addErrorMiddleware(
    (bool)($_ENV['APP_DEBUG'] ?? false),
    true,
    true
);

// Register routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

$app->run();

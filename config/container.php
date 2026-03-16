<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    PDO::class => function (ContainerInterface $c): PDO {
        $config = require __DIR__ . '/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return $pdo;
    },

    App\Models\CategoryModel::class => function (ContainerInterface $c): App\Models\CategoryModel {
        return new App\Models\CategoryModel($c->get(PDO::class));
    },

    App\Models\FeedModel::class => function (ContainerInterface $c): App\Models\FeedModel {
        return new App\Models\FeedModel($c->get(PDO::class));
    },

    App\Models\ArticleModel::class => function (ContainerInterface $c): App\Models\ArticleModel {
        return new App\Models\ArticleModel($c->get(PDO::class));
    },

    App\Services\RssParserService::class => function (ContainerInterface $c): App\Services\RssParserService {
        return new App\Services\RssParserService(
            $c->get(App\Models\FeedModel::class),
            $c->get(App\Models\ArticleModel::class)
        );
    },

    App\Controllers\ArticleController::class => function (ContainerInterface $c): App\Controllers\ArticleController {
        return new App\Controllers\ArticleController($c->get(App\Models\ArticleModel::class));
    },

    App\Controllers\CategoryController::class => function (ContainerInterface $c): App\Controllers\CategoryController {
        return new App\Controllers\CategoryController($c->get(App\Models\CategoryModel::class));
    },

    App\Controllers\FeedController::class => function (ContainerInterface $c): App\Controllers\FeedController {
        return new App\Controllers\FeedController(
            $c->get(App\Models\FeedModel::class),
            $c->get(App\Services\RssParserService::class)
        );
    },
]);

return $containerBuilder->build();

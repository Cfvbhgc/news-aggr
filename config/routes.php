<?php

declare(strict_types=1);

use App\Controllers\ArticleController;
use App\Controllers\CategoryController;
use App\Controllers\FeedController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Articles
        $group->get('/articles', [ArticleController::class, 'index']);
        $group->get('/articles/{id}', [ArticleController::class, 'show']);

        // Categories
        $group->get('/categories', [CategoryController::class, 'index']);

        // Feeds
        $group->get('/feeds', [FeedController::class, 'index']);
        $group->post('/feeds', [FeedController::class, 'create']);
        $group->put('/feeds/{id}', [FeedController::class, 'update']);
        $group->delete('/feeds/{id}', [FeedController::class, 'delete']);
        $group->post('/feeds/{id}/fetch', [FeedController::class, 'fetch']);
    });
};

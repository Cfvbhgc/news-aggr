<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ArticleModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ArticleController
{
    public function __construct(
        private readonly ArticleModel $articleModel
    ) {}

    /**
     * GET /api/articles
     * List articles with pagination and filters.
     */
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $filters = [
            'page'        => $params['page'] ?? 1,
            'per_page'    => $params['per_page'] ?? 20,
            'category_id' => $params['category_id'] ?? null,
            'feed_id'     => $params['feed_id'] ?? null,
            'search'      => $params['search'] ?? null,
            'date_from'   => $params['date_from'] ?? null,
            'date_to'     => $params['date_to'] ?? null,
        ];

        $result = $this->articleModel->findAll(array_filter($filters));

        $response->getBody()->write(json_encode($result));
        return $response->withStatus(200);
    }

    /**
     * GET /api/articles/{id}
     * Get a single article by ID.
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $article = $this->articleModel->findById($id);

        if ($article === null) {
            $response->getBody()->write(json_encode([
                'error' => 'Article not found',
            ]));
            return $response->withStatus(404);
        }

        $response->getBody()->write(json_encode(['data' => $article]));
        return $response->withStatus(200);
    }
}

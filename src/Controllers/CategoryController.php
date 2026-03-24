<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CategoryModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryController
{
    public function __construct(
        private readonly CategoryModel $categoryModel
    ) {}

    /**
     * GET /api/categories
     * List all categories with article counts.
     */
    public function index(Request $request, Response $response): Response
    {
        $categories = $this->categoryModel->findAll();

        $response->getBody()->write(json_encode(['data' => $categories]));
        return $response->withStatus(200);
    }
}

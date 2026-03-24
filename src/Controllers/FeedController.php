<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\FeedModel;
use App\Services\RssParserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FeedController
{
    public function __construct(
        private readonly FeedModel $feedModel,
        private readonly RssParserService $rssParserService
    ) {}

    /**
     * GET /api/feeds
     * List all feeds.
     */
    public function index(Request $request, Response $response): Response
    {
        $feeds = $this->feedModel->findAll();

        $response->getBody()->write(json_encode(['data' => $feeds]));
        return $response->withStatus(200);
    }

    /**
     * POST /api/feeds
     * Create a new feed.
     */
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validate required fields
        $errors = $this->validateFeed($data);
        if (!empty($errors)) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withStatus(422);
        }

        $id = $this->feedModel->create([
            'name'        => $data['name'],
            'url'         => $data['url'],
            'category_id' => (int) $data['category_id'],
            'is_active'   => (int) ($data['is_active'] ?? 1),
        ]);

        $feed = $this->feedModel->findById($id);

        $response->getBody()->write(json_encode(['data' => $feed]));
        return $response->withStatus(201);
    }

    /**
     * PUT /api/feeds/{id}
     * Update an existing feed.
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $feed = $this->feedModel->findById($id);

        if ($feed === null) {
            $response->getBody()->write(json_encode(['error' => 'Feed not found']));
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();
        $this->feedModel->update($id, $data);

        $updatedFeed = $this->feedModel->findById($id);

        $response->getBody()->write(json_encode(['data' => $updatedFeed]));
        return $response->withStatus(200);
    }

    /**
     * DELETE /api/feeds/{id}
     * Delete a feed and its articles.
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $feed = $this->feedModel->findById($id);

        if ($feed === null) {
            $response->getBody()->write(json_encode(['error' => 'Feed not found']));
            return $response->withStatus(404);
        }

        $this->feedModel->delete($id);

        $response->getBody()->write(json_encode(['message' => 'Feed deleted successfully']));
        return $response->withStatus(200);
    }

    /**
     * POST /api/feeds/{id}/fetch
     * Trigger a manual fetch for a specific feed.
     */
    public function fetch(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $feed = $this->feedModel->findById($id);

        if ($feed === null) {
            $response->getBody()->write(json_encode(['error' => 'Feed not found']));
            return $response->withStatus(404);
        }

        try {
            $result = $this->rssParserService->fetchFeed($feed);

            $response->getBody()->write(json_encode([
                'message'      => 'Feed fetched successfully',
                'new_articles' => $result['new'],
                'skipped'      => $result['skipped'],
            ]));
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error'   => 'Failed to fetch feed',
                'details' => $e->getMessage(),
            ]));
            return $response->withStatus(500);
        }
    }

    /**
     * Validate feed input data.
     */
    private function validateFeed(?array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Field "name" is required.';
        }

        if (empty($data['url'])) {
            $errors[] = 'Field "url" is required.';
        } elseif (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Field "url" must be a valid URL.';
        }

        if (empty($data['category_id'])) {
            $errors[] = 'Field "category_id" is required.';
        }

        return $errors;
    }
}

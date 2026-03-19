<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class ArticleModel
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    /**
     * Get paginated list of articles with optional filters.
     *
     * Supported filters:
     * - category_id: filter by category
     * - feed_id: filter by feed
     * - search: full-text search in title and content
     * - date_from: articles published after this date
     * - date_to: articles published before this date
     * - page: page number (default 1)
     * - per_page: items per page (default 20, max 100)
     */
    public function findAll(array $filters = []): array
    {
        $page    = max(1, (int)($filters['page'] ?? 1));
        $perPage = min(100, max(1, (int)($filters['per_page'] ?? 20)));
        $offset  = ($page - 1) * $perPage;

        $where  = [];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'a.category_id = :category_id';
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['feed_id'])) {
            $where[] = 'a.feed_id = :feed_id';
            $params['feed_id'] = (int)$filters['feed_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'a.published_at >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'a.published_at <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $where[] = 'MATCH(a.title, a.content) AGAINST(:search IN BOOLEAN MODE)';
            $params['search'] = $filters['search'];
        }

        $whereClause = '';
        if (!empty($where)) {
            $whereClause = 'WHERE ' . implode(' AND ', $where);
        }

        // Count total
        $countSql = "SELECT COUNT(*) FROM articles a {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch articles
        $sql = "SELECT a.*, f.name AS feed_name, c.name AS category_name, c.slug AS category_slug
                FROM articles a
                LEFT JOIN feeds f ON f.id = a.feed_id
                LEFT JOIN categories c ON c.id = a.category_id
                {$whereClause}
                ORDER BY a.published_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $articles = $stmt->fetchAll();

        return [
            'data' => $articles,
            'pagination' => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'pages'    => (int)ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Find article by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, f.name AS feed_name, c.name AS category_name, c.slug AS category_slug
             FROM articles a
             LEFT JOIN feeds f ON f.id = a.feed_id
             LEFT JOIN categories c ON c.id = a.category_id
             WHERE a.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Check if article with given URL already exists.
     */
    public function existsByUrl(string $url): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM articles WHERE url = :url');
        $stmt->execute(['url' => $url]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Create a new article.
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO articles (feed_id, title, content, url, author, published_at, category_id, image_url)
             VALUES (:feed_id, :title, :content, :url, :author, :published_at, :category_id, :image_url)'
        );

        $stmt->execute([
            'feed_id'      => $data['feed_id'],
            'title'        => $data['title'],
            'content'      => $data['content'] ?? null,
            'url'          => $data['url'],
            'author'       => $data['author'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'category_id'  => $data['category_id'],
            'image_url'    => $data['image_url'] ?? null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}

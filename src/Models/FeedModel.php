<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class FeedModel
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    /**
     * Get all feeds with category info.
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT f.*, c.name AS category_name, c.slug AS category_slug,
                    COUNT(a.id) AS article_count
             FROM feeds f
             LEFT JOIN categories c ON c.id = f.category_id
             LEFT JOIN articles a ON a.feed_id = f.id
             GROUP BY f.id
             ORDER BY f.name ASC'
        );

        return $stmt->fetchAll();
    }

    /**
     * Get all active feeds (for RSS parsing).
     */
    public function findActive(): array
    {
        $stmt = $this->pdo->query(
            'SELECT f.*, c.name AS category_name
             FROM feeds f
             LEFT JOIN categories c ON c.id = f.category_id
             WHERE f.is_active = 1
             ORDER BY f.last_fetched_at ASC'
        );

        return $stmt->fetchAll();
    }

    /**
     * Find feed by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, c.name AS category_name, c.slug AS category_slug
             FROM feeds f
             LEFT JOIN categories c ON c.id = f.category_id
             WHERE f.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Create a new feed.
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO feeds (name, url, category_id, is_active)
             VALUES (:name, :url, :category_id, :is_active)'
        );

        $stmt->execute([
            'name'        => $data['name'],
            'url'         => $data['url'],
            'category_id' => $data['category_id'],
            'is_active'   => $data['is_active'] ?? 1,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update an existing feed.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['name', 'url', 'category_id', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE feeds SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Delete a feed.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM feeds WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Update the last_fetched_at timestamp.
     */
    public function updateLastFetched(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE feeds SET last_fetched_at = NOW() WHERE id = :id'
        );
        return $stmt->execute(['id' => $id]);
    }
}

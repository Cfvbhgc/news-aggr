<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class CategoryModel
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    /**
     * Get all categories.
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT c.*, COUNT(a.id) AS article_count
             FROM categories c
             LEFT JOIN articles a ON a.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name ASC'
        );

        return $stmt->fetchAll();
    }

    /**
     * Find category by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Find category by slug.
     */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch();

        return $result ?: null;
    }
}

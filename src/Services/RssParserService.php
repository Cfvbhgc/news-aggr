<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ArticleModel;
use App\Models\FeedModel;
use Laminas\Feed\Reader\Reader;
use Laminas\Feed\Reader\Entry\EntryInterface;

class RssParserService
{
    public function __construct(
        private readonly FeedModel $feedModel,
        private readonly ArticleModel $articleModel
    ) {}

    /**
     * Fetch and parse all active feeds.
     *
     * @return array Summary of fetch results per feed.
     */
    public function fetchAllActive(): array
    {
        $feeds   = $this->feedModel->findActive();
        $results = [];

        foreach ($feeds as $feed) {
            try {
                $result = $this->fetchFeed($feed);
                $results[] = [
                    'feed_id'   => $feed['id'],
                    'feed_name' => $feed['name'],
                    'status'    => 'success',
                    'new'       => $result['new'],
                    'skipped'   => $result['skipped'],
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'feed_id'   => $feed['id'],
                    'feed_name' => $feed['name'],
                    'status'    => 'error',
                    'error'     => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Fetch and parse a single feed.
     *
     * @param array $feed Feed data from database.
     * @return array Number of new and skipped articles.
     */
    public function fetchFeed(array $feed): array
    {
        $rss = Reader::import($feed['url']);

        $newCount     = 0;
        $skippedCount = 0;

        foreach ($rss as $entry) {
            /** @var EntryInterface $entry */
            $articleUrl = $entry->getLink();

            if (empty($articleUrl)) {
                $skippedCount++;
                continue;
            }

            // Skip if article already exists
            if ($this->articleModel->existsByUrl($articleUrl)) {
                $skippedCount++;
                continue;
            }

            $publishedDate = $entry->getDateCreated() ?? $entry->getDateModified();

            $this->articleModel->create([
                'feed_id'      => $feed['id'],
                'title'        => $this->sanitizeText($entry->getTitle() ?? 'Untitled'),
                'content'      => $this->sanitizeText($entry->getDescription() ?? ''),
                'url'          => $articleUrl,
                'author'       => $this->extractAuthor($entry),
                'published_at' => $publishedDate?->format('Y-m-d H:i:s'),
                'category_id'  => $feed['category_id'],
                'image_url'    => $this->extractImage($entry),
            ]);

            $newCount++;
        }

        // Update last fetched timestamp
        $this->feedModel->updateLastFetched((int) $feed['id']);

        return [
            'new'     => $newCount,
            'skipped' => $skippedCount,
        ];
    }

    /**
     * Extract author name from entry.
     */
    private function extractAuthor(EntryInterface $entry): ?string
    {
        $author = $entry->getAuthor();
        if ($author && isset($author['name'])) {
            return $author['name'];
        }

        $authors = $entry->getAuthors();
        if ($authors && count($authors) > 0) {
            $first = $authors->current();
            return $first['name'] ?? null;
        }

        return null;
    }

    /**
     * Try to extract image URL from entry content.
     */
    private function extractImage(EntryInterface $entry): ?string
    {
        $content = $entry->getContent() ?? $entry->getDescription() ?? '';

        // Try to find img tag
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
            return $matches[1];
        }

        // Try media:content or enclosure
        $enclosure = $entry->getEnclosure();
        if ($enclosure && isset($enclosure->url) && str_starts_with($enclosure->type ?? '', 'image/')) {
            return $enclosure->url;
        }

        return null;
    }

    /**
     * Clean HTML tags and decode entities.
     */
    private function sanitizeText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim($text);

        return $text;
    }
}

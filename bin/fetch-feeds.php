#!/usr/bin/env php
<?php

/**
 * CLI script for fetching RSS feeds.
 *
 * Run manually:   php bin/fetch-feeds.php
 * Via composer:    composer fetch-feeds
 * Via cron:        */30 * * * * cd /var/www/html && php bin/fetch-feeds.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Build container
$container = require __DIR__ . '/../config/container.php';

/** @var \App\Services\RssParserService $parser */
$parser = $container->get(\App\Services\RssParserService::class);

echo "[" . date('Y-m-d H:i:s') . "] Starting RSS feed fetch...\n";

$results = $parser->fetchAllActive();

$totalNew = 0;
$totalSkipped = 0;
$errors = 0;

foreach ($results as $result) {
    if ($result['status'] === 'success') {
        $totalNew += $result['new'];
        $totalSkipped += $result['skipped'];
        echo "  [OK]    {$result['feed_name']}: {$result['new']} new, {$result['skipped']} skipped\n";
    } else {
        $errors++;
        echo "  [ERROR] {$result['feed_name']}: {$result['error']}\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Done. "
    . "New: {$totalNew}, Skipped: {$totalSkipped}, Errors: {$errors}\n";

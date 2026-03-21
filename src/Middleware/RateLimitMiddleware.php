<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Simple in-memory rate limiting middleware.
 *
 * Uses a file-based storage to track request counts per IP.
 * For production, use Redis or a dedicated rate limiter.
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private const STORAGE_DIR = '/tmp/rate_limit';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $maxRequests = (int) ($_ENV['RATE_LIMIT_MAX_REQUESTS'] ?? 60);
        $window      = (int) ($_ENV['RATE_LIMIT_WINDOW_SECONDS'] ?? 60);

        $ip = $this->getClientIp($request);
        $key = md5($ip);

        if (!is_dir(self::STORAGE_DIR)) {
            @mkdir(self::STORAGE_DIR, 0777, true);
        }

        $file = self::STORAGE_DIR . '/' . $key;
        $data = $this->loadData($file);

        $now = time();

        // Reset window if expired
        if ($data === null || ($now - $data['start']) >= $window) {
            $data = ['start' => $now, 'count' => 0];
        }

        $data['count']++;
        $this->saveData($file, $data);

        $remaining = max(0, $maxRequests - $data['count']);
        $resetAt   = $data['start'] + $window;

        // Rate limit exceeded
        if ($data['count'] > $maxRequests) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error'   => 'Too many requests',
                'retry_after' => $resetAt - $now,
            ]));

            return $response
                ->withStatus(429)
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('X-RateLimit-Limit', (string) $maxRequests)
                ->withHeader('X-RateLimit-Remaining', '0')
                ->withHeader('X-RateLimit-Reset', (string) $resetAt)
                ->withHeader('Retry-After', (string) ($resetAt - $now));
        }

        $response = $handler->handle($request);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) $remaining)
            ->withHeader('X-RateLimit-Reset', (string) $resetAt);
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        // Check common proxy headers
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($serverParams[$header])) {
                // Take first IP from comma-separated list
                $ip = explode(',', $serverParams[$header])[0];
                return trim($ip);
            }
        }

        return '127.0.0.1';
    }

    private function loadData(string $file): ?array
    {
        if (!file_exists($file)) {
            return null;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return null;
        }

        return json_decode($content, true);
    }

    private function saveData(string $file, array $data): void
    {
        @file_put_contents($file, json_encode($data), LOCK_EX);
    }
}

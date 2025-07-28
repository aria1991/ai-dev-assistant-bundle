<?php

declare(strict_types=1);

/*
 * This file is part of the AI Development Assistant Bundle.
 *
 * (c) Aria Vahidi <aria.vahidi2020@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aria1991\AIDevAssistantBundle\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Rate limiter service.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class RateLimiter
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Check if request is allowed for the given identifier.
     *
     * @param string $identifier    Request identifier (e.g., user ID, IP)
     * @param int    $maxRequests   Maximum requests allowed
     * @param int    $windowSeconds Time window in seconds
     *
     * @return bool True if request is allowed
     */
    public function isAllowed(string $identifier, int $maxRequests, int $windowSeconds): bool
    {
        $key = $this->getCacheKey($identifier, $windowSeconds);

        try {
            $item = $this->cache->getItem($key);

            if (!$item->isHit()) {
                // First request in window
                $item->set(1);
                $item->expiresAfter($windowSeconds);
                $this->cache->save($item);

                $this->logger->debug('Rate limit: First request', [
                    'identifier' => $identifier,
                    'window' => $windowSeconds,
                ]);

                return true;
            }

            $currentCount = $item->get();

            if ($currentCount >= $maxRequests) {
                $this->logger->warning('Rate limit exceeded', [
                    'identifier' => $identifier,
                    'current_count' => $currentCount,
                    'max_requests' => $maxRequests,
                    'window' => $windowSeconds,
                ]);

                return false;
            }

            // Increment counter
            $item->set($currentCount + 1);
            $this->cache->save($item);

            $this->logger->debug('Rate limit: Request allowed', [
                'identifier' => $identifier,
                'count' => $currentCount + 1,
                'max_requests' => $maxRequests,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Rate limiter error', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);

            // Allow request on error to avoid blocking legitimate requests
            return true;
        }
    }

    /**
     * Get current request count for identifier.
     *
     * @param string $identifier    Request identifier
     * @param int    $windowSeconds Time window in seconds
     *
     * @return int Current request count
     */
    public function getCurrentCount(string $identifier, int $windowSeconds): int
    {
        $key = $this->getCacheKey($identifier, $windowSeconds);

        try {
            $item = $this->cache->getItem($key);

            return $item->isHit() ? $item->get() : 0;
        } catch (\Exception $e) {
            $this->logger->error('Rate limiter get count error', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Reset rate limit for identifier.
     *
     * @param string $identifier    Request identifier
     * @param int    $windowSeconds Time window in seconds
     *
     * @return bool True on success
     */
    public function reset(string $identifier, int $windowSeconds): bool
    {
        $key = $this->getCacheKey($identifier, $windowSeconds);

        try {
            $result = $this->cache->deleteItem($key);

            if ($result) {
                $this->logger->info('Rate limit reset', [
                    'identifier' => $identifier,
                    'window' => $windowSeconds,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Rate limiter reset error', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function getCacheKey(string $identifier, int $windowSeconds): string
    {
        $windowStart = floor(time() / $windowSeconds) * $windowSeconds;

        return \sprintf('rate_limit_%s_%d', hash('sha256', $identifier), $windowStart);
    }
}

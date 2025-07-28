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
 * Cache service for analysis results.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class CacheService
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly bool $enabled = true,
        private readonly int $ttl = 3600
    ) {
    }

    /**
     * Get item from cache.
     *
     * @param string $key Cache key
     * @return mixed|null Cached value or null if not found
     */
    public function get(string $key): mixed
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $item = $this->cache->getItem($key);
            if ($item->isHit()) {
                $this->logger->debug('Cache hit', ['key' => $key]);
                return $item->get();
            }
        } catch (\Exception $e) {
            $this->logger->warning('Cache get failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Set item in cache.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live (null = default)
     * @return bool True on success
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $item = $this->cache->getItem($key);
            $item->set($value);
            $item->expiresAfter($ttl ?? $this->ttl);
            
            $result = $this->cache->save($item);
            
            if ($result) {
                $this->logger->debug('Cache set', ['key' => $key, 'ttl' => $ttl ?? $this->ttl]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->warning('Cache set failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete item from cache.
     *
     * @param string $key Cache key
     * @return bool True on success
     */
    public function delete(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $result = $this->cache->deleteItem($key);
            
            if ($result) {
                $this->logger->debug('Cache delete', ['key' => $key]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->warning('Cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all cache items.
     *
     * @return bool True on success
     */
    public function clear(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $result = $this->cache->clear();
            
            if ($result) {
                $this->logger->info('Cache cleared');
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->warning('Cache clear failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if cache is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}

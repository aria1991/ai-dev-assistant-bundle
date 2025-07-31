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

/**
 * Interface for cache service implementations.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
interface CacheServiceInterface
{
    /**
     * Get item from cache.
     *
     * @param string $key Cache key
     *
     * @return mixed|null Cached value or null if not found
     */
    public function get(string $key): mixed;

    /**
     * Store item in cache.
     *
     * @param string   $key   Cache key
     * @param mixed    $value Value to cache
     * @param int|null $ttl   Time to live in seconds (null for default)
     *
     * @return bool True on success
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Delete item from cache.
     *
     * @param string $key Cache key
     *
     * @return bool True on success
     */
    public function delete(string $key): bool;

    /**
     * Clear all cache items.
     *
     * @return bool True on success
     */
    public function clear(): bool;

    /**
     * Check if cache is enabled.
     *
     * @return bool True if cache is enabled
     */
    public function isEnabled(): bool;
}

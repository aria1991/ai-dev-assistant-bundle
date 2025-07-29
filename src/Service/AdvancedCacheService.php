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
 * Advanced caching service with semantic similarity detection.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AdvancedCacheService
{
    private array $metrics = [
        'hits' => 0,
        'misses' => 0,
        'semantic_hits' => 0,
    ];

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly bool $enabled = true,
        private readonly int $ttl = 3600,
        private readonly float $semanticThreshold = 0.85,
    ) {
    }

    /**
     * Get cached analysis result with semantic similarity fallback.
     */
    public function getAnalysisResult(string $code, string $analyzer): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $primaryKey = $this->generateCacheKey($code, $analyzer);
        $item = $this->cache->getItem($primaryKey);

        if ($item->isHit()) {
            ++$this->metrics['hits'];
            $this->logger->debug('Cache hit for analysis', ['key' => $primaryKey]);

            return $item->get();
        }

        // Try semantic similarity search
        $semanticResult = $this->findSimilarCachedResult($code, $analyzer);
        if ($semanticResult !== null) {
            ++$this->metrics['semantic_hits'];
            $this->logger->debug('Semantic cache hit for analysis', ['similarity' => $semanticResult['similarity']]);

            // Store exact match for future use
            $this->storeAnalysisResult($code, $analyzer, $semanticResult['result']);

            return $semanticResult['result'];
        }

        ++$this->metrics['misses'];

        return null;
    }

    /**
     * Store analysis result with metadata.
     */
    public function storeAnalysisResult(string $code, string $analyzer, array $result): void
    {
        if (!$this->enabled) {
            return;
        }

        $key = $this->generateCacheKey($code, $analyzer);
        $item = $this->cache->getItem($key);

        $cacheData = [
            'result' => $result,
            'metadata' => [
                'cached_at' => time(),
                'code_hash' => $this->generateCodeHash($code),
                'code_signature' => $this->extractCodeSignature($code),
                'analyzer' => $analyzer,
            ],
        ];

        $item->set($cacheData);
        $item->expiresAfter($this->ttl);

        $this->cache->save($item);
        $this->logger->debug('Cached analysis result', ['key' => $key]);
    }

    /**
     * Find similar cached results using code signatures.
     */
    private function findSimilarCachedResult(string $code, string $analyzer): ?array
    {
        $codeSignature = $this->extractCodeSignature($code);
        $searchPattern = "cache_analysis_{$analyzer}_*";

        // This is a simplified implementation
        // In production, you'd use a more sophisticated similarity search
        foreach ($this->getAllCacheKeys($searchPattern) as $key) {
            $item = $this->cache->getItem($key);
            if (!$item->isHit()) {
                continue;
            }

            $cached = $item->get();
            if (!isset($cached['metadata']['code_signature'])) {
                continue;
            }

            $similarity = $this->calculateSimilarity(
                $codeSignature,
                $cached['metadata']['code_signature']
            );

            if ($similarity >= $this->semanticThreshold) {
                return [
                    'result' => $cached['result'],
                    'similarity' => $similarity,
                ];
            }
        }

        return null;
    }

    /**
     * Generate cache key for code analysis.
     */
    private function generateCacheKey(string $code, string $analyzer): string
    {
        $hash = $this->generateCodeHash($code);

        return "cache_analysis_{$analyzer}_{$hash}";
    }

    /**
     * Generate hash for code content.
     */
    private function generateCodeHash(string $code): string
    {
        // Normalize code by removing comments and whitespace variations
        $normalized = preg_replace('/\/\*[\s\S]*?\*\/|\/\/.*$/m', '', $code) ?? $code;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return hash('sha256', trim($normalized) ?: '');
    }

    /**
     * Extract code signature for similarity comparison.
     */
    private function extractCodeSignature(string $code): array
    {
        $signature = [];

        // Extract function/method names
        preg_match_all('/function\s+(\w+)\s*\(/i', $code, $functions);
        $signature['functions'] = array_unique($functions[1]);

        // Extract class names
        preg_match_all('/class\s+(\w+)/i', $code, $classes);
        $signature['classes'] = array_unique($classes[1]);

        // Extract variable names
        preg_match_all('/\$(\w+)/i', $code, $variables);
        $signature['variables'] = array_unique(\array_slice($variables[1], 0, 20)); // Limit to avoid huge arrays

        // Code metrics
        $signature['metrics'] = [
            'lines' => substr_count($code, "\n") + 1,
            'chars' => \strlen($code),
            'complexity_estimate' => substr_count($code, 'if') + substr_count($code, 'for') + substr_count($code, 'while'),
        ];

        return $signature;
    }

    /**
     * Calculate similarity between two code signatures.
     */
    private function calculateSimilarity(array $sig1, array $sig2): float
    {
        $weights = [
            'functions' => 0.4,
            'classes' => 0.3,
            'variables' => 0.2,
            'metrics' => 0.1,
        ];

        $similarity = 0.0;

        foreach ($weights as $component => $weight) {
            $componentSimilarity = 0.0;
            
            if (\in_array($component, ['functions', 'classes', 'variables'], true)) {
                $componentSimilarity = $this->calculateArraySimilarity(
                    $sig1[$component] ?? [],
                    $sig2[$component] ?? []
                );
            } elseif ($component === 'metrics') {
                $componentSimilarity = $this->calculateMetricsSimilarity(
                    $sig1[$component] ?? [],
                    $sig2[$component] ?? []
                );
            }

            $similarity += $componentSimilarity * $weight;
        }

        return $similarity;
    }

    /**
     * Calculate similarity between two arrays.
     */
    private function calculateArraySimilarity(array $arr1, array $arr2): float
    {
        if (empty($arr1) && empty($arr2)) {
            return 1.0;
        }

        if (empty($arr1) || empty($arr2)) {
            return 0.0;
        }

        $intersection = \count(array_intersect($arr1, $arr2));
        $union = \count(array_unique(array_merge($arr1, $arr2)));

        return $union === 0 ? 0.0 : $intersection / $union;
    }

    /**
     * Calculate similarity between metrics.
     */
    private function calculateMetricsSimilarity(array $metrics1, array $metrics2): float
    {
        if (empty($metrics1) || empty($metrics2)) {
            return 0.0;
        }

        $similarity = 0.0;
        $count = 0;

        foreach (['lines', 'chars', 'complexity_estimate'] as $metric) {
            if (isset($metrics1[$metric]) && isset($metrics2[$metric])) {
                $val1 = $metrics1[$metric];
                $val2 = $metrics2[$metric];

                if ($val1 == 0 && $val2 == 0) {
                    $similarity += 1.0;
                } elseif ($val1 > 0 && $val2 > 0) {
                    $similarity += 1.0 - abs($val1 - $val2) / max($val1, $val2);
                }
                ++$count;
            }
        }

        return $count > 0 ? $similarity / $count : 0.0;
    }

    /**
     * Get cache metrics.
     */
    public function getMetrics(): array
    {
        $total = $this->metrics['hits'] + $this->metrics['misses'];
        $hitRate = $total > 0 ? (float) (($this->metrics['hits'] / $total) * 100) : 0.0;

        return [
            'enabled' => $this->enabled,
            'hits' => $this->metrics['hits'],
            'misses' => $this->metrics['misses'],
            'semantic_hits' => $this->metrics['semantic_hits'],
            'hit_rate' => round($hitRate, 2),
            'total_requests' => $total,
        ];
    }

    /**
     * Clear all analysis cache.
     */
    public function clearAnalysisCache(): bool
    {
        return $this->cache->clear();
    }

    /**
     * Get all cache keys matching pattern (simplified implementation).
     */
    private function getAllCacheKeys(string $pattern): array
    {
        // This is a simplified implementation
        // In practice, you'd need to implement this based on your cache backend
        // Redis: SCAN command, APCu: apcu_cache_info(), etc.
        return [];
    }
}

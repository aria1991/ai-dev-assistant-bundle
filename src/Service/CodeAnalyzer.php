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

use Aria1991\AIDevAssistantBundle\Service\Analyzer\AnalyzerInterface;
use Psr\Log\LoggerInterface;

/**
 * Main code analyzer that coordinates all analyzers.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class CodeAnalyzer
{
    /**
     * @param AnalyzerInterface[] $analyzers
     */
    public function __construct(
        private readonly array $analyzers,
        private readonly LoggerInterface $logger,
        private readonly CacheService $cacheService,
    ) {
    }

    /**
     * Analyze code with all available analyzers.
     *
     * @param string $code             The code to analyze
     * @param string $filename         The filename (optional, for context)
     * @param array  $enabledAnalyzers List of analyzer names to run (null = all)
     *
     * @return array Complete analysis results
     */
    public function analyzeCode(string $code, string $filename = '', ?array $enabledAnalyzers = null): array
    {
        $cacheKey = $this->generateCacheKey($code, $filename, $enabledAnalyzers);

        // Try to get from cache first
        if ($cached = $this->cacheService->get($cacheKey)) {
            $this->logger->debug('Using cached analysis result', ['filename' => $filename]);

            return $cached;
        }

        $results = [
            'filename' => $filename,
            'analysis_timestamp' => new \DateTimeImmutable(),
            'analyzers' => [],
            'summary' => [
                'total_issues' => 0,
                'critical_issues' => 0,
                'high_issues' => 0,
                'medium_issues' => 0,
                'low_issues' => 0,
            ],
        ];

        foreach ($this->analyzers as $analyzer) {
            // Skip analyzer if not in enabled list
            if ($enabledAnalyzers !== null && !\in_array($analyzer->getName(), $enabledAnalyzers, true)) {
                continue;
            }

            try {
                $this->logger->info('Running analyzer', [
                    'analyzer' => $analyzer->getName(),
                    'filename' => $filename,
                ]);

                $analyzerResult = $analyzer->analyze($code, $filename);
                $results['analyzers'][$analyzer->getName()] = $analyzerResult;

                // Update summary counts
                if (isset($analyzerResult['issues'])) {
                    $this->updateSummary($results['summary'], $analyzerResult['issues']);
                }
            } catch (\Exception $e) {
                $this->logger->error('Analyzer failed', [
                    'analyzer' => $analyzer->getName(),
                    'error' => $e->getMessage(),
                    'filename' => $filename,
                ]);

                $results['analyzers'][$analyzer->getName()] = [
                    'error' => 'Analyzer failed: ' . $e->getMessage(),
                    'issues' => [],
                ];
            }
        }

        // Calculate overall risk score
        $results['risk_score'] = $this->calculateRiskScore($results['summary']);

        // Cache the results
        $this->cacheService->set($cacheKey, $results);

        return $results;
    }

    /**
     * Analyze a file.
     *
     * @param string $filePath         Path to the file to analyze
     * @param array  $enabledAnalyzers List of analyzer names to run (null = all)
     *
     * @return array Analysis results
     */
    public function analyzeFile(string $filePath, ?array $enabledAnalyzers = null): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("File not readable: {$filePath}");
        }

        $code = file_get_contents($filePath);
        if ($code === false) {
            throw new \RuntimeException("Failed to read file: {$filePath}");
        }

        return $this->analyzeCode($code, $filePath, $enabledAnalyzers);
    }

    /**
     * Get available analyzers.
     *
     * @return AnalyzerInterface[]
     */
    public function getAnalyzers(): array
    {
        return $this->analyzers;
    }

    /**
     * Get analyzer names.
     *
     * @return string[]
     */
    public function getAnalyzerNames(): array
    {
        return array_map(fn ($analyzer) => $analyzer->getName(), $this->analyzers);
    }

    private function generateCacheKey(string $code, string $filename, ?array $enabledAnalyzers): string
    {
        $data = [
            'code_hash' => hash('sha256', $code),
            'filename' => $filename,
            'analyzers' => $enabledAnalyzers ?? $this->getAnalyzerNames(),
        ];

        return 'code_analysis_' . hash('sha256', serialize($data));
    }

    private function updateSummary(array &$summary, array $issues): void
    {
        $summary['total_issues'] += \count($issues);

        foreach ($issues as $issue) {
            $severity = $issue['severity'] ?? 'unknown';
            switch (strtolower($severity)) {
                case 'critical':
                    $summary['critical_issues']++;
                    break;
                case 'high':
                    $summary['high_issues']++;
                    break;
                case 'medium':
                    $summary['medium_issues']++;
                    break;
                case 'low':
                    $summary['low_issues']++;
                    break;
            }
        }
    }

    private function calculateRiskScore(array $summary): string
    {
        $score = 0;
        $score += $summary['critical_issues'] * 10;
        $score += $summary['high_issues'] * 7;
        $score += $summary['medium_issues'] * 4;
        $score += $summary['low_issues'] * 1;

        if ($score === 0) {
            return 'very_low';
        } elseif ($score <= 5) {
            return 'low';
        } elseif ($score <= 15) {
            return 'medium';
        } elseif ($score <= 30) {
            return 'high';
        } else {
            return 'critical';
        }
    }
}

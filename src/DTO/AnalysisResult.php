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

namespace Aria1991\AIDevAssistantBundle\DTO;

/**
 * Data transfer object for analysis results.
 *
 * This immutable DTO encapsulates the complete analysis results
 * with metadata, providing a consistent structure for all analysis outputs.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final readonly class AnalysisResult
{
    /**
     * @param string $filename      The analyzed filename
     * @param array  $results       Results from each analyzer
     * @param array  $metadata      Analysis metadata (timing, tokens, etc.)
     * @param array  $errors        Any errors that occurred during analysis
     * @param bool   $successful    Whether the analysis completed successfully
     * @param float  $executionTime Total execution time in seconds
     */
    public function __construct(
        public string $filename,
        public array $results = [],
        public array $metadata = [],
        public array $errors = [],
        public bool $successful = true,
        public float $executionTime = 0.0,
    ) {
    }

    /**
     * Create a successful result.
     */
    public static function success(
        string $filename,
        array $results,
        array $metadata = [],
        float $executionTime = 0.0,
    ): self {
        return new self(
            $filename,
            $results,
            $metadata,
            [],
            true,
            $executionTime
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(
        string $filename,
        array $errors,
        array $partialResults = [],
        float $executionTime = 0.0,
    ): self {
        return new self(
            $filename,
            $partialResults,
            [],
            $errors,
            false,
            $executionTime
        );
    }

    /**
     * Get results from a specific analyzer.
     */
    public function getAnalyzerResult(string $analyzer): ?array
    {
        return $this->results[$analyzer] ?? null;
    }

    /**
     * Check if a specific analyzer produced results.
     */
    public function hasAnalyzerResult(string $analyzer): bool
    {
        return isset($this->results[$analyzer]);
    }

    /**
     * Get all analyzer names that produced results.
     *
     * @return string[]
     */
    public function getAnalyzerNames(): array
    {
        return array_keys($this->results);
    }

    /**
     * Get total number of issues found across all analyzers.
     */
    public function getTotalIssuesCount(): int
    {
        $total = 0;
        foreach ($this->results as $result) {
            if (isset($result['issues']) && \is_array($result['issues'])) {
                $total += \count($result['issues']);
            }
        }

        return $total;
    }

    /**
     * Get issues by severity level.
     */
    public function getIssuesBySeverity(string $severity): array
    {
        $issues = [];
        foreach ($this->results as $analyzerName => $result) {
            if (isset($result['issues']) && \is_array($result['issues'])) {
                foreach ($result['issues'] as $issue) {
                    if (($issue['severity'] ?? '') === $severity) {
                        $issue['analyzer'] = $analyzerName;
                        $issues[] = $issue;
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * Get all critical/error issues.
     */
    public function getCriticalIssues(): array
    {
        return $this->getIssuesBySeverity('error');
    }

    /**
     * Get all warning issues.
     */
    public function getWarningIssues(): array
    {
        return $this->getIssuesBySeverity('warning');
    }

    /**
     * Get all info issues.
     */
    public function getInfoIssues(): array
    {
        return $this->getIssuesBySeverity('info');
    }

    /**
     * Check if there are any critical issues.
     */
    public function hasCriticalIssues(): bool
    {
        return !empty($this->getCriticalIssues());
    }

    /**
     * Get metadata value with default fallback.
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Get total tokens used in analysis.
     */
    public function getTotalTokensUsed(): int
    {
        return $this->getMetadata('total_tokens', 0);
    }

    /**
     * Check if analysis was cached.
     */
    public function wasCached(): bool
    {
        return $this->getMetadata('cached', false);
    }

    /**
     * Get the AI provider used for analysis.
     */
    public function getAIProvider(): ?string
    {
        return $this->getMetadata('ai_provider');
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'successful' => $this->successful,
            'execution_time' => $this->executionTime,
            'results' => $this->results,
            'metadata' => $this->metadata,
            'errors' => $this->errors,
            'summary' => [
                'total_issues' => $this->getTotalIssuesCount(),
                'critical_issues' => \count($this->getCriticalIssues()),
                'warning_issues' => \count($this->getWarningIssues()),
                'info_issues' => \count($this->getInfoIssues()),
                'analyzers_used' => $this->getAnalyzerNames(),
                'cached' => $this->wasCached(),
                'ai_provider' => $this->getAIProvider(),
            ],
        ];
    }
}

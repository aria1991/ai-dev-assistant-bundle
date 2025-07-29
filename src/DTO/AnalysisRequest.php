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
 * Data transfer object for analysis requests.
 *
 * This immutable DTO encapsulates all parameters needed for code analysis,
 * providing type safety and validation for analysis operations.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final readonly class AnalysisRequest
{
    /**
     * @param string   $code              The source code to analyze
     * @param string   $filename          The filename (optional, for context)
     * @param string[] $enabledAnalyzers  List of analyzer names to use
     * @param array    $options           Additional analysis options
     * @param int      $maxTokens         Maximum tokens for AI requests
     * @param bool     $useCache          Whether to use cached results
     * @param int      $timeout           Request timeout in seconds
     */
    public function __construct(
        public string $code,
        public string $filename = '',
        public array $enabledAnalyzers = [],
        public array $options = [],
        public int $maxTokens = 4000,
        public bool $useCache = true,
        public int $timeout = 30
    ) {
        $this->validate();
    }

    /**
     * Create a new request with modified parameters.
     */
    public function withCode(string $code): self
    {
        return new self(
            $code,
            $this->filename,
            $this->enabledAnalyzers,
            $this->options,
            $this->maxTokens,
            $this->useCache,
            $this->timeout
        );
    }

    public function withFilename(string $filename): self
    {
        return new self(
            $this->code,
            $filename,
            $this->enabledAnalyzers,
            $this->options,
            $this->maxTokens,
            $this->useCache,
            $this->timeout
        );
    }

    public function withAnalyzers(array $analyzers): self
    {
        return new self(
            $this->code,
            $this->filename,
            $analyzers,
            $this->options,
            $this->maxTokens,
            $this->useCache,
            $this->timeout
        );
    }

    public function withOptions(array $options): self
    {
        return new self(
            $this->code,
            $this->filename,
            $this->enabledAnalyzers,
            $options,
            $this->maxTokens,
            $this->useCache,
            $this->timeout
        );
    }

    public function withCaching(bool $useCache): self
    {
        return new self(
            $this->code,
            $this->filename,
            $this->enabledAnalyzers,
            $this->options,
            $this->maxTokens,
            $useCache,
            $this->timeout
        );
    }

    /**
     * Get the file extension from filename.
     */
    public function getFileExtension(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * Check if a specific analyzer is enabled.
     */
    public function hasAnalyzer(string $analyzer): bool
    {
        return in_array($analyzer, $this->enabledAnalyzers, true);
    }

    /**
     * Get option value with default fallback.
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Validate the request parameters.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    private function validate(): void
    {
        if (empty($this->code)) {
            throw new \InvalidArgumentException('Code cannot be empty');
        }

        if ($this->maxTokens <= 0) {
            throw new \InvalidArgumentException('Max tokens must be positive');
        }

        if ($this->timeout <= 0) {
            throw new \InvalidArgumentException('Timeout must be positive');
        }

        foreach ($this->enabledAnalyzers as $analyzer) {
            if (!is_string($analyzer) || empty($analyzer)) {
                throw new \InvalidArgumentException('All analyzer names must be non-empty strings');
            }
        }
    }
}

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
     * @param string   $code             The source code to analyze
     * @param string   $filename         The filename (optional, for context)
     * @param string[] $enabledAnalyzers List of analyzer names to use
     * @param array    $options          Additional analysis options
     * @param int      $maxTokens        Maximum tokens for AI requests
     * @param bool     $useCache         Whether to use cached results
     * @param int      $timeout          Request timeout in seconds
     */
    public function __construct(
        public string $code,
        public string $filename = '',
        public array $enabledAnalyzers = [],
        public array $options = [],
        public int $maxTokens = 4000,
        public bool $useCache = true,
        public int $timeout = 30,
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
        return pathinfo($this->filename, \PATHINFO_EXTENSION);
    }

    /**
     * Check if a specific analyzer is enabled.
     */
    public function hasAnalyzer(string $analyzer): bool
    {
        return \in_array($analyzer, $this->enabledAnalyzers, true);
    }

    /**
     * Get option value with default fallback.
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Create from array data with strict type validation.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: self::validateString($data, 'code', ''),
            filename: self::validateString($data, 'filename', ''),
            enabledAnalyzers: self::validateStringArray($data, 'enabled_analyzers', []),
            options: self::validateArray($data, 'options', []),
            maxTokens: self::validateInt($data, 'max_tokens', 4000),
            useCache: self::validateBool($data, 'use_cache', true),
            timeout: self::validateInt($data, 'timeout', 30),
        );
    }

    /**
     * Create from JSON string with validation.
     */
    public static function fromJson(string $json): self
    {
        try {
            $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Invalid JSON provided: ' . $e->getMessage(), 0, $e);
        }

        if (!\is_array($data)) {
            throw new \InvalidArgumentException('JSON must decode to an array');
        }

        return self::fromArray($data);
    }

    /**
     * Convert to array for serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'filename' => $this->filename,
            'enabled_analyzers' => $this->enabledAnalyzers,
            'options' => $this->options,
            'max_tokens' => $this->maxTokens,
            'use_cache' => $this->useCache,
            'timeout' => $this->timeout,
        ];
    }

    /**
     * Convert to JSON string.
     */
    public function toJson(): string
    {
        try {
            return json_encode($this->toArray(), \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to encode to JSON: ' . $e->getMessage(), 0, $e);
        }
    }

    private static function validateString(array $data, string $key, string $default): string
    {
        $value = $data[$key] ?? $default;

        if (!\is_string($value)) {
            throw new \InvalidArgumentException("Field '{$key}' must be a string");
        }

        return $value;
    }

    private static function validateStringArray(array $data, string $key, array $default): array
    {
        $value = $data[$key] ?? $default;

        if (!\is_array($value)) {
            throw new \InvalidArgumentException("Field '{$key}' must be an array");
        }

        foreach ($value as $item) {
            if (!\is_string($item)) {
                throw new \InvalidArgumentException("All items in '{$key}' must be strings");
            }
        }

        return $value;
    }

    private static function validateArray(array $data, string $key, array $default): array
    {
        $value = $data[$key] ?? $default;

        if (!\is_array($value)) {
            throw new \InvalidArgumentException("Field '{$key}' must be an array");
        }

        return $value;
    }

    private static function validateInt(array $data, string $key, int $default): int
    {
        $value = $data[$key] ?? $default;

        if (!\is_int($value) && !is_numeric($value)) {
            throw new \InvalidArgumentException("Field '{$key}' must be an integer");
        }

        return (int) $value;
    }

    private static function validateBool(array $data, string $key, bool $default): bool
    {
        $value = $data[$key] ?? $default;

        if (!\is_bool($value)) {
            // Allow common truthy/falsy values
            if (\is_string($value)) {
                return \in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
            }
            if (is_numeric($value)) {
                return (bool) $value;
            }

            throw new \InvalidArgumentException("Field '{$key}' must be a boolean");
        }

        return $value;
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
            if (!\is_string($analyzer) || empty($analyzer)) {
                throw new \InvalidArgumentException('All analyzer names must be non-empty strings');
            }
        }
    }
}

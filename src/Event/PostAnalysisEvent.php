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

namespace Aria1991\AIDevAssistantBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched after code analysis completes.
 *
 * This event allows listeners to modify analysis results,
 * add additional insights, or perform post-analysis tasks.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class PostAnalysisEvent extends Event
{
    public function __construct(
        private readonly string $filename,
        private readonly string $code,
        private array $results = [],
        private array $metadata = [],
    ) {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    public function addResult(string $analyzer, array $result): void
    {
        $this->results[$analyzer] = $result;
    }

    public function getResult(string $analyzer = ''): mixed
    {
        if ($analyzer === '') {
            // Return analysis result summary for backward compatibility
            return $this->getAnalysisResult();
        }
        
        return $this->results[$analyzer] ?? null;
    }

    public function hasResult(string $analyzer): bool
    {
        return isset($this->results[$analyzer]);
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getExecutionTime(): ?float
    {
        return $this->metadata['execution_time'] ?? null;
    }

    public function setExecutionTime(float $time): void
    {
        $this->metadata['execution_time'] = $time;
    }

    /**
     * Get analysis result summary for backward compatibility with EventSubscribers.
     */
    public function getAnalysisResult(): object
    {
        $errors = [];
        $hasErrors = false;
        
        // Collect errors from all analyzer results
        foreach ($this->results as $analyzerName => $result) {
            if (isset($result['errors']) && !empty($result['errors'])) {
                $errors[$analyzerName] = $result['errors'];
                $hasErrors = true;
            }
        }

        return (object) [
            'filename' => $this->filename,
            'successful' => !$hasErrors && !empty($this->results),
            'executionTime' => $this->getExecutionTime() ?? 0.0,
            'results' => $this->results,
            'errors' => $errors,
        ];
    }
}

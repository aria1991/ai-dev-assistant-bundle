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
 * Event dispatched before code analysis starts.
 *
 * This event allows listeners to modify the analysis request,
 * add custom analyzers, or perform pre-analysis tasks.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class PreAnalysisEvent extends Event
{
    public function __construct(
        private readonly string $filename,
        private readonly string $code,
        private array $enabledAnalyzers = [],
        private array $options = []
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

    public function getEnabledAnalyzers(): array
    {
        return $this->enabledAnalyzers;
    }

    public function setEnabledAnalyzers(array $analyzers): void
    {
        $this->enabledAnalyzers = $analyzers;
    }

    public function addAnalyzer(string $analyzer): void
    {
        if (!in_array($analyzer, $this->enabledAnalyzers, true)) {
            $this->enabledAnalyzers[] = $analyzer;
        }
    }

    public function removeAnalyzer(string $analyzer): void
    {
        $this->enabledAnalyzers = array_values(
            array_filter($this->enabledAnalyzers, fn($a) => $a !== $analyzer)
        );
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function setOption(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }
}

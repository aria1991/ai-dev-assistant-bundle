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
 * Event dispatched when an AI provider fails.
 *
 * This event allows listeners to handle provider failures,
 * implement custom fallback logic, or log provider issues.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AIProviderFailureEvent extends Event
{
    public function __construct(
        private readonly string $providerName,
        private readonly string $prompt,
        private readonly \Throwable $exception,
        private readonly array $options = [],
        private bool $shouldRetry = false,
    ) {
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function shouldRetry(): bool
    {
        return $this->shouldRetry;
    }

    public function setShouldRetry(bool $retry): void
    {
        $this->shouldRetry = $retry;
    }

    public function isRateLimitError(): bool
    {
        return str_contains(
            strtolower($this->exception->getMessage()),
            'rate limit'
        );
    }

    public function isAuthenticationError(): bool
    {
        return str_contains(
            strtolower($this->exception->getMessage()),
            'unauthorized'
        ) || str_contains(
            strtolower($this->exception->getMessage()),
            'api key'
        );
    }
}

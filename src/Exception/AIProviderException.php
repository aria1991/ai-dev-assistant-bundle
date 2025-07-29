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

namespace Aria1991\AIDevAssistantBundle\Exception;

/**
 * Exception thrown when an AI provider fails.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AIProviderException extends AIDevAssistantException
{
    public function __construct(
        string $message,
        public readonly string $providerName,
        public readonly int $statusCode = 0,
        public readonly bool $isRetryable = false,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $context['provider'] = $this->providerName;
        $context['status_code'] = $this->statusCode;
        $context['retryable'] = $this->isRetryable;

        parent::__construct($message, $statusCode, $previous, $context);
    }

    /**
     * Create exception for rate limit errors.
     */
    public static function rateLimitExceeded(
        string $providerName,
        int $retryAfter = 0,
        ?\Throwable $previous = null
    ): self {
        $message = "Rate limit exceeded for provider '{$providerName}'";
        if ($retryAfter > 0) {
            $message .= ". Retry after {$retryAfter} seconds";
        }

        return new self(
            $message,
            $providerName,
            429,
            true,
            $previous,
            ['retry_after' => $retryAfter]
        );
    }

    /**
     * Create exception for authentication errors.
     */
    public static function authenticationFailed(
        string $providerName,
        ?\Throwable $previous = null
    ): self {
        return new self(
            "Authentication failed for provider '{$providerName}'. Check your API key.",
            $providerName,
            401,
            false,
            $previous
        );
    }

    /**
     * Create exception for quota exceeded errors.
     */
    public static function quotaExceeded(
        string $providerName,
        ?\Throwable $previous = null
    ): self {
        return new self(
            "API quota exceeded for provider '{$providerName}'",
            $providerName,
            402,
            false,
            $previous
        );
    }

    /**
     * Create exception for network/connectivity errors.
     */
    public static function networkError(
        string $providerName,
        ?\Throwable $previous = null
    ): self {
        return new self(
            "Network error connecting to provider '{$providerName}'",
            $providerName,
            0,
            true,
            $previous
        );
    }

    /**
     * Create exception for invalid responses.
     */
    public static function invalidResponse(
        string $providerName,
        string $reason,
        ?\Throwable $previous = null
    ): self {
        return new self(
            "Invalid response from provider '{$providerName}': {$reason}",
            $providerName,
            0,
            false,
            $previous
        );
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isRetryable(): bool
    {
        return $this->isRetryable;
    }

    public function getRetryAfter(): int
    {
        return $this->getContextValue('retry_after', 0);
    }
}

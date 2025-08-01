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

namespace Aria1991\AIDevAssistantBundle\EventSubscriber;

use Aria1991\AIDevAssistantBundle\Event\AIProviderFailureEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for AI provider failures.
 *
 * Handles AI provider failures with logging, metrics, and potential retry logic.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AIProviderFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AIProviderFailureEvent::class => [
                ['onProviderFailure', 10], // Higher priority for logging
                ['updateMetrics', 0],      // Lower priority for metrics
            ],
        ];
    }

    public function onProviderFailure(AIProviderFailureEvent $event): void
    {
        $exception = $event->getException();

        $this->logger->error('AI Provider failure occurred', [
            'provider' => $event->getProviderName(),
            'error' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'is_retryable' => $event->isRetryable(),
            'retry_count' => $event->getRetryCount(),
            'context' => $event->getContext(),
        ]);

        // Log specific failure types with different severity
        if ($exception->getCode() === 429) { // Rate limit
            $this->logger->warning('Rate limit exceeded for AI provider', [
                'provider' => $event->getProviderName(),
                'retry_after' => $event->getContext()['retry_after'] ?? null,
            ]);
        } elseif ($exception->getCode() === 401) { // Authentication
            $this->logger->critical('Authentication failed for AI provider', [
                'provider' => $event->getProviderName(),
                'message' => 'Check API key configuration',
            ]);
        } elseif ($exception->getCode() === 402) { // Quota
            $this->logger->critical('API quota exceeded for AI provider', [
                'provider' => $event->getProviderName(),
                'message' => 'Review usage limits and billing',
            ]);
        }
    }

    public function updateMetrics(AIProviderFailureEvent $event): void
    {
        // Here you could update metrics/monitoring systems
        // For example, increment failure counters, update dashboards, etc.

        $this->logger->debug('Updating failure metrics', [
            'provider' => $event->getProviderName(),
            'failure_type' => $this->getFailureType($event->getException()),
            'retry_count' => $event->getRetryCount(),
        ]);
    }

    private function getFailureType(\Throwable $exception): string
    {
        return match ($exception->getCode()) {
            401 => 'authentication',
            402 => 'quota_exceeded',
            429 => 'rate_limit',
            0 => 'network_error',
            default => 'unknown',
        };
    }
}

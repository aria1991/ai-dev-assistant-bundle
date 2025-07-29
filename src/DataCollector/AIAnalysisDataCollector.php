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

namespace Aria1991\AIDevAssistantBundle\DataCollector;

use Aria1991\AIDevAssistantBundle\Service\AdvancedCacheService;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Data collector for Symfony WebProfiler integration.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AIAnalysisDataCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly ?AdvancedCacheService $cacheService = null,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data = [
            'cache_metrics' => $this->cacheService?->getMetrics() ?? [],
            'request_count' => 0,
            'total_execution_time' => 0.0,
            'providers_status' => [],
            'recent_analyses' => [],
        ];
    }

    public function getCacheMetrics(): array
    {
        return $this->data['cache_metrics'];
    }

    public function getRequestCount(): int
    {
        return $this->data['request_count'];
    }

    public function getTotalExecutionTime(): float
    {
        return $this->data['total_execution_time'];
    }

    public function getProvidersStatus(): array
    {
        return $this->data['providers_status'];
    }

    public function getRecentAnalyses(): array
    {
        return $this->data['recent_analyses'];
    }

    public function getName(): string
    {
        return 'ai_dev_assistant';
    }

    public static function getTemplate(): ?string
    {
        return '@AIDevAssistant/profiler/ai_analysis.html.twig';
    }
}

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

use Aria1991\AIDevAssistantBundle\Event\PostAnalysisEvent;
use Aria1991\AIDevAssistantBundle\Event\PreAnalysisEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for performance monitoring.
 *
 * Tracks analysis performance metrics and identifies bottlenecks.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class PerformanceMonitoringSubscriber implements EventSubscriberInterface
{
    private array $analysisStartTimes = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreAnalysisEvent::class => 'onPreAnalysis',
            PostAnalysisEvent::class => 'onPostAnalysis',
        ];
    }

    public function onPreAnalysis(PreAnalysisEvent $event): void
    {
        $filename = $event->getFilename();
        $this->analysisStartTimes[$filename] = microtime(true);

        // Log performance warnings for large files
        $codeLength = \strlen($event->getCode());
        if ($codeLength > 100000) { // > 100KB
            $this->logger->warning('Analyzing large file, performance may be impacted', [
                'filename' => $filename,
                'code_length' => $codeLength,
                'size_mb' => round($codeLength / 1024 / 1024, 2),
            ]);
        }
    }

    public function onPostAnalysis(PostAnalysisEvent $event): void
    {
        $result = $event->getResult();
        $filename = $result->filename;

        if (!isset($this->analysisStartTimes[$filename])) {
            return;
        }

        $totalTime = microtime(true) - $this->analysisStartTimes[$filename];
        unset($this->analysisStartTimes[$filename]);

        // Log performance metrics
        $this->logger->info('Analysis performance metrics', [
            'filename' => $filename,
            'total_time' => round($totalTime, 3),
            'execution_time' => round($result->executionTime, 3),
            'overhead_time' => round($totalTime - $result->executionTime, 3),
            'analyzer_count' => \count($result->results),
            'time_per_analyzer' => \count($result->results) > 0
                ? round($result->executionTime / \count($result->results), 3)
                : 0,
        ]);

        // Warn about slow analyses
        if ($totalTime > 10.0) { // > 10 seconds
            $this->logger->warning('Slow analysis detected', [
                'filename' => $filename,
                'total_time' => round($totalTime, 3),
                'threshold' => '10.0s',
                'recommendation' => 'Consider optimizing analyzers or using cache',
            ]);
        }

        // Track memory usage if available
        if (\function_exists('memory_get_peak_usage')) {
            $peakMemory = memory_get_peak_usage(true);
            $currentMemory = memory_get_usage(true);

            $this->logger->debug('Memory usage metrics', [
                'filename' => $filename,
                'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
                'current_memory_mb' => round($currentMemory / 1024 / 1024, 2),
            ]);

            // Warn about high memory usage
            if ($peakMemory > 512 * 1024 * 1024) { // > 512MB
                $this->logger->warning('High memory usage detected', [
                    'filename' => $filename,
                    'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
                    'threshold' => '512MB',
                ]);
            }
        }
    }
}

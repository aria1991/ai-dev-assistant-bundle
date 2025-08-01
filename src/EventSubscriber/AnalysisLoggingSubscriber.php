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
 * Event subscriber for analysis logging.
 *
 * Logs analysis events for monitoring and debugging purposes.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AnalysisLoggingSubscriber implements EventSubscriberInterface
{
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
        $this->logger->info('Starting code analysis', [
            'filename' => $event->getFilename(),
            'code_length' => \strlen($event->getCode()),
            'enabled_analyzers' => $event->getEnabledAnalyzers(),
            'options' => $event->getOptions(),
        ]);
    }

    public function onPostAnalysis(PostAnalysisEvent $event): void
    {
        $result = $event->getResult();

        $this->logger->info('Completed code analysis', [
            'filename' => $result->filename,
            'successful' => $result->successful,
            'execution_time' => $result->executionTime,
            'analyzer_count' => \count($result->results),
            'error_count' => \count($result->errors),
        ]);

        if (!$result->successful) {
            $this->logger->warning('Analysis completed with errors', [
                'filename' => $result->filename,
                'errors' => $result->errors,
            ]);
        }
    }
}

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

namespace Aria1991\AIDevAssistantBundle\DependencyInjection\Compiler;

use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for auto-registering analyzers.
 *
 * This pass automatically discovers and registers all services tagged with
 * 'ai_dev_assistant.analyzer' into the CodeAnalyzer service, making the
 * system extensible without manual configuration.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AnalyzerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(CodeAnalyzer::class)) {
            return;
        }

        $definition = $container->findDefinition(CodeAnalyzer::class);
        $taggedServices = $container->findTaggedServiceIds('ai_dev_assistant.analyzer');

        $analyzers = [];
        foreach ($taggedServices as $id => $tags) {
            $priority = $tags[0]['priority'] ?? 0;
            $analyzers[$priority] = new Reference($id);
        }

        // Sort by priority (highest first)
        krsort($analyzers);

        $definition->replaceArgument('$analyzers', array_values($analyzers));
    }
}

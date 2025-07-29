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

use Aria1991\AIDevAssistantBundle\Service\AIManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for auto-registering AI providers with priority ordering.
 *
 * This pass automatically discovers AI providers and orders them by priority
 * for the fallback chain in AIManager.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AIProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(AIManager::class)) {
            return;
        }

        $definition = $container->findDefinition(AIManager::class);
        $taggedServices = $container->findTaggedServiceIds('ai_dev_assistant.provider');

        $providers = [];
        foreach ($taggedServices as $id => $tags) {
            $priority = $tags[0]['priority'] ?? 0;
            $providers[$priority] = new Reference($id);
        }

        // Sort by priority (highest first)
        krsort($providers);

        $definition->replaceArgument('$providers', array_values($providers));
    }
}

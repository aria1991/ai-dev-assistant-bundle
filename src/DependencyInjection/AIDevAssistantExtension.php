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

namespace Aria1991\AIDevAssistantBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * AI Development Assistant Bundle extension.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AIDevAssistantExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        // Set configuration parameters with proper defaults
        $container->setParameter('ai_dev_assistant.enabled', $config['enabled']);

        // AI providers configuration
        $container->setParameter('ai_dev_assistant.ai.providers.openai.model', $config['ai']['providers']['openai']['model'] ?? 'gpt-4');
        $container->setParameter('ai_dev_assistant.ai.providers.openai.max_tokens', $config['ai']['providers']['openai']['max_tokens'] ?? 4000);
        $container->setParameter('ai_dev_assistant.ai.providers.anthropic.model', $config['ai']['providers']['anthropic']['model'] ?? 'claude-3-sonnet-20240229');
        $container->setParameter('ai_dev_assistant.ai.providers.anthropic.max_tokens', $config['ai']['providers']['anthropic']['max_tokens'] ?? 4000);
        $container->setParameter('ai_dev_assistant.ai.providers.google.model', $config['ai']['providers']['google']['model'] ?? 'gemini-pro');

        // Cache configuration
        $container->setParameter('ai_dev_assistant.cache.enabled', $config['cache']['enabled']);
        $container->setParameter('ai_dev_assistant.cache.ttl', $config['cache']['ttl']);

        // Analysis configuration
        if (isset($config['analysis'])) {
            $container->setParameter('ai_dev_assistant.analysis', $config['analysis']);
        }

        // Rate limiting configuration
        if (isset($config['rate_limiting'])) {
            $container->setParameter('ai_dev_assistant.rate_limiting', $config['rate_limiting']);
        }

        // Disable services if bundle is disabled
        if (!$config['enabled']) {
            $container->removeDefinition('Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer');
            $container->removeDefinition('Aria1991\AIDevAssistantBundle\Service\AIManager');
        }
    }

    public function getAlias(): string
    {
        return 'ai_dev_assistant';
    }
}

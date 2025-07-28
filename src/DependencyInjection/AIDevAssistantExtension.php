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

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        // Set configuration parameters
        $container->setParameter('ai_dev_assistant.enabled', $config['enabled']);
        $container->setParameter('ai_dev_assistant.ai_providers', $config['ai']['providers'] ?? []);
        $container->setParameter('ai_dev_assistant.cache_enabled', $config['cache']['enabled']);
        $container->setParameter('ai_dev_assistant.cache_ttl', $config['cache']['ttl']);

        if (isset($config['analysis'])) {
            $container->setParameter('ai_dev_assistant.analysis', $config['analysis']);
        }

        if (isset($config['rate_limiting'])) {
            $container->setParameter('ai_dev_assistant.rate_limiting', $config['rate_limiting']);
        }
    }

    public function getAlias(): string
    {
        return 'ai_dev_assistant';
    }
}

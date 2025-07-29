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

namespace Aria1991\AIDevAssistantBundle\Tests\Integration;

use Aria1991\AIDevAssistantBundle\AIDevAssistantBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class BundleIntegrationTest extends TestCase
{
    public function testBundleLoadInKernel(): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        
        $container = $kernel->getContainer();
        
        // Test that our main services are registered
        $this->assertTrue($container->has('Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer'));
        $this->assertTrue($container->has('Aria1991\AIDevAssistantBundle\Service\AIManager'));
        
        $kernel->shutdown();
    }
}

class TestKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new AIDevAssistantBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => true,
                'http_client' => [
                    'enabled' => true,
                ],
                'cache' => [
                    'app' => 'cache.adapter.array',
                ],
            ]);

            $container->loadFromExtension('ai_dev_assistant', [
                'enabled' => true,
                'ai' => [
                    'providers' => [
                        'openai' => [
                            'api_key' => 'test-key',
                            'model' => 'gpt-4',
                        ],
                    ],
                ],
                'cache' => [
                    'enabled' => true,
                    'ttl' => 3600,
                ],
            ]);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/ai_dev_assistant_test_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/ai_dev_assistant_test_logs';
    }
}

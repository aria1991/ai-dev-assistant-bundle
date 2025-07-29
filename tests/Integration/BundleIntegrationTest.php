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
use Aria1991\AIDevAssistantBundle\DependencyInjection\AIDevAssistantExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BundleIntegrationTest extends TestCase
{
    public function testBundleContainerBuild(): void
    {
        $container = new ContainerBuilder();
        $bundle = new AIDevAssistantBundle();

        // This should not throw any exceptions
        $bundle->build($container);

        // Verify compiler passes were added
        $passes = $container->getCompilerPassConfig()->getPasses();
        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);
    }

    public function testExtensionCanLoadConfiguration(): void
    {
        $extension = new AIDevAssistantExtension();
        $container = new ContainerBuilder();

        // Test with minimal configuration
        $config = [
            'enabled' => true,
            'ai' => [
                'providers' => [
                    'openai' => [
                        'api_key' => 'test-key',
                        'model' => 'gpt-4',
                    ],
                ],
            ],
        ];

        // This should not throw any exceptions
        $extension->load(['ai_dev_assistant' => $config], $container);

        $this->assertTrue($container->hasParameter('ai_dev_assistant.enabled'));
    }
}

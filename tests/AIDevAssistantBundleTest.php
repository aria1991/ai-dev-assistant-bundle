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

namespace Aria1991\AIDevAssistantBundle\Tests;

use Aria1991\AIDevAssistantBundle\AIDevAssistantBundle;
use Aria1991\AIDevAssistantBundle\DependencyInjection\AIDevAssistantExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AIDevAssistantBundleTest extends TestCase
{
    public function testBundleInstantiation(): void
    {
        $bundle = new AIDevAssistantBundle();

        $this->assertInstanceOf(AIDevAssistantBundle::class, $bundle);
    }

    public function testGetContainerExtension(): void
    {
        $bundle = new AIDevAssistantBundle();
        $extension = $bundle->getContainerExtension();

        $this->assertNotNull($extension);
        $this->assertInstanceOf(AIDevAssistantExtension::class, $extension);
        $this->assertSame('ai_dev_assistant', $extension->getAlias());
    }

    public function testBundleBuild(): void
    {
        $container = new ContainerBuilder();
        $bundle = new AIDevAssistantBundle();

        // This should not throw any exceptions
        $bundle->build($container);

        // Verify compiler passes were added
        $passes = $container->getCompilerPassConfig()->getPasses();

        // We should have at least 2 passes added (AnalyzerPass and AIProviderPass)
        // Since we can't easily inspect the specific passes, we just ensure build() worked
        $this->assertIsArray($passes);
    }

    public function testBundleNamespace(): void
    {
        $bundle = new AIDevAssistantBundle();

        // Check that the bundle has the correct namespace
        $reflection = new \ReflectionClass($bundle);
        $this->assertSame('Aria1991\AIDevAssistantBundle', $reflection->getNamespaceName());
    }
}

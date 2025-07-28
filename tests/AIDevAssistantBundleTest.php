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
use PHPUnit\Framework\TestCase;

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
        $this->assertSame('ai_dev_assistant', $extension->getAlias());
    }
}

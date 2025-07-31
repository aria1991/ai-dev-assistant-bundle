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

namespace Aria1991\AIDevAssistantBundle\Service;

use Aria1991\AIDevAssistantBundle\Service\Provider\AIProviderInterface;

/**
 * Interface for AI Manager implementations.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
interface AIManagerInterface
{
    /**
     * Send a request to AI providers with fallback chain.
     *
     * @param string $prompt  The prompt to send
     * @param array  $options Additional options for the request
     *
     * @throws \Exception If all providers fail
     *
     * @return string The AI response
     */
    public function request(string $prompt, array $options = []): string;

    /**
     * Get available providers.
     *
     * @return AIProviderInterface[]
     */
    public function getAvailableProviders(): array;

    /**
     * Check if any provider is available.
     */
    public function hasAvailableProvider(): bool;
}

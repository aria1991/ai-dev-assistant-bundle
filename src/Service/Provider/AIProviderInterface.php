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

namespace Aria1991\AIDevAssistantBundle\Service\Provider;

/**
 * Interface for AI providers.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
interface AIProviderInterface
{
    /**
     * Send a request to the AI provider.
     *
     * @param string $prompt The prompt to send
     * @param array $options Additional options for the request
     * @return string The AI response
     * @throws \Exception If the request fails
     */
    public function request(string $prompt, array $options = []): string;

    /**
     * Check if the provider is available.
     *
     * @return bool True if the provider is available
     */
    public function isAvailable(): bool;

    /**
     * Get the provider name.
     *
     * @return string The provider name
     */
    public function getName(): string;
}

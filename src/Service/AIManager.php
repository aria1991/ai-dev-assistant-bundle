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
use Psr\Log\LoggerInterface;

/**
 * AI Manager with provider fallback chain.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AIManager
{
    /**
     * @param AIProviderInterface[] $providers
     */
    public function __construct(
        private readonly array $providers,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Send a request to AI providers with fallback chain.
     *
     * @param string $prompt The prompt to send
     * @param array $options Additional options for the request
     * @return string The AI response
     * @throws \Exception If all providers fail
     */
    public function request(string $prompt, array $options = []): string
    {
        $lastException = null;

        foreach ($this->providers as $provider) {
            if (!$provider->isAvailable()) {
                $this->logger->debug('Provider not available', ['provider' => $provider->getName()]);
                continue;
            }

            try {
                $response = $provider->request($prompt, $options);
                $this->logger->info('AI request successful', [
                    'provider' => $provider->getName(),
                    'prompt_length' => strlen($prompt),
                    'response_length' => strlen($response),
                ]);
                return $response;
            } catch (\Exception $e) {
                $lastException = $e;
                $this->logger->warning('Provider request failed, trying next', [
                    'provider' => $provider->getName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw new \Exception(
            'All AI providers failed. Last error: ' . ($lastException?->getMessage() ?? 'No providers available')
        );
    }

    /**
     * Get available providers.
     *
     * @return AIProviderInterface[]
     */
    public function getAvailableProviders(): array
    {
        return array_filter($this->providers, fn($provider) => $provider->isAvailable());
    }

    /**
     * Check if any provider is available.
     *
     * @return bool
     */
    public function hasAvailableProvider(): bool
    {
        return count($this->getAvailableProviders()) > 0;
    }
}

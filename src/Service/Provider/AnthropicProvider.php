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

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Anthropic Claude provider for AI requests.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AnthropicProvider implements AIProviderInterface
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $apiKey,
        private readonly string $model = 'claude-3-sonnet-20240229',
        private readonly int $maxTokens = 4000,
    ) {
    }

    public function request(string $prompt, array $options = []): string
    {
        if (!$this->isAvailable()) {
            throw new \Exception('Anthropic provider is not available');
        }

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $options['model'] ?? $this->model,
                    'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['content'][0]['text'])) {
                throw new \Exception('Invalid response from Anthropic API');
            }

            return $data['content'][0]['text'];
        } catch (\Exception $e) {
            $this->logger->error('Anthropic API request failed', [
                'error' => $e->getMessage(),
                'prompt_length' => \strlen($prompt),
            ]);
            throw $e;
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey)
            && $this->apiKey !== 'your_anthropic_api_key_here'
            && str_starts_with($this->apiKey, 'sk-ant-');
    }

    public function getName(): string
    {
        return 'anthropic';
    }
}

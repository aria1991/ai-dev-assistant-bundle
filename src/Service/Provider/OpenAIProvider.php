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
 * OpenAI provider for AI requests.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class OpenAIProvider implements AIProviderInterface
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4',
        private readonly int $maxTokens = 4000
    ) {
    }

    public function request(string $prompt, array $options = []): string
    {
        if (!$this->isAvailable()) {
            throw new \Exception('OpenAI provider is not available');
        }

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $options['model'] ?? $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                    'temperature' => $options['temperature'] ?? 0.7,
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response from OpenAI API');
            }

            return $data['choices'][0]['message']['content'];
        } catch (\Exception $e) {
            $this->logger->error('OpenAI API request failed', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt),
            ]);
            throw $e;
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) 
            && $this->apiKey !== 'your_openai_api_key_here'
            && str_starts_with($this->apiKey, 'sk-');
    }

    public function getName(): string
    {
        return 'openai';
    }
}

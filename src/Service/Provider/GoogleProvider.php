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
 * Google AI provider for AI requests.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class GoogleProvider implements AIProviderInterface
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $apiKey,
        private readonly string $model = 'gemini-pro'
    ) {
    }

    public function request(string $prompt, array $options = []): string
    {
        if (!$this->isAvailable()) {
            throw new \Exception('Google AI provider is not available');
        }

        try {
            $model = $options['model'] ?? $this->model;
            $url = self::API_URL . '/' . $model . ':generateContent?key=' . $this->apiKey;

            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => $options['temperature'] ?? 0.7,
                        'maxOutputTokens' => $options['max_tokens'] ?? 4000,
                    ],
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Invalid response from Google AI API');
            }

            return $data['candidates'][0]['content']['parts'][0]['text'];
        } catch (\Exception $e) {
            $this->logger->error('Google AI API request failed', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt),
            ]);
            throw $e;
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) 
            && $this->apiKey !== 'your_google_api_key_here'
            && (str_starts_with($this->apiKey, 'AI') || strlen($this->apiKey) > 20);
    }

    public function getName(): string
    {
        return 'google';
    }
}

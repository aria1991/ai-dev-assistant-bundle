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

/**
 * Helper service for managing environment variables and configuration.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class ConfigurationHelper
{
    /**
     * Check if API key is properly configured.
     */
    public static function isApiKeyConfigured(string $apiKey): bool
    {
        if (empty($apiKey)) {
            return false;
        }

        // Check for placeholder values
        $placeholders = [
            'your_openai_api_key_here',
            'your_anthropic_api_key_here', 
            'your_google_api_key_here',
            'sk-placeholder',
            'placeholder',
            'change_me',
            'null',
            'false'
        ];

        return !in_array(strtolower(trim($apiKey)), $placeholders, true);
    }

    /**
     * Get user-friendly provider setup instructions.
     */
    public static function getProviderInstructions(): array
    {
        return [
            'openai' => [
                'name' => 'OpenAI',
                'url' => 'https://platform.openai.com/api-keys',
                'key_format' => 'sk-...',
                'notes' => 'Most reliable, requires billing account'
            ],
            'anthropic' => [
                'name' => 'Anthropic Claude',
                'url' => 'https://console.anthropic.com/',
                'key_format' => 'sk-ant-...',
                'notes' => 'Excellent for code analysis'
            ],
            'google' => [
                'name' => 'Google AI',
                'url' => 'https://makersuite.google.com/app/apikey',
                'key_format' => 'AI...',
                'notes' => 'Free tier available'
            ]
        ];
    }

    /**
     * Generate environment file content.
     */
    public static function generateEnvContent(): string
    {
        return <<<ENV

###> ai-dev-assistant-bundle ###
# AI Provider API Keys
# You only need ONE key to start using the bundle
# Get your free API keys from:

# OpenAI (Most reliable) - https://platform.openai.com/api-keys
OPENAI_API_KEY=your_openai_api_key_here

# Anthropic Claude (Great for code) - https://console.anthropic.com/
ANTHROPIC_API_KEY=your_anthropic_api_key_here

# Google AI (Free tier) - https://makersuite.google.com/app/apikey
GOOGLE_AI_API_KEY=your_google_api_key_here
###< ai-dev-assistant-bundle ###

ENV;
    }

    /**
     * Validate configuration and provide helpful error messages.
     */
    public static function validateConfiguration(array $config): array
    {
        $errors = [];
        $warnings = [];

        // Check if at least one provider is configured
        $hasConfiguredProvider = false;
        $providers = $config['ai']['providers'] ?? [];

        foreach ($providers as $providerName => $providerConfig) {
            $apiKey = $providerConfig['api_key'] ?? '';
            if (self::isApiKeyConfigured($apiKey)) {
                $hasConfiguredProvider = true;
                break;
            }
        }

        if (!$hasConfiguredProvider) {
            $errors[] = 'No AI providers are configured. Please add at least one API key to your .env file.';
            $errors[] = 'Run: php bin/console ai-dev-assistant:install for setup instructions.';
        }

        // Check cache configuration
        if (($config['cache']['enabled'] ?? true) && !class_exists('Symfony\Component\Cache\CacheItem')) {
            $warnings[] = 'Cache is enabled but symfony/cache is not installed. Run: composer require symfony/cache';
        }

        // Check analysis configuration
        $enabledAnalyzers = $config['analysis']['enabled_analyzers'] ?? [];
        if (empty($enabledAnalyzers)) {
            $warnings[] = 'No analyzers are enabled. Code analysis will not work.';
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'is_valid' => empty($errors)
        ];
    }
}

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

namespace Aria1991\AIDevAssistantBundle\Exception;

/**
 * Exception thrown when bundle configuration is invalid.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class ConfigurationException extends AIDevAssistantException
{
    public function __construct(
        string $message,
        public readonly string $configKey = '',
        public readonly mixed $configValue = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $context['config_key'] = $this->configKey;
        $context['config_value'] = $this->configValue;

        parent::__construct($message, 0, $previous, $context);
    }

    /**
     * Create exception for missing required configuration.
     */
    public static function missingRequired(string $configKey): self
    {
        return new self(
            "Required configuration '{$configKey}' is missing",
            $configKey
        );
    }

    /**
     * Create exception for invalid configuration values.
     */
    public static function invalidValue(string $configKey, mixed $value, string $reason): self
    {
        return new self(
            "Invalid value for configuration '{$configKey}': {$reason}",
            $configKey,
            $value,
            null,
            ['reason' => $reason]
        );
    }

    /**
     * Create exception for missing API keys.
     */
    public static function missingApiKeys(): self
    {
        return new self(
            'No AI provider API keys are configured. Please add at least one API key to your .env file'
        );
    }

    /**
     * Create exception for invalid API keys.
     */
    public static function invalidApiKey(string $provider, string $key): self
    {
        return new self(
            "Invalid API key format for provider '{$provider}'",
            "{$provider}.api_key",
            $key,
            null,
            ['provider' => $provider]
        );
    }

    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    public function getConfigValue(): mixed
    {
        return $this->configValue;
    }
}

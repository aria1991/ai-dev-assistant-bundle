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
 * Base exception for all AI Development Assistant Bundle exceptions.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
abstract class AIDevAssistantException extends \Exception
{
    /**
     * @param string          $message  Exception message
     * @param int             $code     Exception code
     * @param \Throwable|null $previous Previous exception
     * @param array           $context  Additional context data
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        protected array $context = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get additional context data.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get specific context value.
     */
    public function getContextValue(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Add context data.
     */
    public function addContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }
}

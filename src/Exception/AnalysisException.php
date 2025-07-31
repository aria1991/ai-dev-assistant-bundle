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
 * Base exception for all analysis-related errors.
 *
 * This serves as the parent class for all exceptions thrown during
 * code analysis operations.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
class AnalysisException extends \Exception
{
    public function __construct(
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create a generic analysis error.
     */
    public static function generic(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, $previous);
    }

    /**
     * Create an error for when analysis fails due to timeout.
     */
    public static function timeout(int $seconds): self
    {
        return new self("Analysis timed out after {$seconds} seconds");
    }

    /**
     * Create an error for analyzer failures.
     */
    public static function analyzerFailed(string $analyzerName, string $reason, ?\Throwable $previous = null): self
    {
        return new self("Analyzer '{$analyzerName}' failed: {$reason}", $previous);
    }
}

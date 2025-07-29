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
 * Exception thrown when code analysis fails.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class AnalysisException extends AIDevAssistantException
{
    public function __construct(
        string $message,
        public readonly string $filename = '',
        public readonly string $analyzerName = '',
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $context['filename'] = $this->filename;
        $context['analyzer'] = $this->analyzerName;

        parent::__construct($message, 0, $previous, $context);
    }

    /**
     * Create exception for file read errors.
     */
    public static function fileNotReadable(string $filename, ?\Throwable $previous = null): self
    {
        return new self(
            "Cannot read file '{$filename}' for analysis",
            $filename,
            '',
            $previous
        );
    }

    /**
     * Create exception for unsupported file types.
     */
    public static function unsupportedFileType(string $filename, string $extension): self
    {
        return new self(
            "Unsupported file type '{$extension}' for file '{$filename}'",
            $filename,
            '',
            null,
            ['extension' => $extension]
        );
    }

    /**
     * Create exception for file size limits.
     */
    public static function fileTooLarge(string $filename, int $size, int $maxSize): self
    {
        return new self(
            "File '{$filename}' is too large ({$size} bytes). Maximum size is {$maxSize} bytes",
            $filename,
            '',
            null,
            ['size' => $size, 'max_size' => $maxSize]
        );
    }

    /**
     * Create exception for analyzer failures.
     */
    public static function analyzerFailed(
        string $analyzerName,
        string $filename,
        ?\Throwable $previous = null
    ): self {
        return new self(
            "Analyzer '{$analyzerName}' failed to analyze file '{$filename}'",
            $filename,
            $analyzerName,
            $previous
        );
    }

    /**
     * Create exception for invalid code input.
     */
    public static function invalidCode(string $reason, string $filename = ''): self
    {
        return new self(
            "Invalid code input: {$reason}",
            $filename,
            '',
            null,
            ['reason' => $reason]
        );
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getAnalyzerName(): string
    {
        return $this->analyzerName;
    }
}

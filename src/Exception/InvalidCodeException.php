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
 * Exception thrown when code syntax is invalid.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class InvalidCodeException extends AnalysisException
{
    public static function emptySyntax(): self
    {
        return new self('Code cannot be empty');
    }

    public static function invalidSyntax(string $error = ''): self
    {
        $message = 'Invalid PHP syntax';
        if ($error !== '') {
            $message .= ': ' . $error;
        }

        return new self($message);
    }

    public static function unsupportedFileType(string $filePath, array $supportedTypes): self
    {
        return new self(\sprintf(
            'Unsupported file type for %s. Supported types: %s',
            $filePath,
            implode(', ', $supportedTypes)
        ));
    }
}

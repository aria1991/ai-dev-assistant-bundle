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
 * Exception thrown when file operations fail.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class FileException extends AnalysisException
{
    public static function fileNotFound(string $filePath): self
    {
        return new self(\sprintf('File not found: %s', $filePath));
    }

    public static function fileNotReadable(string $filePath): self
    {
        return new self(\sprintf('File is not readable: %s', $filePath));
    }

    public static function directoryNotFound(string $directoryPath): self
    {
        return new self(\sprintf('Directory not found: %s', $directoryPath));
    }

    public static function fileTooBig(string $filePath, int $maxSize, int $actualSize): self
    {
        return new self(\sprintf(
            'File %s is too large (%d bytes). Maximum allowed size is %d bytes.',
            $filePath,
            $actualSize,
            $maxSize
        ));
    }
}

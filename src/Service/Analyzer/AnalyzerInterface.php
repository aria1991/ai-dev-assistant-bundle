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

namespace Aria1991\AIDevAssistantBundle\Service\Analyzer;

/**
 * Interface for code analyzers.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
interface AnalyzerInterface
{
    /**
     * Analyze the given code.
     *
     * @param string $code The code to analyze
     * @param string $filename The filename (optional, for context)
     * @return array Analysis results
     */
    public function analyze(string $code, string $filename = ''): array;

    /**
     * Get the analyzer name.
     *
     * @return string The analyzer name
     */
    public function getName(): string;

    /**
     * Get the analysis prompt template.
     *
     * @return string The prompt template
     */
    public function getPromptTemplate(): string;
}

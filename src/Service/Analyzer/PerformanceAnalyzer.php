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

use Aria1991\AIDevAssistantBundle\Service\AIManager;

/**
 * Performance analyzer for detecting performance issues.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class PerformanceAnalyzer implements AnalyzerInterface
{
    public function __construct(
        private readonly AIManager $aiManager,
    ) {
    }

    public function analyze(string $code, string $filename = ''): array
    {
        $prompt = \sprintf($this->getPromptTemplate(), $filename, $code);

        try {
            $response = $this->aiManager->request($prompt);

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to analyze performance: ' . $e->getMessage(),
                'issues' => [],
            ];
        }
    }

    public function getName(): string
    {
        return 'performance';
    }

    public function getPromptTemplate(): string
    {
        return <<<PROMPT
Analyze the following PHP code for performance issues and optimization opportunities. Focus on:

1. Database query optimization (N+1 queries, missing indexes)
2. Memory usage issues (large arrays, memory leaks)
3. CPU-intensive operations (nested loops, inefficient algorithms)
4. I/O operations (file operations, network calls)
5. Caching opportunities
6. Symfony-specific performance issues (service container, event listeners)
7. Array and collection performance
8. String manipulation efficiency

File: %s

Code:
```php
%s
```

Provide your analysis in JSON format:
{
    "performance_score": "1-10 scale",
    "issues": [
        {
            "line": number,
            "type": "performance_issue_type",
            "impact": "low|medium|high",
            "message": "Description of the performance issue",
            "suggestion": "How to optimize it"
        }
    ],
    "optimizations": [
        {
            "description": "Optimization opportunity",
            "impact": "Expected performance improvement"
        }
    ],
    "summary": "Overall performance assessment"
}
PROMPT;
    }

    private function parseResponse(string $response): array
    {
        // Try to extract JSON from the response
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $decoded = json_decode($matches[0], true);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        // Fallback to basic parsing
        return [
            'performance_score' => 'unknown',
            'issues' => [],
            'optimizations' => [],
            'summary' => $response,
        ];
    }
}

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
 * Code quality analyzer for detecting code quality issues.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class QualityAnalyzer implements AnalyzerInterface
{
    public function __construct(
        private readonly AIManager $aiManager
    ) {
    }

    public function analyze(string $code, string $filename = ''): array
    {
        $prompt = sprintf($this->getPromptTemplate(), $filename, $code);
        
        try {
            $response = $this->aiManager->request($prompt);
            return $this->parseResponse($response);
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to analyze code quality: ' . $e->getMessage(),
                'issues' => [],
            ];
        }
    }

    public function getName(): string
    {
        return 'quality';
    }

    public function getPromptTemplate(): string
    {
        return <<<PROMPT
Analyze the following PHP code for code quality issues and best practices. Focus on:

1. SOLID principles violations
2. Code complexity (cyclomatic complexity, nesting depth)
3. Code duplication
4. Naming conventions
5. Class and method size
6. Design patterns usage
7. Error handling practices
8. Type declarations and strict types
9. PSR compliance (PSR-1, PSR-2, PSR-4, PSR-12)
10. Symfony best practices

File: %s

Code:
```php
%s
```

Provide your analysis in JSON format:
{
    "quality_score": "1-10 scale",
    "issues": [
        {
            "line": number,
            "type": "quality_issue_type",
            "severity": "info|warning|error",
            "message": "Description of the quality issue",
            "suggestion": "How to improve it"
        }
    ],
    "metrics": {
        "complexity": "low|medium|high",
        "maintainability": "low|medium|high",
        "readability": "low|medium|high"
    },
    "summary": "Overall code quality assessment"
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
            'quality_score' => 'unknown',
            'issues' => [],
            'metrics' => [
                'complexity' => 'unknown',
                'maintainability' => 'unknown',
                'readability' => 'unknown',
            ],
            'summary' => $response,
        ];
    }
}

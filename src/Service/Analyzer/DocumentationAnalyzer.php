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
 * Documentation analyzer for detecting documentation issues.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class DocumentationAnalyzer implements AnalyzerInterface
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
                'error' => 'Failed to analyze documentation: ' . $e->getMessage(),
                'issues' => [],
            ];
        }
    }

    public function getName(): string
    {
        return 'documentation';
    }

    public function getPromptTemplate(): string
    {
        return <<<PROMPT
Analyze the following PHP code for documentation quality and completeness. Focus on:

1. PHPDoc blocks for classes, methods, and properties
2. Parameter and return type documentation
3. Exception documentation (@throws)
4. Code comments quality and usefulness
5. API documentation completeness
6. Usage examples in documentation
7. Deprecation notices
8. @author, @since, @version tags
9. Interface and abstract class documentation
10. Complex logic explanation

File: %s

Code:
```php
%s
```

Provide your analysis in JSON format:
{
    "documentation_score": "1-10 scale",
    "issues": [
        {
            "line": number,
            "type": "documentation_issue_type",
            "severity": "info|warning|error",
            "message": "Description of the documentation issue",
            "suggestion": "How to improve the documentation"
        }
    ],
    "coverage": {
        "classes": "percentage or assessment",
        "methods": "percentage or assessment",
        "properties": "percentage or assessment"
    },
    "suggestions": [
        "General documentation improvement suggestions"
    ],
    "summary": "Overall documentation assessment"
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
            'documentation_score' => 'unknown',
            'issues' => [],
            'coverage' => [
                'classes' => 'unknown',
                'methods' => 'unknown',
                'properties' => 'unknown',
            ],
            'suggestions' => [],
            'summary' => $response,
        ];
    }
}

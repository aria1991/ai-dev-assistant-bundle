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
 * Security analyzer for detecting potential security issues.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class SecurityAnalyzer implements AnalyzerInterface
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
                'error' => 'Failed to analyze security: ' . $e->getMessage(),
                'issues' => [],
            ];
        }
    }

    public function getName(): string
    {
        return 'security';
    }

    public function getPromptTemplate(): string
    {
        return <<<PROMPT
Analyze the following PHP code for security vulnerabilities and issues. Focus on:

1. SQL injection vulnerabilities
2. XSS (Cross-Site Scripting) vulnerabilities  
3. CSRF (Cross-Site Request Forgery) issues
4. Input validation problems
5. Authentication and authorization flaws
6. File upload security issues
7. Information disclosure vulnerabilities
8. Insecure cryptographic practices

File: %s

Code:
```php
%s
```

Provide your analysis in JSON format:
{
    "severity": "low|medium|high|critical",
    "issues": [
        {
            "line": number,
            "type": "vulnerability_type",
            "severity": "low|medium|high|critical", 
            "message": "Description of the issue",
            "suggestion": "How to fix it"
        }
    ],
    "summary": "Overall security assessment"
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
            'severity' => 'unknown',
            'issues' => [],
            'summary' => $response,
        ];
    }
}

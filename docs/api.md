# API Documentation

Complete API reference for the AI Development Assistant Bundle.

## REST API Endpoints

The bundle provides RESTful endpoints for code analysis and management.

### Base URL
```
/api/ai-dev-assistant
```

## Authentication

### API Key Authentication

Add the API key to your request headers:

```http
Authorization: Bearer your-api-key
Content-Type: application/json
```

### JWT Authentication

```http
Authorization: Bearer your-jwt-token
Content-Type: application/json
```

## Code Analysis Endpoints

### Analyze Code

Analyze PHP code for quality, security, and performance issues.

**Endpoint**: `POST /api/ai-dev-assistant/analyze`

**Request Body**:
```json
{
    "code": "<?php\nclass Example {\n    public function test() {\n        return 'Hello World';\n    }\n}",
    "filename": "src/Example.php",
    "analysis_type": "comprehensive",
    "options": {
        "rules": ["psr12", "security_focused"],
        "include_suggestions": true,
        "max_issues": 50
    }
}
```

**Response**:
```json
{
    "success": true,
    "analysis_id": "anal_1234567890",
    "timestamp": "2025-07-28T10:30:00Z",
    "summary": {
        "total_issues": 3,
        "critical": 0,
        "warning": 2,
        "info": 1,
        "quality_score": 8.5,
        "security_score": 9.2
    },
    "issues": [
        {
            "id": "issue_001",
            "type": "code_quality",
            "severity": "warning",
            "title": "Missing return type declaration",
            "description": "Method 'test' should declare its return type",
            "file": "src/Example.php",
            "line": 3,
            "column": 12,
            "rule": "psr12_return_types",
            "suggestion": {
                "description": "Add ': string' return type declaration",
                "code_snippet": "public function test(): string"
            }
        }
    ],
    "metrics": {
        "lines_analyzed": 150,
        "complexity_score": 7.2,
        "maintainability_index": 72,
        "test_coverage": null
    },
    "suggestions": [
        {
            "category": "performance",
            "title": "Consider using typed properties",
            "description": "Adding type declarations improves performance and readability"
        }
    ]
}
```

### Analyze File

Analyze a file by its path.

**Endpoint**: `POST /api/ai-dev-assistant/analyze/file`

**Request Body**:
```json
{
    "file_path": "src/Controller/HomeController.php",
    "analysis_type": "security",
    "options": {
        "rules": ["security_focused", "sql_injection"],
        "exclude_warnings": false
    }
}
```

### Analyze Directory

Analyze multiple files in a directory.

**Endpoint**: `POST /api/ai-dev-assistant/analyze/directory`

**Request Body**:
```json
{
    "directory_path": "src/",
    "analysis_type": "comprehensive",
    "options": {
        "recursive": true,
        "file_extensions": ["php", "twig"],
        "exclude_patterns": ["*Test.php", "*/migrations/*"],
        "max_files": 100
    }
}
```

## Analysis Results

### Get Analysis Result

Retrieve a specific analysis result.

**Endpoint**: `GET /api/ai-dev-assistant/analysis/{analysis_id}`

**Response**:
```json
{
    "success": true,
    "analysis": {
        "id": "anal_1234567890",
        "status": "completed",
        "created_at": "2025-07-28T10:30:00Z",
        "completed_at": "2025-07-28T10:30:15Z",
        "duration_ms": 15000,
        "type": "comprehensive",
        "summary": { /* ... */ },
        "issues": [ /* ... */ ],
        "metrics": { /* ... */ }
    }
}
```

### List Analysis History

Get a list of previous analyses.

**Endpoint**: `GET /api/ai-dev-assistant/analysis`

**Query Parameters**:
- `limit` (int): Number of results (default: 20, max: 100)
- `offset` (int): Pagination offset (default: 0)
- `type` (string): Filter by analysis type
- `status` (string): Filter by status (pending, completed, failed)
- `date_from` (string): Filter from date (ISO 8601)
- `date_to` (string): Filter to date (ISO 8601)

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": "anal_1234567890",
            "type": "comprehensive",
            "status": "completed",
            "created_at": "2025-07-28T10:30:00Z",
            "summary": {
                "total_issues": 3,
                "quality_score": 8.5
            }
        }
    ],
    "pagination": {
        "total": 150,
        "limit": 20,
        "offset": 0,
        "has_more": true
    }
}
```

## Configuration Endpoints

### Get Configuration

Retrieve current bundle configuration.

**Endpoint**: `GET /api/ai-dev-assistant/config`

**Response**:
```json
{
    "success": true,
    "config": {
        "enabled": true,
        "ai_providers": ["openai", "anthropic"],
        "cache_enabled": true,
        "rate_limiting": {
            "enabled": true,
            "requests_per_minute": 60
        },
        "analysis_rules": ["psr12", "solid", "security_focused"]
    }
}
```

### Test AI Providers

Test connectivity to configured AI providers.

**Endpoint**: `POST /api/ai-dev-assistant/test-providers`

**Request Body**:
```json
{
    "providers": ["openai", "anthropic"],
    "test_prompt": "Test connectivity"
}
```

**Response**:
```json
{
    "success": true,
    "results": {
        "openai": {
            "status": "connected",
            "response_time_ms": 1200,
            "model": "gpt-4",
            "error": null
        },
        "anthropic": {
            "status": "failed",
            "response_time_ms": null,
            "model": "claude-3-sonnet-20240229",
            "error": "Invalid API key"
        }
    }
}
```

## Error Responses

### Standard Error Format

All API errors follow this format:

```json
{
    "success": false,
    "error": {
        "code": "INVALID_INPUT",
        "message": "The provided code contains syntax errors",
        "details": {
            "line": 5,
            "column": 12,
            "syntax_error": "Unexpected token ';'"
        },
        "timestamp": "2025-07-28T10:30:00Z",
        "request_id": "req_1234567890"
    }
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `INVALID_INPUT` | 400 | Invalid request data |
| `FILE_NOT_FOUND` | 404 | Specified file doesn't exist |
| `FILE_TOO_LARGE` | 413 | File exceeds size limit |
| `RATE_LIMITED` | 429 | Rate limit exceeded |
| `AI_PROVIDER_ERROR` | 502 | AI provider unavailable |
| `ANALYSIS_FAILED` | 500 | Analysis processing failed |
| `UNAUTHORIZED` | 401 | Invalid authentication |
| `FORBIDDEN` | 403 | Insufficient permissions |

## PHP Service Classes

### CodeAnalyzer Service

The main service for code analysis.

```php
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class YourService
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer
    ) {
    }

    public function analyzeCode(string $code, array $options = []): array
    {
        return $this->codeAnalyzer->analyze(
            code: $code,
            filePath: 'src/Example.php',
            analysisType: 'comprehensive',
            options: $options
        );
    }
}
```

#### Methods

**`analyze(string $code, string $filePath = '', string $analysisType = 'comprehensive', array $options = []): array`**

- `$code`: PHP code to analyze
- `$filePath`: File path for context
- `$analysisType`: Type of analysis ('quality', 'security', 'performance', 'comprehensive')
- `$options`: Additional options

**`analyzeFile(string $filePath, string $analysisType = 'comprehensive', array $options = []): array`**

- `$filePath`: Path to file to analyze
- `$analysisType`: Type of analysis
- `$options`: Additional options

**`analyzeDirectory(string $directoryPath, array $options = []): array`**

- `$directoryPath`: Directory to analyze
- `$options`: Options including file filters

### AIManager Service

Manages AI provider interactions.

```php
use Aria1991\AIDevAssistantBundle\Service\AIManager;

class YourService
{
    public function __construct(
        private AIManager $aiManager
    ) {
    }

    public function customAnalysis(string $prompt): string
    {
        return $this->aiManager->request(
            prompt: $prompt,
            provider: 'openai',
            options: [
                'model' => 'gpt-4',
                'temperature' => 0.1,
                'max_tokens' => 2000
            ]
        );
    }
}
```

#### Methods

**`request(string $prompt, string $provider = null, array $options = []): string`**

- `$prompt`: Prompt to send to AI
- `$provider`: Specific provider ('openai', 'anthropic', 'gemini')
- `$options`: Provider-specific options

**`isAvailable(string $provider = null): bool`**

Check if a provider is available.

**`getAvailableProviders(): array`**

Get list of configured and available providers.

### Analyzer Classes

Individual analyzer services for specific analysis types.

#### QualityAnalyzer

```php
use Aria1991\AIDevAssistantBundle\Service\Analyzer\QualityAnalyzer;

public function __construct(
    private QualityAnalyzer $qualityAnalyzer
) {
}

public function analyzeQuality(string $code): array
{
    return $this->qualityAnalyzer->analyze($code, 'src/Example.php');
}
```

#### SecurityAnalyzer

```php
use Aria1991\AIDevAssistantBundle\Service\Analyzer\SecurityAnalyzer;

public function checkSecurity(string $code): array
{
    return $this->securityAnalyzer->analyze($code);
}
```

#### PerformanceAnalyzer

```php
use Aria1991\AIDevAssistantBundle\Service\Analyzer\PerformanceAnalyzer;

public function checkPerformance(string $code): array
{
    return $this->performanceAnalyzer->analyze($code);
}
```

## Event System

### Analysis Events

The bundle dispatches events during analysis:

```php
use Aria1991\AIDevAssistantBundle\Event\AnalysisStartedEvent;
use Aria1991\AIDevAssistantBundle\Event\AnalysisCompletedEvent;
use Aria1991\AIDevAssistantBundle\Event\AnalysisFailedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AnalysisSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AnalysisStartedEvent::class => 'onAnalysisStarted',
            AnalysisCompletedEvent::class => 'onAnalysisCompleted',
            AnalysisFailedEvent::class => 'onAnalysisFailed',
        ];
    }

    public function onAnalysisStarted(AnalysisStartedEvent $event): void
    {
        // Log analysis start
        $this->logger->info('Analysis started', [
            'file' => $event->getFilePath(),
            'type' => $event->getAnalysisType()
        ]);
    }

    public function onAnalysisCompleted(AnalysisCompletedEvent $event): void
    {
        // Process results
        $result = $event->getResult();
        // Send notification, save to database, etc.
    }
}
```

## Rate Limiting

### Check Rate Limit

```php
use Aria1991\AIDevAssistantBundle\Service\RateLimiter;

public function __construct(
    private RateLimiter $rateLimiter
) {
}

public function checkLimit(string $identifier): bool
{
    return $this->rateLimiter->isAllowed($identifier);
}

public function getRemainingRequests(string $identifier): int
{
    return $this->rateLimiter->getRemainingRequests($identifier);
}
```

## Caching

### Cache Management

```php
use Aria1991\AIDevAssistantBundle\Service\CacheService;

public function __construct(
    private CacheService $cacheService
) {
}

public function getCachedResult(string $cacheKey): ?array
{
    return $this->cacheService->get($cacheKey);
}

public function setCachedResult(string $cacheKey, array $result): void
{
    $this->cacheService->set($cacheKey, $result, 3600); // 1 hour TTL
}

public function clearCache(): void
{
    $this->cacheService->clear();
}
```

## Examples

### Complete Analysis Example

```php
<?php

namespace App\Service;

use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;
use Symfony\Component\HttpFoundation\JsonResponse;

class CodeReviewService
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer
    ) {
    }

    public function reviewPullRequest(array $changedFiles): JsonResponse
    {
        $results = [];
        
        foreach ($changedFiles as $file) {
            if (!str_ends_with($file, '.php')) {
                continue;
            }
            
            $code = file_get_contents($file);
            
            $analysis = $this->codeAnalyzer->analyze(
                code: $code,
                filePath: $file,
                analysisType: 'comprehensive',
                options: [
                    'rules' => ['psr12', 'solid', 'security_focused'],
                    'include_suggestions' => true,
                    'severity_filter' => ['warning', 'error']
                ]
            );
            
            if ($analysis['summary']['total_issues'] > 0) {
                $results[$file] = $analysis;
            }
        }
        
        return new JsonResponse([
            'files_analyzed' => count($changedFiles),
            'files_with_issues' => count($results),
            'results' => $results
        ]);
    }
}
```

### Custom Analysis Rule

```php
<?php

namespace App\Analysis;

use Aria1991\AIDevAssistantBundle\Service\Analyzer\AnalyzerInterface;
use Aria1991\AIDevAssistantBundle\Service\AIManager;

class CustomSecurityAnalyzer implements AnalyzerInterface
{
    public function __construct(
        private AIManager $aiManager
    ) {
    }

    public function analyze(string $code, string $filename = ''): array
    {
        $prompt = sprintf(
            "Analyze this PHP code for company-specific security issues:\n\n%s\n\n" .
            "Look for:\n" .
            "1. Hardcoded secrets or API keys\n" .
            "2. Direct database queries without ORM\n" .
            "3. File operations without validation\n" .
            "4. Missing CSRF protection\n\n" .
            "Return JSON format with issues found.",
            $code
        );

        $response = $this->aiManager->request($prompt);
        
        return $this->parseResponse($response);
    }

    public function getName(): string
    {
        return 'custom_security';
    }
    
    private function parseResponse(string $response): array
    {
        // Parse AI response and return structured data
        // Implementation details...
    }
}
```

---

**Next**: Check the [Troubleshooting Guide](troubleshooting.md) for common issues and solutions.

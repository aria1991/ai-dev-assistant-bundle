# Service API Documentation

Complete service reference for the AI Development Assistant Bundle.

## Service Container Integration

The bundle provides injectable services for code analysis in your Symfony application.

### Core Services

All services are automatically registered in Symfony's container and can be injected via dependency injection:

```php
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;
use Aria1991\AIDevAssistantBundle\Service\AIManager;

class YourController extends AbstractController 
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer,
        private AIManager $aiManager
    ) {}
}
```

## CodeAnalyzer Service

Primary service for analyzing PHP code.

### Basic Usage

```php
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class AnalysisController extends AbstractController
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer
    ) {}
    
    public function analyzeCode(): JsonResponse
    {
        $code = file_get_contents('src/Service/Example.php');
        $result = $this->codeAnalyzer->analyze($code, 'src/Service/Example.php');
        
        return $this->json([
            'success' => true,
            'analysis' => $result,
            'timestamp' => new \DateTime()
```

## Analysis Methods

### analyze()
```php
$result = $this->codeAnalyzer->analyze(
    code: $phpCode,
    filePath: 'src/Service/Example.php',
    analysisType: 'comprehensive', // 'quality', 'security', 'performance', 'documentation'
    options: [
        'rules' => ['psr12', 'solid', 'security_focused'],
        'include_suggestions' => true,
        'severity_filter' => ['warning', 'error']
    ]
);
```

### analyzeFile()
```php
$result = $this->codeAnalyzer->analyzeFile(
    filePath: 'src/Controller/HomeController.php',
    analysisType: 'security'
);
```

### analyzeDirectory()
```php
$result = $this->codeAnalyzer->analyzeDirectory(
    directoryPath: 'src/',
    options: [
        'recursive' => true,
        'exclude_patterns' => ['*Test.php', '*/migrations/*']
    ]
);
```

## Response Format

All analysis methods return the same structured array:

```php
[
    'success' => true,
    'summary' => [
        'total_issues' => 3,
        'critical' => 0,
        'warning' => 2,
        'info' => 1,
        'quality_score' => 8.5,
        'security_score' => 9.2
    ],
    'issues' => [
        [
            'type' => 'code_quality',
            'severity' => 'warning',
            'title' => 'Missing return type declaration',
            'description' => 'Method should declare its return type',
            'file' => 'src/Example.php',
            'line' => 15,
            'suggestion' => 'Add `: string` return type declaration'
        ]
    ],
    'metrics' => [
        'lines_analyzed' => 150,
        'complexity_score' => 7.2,
        'maintainability_index' => 72
    ]
]
```

## AIManager Service

Low-level service for direct AI provider interaction:

```php
use Aria1991\AIDevAssistantBundle\Service\AIManager;

class CustomAnalysisService
{
    public function __construct(
        private AIManager $aiManager
    ) {}

    public function customPrompt(string $code): string
    {
        $prompt = "Review this PHP code and suggest improvements:\n\n" . $code;
        
        return $this->aiManager->request(
            prompt: $prompt,
            provider: 'openai', // or 'anthropic', 'google'
            options: [
                'model' => 'gpt-4',
                'temperature' => 0.1,
                'max_tokens' => 2000
            ]
        );
    }
}
```

## Individual Analyzer Services

Inject specific analyzers for targeted analysis:

### QualityAnalyzer
```php
use Aria1991\AIDevAssistantBundle\Service\Analyzer\QualityAnalyzer;

$result = $this->qualityAnalyzer->analyze($code, $filePath);
```

### SecurityAnalyzer
```php
use Aria1991\AIDevAssistantBundle\Service\Analyzer\SecurityAnalyzer;

$result = $this->securityAnalyzer->analyze($code, $filePath);
```

### PerformanceAnalyzer
```php
use Aria1991\AIDevAssistantBundle\Service\Analyzer\PerformanceAnalyzer;

$result = $this->performanceAnalyzer->analyze($code, $filePath);
```

### DocumentationAnalyzer
```php
use Aria1991\AIDevAssistantBundle\Service\Analyzer\DocumentationAnalyzer;

$result = $this->documentationAnalyzer->analyze($code, $filePath);
```

## Console Commands (Programmatic Usage)

You can also execute console commands programmatically:

```php
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ProgrammaticAnalysis
{
    public function runAnalysis(string $path): array
    {
        $application = new Application();
        $command = $application->find('ai-dev-assistant:analyze');
        
        $input = new ArrayInput([
            'path' => $path,
            '--format' => 'json'
        ]);
        
        $output = new BufferedOutput();
        $command->run($input, $output);
        
        return json_decode($output->fetch(), true);
    }
}
```

## Error Handling

All services throw typed exceptions:

```php
use Aria1991\AIDevAssistantBundle\Exception\AnalysisException;
use Aria1991\AIDevAssistantBundle\Exception\ProviderException;

try {
    $result = $this->codeAnalyzer->analyze($code);
} catch (AnalysisException $e) {
    // Handle analysis-specific errors
    $this->logger->error('Analysis failed: ' . $e->getMessage());
} catch (ProviderException $e) {
    // Handle AI provider errors
    $this->logger->error('AI provider error: ' . $e->getMessage());
}
```

## Configuration in Services

Access bundle configuration in your services:

```php
use Aria1991\AIDevAssistantBundle\Service\ConfigurationHelper;

class YourService
{
    public function __construct(
        private ConfigurationHelper $config
    ) {}

    public function getMaxFileSize(): int
    {
        return $this->config->get('analysis.max_file_size');
    }

    public function isEnabled(): bool
    {
        return $this->config->get('enabled');
    }
}
```

## Real-World Examples

### 1. Code Review API Endpoint

```php
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class CodeReviewController extends AbstractController
{
    #[Route('/api/review', methods: ['POST'])]
    public function review(
        Request $request,
        CodeAnalyzer $codeAnalyzer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        $result = $codeAnalyzer->analyze(
            code: $data['code'],
            filePath: $data['filename'] ?? 'unknown.php'
        );
        
        return $this->json($result);
    }
}
```

### 2. Pre-commit Hook Integration

```php
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class GitHookService
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer
    ) {}

    public function validateCommit(array $changedFiles): bool
    {
        foreach ($changedFiles as $file) {
            if (!str_ends_with($file, '.php')) continue;
            
            $result = $this->codeAnalyzer->analyzeFile($file);
            
            if ($result['summary']['critical'] > 0) {
                return false; // Block commit
            }
        }
        
        return true;
    }
}
```

### 3. CI/CD Integration

```php
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class CIService
{
    public function analyzeProject(): array
    {
        $result = $this->codeAnalyzer->analyzeDirectory('src/');
        
        // Fail CI if quality score too low
        if ($result['summary']['quality_score'] < 7.0) {
            throw new \Exception('Code quality below threshold');
        }
        
        return $result;
    }
}
```

This documentation covers the **actual** service-based API that your bundle provides, not fake REST endpoints!

**Next**: Check the [Troubleshooting Guide](troubleshooting.md) for common issues and solutions.

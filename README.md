# AI Development Assistant Bundle

[![Latest Stable Version](https://poser.pugx.org/aria1991/ai-dev-assistant-bundle/v/stable)](https://packagist.org/packages/aria1991/ai-dev-assistant-bundle)
[![Total Downloads](https://poser.pugx.org/aria1991/ai-dev-assistant-bundle/downloads)](https://packagist.org/packages/aria1991/ai-dev-assistant-bundle)
[![License](https://poser.pugx.org/aria1991/ai-dev-assistant-bundle/license)](https://packagist.org/packages/aria1991/ai-dev-assistant-bundle)
[![CI Pipeline](https://github.com/aria1991/ai-dev-assistant-bundle/workflows/CI%20Pipeline/badge.svg)](https://github.com/aria1991/ai-dev-assistant-bundle/actions)
[![PHP Version](https://poser.pugx.org/aria1991/ai-dev-assistant-bundle/require/php)](https://packagist.org/packages/aria1991/ai-dev-assistant-bundle)
[![Symfony Version](https://img.shields.io/badge/Symfony-6.4%20%7C%207.0-blue.svg)](https://symfony.com/)

A professional-grade Symfony bundle that provides **AI-powered code analysis** with **enterprise-grade architecture**. Get intelligent insights about your code quality, security vulnerabilities, performance bottlenecks, and documentation completeness.

## 🚀 5-Minute Quick Start

```bash
# 1. Install (latest stable: v1.4.0)
composer require aria1991/ai-dev-assistant-bundle

# 2. Auto-configure everything
php bin/console ai-dev-assistant:install

# 3. Add ONE API key to .env (get free keys from links shown in console)
OPENAI_API_KEY=your-key-here

# 4. Test it works
php bin/console ai-dev-assistant:health
php bin/console ai-dev-assistant:config-test

# 5. Analyze your code
php bin/console ai-dev-assistant:analyze src/
```

**That's it! 🎉** Your Symfony app now has enterprise-grade AI-powered code analysis.

## ✨ What You Get

### 🔍 **Intelligent Analysis**
- **Security Vulnerabilities**: SQL injection, XSS, hardcoded secrets
- **Performance Issues**: N+1 queries, memory leaks, slow algorithms  
- **Code Quality**: SOLID principles, design patterns, best practices
- **Documentation**: PHPDoc completeness, comment quality

### 🏗️ **Enterprise Architecture (v1.1+)**
- **Event-Driven**: Extensible with PreAnalysisEvent, PostAnalysisEvent
- **Type-Safe DTOs**: AnalysisRequest/AnalysisResult for better API contracts
- **Auto-Discovery**: Compiler passes for automatic service registration
- **Exception Hierarchy**: Professional error handling with rich context
- **Health Monitoring**: Real-time bundle and provider status checking

### 🚄 **Performance & Caching (v1.3+)**
- **Semantic Caching**: Intelligent reuse of similar code analysis
- **Multi-Provider Fallback**: OpenAI, Anthropic Claude, Google AI
- **Advanced Metrics**: Cache hit rates, performance tracking
- **Background Processing**: Non-blocking analysis workflows

### 🛠️ **Developer Experience (v1.4+)**
- **WebProfiler Integration**: Debug panel in Symfony toolbar
- **Health Check Command**: `php bin/console ai-dev-assistant:health`
- **Zero Config**: Works out of the box with sensible defaults
- **Console Commands**: Perfect for CI/CD pipelines
- **JSON Output**: Machine-readable results for automation

## 📋 Requirements

- **PHP**: 8.2+ (including PHP 8.4 ✅)
- **Symfony**: 6.4+ or 7.0+
- **Extensions**: `ext-json` (required), `ext-curl` (recommended)
- **Internet**: Required for AI provider APIs

> **PHP 8.4 Compatibility**: Fully tested and supported! All features work perfectly with the latest PHP version.

## 📦 Installation

Install the bundle via Composer:

```bash
composer require aria1991/ai-dev-assistant-bundle
```

The bundle will automatically:
- ✅ Register itself in `config/bundles.php`
- ✅ Create configuration files  
- ✅ Set up environment variables
- ✅ Show you exactly what to do next

**Just add one API key and you're ready!**

### Get Your API Key (Choose One)

| Provider | Free Tier | Best For | Get Key |
|----------|-----------|----------|---------|
| **OpenAI** | No (but reliable) | General analysis | [Get Key](https://platform.openai.com/api-keys) |
| **Anthropic** | Limited | Code analysis | [Get Key](https://console.anthropic.com/) |
| **Google AI** | Yes | Getting started | [Get Key](https://makersuite.google.com/app/apikey) |

Add to your `.env` file:
```bash
OPENAI_API_KEY=sk-your-key-here
# OR
ANTHROPIC_API_KEY=sk-ant-your-key-here  
# OR
GOOGLE_AI_API_KEY=your-google-key-here
```

## ⚙️ Configuration

The bundle auto-configures itself! For custom configuration, create `config/packages/ai_dev_assistant.yaml`:

```yaml
ai_dev_assistant:
    enabled: true
    ai:
        providers:
            openai:
                api_key: '%env(OPENAI_API_KEY)%'
                model: 'gpt-4'
            anthropic:
                api_key: '%env(ANTHROPIC_API_KEY)%'  
                model: 'claude-3-sonnet-20240229'
            google:
                api_key: '%env(GOOGLE_AI_API_KEY)%'
                model: 'gemini-pro'
    cache:
        enabled: true
        ttl: 3600
    analysis:
        enabled_analyzers: ['security', 'performance', 'quality', 'documentation']
```

## 🚀 Usage

### Health Check & Status
```bash
# Check bundle health and configuration
php bin/console ai-dev-assistant:health

# Test AI provider connectivity  
php bin/console ai-dev-assistant:config-test
```

### Code Analysis
```bash
# Analyze entire src directory
php bin/console ai-dev-assistant:analyze src/

# Analyze specific file
php bin/console ai-dev-assistant:analyze src/Controller/HomeController.php

# Analyze with specific analyzers
php bin/console ai-dev-assistant:analyze src/ --analyzers=security,performance

# JSON output for automation
php bin/console ai-dev-assistant:analyze src/ --format=json
```

### Advanced Usage (v1.1+)

**Custom Analyzers**: Create your own by implementing `AnalyzerInterface`
```php
final class MyCustomAnalyzer implements AnalyzerInterface
{
    public function getName(): string { return 'custom'; }
    
    public function analyze(string $code, string $filename = ''): array
    {
        // Your analysis logic
        return ['issues' => []];
    }
}
```

**Event Listeners**: Hook into the analysis process
```php
#[AsEventListener]
final class AnalysisListener 
{
    public function onPreAnalysis(PreAnalysisEvent $event): void
    {
        // Modify analysis request
        $event->addAnalyzer('custom');
    }
    
    public function onPostAnalysis(PostAnalysisEvent $event): void  
    {
        // Process results
        $results = $event->getResults();
    }
}
```

### WebProfiler Integration (v1.4+)

The bundle integrates seamlessly with Symfony's WebProfiler for debugging and performance monitoring:

- **Real-time Metrics**: Cache hit rates, provider status, execution times
- **Debug Information**: Analysis results, errors, performance bottlenecks  
- **Provider Monitoring**: Track which AI providers are being used
- **Cache Analytics**: Understand caching effectiveness

Access the AI Analysis panel in your Symfony WebProfiler toolbar during development.

### Type-Safe API Usage (v1.1+)

Use the new DTOs for better type safety and IDE support:

```php
use Aria1991\AIDevAssistantBundle\DTO\AnalysisRequest;
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

public function analyzeWithTypes(CodeAnalyzer $analyzer): void
{
    $request = new AnalysisRequest(
        code: file_get_contents('src/Service/MyService.php'),
        filename: 'src/Service/MyService.php',
        enabledAnalyzers: ['security', 'quality'],
        useCache: true,
        maxTokens: 4000
    );
    
    $result = $analyzer->analyzeFromRequest($request);
    
    if ($result->isSuccessful()) {
        $criticalIssues = $result->getCriticalIssues();
        $cacheHit = $result->wasCached();
        $provider = $result->getAIProvider();
    }
}
```

## ⚙️ Configuration

Create a configuration file `config/packages/ai_dev_assistant.yaml`:

```yaml
ai_dev_assistant:
    enabled: true
    ai:
        providers:
            openai:
                api_key: '%env(OPENAI_API_KEY)%'
                model: 'gpt-4'
            anthropic:
                api_key: '%env(ANTHROPIC_API_KEY)%'  
                model: 'claude-3-sonnet-20240229'
            gemini:
                api_key: '%env(GOOGLE_API_KEY)%'
                model: 'gemini-pro'
    cache:
        enabled: true
        ttl: 3600
```

### Environment Variables

Add your AI provider API keys to `.env`:

```bash
# OpenAI Configuration
OPENAI_API_KEY=sk-your-openai-api-key

# Anthropic Configuration  
ANTHROPIC_API_KEY=your-anthropic-api-key

# Google Gemini Configuration
GOOGLE_API_KEY=your-google-api-key
```

## 🚀 Usage

### Console Commands

Test AI provider connectivity:
```bash
php bin/console ai:test-providers
php bin/console ai:test-providers --provider=openai
php bin/console ai:test-providers --provider=openai --model=gpt-4
```

### Programmatic Usage

```php
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class YourService
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer
    ) {
    }

    public function analyzeCodeFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        
        return $this->codeAnalyzer->analyze(
            code: $code,
            filePath: $filePath,
            analysisType: 'comprehensive' // 'code_quality', 'architecture', 'performance', 'comprehensive'
        );
    }
}
```

### Integration in Services

```php
use Aria1991\AIDevAssistantBundle\Analyzer\HybridCodeQualityAnalyzer;

class YourService
{
    public function __construct(
        private HybridCodeQualityAnalyzer $analyzer
    ) {
    }

    public function reviewPullRequest(string $code): array
    {
        if (!$this->analyzer->supports($code)) {
            throw new \InvalidArgumentException('Code not supported');
        }

        return $this->analyzer->analyze($code, [
            'rules' => ['psr12', 'solid', 'symfony_standards'],
            'depth' => 'comprehensive',
            'include_suggestions' => true
        ]);
    }
}
```

## 🔧 Advanced Configuration

### Custom Analysis Rules

```yaml
ai_dev_assistant:
    analysis:
        rules:
            - psr12
            - solid  
            - symfony_standards
            - security_focused
        custom_rules:
            - name: 'company_standards'
              description: 'Company-specific coding standards'
              patterns:
                  - '/\$this->getDoctrine()/' # Detect deprecated getDoctrine()
    
    static_analysis:
        tools:
            - phpstan
            - psalm
            - php-cs-fixer
```

### Rate Limiting

```yaml
ai_dev_assistant:
    rate_limiting:
        requests_per_minute: 60
        burst_limit: 10
        retry_delay: 1000 # milliseconds
```

## 📊 Analysis Output

The bundle provides comprehensive analysis results:

```php
[
    'success' => true,
    'type' => 'comprehensive',
    'summary' => 'Analysis completed with 3 issues found',
    'issues' => [
        [
            'id' => 'sql_injection_001',
            'title' => 'Potential SQL Injection',
            'description' => 'Raw SQL query detected without parameter binding',
            'severity' => 'critical',
            'category' => 'security',
            'file' => 'src/Repository/UserRepository.php',
            'line' => 42,
            'rule' => 'security_sql_injection',
            'fixSuggestion' => 'Use parameterized queries or the Query Builder'
        ]
    ],
    'suggestions' => [
        [
            'type' => 'performance',
            'title' => 'Optimize Database Queries',
            'description' => 'Consider adding database indexes for frequently queried fields'
        ]
    ],
    'metrics' => [
        'files_analyzed' => 1,
        'lines_of_code' => 150,
        'complexity_score' => 7.2,
        'security_score' => 8.5,
        'maintainability_index' => 72
    ]
]
```

## 🔧 Troubleshooting

### Common Issues

**CI Failures on GitHub Actions**
```bash
# Check PHP version compatibility
php -v  # Should be 8.2+

# Verify all extensions are loaded
php -m | grep -E "(curl|mbstring|json|openssl)"

# Test syntax of new files
php validate-syntax.php
```

**Bundle Services Not Found**
```bash
# Clear Symfony cache
php bin/console cache:clear

# Check bundle registration
php bin/console debug:container ai_dev_assistant

# Verify configuration
php bin/console ai-dev-assistant:config-test
```

**WebProfiler Integration Issues**
```yaml
# Ensure WebProfiler is installed (dev only)
composer require --dev symfony/web-profiler-bundle

# Check if data collector is registered
php bin/console debug:container --tag=data_collector
```

## 🧪 Testing

Run the test suite:

```bash
# Install dependencies
composer install

# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage

# Static analysis
vendor/bin/phpstan analyse

# Code style (automatic download & fix)
.\fix-cs.ps1  # Windows PowerShell
# OR use composer if available
vendor/bin/php-cs-fixer fix --dry-run
```

## 📚 Documentation

- [Installation Guide](docs/installation.md)
- [Configuration Reference](docs/configuration.md)
- [API Documentation](docs/api.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Contributing](CONTRIBUTING.md)

## 🏷️ Versioning & Releases

This project follows [Semantic Versioning](https://semver.org/) (SemVer):

- **Current Version**: `v1.4.0` - Latest stable release with WebProfiler integration and advanced caching
- **Packagist**: Auto-updates on new tags via webhook
- **Release Strategy**: See [TAGGING_STRATEGY.md](TAGGING_STRATEGY.md)

### Recent Release History

- **v1.4.0** - WebProfiler integration, advanced caching, improved developer experience
- **v1.3.0** - Semantic caching system and performance optimizations  
- **v1.2.0** - Health monitoring and comprehensive diagnostics
- **v1.1.0** - Event system, DTOs, and enterprise architecture patterns

### Quick Release Commands

```bash
# Create a patch release (bug fixes)
.\.github\scripts\create-release.ps1 -Version "1.0.1" -Type "patch" -Message "Fix security issue"

# Create a minor release (new features)  
.\.github\scripts\create-release.ps1 -Version "1.1.0" -Type "minor" -Message "Add new analyzer"

# Create a major release (breaking changes)
.\.github\scripts\create-release.ps1 -Version "2.0.0" -Type "major" -Message "API redesign"
```

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

This bundle is licensed under the MIT License. See [LICENSE](LICENSE) for details.

## 🙏 Acknowledgments

- Built for the Symfony community
- Integrates with leading AI providers
- Inspired by enterprise software development best practices

---

**Made with ❤️ by [Aria Vahidi](https://github.com/aria1991) for the Symfony community**
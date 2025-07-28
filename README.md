# AI Development Assistant Bundle

[![Latest Stable Version](https://poser.pugx.org/aria1991/ai-dev-assistant-bundle/v/stable)](https://packagist.org/packages/aria1991/ai-dev-assistant-bundle)
[![Total Downloads](https://poser.pugx.org/aria1991/ai-dev-assistant-bundle/downloads)](https://packagist.org/packages/aria1991/ai-dev-assistant-bundle)
[![License](https://poser.pugx.org/aria1991/ai-dev-assistant-bundle/license)](https://packagist.org/packages/aria1991/ai-dev-assistant-bundle)

A professional-grade Symfony bundle that provides **AI-powered code analysis** with **5-minute setup**. Get intelligent insights about your code quality, security vulnerabilities, performance bottlenecks, and documentation completeness.

## 🚀 5-Minute Quick Start

```bash
# 1. Install
composer require aria1991/ai-dev-assistant-bundle

# 2. Auto-configure everything
php bin/console ai-dev-assistant:install

# 3. Add ONE API key to .env (get free keys from links shown in console)
OPENAI_API_KEY=your-key-here

# 4. Test it works
php bin/console ai-dev-assistant:config-test

# 5. Analyze your code
php bin/console ai-dev-assistant:analyze src/
```

**That's it! 🎉** Your Symfony app now has AI-powered code analysis.

## ✨ What You Get

### 🔍 **Intelligent Analysis**
- **Security Vulnerabilities**: SQL injection, XSS, hardcoded secrets
- **Performance Issues**: N+1 queries, memory leaks, slow algorithms  
- **Code Quality**: SOLID principles, design patterns, best practices
- **Documentation**: PHPDoc completeness, comment quality

### 🏗️ **Production-Ready Features**
- **Zero Config**: Works out of the box with sensible defaults
- **Multi-Provider**: OpenAI, Anthropic Claude, Google AI with auto-fallback
- **Caching**: Built-in result caching (1 hour default)
- **Rate Limiting**: API protection against abuse
- **REST API**: Ready-to-use endpoints for integrations
- **Security First**: Input validation, file type restrictions

### 🛠️ **Developer Experience**
- **Console Commands**: Perfect for CI/CD pipelines
- **JSON Output**: Machine-readable results for automation
- **Flexible**: Analyze single files or entire directories
- **Fast**: Cached results, optimized for large codebases

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

class YourController
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer
    ) {
    }

    public function analyzeCode(): Response
    {
        $code = file_get_contents('path/to/your/file.php');
        
        $result = $this->codeAnalyzer->analyze(
            code: $code,
            filePath: 'src/Controller/YourController.php',
            analysisType: 'comprehensive' // 'code_quality', 'architecture', 'performance', 'comprehensive'
        );
        
        return $this->json($result);
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

# Code style
vendor/bin/php-cs-fixer fix --dry-run
```

## 📚 Documentation

- [Installation Guide](docs/installation.md)
- [Configuration Reference](docs/configuration.md)
- [API Documentation](docs/api.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Contributing](CONTRIBUTING.md)

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

This bundle is licensed under the MIT License. See [LICENSE](LICENSE) for details.

## 🙏 Acknowledgments

- Built for the Symfony community
- Integrates with leading AI providers
- Inspired by enterprise software development best practices

---

**Made with ❤️ 


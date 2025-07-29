# 🚀 Quick Start Guide - AI Development Assistant Bundle

## 5-Minute Setup

### 1. Install the Bundle
```bash
composer require aria1991/ai-dev-assistant-bundle
```

### 2. Auto-Configure Everything
```bash
php bin/console ai-dev-assistant:install
```
This command will:
- ✅ Create configuration files
- ✅ Add environment variables to .env
- ✅ Set up sensible defaults
- ✅ Show you exactly what to do next

### 3. Add Your API Key (Choose One)
Edit your `.env` file and add at least ONE API key:

```bash
# Option 1: OpenAI (Most reliable)
OPENAI_API_KEY=sk-your-openai-key-here

# Option 2: Anthropic Claude (Great for code)  
ANTHROPIC_API_KEY=sk-ant-your-anthropic-key

# Option 3: Google AI (Free tier available)
GOOGLE_AI_API_KEY=your-google-ai-key
```

### 4. Test It Works
```bash
php bin/console ai-dev-assistant:config-test
```

### 5. Analyze Your Code
```bash
# Analyze a single file
php bin/console ai-dev-assistant:analyze src/Controller/HomeController.php

# Analyze entire src/ directory  
php bin/console ai-dev-assistant:analyze src/

# Get JSON output for CI/CD
php bin/console ai-dev-assistant:analyze src/ --format=json
```

## 🎯 That's It!

You now have AI-powered code analysis running on your Symfony application!

## 💻 Service Integration (Optional)

Want to use AI analysis in your own code? Inject the services:

```php
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class YourController extends AbstractController 
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer
    ) {}
    
    public function analyzeCode(): JsonResponse
    {
        $code = file_get_contents('src/Service/Example.php');
        $result = $this->codeAnalyzer->analyze($code, 'src/Service/Example.php');
        
        return $this->json($result);
    }
}
```

## 🔧 Common Issues

**"No providers available"** → Add at least one API key to .env  
**"Rate limit exceeded"** → You're making too many requests, wait a minute  
**"File too large"** → Files must be under 1MB, configure `max_file_size` if needed

## 🎉 What You Get

- **Console Commands**: Perfect for CI/CD integration
- **Multiple Analyzers**: Security, Performance, Quality, Documentation  
- **Caching**: Results are cached for 1 hour by default
- **Rate Limiting**: Built-in protection against API abuse
- **Fallback Chain**: If OpenAI fails, tries Anthropic, then Google
- **Service Integration**: Use AI analysis in your own controllers/services
- **Production Ready**: Full logging, error handling, metrics

Need help? Check the full [README.md](README.md) for detailed documentation.

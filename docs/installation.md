# Installation Guide

This guide provides detailed installation instructions for the AI Development Assistant Bundle.

## Prerequisites

Before installing the bundle, ensure your environment meets these requirements:

### System Requirements
- **PHP**: 8.2 or higher (PHP 8.4 fully supported ✅)
- **Symfony**: 6.4+ or 7.0+
- **Composer**: 2.0 or higher
- **Memory**: At least 256MB PHP memory limit
- **Internet**: Required for AI provider API access

### Required PHP Extensions
- `ext-json` (required) - JSON processing
- `ext-curl` (recommended) - HTTP client functionality
- `ext-mbstring` (recommended) - String handling
- `ext-openssl` (recommended) - Secure HTTP requests

Check your PHP configuration:
```bash
php -m | grep -E "json|curl|mbstring|openssl"
```

## Installation Methods

### Method 1: Composer (Recommended)

Install via Composer in your Symfony project:

```bash
# Navigate to your Symfony project
cd /path/to/your/symfony-project

# Install the bundle
composer require aria1991/ai-dev-assistant-bundle

# The bundle will automatically register itself
# Check config/bundles.php to confirm
```

### Method 2: Manual Installation

If you need manual installation:

1. **Download the bundle**:
```bash
composer require aria1991/ai-dev-assistant-bundle --no-scripts
```

2. **Register the bundle manually** in `config/bundles.php`:
```php
<?php

return [
    // ... other bundles
    Aria1991\AIDevAssistantBundle\AIDevAssistantBundle::class => ['all' => true],
];
```

3. **Create configuration file** `config/packages/ai_dev_assistant.yaml`:
```yaml
ai_dev_assistant:
    enabled: true
```

## Quick Setup Command

After installation, run the setup command for automatic configuration:

```bash
php bin/console ai-dev-assistant:install
```

This command will:
- ✅ Create necessary configuration files
- ✅ Set up environment variables template
- ✅ Configure default settings
- ✅ Show you next steps
- ✅ Test basic functionality

## AI Provider Setup

Choose one or more AI providers for analysis:

### OpenAI (Most Reliable)

1. **Get API Key**: Visit [OpenAI Platform](https://platform.openai.com/api-keys)
2. **Add to .env**:
```bash
OPENAI_API_KEY=sk-your-openai-api-key-here
```
3. **Configure** (optional, defaults are good):
```yaml
# config/packages/ai_dev_assistant.yaml
ai_dev_assistant:
    ai:
        providers:
            openai:
                api_key: '%env(OPENAI_API_KEY)%'
                model: 'gpt-4'  # or 'gpt-3.5-turbo' for cost savings
                max_tokens: 2000
                temperature: 0.1
```

### Anthropic Claude

1. **Get API Key**: Visit [Anthropic Console](https://console.anthropic.com/)
2. **Add to .env**:
```bash
ANTHROPIC_API_KEY=sk-ant-your-anthropic-key-here
```
3. **Configure**:
```yaml
ai_dev_assistant:
    ai:
        providers:
            anthropic:
                api_key: '%env(ANTHROPIC_API_KEY)%'
                model: 'claude-3-sonnet-20240229'
                max_tokens: 4000
```

### Google Gemini (Free Tier Available)

1. **Get API Key**: Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. **Add to .env**:
```bash
GOOGLE_AI_API_KEY=your-google-ai-key-here
```
3. **Configure**:
```yaml
ai_dev_assistant:
    ai:
        providers:
            gemini:
                api_key: '%env(GOOGLE_AI_API_KEY)%'
                model: 'gemini-pro'
                max_tokens: 2048
```

## Verification

Test your installation:

```bash
# Test configuration
php bin/console ai-dev-assistant:config-test

# Test AI provider connectivity
php bin/console ai-dev-assistant:test-providers

# Run a quick analysis
php bin/console ai-dev-assistant:analyze src/Controller --type=quality
```

Expected output:
```bash
✅ Configuration loaded successfully
✅ OpenAI provider: Connected
✅ Cache service: Working
✅ Analysis complete: 0 critical issues found
```

## Docker Installation

For Docker environments:

```dockerfile
# In your Dockerfile
FROM php:8.4-fpm

# Install required extensions
RUN docker-php-ext-install json

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install your Symfony app
COPY . /var/www/html
WORKDIR /var/www/html

# Install dependencies including the bundle
RUN composer install --no-dev --optimize-autoloader

# Set environment variables
ENV OPENAI_API_KEY=your-key-here
```

## Troubleshooting Installation

### Common Issues

**Issue**: Bundle not found
```bash
composer require aria1991/ai-dev-assistant-bundle
# Error: Package not found
```
**Solution**: Ensure you have access to Packagist and check package name spelling.

**Issue**: PHP version conflict
```bash
# Error: requires php ^8.2
```
**Solution**: Upgrade PHP or use compatible version:
```bash
composer require aria1991/ai-dev-assistant-bundle:"^1.0" --with-all-dependencies
```

**Issue**: Extension missing
```bash
# Error: ext-json is missing
```
**Solution**: Install required extensions:
```bash
# Ubuntu/Debian
sudo apt-get install php8.4-json php8.4-curl

# macOS with Homebrew
brew install php@8.4

# Windows
# Enable extensions in php.ini
extension=json
extension=curl
```

### Development Installation

For contributing or development:

```bash
# Clone the repository
git clone https://github.com/aria1991/ai-dev-assistant-bundle.git
cd ai-dev-assistant-bundle

# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Check code style
./fix-cs.ps1  # Windows
# or
vendor/bin/php-cs-fixer fix --dry-run
```

## Next Steps

After successful installation:

1. 📖 Read the [Configuration Reference](configuration.md)
2. 🚀 Check the [API Documentation](api.md)
3. 🧪 Run your first analysis
4. 🔧 Customize settings for your project

## Performance Optimization

For better performance in production:

```bash
# Optimize Composer autoloader
composer dump-autoload --optimize --no-dev

# Enable OPcache
# Add to php.ini:
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Configure caching
# See configuration.md for cache settings
```

---

**Need help?** Check our [Troubleshooting Guide](troubleshooting.md) or open an issue on GitHub.

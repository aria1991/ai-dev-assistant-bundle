# Troubleshooting Guide

Common issues and solutions for the AI Development Assistant Bundle.

## Installation Issues

### Bundle Not Found

**Problem**: 
```bash
composer require aria1991/ai-dev-assistant-bundle
# Could not find package aria1991/ai-dev-assistant-bundle
```

**Solutions**:
1. **Check package name spelling**:
   ```bash
   composer search aria1991/ai-dev-assistant
   ```

2. **Clear Composer cache**:
   ```bash
   composer clear-cache
   composer update
   ```

3. **Check Packagist availability**:
   ```bash
   composer config repositories.packagist.org composer https://packagist.org
   ```

### PHP Version Conflicts

**Problem**:
```bash
# Error: Your PHP version (8.1.0) does not satisfy requirements (^8.2)
```

**Solutions**:
1. **Upgrade PHP**:
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install php8.4
   
   # macOS with Homebrew
   brew install php@8.4
   brew link php@8.4
   
   # Windows - Download from php.net
   ```

2. **Use compatible version**:
   ```bash
   composer require aria1991/ai-dev-assistant-bundle --ignore-platform-reqs
   ```

3. **Check current PHP version**:
   ```bash
   php -v
   composer config platform.php 8.4.0
   ```

### Missing PHP Extensions

**Problem**:
```bash
# Error: ext-json is missing from your system
```

**Solutions**:
1. **Install required extensions**:
   ```bash
   # Ubuntu/Debian
   sudo apt install php8.4-json php8.4-curl php8.4-mbstring
   
   # CentOS/RHEL
   sudo yum install php-json php-curl php-mbstring
   
   # macOS with Homebrew
   brew install php@8.4
   
   # Windows - Enable in php.ini
   extension=json
   extension=curl
   extension=mbstring
   ```

2. **Verify extensions**:
   ```bash
   php -m | grep -E "json|curl|mbstring"
   ```

## Configuration Issues

### Bundle Not Registered

**Problem**:
```bash
# Error: Bundle "AIDevAssistantBundle" is not registered
```

**Solutions**:
1. **Check bundle registration** in `config/bundles.php`:
   ```php
   <?php
   return [
       // ... other bundles
       Aria1991\AIDevAssistantBundle\AIDevAssistantBundle::class => ['all' => true],
   ];
   ```

2. **Clear cache**:
   ```bash
   php bin/console cache:clear
   ```

3. **Run Symfony recipe install**:
   ```bash
   composer symfony:sync-recipes
   ```

### Configuration File Not Found

**Problem**:
```bash
# Error: Configuration file not found
```

**Solutions**:
1. **Create configuration file** `config/packages/ai_dev_assistant.yaml`:
   ```yaml
   ai_dev_assistant:
       enabled: true
   ```

2. **Run setup command**:
   ```bash
   php bin/console ai-dev-assistant:install
   ```

3. **Check file permissions**:
   ```bash
   ls -la config/packages/
   chmod 644 config/packages/ai_dev_assistant.yaml
   ```

## AI Provider Issues

### OpenAI API Errors

**Problem**:
```bash
# Error: Invalid API key provided
```

**Solutions**:
1. **Verify API key**:
   ```bash
   # Check if key is set
   echo $OPENAI_API_KEY
   
   # Test with curl
   curl -H "Authorization: Bearer $OPENAI_API_KEY" \
        https://api.openai.com/v1/models
   ```

2. **Check API key format**:
   ```bash
   # Should start with 'sk-'
   OPENAI_API_KEY=sk-your-key-here
   ```

3. **Verify API quota**:
   - Check usage at https://platform.openai.com/usage
   - Ensure billing is set up
   - Check rate limits

### Anthropic Connection Issues

**Problem**:
```bash
# Error: Failed to connect to Anthropic API
```

**Solutions**:
1. **Check API key format**:
   ```bash
   # Should start with 'sk-ant-'
   ANTHROPIC_API_KEY=sk-ant-your-key-here
   ```

2. **Test connectivity**:
   ```bash
   curl -H "Authorization: Bearer $ANTHROPIC_API_KEY" \
        -H "Content-Type: application/json" \
        -H "anthropic-version: 2023-06-01" \
        https://api.anthropic.com/v1/messages
   ```

3. **Check model availability**:
   ```yaml
   # Use correct model name
   ai_dev_assistant:
       ai:
           providers:
               anthropic:
                   model: 'claude-3-sonnet-20240229'  # Not 'claude-3'
   ```

### Google AI Connection Issues

**Problem**:
```bash
# Error: Google AI API authentication failed
```

**Solutions**:
1. **Enable API**:
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Enable "Generative Language API"
   - Create API credentials

2. **Check API key**:
   ```bash
   curl "https://generativelanguage.googleapis.com/v1/models?key=$GOOGLE_AI_API_KEY"
   ```

3. **Verify quota**:
   - Check quotas in Google Cloud Console
   - Ensure billing is enabled if needed

## Analysis Issues

### Analysis Timeouts

**Problem**:
```bash
# Error: Analysis request timed out
```

**Solutions**:
1. **Increase timeout in configuration**:
   ```yaml
   ai_dev_assistant:
       ai:
           timeout: 60  # Increase from default 30
   ```

2. **Check file size**:
   ```bash
   # Reduce file size or split analysis
   wc -l src/LargeFile.php
   ```

3. **Use streaming for large files**:
   ```php
   $result = $this->codeAnalyzer->analyze(
       $code,
       $filePath,
       'quality',
       ['stream' => true, 'chunk_size' => 1000]
   );
   ```

### Memory Issues

**Problem**:
```bash
# Fatal error: Allowed memory size exhausted
```

**Solutions**:
1. **Increase PHP memory limit**:
   ```bash
   # In php.ini
   memory_limit = 512M
   
   # Or via command line
   php -d memory_limit=512M bin/console ai-dev-assistant:analyze src/
   ```

2. **Process files in batches**:
   ```php
   // Instead of analyzing entire directory at once
   $files = glob('src/**/*.php');
   $batches = array_chunk($files, 10);
   
   foreach ($batches as $batch) {
       foreach ($batch as $file) {
           $this->codeAnalyzer->analyzeFile($file);
       }
       gc_collect_cycles(); // Free memory
   }
   ```

3. **Enable result streaming**:
   ```yaml
   ai_dev_assistant:
       analysis:
           stream_results: true
           batch_size: 5
   ```

### Invalid JSON Response

**Problem**:
```bash
# Error: Invalid JSON in AI response
```

**Solutions**:
1. **Enable response validation**:
   ```yaml
   ai_dev_assistant:
       ai:
           validate_responses: true
           fallback_on_invalid: true
   ```

2. **Increase model temperature**:
   ```yaml
   ai_dev_assistant:
       ai:
           providers:
               openai:
                   temperature: 0.0  # More deterministic responses
   ```

3. **Use structured prompts**:
   ```php
   // In custom analyzer
   $prompt = "Return ONLY valid JSON in this exact format:\n" .
            '{"issues": [], "score": 0}\n\n' .
            "Analyze: " . $code;
   ```

## Cache Issues

### Cache Permission Errors

**Problem**:
```bash
# Error: Unable to write to cache directory
```

**Solutions**:
1. **Fix directory permissions**:
   ```bash
   sudo chown -R www-data:www-data var/cache/
   chmod -R 755 var/cache/
   ```

2. **Clear cache**:
   ```bash
   php bin/console cache:clear
   php bin/console cache:warmup
   ```

3. **Check disk space**:
   ```bash
   df -h
   du -sh var/cache/
   ```

### Redis Connection Issues

**Problem**:
```bash
# Error: Redis connection refused
```

**Solutions**:
1. **Check Redis service**:
   ```bash
   # Start Redis
   sudo systemctl start redis
   sudo systemctl enable redis
   
   # Check status
   redis-cli ping
   ```

2. **Verify Redis configuration**:
   ```yaml
   ai_dev_assistant:
       cache:
           driver: 'redis'
           redis:
               host: 'localhost'  # Check correct host
               port: 6379         # Check correct port
               password: '%env(REDIS_PASSWORD)%'
   ```

3. **Test Redis connectivity**:
   ```bash
   redis-cli -h localhost -p 6379 ping
   ```

## Rate Limiting Issues

### Rate Limit Exceeded

**Problem**:
```bash
# Error: Rate limit exceeded, try again later
```

**Solutions**:
1. **Check current limits**:
   ```bash
   php bin/console ai-dev-assistant:config-test --show-limits
   ```

2. **Adjust rate limits**:
   ```yaml
   ai_dev_assistant:
       rate_limiting:
           requests_per_minute: 120  # Increase limit
           burst_limit: 20          # Allow higher bursts
   ```

3. **Implement backoff strategy**:
   ```php
   try {
       $result = $this->codeAnalyzer->analyze($code);
   } catch (RateLimitException $e) {
       sleep($e->getRetryAfter());
       $result = $this->codeAnalyzer->analyze($code);
   }
   ```

## Performance Issues

### Slow Analysis

**Problem**: Analysis takes too long to complete

**Solutions**:
1. **Enable caching**:
   ```yaml
   ai_dev_assistant:
       cache:
           enabled: true
           ttl: 7200  # Cache for 2 hours
   ```

2. **Use faster models**:
   ```yaml
   ai_dev_assistant:
       ai:
           providers:
               openai:
                   model: 'gpt-3.5-turbo'  # Faster than gpt-4
   ```

3. **Optimize file selection**:
   ```bash
   # Exclude unnecessary files
   php bin/console ai-dev-assistant:analyze src/ \
       --exclude="*/tests/*" \
       --exclude="*/vendor/*" \
       --max-files=50
   ```

4. **Use parallel processing**:
   ```yaml
   ai_dev_assistant:
       analysis:
           parallel_processing: true
           max_workers: 4
   ```

### High Memory Usage

**Problem**: Application uses too much memory

**Solutions**:
1. **Monitor memory usage**:
   ```bash
   php -d memory_limit=256M bin/console ai-dev-assistant:analyze src/ --verbose
   ```

2. **Process in smaller batches**:
   ```php
   // Configure smaller batch sizes
   $analyzer->setBatchSize(5);
   ```

3. **Clear memory between analyses**:
   ```php
   foreach ($files as $file) {
       $result = $analyzer->analyzeFile($file);
       unset($result); // Free memory
       gc_collect_cycles();
   }
   ```

## Console Command Issues

### Command Not Found

**Problem**:
```bash
# Error: Command "ai-dev-assistant:analyze" is not defined
```

**Solutions**:
1. **Clear cache**:
   ```bash
   php bin/console cache:clear
   ```

2. **Check bundle registration**:
   ```bash
   php bin/console debug:container | grep AIDevAssistant
   ```

3. **Verify autoload**:
   ```bash
   composer dump-autoload
   ```

### Command Arguments Invalid

**Problem**:
```bash
# Error: Invalid argument provided
```

**Solutions**:
1. **Check command help**:
   ```bash
   php bin/console ai-dev-assistant:analyze --help
   ```

2. **Use correct syntax**:
   ```bash
   # Correct
   php bin/console ai-dev-assistant:analyze src/ --type=quality
   
   # Incorrect
   php bin/console ai-dev-assistant:analyze --type quality src/
   ```

## Security Issues

### File Access Denied

**Problem**:
```bash
# Error: Permission denied reading file
```

**Solutions**:
1. **Check file permissions**:
   ```bash
   ls -la src/
   chmod 644 src/*.php
   ```

2. **Verify user permissions**:
   ```bash
   # Run as web server user
   sudo -u www-data php bin/console ai-dev-assistant:analyze src/
   ```

3. **Configure allowed paths**:
   ```yaml
   ai_dev_assistant:
       security:
           allowed_paths:
               - 'src/'
               - 'lib/'
           blocked_paths:
               - 'var/'
               - 'vendor/'
   ```

## Development Issues

### Debug Mode

Enable debug mode for troubleshooting:

```yaml
# config/packages/dev/ai_dev_assistant.yaml
ai_dev_assistant:
    debug: true
    logging:
        enabled: true
        level: 'debug'
```

### Logging Configuration

```yaml
# config/packages/monolog.yaml
monolog:
    channels: ['ai_dev_assistant']
    handlers:
        ai_dev_assistant:
            type: stream
            path: '%kernel.logs_dir%/ai_dev_assistant.log'
            level: debug
            channels: ['ai_dev_assistant']
```

### Profiling

Use Symfony Profiler to debug performance:

```yaml
# config/packages/dev/web_profiler.yaml
web_profiler:
    toolbar: true
    intercept_redirects: false
    
framework:
    profiler:
        only_exceptions: false
        collect_serializer_data: true
```

## Getting Help

### Diagnostic Commands

Run these commands to gather diagnostic information:

```bash
# System information
php --version
composer --version

# Bundle information
php bin/console debug:container | grep AIDevAssistant
php bin/console ai-dev-assistant:config-test

# Configuration check
php bin/console debug:config ai_dev_assistant

# Environment check
php bin/console about
```

### Log Analysis

Check logs for detailed error information:

```bash
# Application logs
tail -f var/log/dev.log | grep ai_dev_assistant

# Symfony logs
tail -f var/log/ai_dev_assistant.log

# Web server logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
```

### Performance Profiling

```bash
# Profile memory usage
php -d memory_limit=1G bin/console ai-dev-assistant:analyze src/ --memory-profile

# Profile execution time
time php bin/console ai-dev-assistant:analyze src/

# Use Xdebug profiling
php -d xdebug.mode=profile bin/console ai-dev-assistant:analyze src/
```

## Common Error Messages

### "AI provider unavailable"
- Check internet connectivity
- Verify API keys
- Check provider status pages
- Try alternative providers

### "Analysis failed with empty response"
- Check file content is valid PHP
- Increase timeout settings
- Try smaller code samples
- Check AI provider limits

### "Configuration validation failed"
- Validate YAML syntax
- Check required parameters
- Clear configuration cache
- Run config:dump to see resolved config

### "Cache write failed"
- Check directory permissions
- Verify disk space
- Test cache connectivity (Redis/Memcached)
- Clear existing cache

## Support Resources

- **Documentation**: [docs/](../docs/)
- **GitHub Issues**: [Report bugs and request features](https://github.com/aria1991/ai-dev-assistant-bundle/issues)
- **Contributing**: [CONTRIBUTING.md](../CONTRIBUTING.md)
- **Security**: [SECURITY.md](../SECURITY.md)

---

**Still having issues?** Open an issue on GitHub with:
1. PHP version (`php --version`)
2. Symfony version (`composer show symfony/framework-bundle`)
3. Bundle version (`composer show aria1991/ai-dev-assistant-bundle`)
4. Complete error message and stack trace
5. Configuration files (with sensitive data redacted)

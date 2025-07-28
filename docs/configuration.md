# Configuration Reference

Complete configuration guide for the AI Development Assistant Bundle.

## Configuration File Structure

The main configuration file is `config/packages/ai_dev_assistant.yaml`:

```yaml
ai_dev_assistant:
    enabled: true                    # Master enable/disable switch
    ai:                             # AI provider configuration
        providers: { }              # Provider-specific settings
        fallback_enabled: true      # Enable automatic fallback
        timeout: 30                 # Request timeout in seconds
    cache:                          # Caching configuration
        enabled: true               # Enable/disable caching
        ttl: 3600                  # Cache time-to-live in seconds
        driver: 'file'             # Cache driver (file, redis, memcached)
    rate_limiting:                  # Rate limiting configuration
        enabled: true               # Enable rate limiting
        requests_per_minute: 60     # Maximum requests per minute
        burst_limit: 10            # Burst allowance
    analysis:                       # Analysis configuration
        rules: []                  # Analysis rules to apply
        exclude_patterns: []       # File patterns to exclude
        max_file_size: 1048576    # Maximum file size in bytes (1MB)
```

## AI Provider Configuration

### OpenAI Configuration

```yaml
ai_dev_assistant:
    ai:
        providers:
            openai:
                enabled: true
                api_key: '%env(OPENAI_API_KEY)%'
                model: 'gpt-4'                    # Model to use
                base_url: 'https://api.openai.com'
                max_tokens: 2000                  # Maximum response tokens
                temperature: 0.1                  # Creativity level (0-1)
                timeout: 30                       # Request timeout
                retry_attempts: 3                 # Retry failed requests
                retry_delay: 1000                # Retry delay in milliseconds
```

**Available Models**:
- `gpt-4` - Most capable, higher cost
- `gpt-4-turbo` - Fast and capable
- `gpt-3.5-turbo` - Cost-effective option

### Anthropic Configuration

```yaml
ai_dev_assistant:
    ai:
        providers:
            anthropic:
                enabled: true
                api_key: '%env(ANTHROPIC_API_KEY)%'
                model: 'claude-3-sonnet-20240229'
                base_url: 'https://api.anthropic.com'
                max_tokens: 4000
                temperature: 0.1
                timeout: 30
                retry_attempts: 3
```

**Available Models**:
- `claude-3-opus-20240229` - Most powerful
- `claude-3-sonnet-20240229` - Balanced performance
- `claude-3-haiku-20240307` - Fast and efficient

### Google Gemini Configuration

```yaml
ai_dev_assistant:
    ai:
        providers:
            gemini:
                enabled: true
                api_key: '%env(GOOGLE_AI_API_KEY)%'
                model: 'gemini-pro'
                base_url: 'https://generativelanguage.googleapis.com'
                max_tokens: 2048
                temperature: 0.1
                timeout: 30
```

**Available Models**:
- `gemini-pro` - General purpose
- `gemini-pro-vision` - With image understanding

## Cache Configuration

### File Cache (Default)

```yaml
ai_dev_assistant:
    cache:
        enabled: true
        driver: 'file'
        ttl: 3600                    # 1 hour
        path: '%kernel.cache_dir%/ai_dev_assistant'
        cleanup_probability: 0.01    # 1% chance to cleanup expired entries
```

### Redis Cache

```yaml
ai_dev_assistant:
    cache:
        enabled: true
        driver: 'redis'
        ttl: 7200                    # 2 hours
        redis:
            host: 'localhost'
            port: 6379
            database: 0
            password: '%env(REDIS_PASSWORD)%'
            prefix: 'ai_dev_assistant:'
```

### Memcached Cache

```yaml
ai_dev_assistant:
    cache:
        enabled: true
        driver: 'memcached'
        ttl: 3600
        memcached:
            servers:
                - { host: 'localhost', port: 11211, weight: 100 }
            options:
                compression: true
                prefix: 'ai_dev_assistant_'
```

## Analysis Rules Configuration

### Built-in Rules

```yaml
ai_dev_assistant:
    analysis:
        rules:
            # Code Quality Rules
            - 'psr12'                # PSR-12 coding standard
            - 'solid'                # SOLID principles
            - 'symfony_standards'    # Symfony best practices
            - 'design_patterns'      # Design pattern usage
            
            # Security Rules
            - 'security_focused'     # Security vulnerability detection
            - 'sql_injection'        # SQL injection prevention
            - 'xss_protection'       # XSS vulnerability detection
            - 'csrf_protection'      # CSRF protection checks
            
            # Performance Rules
            - 'performance_focused'  # Performance optimization
            - 'database_efficiency'  # Database query optimization
            - 'memory_usage'         # Memory efficiency
            
            # Documentation Rules
            - 'documentation_complete' # PHPDoc completeness
            - 'comment_quality'       # Comment quality assessment
```

### Custom Rules

```yaml
ai_dev_assistant:
    analysis:
        custom_rules:
            - name: 'company_standards'
              description: 'Company-specific coding standards'
              severity: 'warning'
              patterns:
                  # Deprecated Symfony methods
                  - pattern: '/\$this->getDoctrine\(\)/'
                    message: 'getDoctrine() is deprecated, inject repositories directly'
                    suggestion: 'Use dependency injection for repositories'
                  
                  # Hardcoded configuration
                  - pattern: '/define\([\'"].*[\'"],.*\)/'
                    message: 'Avoid hardcoded configuration'
                    suggestion: 'Use environment variables or configuration files'
                  
                  # Non-typed properties
                  - pattern: '/private \$\w+;/'
                    message: 'Properties should be typed'
                    suggestion: 'Add type declarations to properties'
            
            - name: 'api_standards'
              description: 'API development standards'
              patterns:
                  - pattern: '/public function [a-z][A-Za-z]*\(\).*Response/'
                    message: 'API methods should have return type declarations'
```

## Rate Limiting Configuration

### Basic Rate Limiting

```yaml
ai_dev_assistant:
    rate_limiting:
        enabled: true
        requests_per_minute: 60      # Total requests per minute
        burst_limit: 10             # Burst allowance
        retry_delay: 1000           # Delay between retries (ms)
        storage: 'cache'            # Storage backend (cache, redis, database)
```

### Advanced Rate Limiting

```yaml
ai_dev_assistant:
    rate_limiting:
        enabled: true
        strategies:
            global:
                requests_per_minute: 60
                burst_limit: 10
            per_user:
                requests_per_minute: 20
                burst_limit: 5
            per_ip:
                requests_per_minute: 100
                burst_limit: 20
        storage:
            driver: 'redis'
            redis:
                host: '%env(REDIS_HOST)%'
                port: '%env(REDIS_PORT)%'
```

## Security Configuration

### Input Validation

```yaml
ai_dev_assistant:
    security:
        input_validation:
            enabled: true
            max_file_size: 1048576      # 1MB
            allowed_extensions:
                - 'php'
                - 'twig'
                - 'yaml'
                - 'yml'
                - 'xml'
            blocked_patterns:
                - '/eval\(/'            # Dangerous functions
                - '/system\(/'
                - '/exec\(/'
        
        output_sanitization:
            enabled: true
            strip_dangerous_content: true
            escape_html: true
```

### API Security

```yaml
ai_dev_assistant:
    api:
        authentication:
            enabled: true
            methods: ['api_key', 'jwt']
        cors:
            enabled: true
            allowed_origins: ['*']
            allowed_methods: ['GET', 'POST']
            allowed_headers: ['Content-Type', 'Authorization']
```

## Environment Variables

### Required Variables

```bash
# AI Provider API Keys (choose at least one)
OPENAI_API_KEY=sk-your-openai-key
ANTHROPIC_API_KEY=sk-ant-your-anthropic-key  
GOOGLE_AI_API_KEY=your-google-key

# Optional Redis Configuration
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=your-redis-password

# Optional Database Configuration (for rate limiting storage)
DATABASE_URL="mysql://user:pass@localhost:3306/dbname"
```

### Optional Variables

```bash
# Override default settings
AI_DEV_ASSISTANT_ENABLED=true
AI_DEV_ASSISTANT_CACHE_TTL=7200
AI_DEV_ASSISTANT_RATE_LIMIT=100
AI_DEV_ASSISTANT_MAX_FILE_SIZE=2097152

# Development/Debug settings
AI_DEV_ASSISTANT_DEBUG=false
AI_DEV_ASSISTANT_LOG_LEVEL=info
```

## Performance Tuning

### Production Configuration

```yaml
ai_dev_assistant:
    enabled: true
    
    # Optimize for production
    cache:
        enabled: true
        driver: 'redis'             # Use Redis for better performance
        ttl: 7200                   # Cache for 2 hours
    
    rate_limiting:
        enabled: true
        requests_per_minute: 120    # Higher limit for production
        storage: 'redis'            # Use Redis for rate limiting
    
    ai:
        providers:
            openai:
                timeout: 60         # Longer timeout for complex analysis
                retry_attempts: 5   # More retries for reliability
                model: 'gpt-4'      # Use best model in production
```

### Development Configuration

```yaml
ai_dev_assistant:
    enabled: true
    
    # Optimize for development
    cache:
        enabled: true
        driver: 'file'              # Simple file cache for dev
        ttl: 300                    # Short cache for testing
    
    rate_limiting:
        enabled: false              # Disable rate limiting in dev
    
    ai:
        providers:
            openai:
                model: 'gpt-3.5-turbo'  # Cost-effective for development
                temperature: 0.2         # Slightly more creative for suggestions
```

## Logging Configuration

```yaml
ai_dev_assistant:
    logging:
        enabled: true
        level: 'info'               # debug, info, warning, error
        channels:
            analysis: true          # Log analysis requests
            cache: false           # Log cache operations
            rate_limiting: true    # Log rate limiting events
            ai_requests: true      # Log AI API requests
        
        # Log to specific files
        handlers:
            file:
                path: '%kernel.logs_dir%/ai_dev_assistant.log'
                level: 'info'
            syslog:
                enabled: false
```

## Complete Example Configuration

```yaml
# config/packages/ai_dev_assistant.yaml
ai_dev_assistant:
    enabled: true
    
    ai:
        providers:
            openai:
                enabled: true
                api_key: '%env(OPENAI_API_KEY)%'
                model: 'gpt-4'
                max_tokens: 2000
                temperature: 0.1
                timeout: 30
                retry_attempts: 3
            
            anthropic:
                enabled: true
                api_key: '%env(ANTHROPIC_API_KEY)%'
                model: 'claude-3-sonnet-20240229'
        
        fallback_enabled: true
        timeout: 30
    
    cache:
        enabled: true
        driver: 'redis'
        ttl: 3600
        redis:
            host: '%env(REDIS_HOST)%'
            port: '%env(REDIS_PORT)%'
            password: '%env(REDIS_PASSWORD)%'
    
    rate_limiting:
        enabled: true
        requests_per_minute: 60
        burst_limit: 10
        storage: 'redis'
    
    analysis:
        rules:
            - 'psr12'
            - 'solid'
            - 'symfony_standards'
            - 'security_focused'
            - 'performance_focused'
        
        exclude_patterns:
            - 'var/*'
            - 'vendor/*'
            - '*.min.js'
        
        max_file_size: 1048576
    
    security:
        input_validation:
            enabled: true
            allowed_extensions: ['php', 'twig', 'yaml']
        output_sanitization:
            enabled: true
    
    logging:
        enabled: true
        level: 'info'
```

---

**Next**: Check the [API Documentation](api.md) for usage examples.

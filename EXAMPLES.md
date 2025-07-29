# Usage Examples - AI Development Assistant Bundle

## 🎯 Real-World Usage Examples

### 1. **CI/CD Integration**

Add to your `.github/workflows/ci.yml`:

```yaml
- name: AI Code Analysis
  run: |
    php bin/console ai-dev-assistant:analyze src/ --format=json > analysis.json
    # Fail if critical issues found
    if grep -q '"critical_issues":[1-9]' analysis.json; then
      echo "❌ Critical security issues found!"
      exit 1
    fi
```

### 2. **Pre-commit Hook**

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash
echo "🤖 Running AI code analysis..."
php bin/console ai-dev-assistant:analyze $(git diff --cached --name-only --diff-filter=ACMR | grep '\.php$')
if [ $? -ne 0 ]; then
    echo "❌ Code analysis failed. Please fix issues before committing."
    exit 1
fi
echo "✅ Code analysis passed!"
```

### 3. **Analyzing Specific Issues**

```bash
# Focus on security only
php bin/console ai-dev-assistant:analyze src/Controller/ --analyzers=security

# Check performance in services
php bin/console ai-dev-assistant:analyze src/Service/ --analyzers=performance

# Documentation review
php bin/console ai-dev-assistant:analyze src/ --analyzers=documentation
```

### 4. **Service Integration**

```php
// In your Symfony controller
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class CodeReviewController extends AbstractController
{
    public function __construct(
        private CodeAnalyzer $codeAnalyzer
    ) {}

    #[Route('/review', methods: ['POST'])]
    public function review(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';
        
        $result = $this->codeAnalyzer->analyze(
            code: $code,
            filePath: $data['filename'] ?? 'uploaded.php'
        );
        
        return $this->json($result);
    }
}
```

### 5. **JavaScript Frontend Integration**

```javascript
// Submit code for analysis via your controller
async function analyzeCode(code, filename) {
    const response = await fetch('/review', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code, filename })
    });
    
    const analysis = await response.json();
    displayResults(analysis);
}

function displayResults(analysis) {
    const issues = analysis.summary.total_issues;
    const riskScore = analysis.risk_score;
    
    console.log(`Found ${issues} issues with ${riskScore} risk level`);
}
```

### 6. **Batch Processing**

```bash
#!/bin/bash
# Analyze multiple directories
DIRS=("src/Controller" "src/Service" "src/Entity")

for dir in "${DIRS[@]}"; do
    echo "Analyzing $dir..."
    php bin/console ai-dev-assistant:analyze "$dir" \
        --format=json > "analysis-$(basename $dir).json"
done

# Combine results
jq -s 'add' analysis-*.json > combined-analysis.json
```

### 7. **Custom Configuration**

```yaml
# config/packages/ai_dev_assistant.yaml
ai_dev_assistant:
    # Only analyze what you need
    analysis:
        enabled_analyzers: ['security', 'performance']
        max_file_size: 2097152  # 2MB for larger files
        excluded_paths:
            - 'src/Migrations/'
            - 'tests/'
            - 'var/'
    
    # Aggressive caching for large projects
    cache:
        ttl: 7200  # 2 hours
    
    # Relaxed rate limiting for internal use
    rate_limiting:
        requests_per_minute: 120
```

### 8. **Docker Integration**

```dockerfile
# Dockerfile
FROM php:8.2-cli
COPY . /app
WORKDIR /app
RUN composer install
ENV OPENAI_API_KEY=your-key-here
CMD ["php", "bin/console", "ai-dev-assistant:analyze", "src/"]
```

### 9. **Quality Gates**

```php
// Custom quality checker
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;

class QualityGate
{
    public function __construct(private CodeAnalyzer $analyzer) {}
    
    public function checkQuality(string $filePath): bool
    {
        $analysis = $this->analyzer->analyzeFile($filePath);
        
        // Fail if critical issues found
        if (($analysis['summary']['critical_issues'] ?? 0) > 0) {
            return false;
        }
        
        // Fail if too many high severity issues
        if (($analysis['summary']['high_issues'] ?? 0) > 5) {
            return false;
        }
        
        return true;
    }
}
```

### 10. **IDE Integration**

For PhpStorm, create External Tool:

- **Program**: `php`
- **Arguments**: `bin/console ai-dev-assistant:analyze $FilePath$`
- **Working Directory**: `$ProjectFileDir$`

### 11. **Monitoring & Alerts**

```php
// Monitor analysis results
use Psr\Log\LoggerInterface;

class AnalysisMonitor
{
    public function __construct(private LoggerInterface $logger) {}
    
    public function monitorProject(string $path): void
    {
        $analysis = $this->analyzer->analyzeFile($path);
        $criticalIssues = $analysis['summary']['critical_issues'] ?? 0;
        
        if ($criticalIssues > 0) {
            $this->logger->critical('Critical security issues found', [
                'file' => $path,
                'issues' => $criticalIssues
            ]);
            
            // Send Slack notification, email, etc.
            $this->sendAlert($analysis);
        }
    }
}
```

## 📊 Output Examples

### Security Analysis Output
```json
{
    "analyzers": {
        "security": {
            "severity": "high",
            "issues": [
                {
                    "line": 42,
                    "type": "sql_injection",
                    "severity": "critical",
                    "message": "Potential SQL injection vulnerability",
                    "suggestion": "Use parameterized queries or Doctrine ORM"
                }
            ]
        }
    }
}
```

### Performance Analysis Output
```json
{
    "analyzers": {
        "performance": {
            "performance_score": "6",
            "issues": [
                {
                    "line": 15,
                    "type": "n_plus_one_query",
                    "impact": "high",
                    "message": "Potential N+1 query problem in loop",
                    "suggestion": "Use JOIN or batch loading"
                }
            ]
        }
    }
}
```

## 🔧 Troubleshooting

**Problem**: "No providers available"  
**Solution**: Run `php bin/console ai-dev-assistant:config-test` to see which API keys are missing

**Problem**: Analysis is slow  
**Solution**: Enable caching and exclude large directories in configuration

**Problem**: Too many API requests  
**Solution**: Increase cache TTL and use rate limiting configuration

## 💡 Tips

1. **Start Small**: Begin with single files before analyzing entire directories
2. **Use Caching**: Enable caching for repeated analysis of same files
3. **Filter Results**: Use specific analyzers for focused reviews
4. **CI Integration**: Set up automated analysis in your deployment pipeline
5. **Monitor Costs**: AI API calls cost money - use caching and rate limiting

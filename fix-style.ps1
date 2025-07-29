# PowerShell script to apply PHP CS Fixer corrections
Write-Host "Applying PHP CS Fixer corrections..." -ForegroundColor Green

# Check if we're in a git repository
if (-not (Test-Path ".git")) {
    Write-Host "Error: Not in a git repository" -ForegroundColor Red
    exit 1
}

# Check if PHP CS Fixer config exists
if (-not (Test-Path ".php-cs-fixer.dist.php")) {
    Write-Host "Error: PHP CS Fixer config not found" -ForegroundColor Red
    exit 1
}

try {
    # Download PHP CS Fixer if not available
    if (-not (Test-Path "vendor/bin/php-cs-fixer") -and -not (Test-Path "vendor/bin/php-cs-fixer.bat")) {
        Write-Host "PHP CS Fixer not found in vendor, downloading..." -ForegroundColor Yellow
        
        # Download PHP CS Fixer phar
        $url = "https://cs.symfony.com/download/php-cs-fixer-v3.phar"
        $output = "php-cs-fixer.phar"
        
        Invoke-WebRequest -Uri $url -OutFile $output
        
        if (Test-Path $output) {
            Write-Host "Downloaded PHP CS Fixer successfully" -ForegroundColor Green
            
            # Apply fixes using downloaded phar
            Write-Host "Applying code style fixes..." -ForegroundColor Yellow
            php php-cs-fixer.phar fix --config=.php-cs-fixer.dist.php
            
            # Clean up
            Remove-Item $output -Force
        } else {
            Write-Host "Failed to download PHP CS Fixer" -ForegroundColor Red
            exit 1
        }
    } else {
        # Use vendor version
        Write-Host "Using vendor PHP CS Fixer..." -ForegroundColor Yellow
        
        if (Test-Path "vendor/bin/php-cs-fixer") {
            vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
        } else {
            vendor/bin/php-cs-fixer.bat fix --config=.php-cs-fixer.dist.php
        }
    }
    
    Write-Host "PHP CS Fixer completed successfully!" -ForegroundColor Green
    
    # Show what was changed
    Write-Host "Checking for changes..." -ForegroundColor Yellow
    git status --porcelain
    
} catch {
    Write-Host "Error applying PHP CS Fixer: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

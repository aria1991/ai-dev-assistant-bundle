# PHP CS Fixer Download and Run Script
# This script downloads PHP CS Fixer and runs it to fix code style issues

Write-Host "PHP CS Fixer Download and Fix Script" -ForegroundColor Cyan
Write-Host ""

# Check if PHP CS Fixer already exists
if (!(Test-Path "php-cs-fixer.phar")) {
    Write-Host "Downloading PHP CS Fixer..." -ForegroundColor Yellow
    try {
        Invoke-WebRequest -Uri "https://cs.symfony.com/download/php-cs-fixer-v3.phar" -OutFile "php-cs-fixer.phar"
        Write-Host "Downloaded PHP CS Fixer successfully" -ForegroundColor Green
    } catch {
        Write-Host "Failed to download PHP CS Fixer: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "PHP CS Fixer already exists" -ForegroundColor Green
}

Write-Host ""
Write-Host "Running PHP CS Fixer..." -ForegroundColor Cyan

# Run PHP CS Fixer to fix code style issues
try {
    php php-cs-fixer.phar fix --config=.php-cs-fixer.dist.php
    Write-Host ""
    Write-Host "PHP CS Fixer completed successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Code style issues have been fixed." -ForegroundColor White
    Write-Host "You can now commit your changes:" -ForegroundColor Yellow
    Write-Host "  git add ." -ForegroundColor Gray
    Write-Host "  git commit -m 'fix: apply PHP CS Fixer code style fixes'" -ForegroundColor Gray
} catch {
    Write-Host "PHP CS Fixer failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

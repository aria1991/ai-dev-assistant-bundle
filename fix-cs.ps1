# PHP CS Fixer Script
# This script tries to run PHP CS Fixer using available methods

Write-Host "PHP CS Fixer Script" -ForegroundColor Cyan
Write-Host ""

# Try different methods to run PHP CS Fixer
$csFixerFound = $false

# Method 1: Try vendor/bin/php-cs-fixer (Composer local install)
if (Test-Path "vendor/bin/php-cs-fixer") {
    Write-Host "Using Composer-installed PHP CS Fixer..." -ForegroundColor Green
    php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
    $csFixerFound = $true
}
# Method 2: Try global php-cs-fixer command
elseif (Get-Command "php-cs-fixer" -ErrorAction SilentlyContinue) {
    Write-Host "Using globally installed PHP CS Fixer..." -ForegroundColor Green
    php-cs-fixer fix --config=.php-cs-fixer.dist.php
    $csFixerFound = $true
}
# Method 3: Download temporarily (fallback)
else {
    Write-Host "PHP CS Fixer not found. Downloading temporarily..." -ForegroundColor Yellow
    try {
        Invoke-WebRequest -Uri "https://cs.symfony.com/download/php-cs-fixer-v3.phar" -OutFile "php-cs-fixer.phar"
        Write-Host "Downloaded PHP CS Fixer temporarily" -ForegroundColor Green
        php php-cs-fixer.phar fix --config=.php-cs-fixer.dist.php
        Remove-Item "php-cs-fixer.phar" -Force
        Write-Host "Cleaned up temporary file" -ForegroundColor Blue
        $csFixerFound = $true
    } catch {
        Write-Host "Failed to download PHP CS Fixer: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

if ($csFixerFound) {
    Write-Host ""
    Write-Host "PHP CS Fixer completed successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Recommendation: Install PHP CS Fixer globally or via Composer:" -ForegroundColor Yellow
    Write-Host "  composer global require friendsofphp/php-cs-fixer" -ForegroundColor Gray
    Write-Host "  # OR add to composer.json require-dev and run: composer install" -ForegroundColor Gray
    Write-Host "You can now commit your changes:" -ForegroundColor Yellow
    Write-Host "  git add ." -ForegroundColor Gray
    Write-Host "  git commit -m 'fix: apply PHP CS Fixer code style fixes'" -ForegroundColor Gray
} catch {
    Write-Host "PHP CS Fixer failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

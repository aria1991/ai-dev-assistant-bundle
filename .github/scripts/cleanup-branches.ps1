# Branch Cleanup and Synchronization Script
# This script helps clean up redundant branches and sync main with master

Write-Host "Branch Cleanup and Synchronization" -ForegroundColor Cyan
Write-Host "Repository: aria1991/ai-dev-assistant-bundle" -ForegroundColor Yellow
Write-Host ""

# Check if GitHub CLI is installed
if (!(Get-Command gh -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: GitHub CLI (gh) is not installed." -ForegroundColor Red
    Write-Host "Please install it from: https://cli.github.com/" -ForegroundColor Yellow
    exit 1
}

# Check if authenticated
try {
    gh auth status 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: Not authenticated with GitHub" -ForegroundColor Red
        Write-Host "Please run: gh auth login" -ForegroundColor Yellow
        exit 1
    }
} catch {
    Write-Host "ERROR: Not authenticated with GitHub" -ForegroundColor Red
    Write-Host "Please run: gh auth login" -ForegroundColor Yellow
    exit 1
}

$repo = "aria1991/ai-dev-assistant-bundle"

# Option 1: Sync main with master
Write-Host "Option 1: Sync main branch with master" -ForegroundColor Cyan
$syncChoice = Read-Host "Do you want to sync main branch with master? (y/n)"

if ($syncChoice -eq 'y' -or $syncChoice -eq 'Y') {
    try {
        # Get master branch SHA
        $masterSha = gh api repos/$repo/git/refs/heads/master --jq '.object.sha'
        
        # Update main branch to point to master
        gh api repos/$repo/git/refs/heads/main --method PATCH --field sha="$masterSha"
        
        Write-Host "SUCCESS: main branch synchronized with master" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: Failed to sync main with master: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Option 2: Delete main branch (recommended if using GitFlow)
Write-Host ""
Write-Host "Option 2: Delete main branch (recommended for GitFlow)" -ForegroundColor Cyan
Write-Host "Current setup: develop (integration) -> master (production)" -ForegroundColor Gray
$deleteChoice = Read-Host "Do you want to delete the main branch? (y/n)"

if ($deleteChoice -eq 'y' -or $deleteChoice -eq 'Y') {
    try {
        # Delete main branch
        gh api repos/$repo/git/refs/heads/main --method DELETE
        
        Write-Host "SUCCESS: main branch deleted" -ForegroundColor Green
        Write-Host "Repository now uses GitFlow: develop -> master" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: Failed to delete main branch: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Branch cleanup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Current recommended workflow:" -ForegroundColor Yellow
Write-Host "1. develop = default branch (integration)" -ForegroundColor White
Write-Host "2. feature/* = feature branches (merge to develop)" -ForegroundColor White
Write-Host "3. master = production releases (from develop)" -ForegroundColor White

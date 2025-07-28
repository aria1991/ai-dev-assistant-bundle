# Simplified Branch Protection Setup
# This script sets up basic branch protection rules that work reliably

param(
    [string]$repo = "aria1991/ai-dev-assistant-bundle"
)

Write-Host "GitHub Branch Protection Setup (Simplified)" -ForegroundColor Cyan
Write-Host "Repository: $repo" -ForegroundColor Yellow
Write-Host ""

# Check if GitHub CLI is installed and authenticated
if (!(Get-Command gh -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: GitHub CLI (gh) is not installed." -ForegroundColor Red
    exit 1
}

try {
    gh auth status 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: Not authenticated with GitHub" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "ERROR: Not authenticated with GitHub" -ForegroundColor Red
    exit 1
}

Write-Host "SUCCESS: GitHub CLI is installed and authenticated" -ForegroundColor Green

# Set up Master Branch Protection (Production)
Write-Host "Setting up Master Branch Protection..." -ForegroundColor Cyan
try {
    $masterProtection = @{
        required_status_checks = @{
            strict = $true
            contexts = @()  # Will be populated when CI is set up
        }
        enforce_admins = $false
        required_pull_request_reviews = @{
            required_approving_review_count = 1
            dismiss_stale_reviews = $true
            require_code_owner_reviews = $false
        }
        restrictions = $null
        allow_force_pushes = $false
        allow_deletions = $false
    } | ConvertTo-Json -Depth 10

    $masterProtection | gh api repos/$repo/branches/master/protection --method PUT --input -
    Write-Host "SUCCESS: Master branch protection configured" -ForegroundColor Green
} catch {
    Write-Host "INFO: Master branch protection may already exist or branch doesn't exist" -ForegroundColor Yellow
}

# Set up Develop Branch Protection (Integration)
Write-Host "Setting up Develop Branch Protection..." -ForegroundColor Cyan
try {
    $developProtection = @{
        required_status_checks = @{
            strict = $true
            contexts = @()  # Will be populated when CI is set up
        }
        enforce_admins = $false
        required_pull_request_reviews = $null  # Allow faster integration
        restrictions = $null
        allow_force_pushes = $false
        allow_deletions = $false
    } | ConvertTo-Json -Depth 10

    $developProtection | gh api repos/$repo/branches/develop/protection --method PUT --input -
    Write-Host "SUCCESS: Develop branch protection configured" -ForegroundColor Green
} catch {
    Write-Host "INFO: Develop branch protection may already exist or branch doesn't exist" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Branch Protection Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Current Configuration:" -ForegroundColor Yellow
Write-Host "- Default branch: develop (already set)" -ForegroundColor Green
Write-Host "- Master: Protected for production releases" -ForegroundColor White
Write-Host "- Develop: Protected integration branch" -ForegroundColor White
Write-Host ""
Write-Host "GitFlow Workflow:" -ForegroundColor Yellow
Write-Host "1. feature/* -> develop (feature integration)" -ForegroundColor White
Write-Host "2. develop -> master (production releases)" -ForegroundColor White

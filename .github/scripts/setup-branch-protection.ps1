# GitHub Branch Protection Setup Script
# This script helps set up branch protection rules for the ai-dev-assistant-bundle repository

Write-Host "GitHub Branch Protection Setup" -ForegroundColor Cyan
Write-Host "Repository: aria1991/ai-dev-assistant-bundle" -ForegroundColor Yellow
Write-Host ""

# Check if GitHub CLI is installed
if (!(Get-Command gh -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: GitHub CLI (gh) is not installed." -ForegroundColor Red
    Write-Host "Please install it from: https://cli.github.com/" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "After installation, run:" -ForegroundColor Green
    Write-Host "  gh auth login" -ForegroundColor White
    Write-Host "  .\setup-branch-protection.ps1" -ForegroundColor White
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

Write-Host "SUCCESS: GitHub CLI is installed and authenticated" -ForegroundColor Green
Write-Host ""

# Set repository context
$repo = "aria1991/ai-dev-assistant-bundle"

Write-Host "Setting up Master Branch Protection..." -ForegroundColor Cyan

# Master branch protection (production-ready code only)
try {
    $statusChecks = @"
{"strict":true,"checks":[{"context":"quality-assurance"},{"context":"security-analysis"},{"context":"package-validation"}]}
"@
    $prReviews = @"
{"required_approving_review_count":1,"dismiss_stale_reviews":true,"require_code_owner_reviews":false}
"@
    $restrictions = @"
{"users":[],"teams":[],"apps":[]}
"@
    
    gh api repos/$repo/branches/master/protection `
        --method PUT `
        --raw-field "required_status_checks=$statusChecks" `
        --raw-field "enforce_admins=true" `
        --raw-field "required_pull_request_reviews=$prReviews" `
        --raw-field "restrictions=$restrictions" `
        --raw-field "allow_force_pushes=false" `
        --raw-field "allow_deletions=false"

    Write-Host "SUCCESS: Master branch protection configured" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to configure master branch protection: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Setting up Develop Branch Protection..." -ForegroundColor Cyan

# Develop branch protection (integration branch with auto-merge)
try {
    $devStatusChecks = @"
{"strict":true,"checks":[{"context":"quality-assurance"},{"context":"security-analysis"}]}
"@
    
    gh api repos/$repo/branches/develop/protection `
        --method PUT `
        --raw-field "required_status_checks=$devStatusChecks" `
        --raw-field "enforce_admins=false" `
        --raw-field "required_pull_request_reviews=null" `
        --raw-field "restrictions=null" `
        --raw-field "allow_force_pushes=false" `
        --raw-field "allow_deletions=false"

    Write-Host "SUCCESS: Develop branch protection configured" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to configure develop branch protection: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Configuring Repository Settings..." -ForegroundColor Cyan

# Enable auto-merge for the repository
try {
    gh api repos/$repo --method PATCH --field allow_auto_merge=true
    Write-Host "SUCCESS: Auto-merge enabled" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to enable auto-merge: $($_.Exception.Message)" -ForegroundColor Red
}

# Set default branch to master (if not already)
try {
    gh api repos/$repo --method PATCH --field default_branch=master
    Write-Host "SUCCESS: Default branch set to master" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to set default branch: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Branch Protection Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Summary of applied rules:" -ForegroundColor Yellow
Write-Host "Master Branch:" -ForegroundColor White
Write-Host "  - Requires 1 PR approval" -ForegroundColor Gray
Write-Host "  - Requires CI checks: quality-assurance, security-analysis, package-validation" -ForegroundColor Gray
Write-Host "  - No direct pushes (admins only)" -ForegroundColor Gray
Write-Host "  - No force pushes or deletions" -ForegroundColor Gray
Write-Host ""
Write-Host "Develop Branch:" -ForegroundColor White
Write-Host "  - Requires CI checks: quality-assurance, security-analysis" -ForegroundColor Gray
Write-Host "  - Direct pushes allowed for maintainers" -ForegroundColor Gray
Write-Host "  - Auto-merge enabled" -ForegroundColor Gray
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Verify the protection rules in GitHub web interface" -ForegroundColor White
Write-Host "2. Test the workflow with a feature branch" -ForegroundColor White

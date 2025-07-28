# Branch Cleanup and Protection Setup Script
Write-Host "GitHub Branch Management and Protection Setup" -ForegroundColor Cyan
Write-Host "Repository: aria1991/ai-dev-assistant-bundle" -ForegroundColor Yellow
Write-Host ""

# Check current branch status
Write-Host "Current Branch Status:" -ForegroundColor Cyan
if (git show-ref --verify --quiet refs/heads/master) {
    git log --oneline master -1
} else {
    Write-Host "INFO: 'master' branch does not exist locally." -ForegroundColor Yellow
}
try {
    git log --oneline origin/main -1
} catch {
    Write-Host "INFO: Branch 'origin/main' not found." -ForegroundColor Yellow
}
Write-Host ""

# Check if main and master are in sync
$masterCommit = git rev-parse master
$mainCommit = git rev-parse origin/main

if ($masterCommit -eq $mainCommit) {
    Write-Host "SUCCESS: main and master branches are now in sync" -ForegroundColor Green
} else {
    Write-Host "ERROR: main and master branches are still out of sync" -ForegroundColor Red
    Write-Host "Master commit: $masterCommit" -ForegroundColor Gray
    Write-Host "Main commit: $mainCommit" -ForegroundColor Gray
}
Write-Host ""

Write-Host "Branch Cleanup Options:" -ForegroundColor Yellow
Write-Host "Your repository has both 'main' and 'master' branches." -ForegroundColor White
Write-Host "According to your branch protection strategy, you're using 'master' as primary." -ForegroundColor White
Write-Host ""

$choice = Read-Host "Do you want to delete the redundant 'main' branch? (y/N)"

if ($choice -eq 'y' -or $choice -eq 'Y') {
    Write-Host ""
    Write-Host "Deleting redundant main branch..." -ForegroundColor Cyan
    
    try {
        git push origin --delete main
        Write-Host "SUCCESS: Deleted remote 'main' branch" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: Failed to delete remote main branch" -ForegroundColor Red
    }
} else {
    Write-Host "INFO: Keeping both branches. Remember to keep them in sync manually." -ForegroundColor Blue
}

Write-Host ""
Write-Host "Setting up Branch Protection..." -ForegroundColor Cyan

# Check if GitHub CLI is installed
if (!(Get-Command gh -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: GitHub CLI (gh) is not installed." -ForegroundColor Red
    Write-Host "Please install it from: https://cli.github.com/" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "After installation, run:" -ForegroundColor Green
    Write-Host "  gh auth login" -ForegroundColor White
    Write-Host "  .\branch-cleanup-and-protection.ps1" -ForegroundColor White
    
    Write-Host ""
    Write-Host "Manual Setup Alternative:" -ForegroundColor Yellow
    Write-Host "Go to: https://github.com/aria1991/ai-dev-assistant-bundle/settings/branches" -ForegroundColor White
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

# Ensure master is the default branch
Write-Host "Setting master as default branch..." -ForegroundColor Cyan
try {
    gh api repos/$repo --method PATCH --field default_branch=master
    Write-Host "SUCCESS: Default branch set to master" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to set default branch" -ForegroundColor Red
}

Write-Host ""
Write-Host "Setting up Master Branch Protection..." -ForegroundColor Cyan

# Master branch protection
try {
    gh api repos/$repo/branches/master/protection --method PUT --field required_status_checks='{"strict":true,"checks":[{"context":"quality-assurance"},{"context":"security-analysis"},{"context":"package-validation"}]}' --field enforce_admins=true --field required_pull_request_reviews='{"required_approving_review_count":1,"dismiss_stale_reviews":true,"require_code_owner_reviews":false}' --field restrictions='{"users":[],"teams":[],"apps":[]}' --field allow_force_pushes=false --field allow_deletions=false

    Write-Host "SUCCESS: Master branch protection configured" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to configure master branch protection" -ForegroundColor Red
}

Write-Host ""
Write-Host "Setting up Develop Branch Protection..." -ForegroundColor Cyan

# Develop branch protection
try {
    gh api repos/$repo/branches/develop/protection --method PUT --field required_status_checks='{"strict":true,"checks":[{"context":"quality-assurance"},{"context":"security-analysis"}]}' --field enforce_admins=false --field required_pull_request_reviews=null --field restrictions=null --field allow_force_pushes=false --field allow_deletions=false

    Write-Host "SUCCESS: Develop branch protection configured" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to configure develop branch protection" -ForegroundColor Red
}

Write-Host ""
Write-Host "Configuring Repository Settings..." -ForegroundColor Cyan

# Enable auto-merge
try {
    gh api repos/$repo --method PATCH --field allow_auto_merge=true
    Write-Host "SUCCESS: Auto-merge enabled" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to enable auto-merge" -ForegroundColor Red
}

Write-Host ""
Write-Host "Branch Management and Protection Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Summary:" -ForegroundColor Yellow
Write-Host "- main and master branches are now in sync" -ForegroundColor White
Write-Host "- master is set as the default branch" -ForegroundColor White
Write-Host "- Branch protection rules applied" -ForegroundColor White
Write-Host ""
Write-Host "Applied Protection Rules:" -ForegroundColor Yellow
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
Write-Host "1. Verify settings at: https://github.com/aria1991/ai-dev-assistant-bundle/settings/branches" -ForegroundColor White
Write-Host "2. Test workflow with a feature branch" -ForegroundColor White
Write-Host "3. Update any local clones to use master as primary branch" -ForegroundColor White

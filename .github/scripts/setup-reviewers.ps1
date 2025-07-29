#!/usr/bin/env pwsh

<#
.SYNOPSIS
    Configure branch protection with required reviewers
.DESCRIPTION
    Sets up branch protection rules requiring pull request reviews
.PARAMETER Token
    GitHub personal access token (optional, will prompt if not provided)
.EXAMPLE
    .\setup-reviewers.ps1
#>

param(
    [string]$Token = $env:GITHUB_TOKEN
)

$ErrorActionPreference = "Stop"

Write-Host "🔧 Setting up branch protection with required reviewers..." -ForegroundColor Cyan

# GitHub API settings
$owner = "aria1991"
$repo = "ai-dev-assistant-bundle"
$branch = "master"

if (!$Token) {
    $Token = Read-Host "Enter your GitHub Personal Access Token" -AsSecureString
    $Token = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($Token))
}

$headers = @{
    "Authorization" = "token $Token"
    "Accept" = "application/vnd.github.v3+json"
    "User-Agent" = "PowerShell-Script"
}

# Branch protection configuration
$protectionConfig = @{
    required_status_checks = $null
    enforce_admins = $false
    required_pull_request_reviews = @{
        required_approving_review_count = 1
        dismiss_stale_reviews = $true
        require_code_owner_reviews = $false
        require_last_push_approval = $false
    }
    restrictions = $null
    allow_force_pushes = $false
    allow_deletions = $false
} | ConvertTo-Json -Depth 10

try {
    Write-Host "📝 Updating branch protection for master branch..." -ForegroundColor Yellow
    
    $uri = "https://api.github.com/repos/$owner/$repo/branches/$branch/protection"
    
    $result = Invoke-RestMethod -Uri $uri -Method Put -Headers $headers -Body $protectionConfig -ContentType "application/json"
    
    Write-Host "✅ Branch protection configured successfully!" -ForegroundColor Green
    Write-Host "🔒 Master branch now requires:" -ForegroundColor Cyan
    Write-Host "  • 1 required reviewer" -ForegroundColor White
    Write-Host "  • Pull request reviews" -ForegroundColor White
    Write-Host "  • Dismisses stale reviews on push" -ForegroundColor White
    
} catch {
    if ($_.Exception.Response.StatusCode -eq 403) {
        Write-Warning "Access denied. Make sure your token has 'repo' permissions."
    } elseif ($_.Exception.Response.StatusCode -eq 404) {
        Write-Warning "Repository not found or token doesn't have access."
    } else {
        Write-Error "Failed to configure branch protection: $($_.Exception.Message)"
    }
    exit 1
}

Write-Host ""
Write-Host "🎯 Next steps:" -ForegroundColor Yellow
Write-Host "1. Go to GitHub Settings > Branches" -ForegroundColor Gray
Write-Host "2. Edit the master branch protection rule" -ForegroundColor Gray
Write-Host "3. Add yourself as a required reviewer in 'Restrict pushes that create files'" -ForegroundColor Gray
Write-Host "4. Or use CODEOWNERS file for automatic reviewer assignment" -ForegroundColor Gray

#!/usr/bin/env pwsh

<#
.SYNOPSIS
    Create a pull request from develop to master
.DESCRIPTION
    Creates a pull request using GitHub API with proper title and description
.PARAMETER Token
    GitHub personal access token (optional, will use environment variable)
.EXAMPLE
    .\create-pr.ps1
#>

param(
    [string]$Token = $env:GITHUB_TOKEN
)

$ErrorActionPreference = "Stop"

Write-Host "🚀 Creating Pull Request: develop → master" -ForegroundColor Cyan

# GitHub repository details
$owner = "aria1991"
$repo = "ai-dev-assistant-bundle"
$head = "develop"
$base = "master"

# PR details
$title = "Release: Major Bundle Improvements and Fixes"
$body = @"
## 🎯 Summary
Major improvements to the AI Development Assistant Bundle including Symfony configuration fixes, bundle cleanup, and enhanced automation.

## 📋 Changes Included

### 🔧 Configuration Improvements
- **Fixed Symfony bundle configuration** following best practices
- **Removed inappropriate user config** from bundle
- **Improved environment variable handling** with proper defaults
- **Added example configuration** in docs/config_example.yaml

### 🧹 Bundle Cleanup  
- **Removed .phar files** (6.4MB+ reduction)
- **Improved tooling approach** - users manage their own tools
- **Better .gitignore** excluding unnecessary files
- **Lightweight bundle** focused on AI analysis capabilities

### 🤖 Automation Enhancements
- **Branch sync automation** script to prevent drift
- **Enhanced reviewer assignment** with CODEOWNERS and workflows
- **Auto-reviewer workflow** for external contributions
- **Branch protection setup** script

### 📚 Documentation
- **Example configuration** moved to docs/
- **Proper Symfony bundle structure** documentation
- **Updated installation instructions**

## 🚨 Breaking Changes
- Users must copy `docs/config_example.yaml` to `config/packages/ai_dev_assistant.yaml`
- Configuration structure slightly changed for better Symfony compliance

## ✅ Tested
- [x] Configuration validation
- [x] Service container compilation
- [x] Environment variable handling
- [x] Bundle installation process

## 📦 Ready for Release
This PR brings the bundle to production-ready state with proper Symfony best practices.
"@

if (!$Token) {
    Write-Error "GitHub token not found. Set GITHUB_TOKEN environment variable or pass -Token parameter."
    exit 1
}

$headers = @{
    "Authorization" = "token $Token"
    "Accept" = "application/vnd.github.v3+json"
    "User-Agent" = "PowerShell-Script"
}

$prData = @{
    title = $title
    head = $head
    base = $base
    body = $body
    maintainer_can_modify = $true
} | ConvertTo-Json -Depth 10

try {
    Write-Host "📝 Creating pull request..." -ForegroundColor Yellow
    
    $uri = "https://api.github.com/repos/$owner/$repo/pulls"
    $result = Invoke-RestMethod -Uri $uri -Method Post -Headers $headers -Body $prData -ContentType "application/json"
    
    Write-Host "✅ Pull request created successfully!" -ForegroundColor Green
    Write-Host "🔗 PR URL: $($result.html_url)" -ForegroundColor Blue
    Write-Host "📊 PR Number: #$($result.number)" -ForegroundColor Cyan
    
    # Auto-open in browser
    Start-Process $result.html_url
    
} catch {
    if ($_.Exception.Response.StatusCode -eq 422) {
        Write-Warning "Pull request might already exist or there are no differences."
        Write-Host "🔗 Check existing PRs: https://github.com/$owner/$repo/pulls" -ForegroundColor Blue
    } elseif ($_.Exception.Response.StatusCode -eq 403) {
        Write-Error "Access denied. Make sure your token has 'repo' permissions."
    } else {
        Write-Error "Failed to create pull request: $($_.Exception.Message)"
        Write-Host "🔗 Manual PR creation: https://github.com/$owner/$repo/compare/$base...$head" -ForegroundColor Yellow
    }
    exit 1
}

#!/usr/bin/env pwsh

<#
.SYNOPSIS
    Sync develop branch with master to prevent branch drift
.DESCRIPTION
    This script ensures develop and master branches stay in sync by:
    1. Fetching latest changes
    2. Fast-forwarding develop to match master
    3. Pushing the updated develop branch
.EXAMPLE
    .\sync-branches.ps1
#>

$ErrorActionPreference = "Stop"

Write-Host "🔄 Syncing develop with master..." -ForegroundColor Cyan

# Ensure we're in the right directory
if (!(Test-Path ".git")) {
    Write-Error "Must be run from git repository root"
    exit 1
}

# Fetch latest changes
Write-Host "📥 Fetching latest changes..." -ForegroundColor Yellow
git fetch origin

# Check current branch
$currentBranch = git branch --show-current
Write-Host "Current branch: $currentBranch" -ForegroundColor Blue

# Switch to master and pull
Write-Host "🔄 Updating master..." -ForegroundColor Yellow
git checkout master
git pull origin master

# Switch to develop and sync
Write-Host "🔄 Syncing develop with master..." -ForegroundColor Yellow
git checkout develop
git reset --hard master

# Push updated develop
Write-Host "📤 Pushing synced develop..." -ForegroundColor Green
git push origin develop --force-with-lease

Write-Host "✅ Branches synced successfully!" -ForegroundColor Green
Write-Host "📊 Current state:" -ForegroundColor Cyan
git log --oneline -3

# Return to original branch
if ($currentBranch -ne "develop") {
    git checkout $currentBranch
    Write-Host "🔙 Returned to $currentBranch" -ForegroundColor Blue
}

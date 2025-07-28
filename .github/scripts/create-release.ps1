#!/usr/bin/env pwsh

<#
.SYNOPSIS
    Create a new release tag following semantic versioning
.DESCRIPTION
    This script helps create properly formatted release tags with messages
.PARAMETER Version
    The version number (e.g., 1.0.1, 1.1.0, 2.0.0)
.PARAMETER Type
    Release type: patch, minor, major
.PARAMETER Message
    Release message describing the changes
.EXAMPLE
    .\create-release.ps1 -Version "1.0.1" -Type "patch" -Message "Fix security vulnerability"
#>

param(
    [Parameter(Mandatory=$true)]
    [string]$Version,
    
    [Parameter(Mandatory=$true)]
    [ValidateSet("patch", "minor", "major")]
    [string]$Type,
    
    [Parameter(Mandatory=$true)]
    [string]$Message
)

$ErrorActionPreference = "Stop"

Write-Host "🚀 Creating release v$Version..." -ForegroundColor Cyan

# Validate version format
if ($Version -notmatch '^\d+\.\d+\.\d+$') {
    Write-Error "Version must be in format MAJOR.MINOR.PATCH (e.g., 1.0.1)"
    exit 1
}

# Ensure we're on master branch
$currentBranch = git branch --show-current
if ($currentBranch -ne "master") {
    Write-Error "Must be on master branch to create release. Current: $currentBranch"
    exit 1
}

# Pull latest changes
Write-Host "📥 Pulling latest changes..." -ForegroundColor Yellow
git pull origin master

# Check if tag already exists
$existingTag = git tag -l "v$Version"
if ($existingTag) {
    Write-Error "Tag v$Version already exists!"
    exit 1
}

# Create the tag
$tagMessage = "Release v$Version`: $Message"
Write-Host "🏷️  Creating tag v$Version..." -ForegroundColor Green
git tag -a "v$Version" -m $tagMessage

# Push the tag
Write-Host "📤 Pushing tag to GitHub..." -ForegroundColor Green
git push origin "v$Version"

Write-Host "✅ Release v$Version created successfully!" -ForegroundColor Green
Write-Host "📦 Packagist will auto-update via webhook" -ForegroundColor Cyan
Write-Host "🔗 View release: https://github.com/aria1991/ai-dev-assistant-bundle/releases/tag/v$Version" -ForegroundColor Blue

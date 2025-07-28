# Manual GitHub Branch Protection Setup Guide

This guide provides step-by-step instructions for setting up branch protection rules for the `ai-dev-assistant-bundle` repository when you can't use the automated script.

## Repository Information
- **Repository**: `aria1991/ai-dev-assistant-bundle`
- **Master Branch**: Production-ready code (heavily protected)
- **Develop Branch**: Integration branch (lightly protected)

## Method 1: Using GitHub Web Interface

### Step 1: Access Repository Settings
1. Go to https://github.com/aria1991/ai-dev-assistant-bundle
2. Click on **Settings** tab
3. In the left sidebar, click **Branches**

### Step 2: Configure Master Branch Protection

1. Click **Add rule** or find the existing rule for `master`
2. **Branch name pattern**: `master`
3. Configure the following settings:

#### Required Settings:
- ✅ **Require a pull request before merging**
  - Required number of approvals: `1`
  - ✅ Dismiss stale PR approvals when new commits are pushed
  - ❌ Require review from code owners (optional)

- ✅ **Require status checks to pass before merging**
  - ✅ Require branches to be up to date before merging
  - Add these status checks:
    - `quality-assurance`
    - `security-analysis` 
    - `package-validation`

- ✅ **Require conversation resolution before merging**

- ✅ **Restrict pushes that create files**
  - Add administrators to exceptions

- ✅ **Lock branch** (prevents any pushes, only PRs allowed)

- ❌ **Do not allow bypassing the above settings**

- ❌ **Allow force pushes**

- ❌ **Allow deletions**

### Step 3: Configure Develop Branch Protection

1. Click **Add rule** for a new rule
2. **Branch name pattern**: `develop`
3. Configure the following settings:

#### Required Settings:
- ❌ **Require a pull request before merging** (allow direct pushes)

- ✅ **Require status checks to pass before merging**
  - ✅ Require branches to be up to date before merging
  - Add these status checks:
    - `quality-assurance`
    - `security-analysis`

- ✅ **Require conversation resolution before merging**

- ❌ **Restrict pushes that create files**

- ❌ **Lock branch**

- ❌ **Do not allow bypassing the above settings**

- ❌ **Allow force pushes**

- ❌ **Allow deletions**

### Step 4: Repository General Settings

1. Go to **Settings** → **General**
2. Under **Pull Requests**:
   - ✅ **Allow auto-merge**
   - ✅ **Automatically delete head branches**
3. Under **Default branch**: Set to `master`

## Method 2: Using GitHub CLI (Automated)

If you have GitHub CLI installed, you can use the provided script:

```powershell
# Install GitHub CLI first (if not installed)
# Download from: https://cli.github.com/

# Authenticate
gh auth login

# Run the setup script
.\.github\scripts\setup-branch-protection.ps1
```

## Method 3: Using REST API (Advanced)

You can also configure branch protection using curl or PowerShell with the GitHub REST API:

### Master Branch Protection:
```powershell
$headers = @{
    'Authorization' = 'token YOUR_GITHUB_TOKEN'
    'Accept' = 'application/vnd.github.v3+json'
}

$body = @{
    required_status_checks = @{
        strict = $true
        checks = @(
            @{ context = "quality-assurance" },
            @{ context = "security-analysis" },
            @{ context = "package-validation" }
        )
    }
    enforce_admins = $true
    required_pull_request_reviews = @{
        required_approving_review_count = 1
        dismiss_stale_reviews = $true
        require_code_owner_reviews = $false
    }
    restrictions = $null
    allow_force_pushes = $false
    allow_deletions = $false
} | ConvertTo-Json -Depth 4

Invoke-RestMethod -Uri "https://api.github.com/repos/aria1991/ai-dev-assistant-bundle/branches/master/protection" -Method PUT -Headers $headers -Body $body -ContentType "application/json"
```

## Verification

After setting up the protection rules, verify them by:

1. **Check in GitHub UI**: Go to Settings → Branches and confirm rules are applied
2. **Test with a feature branch**:
   ```bash
   git checkout develop
   git checkout -b feature/test-protection
   echo "test" > test.txt
   git add test.txt
   git commit -m "Test protection"
   git push origin feature/test-protection
   ```
3. **Create a PR** to develop and verify auto-merge works
4. **Create a PR** from develop to master and verify approval is required

## Troubleshooting

### Common Issues:
1. **Status checks not found**: Make sure your CI/CD workflows are set up first
2. **Cannot push to develop**: Check if you have the right permissions
3. **Auto-merge not working**: Ensure it's enabled in repository settings

### Required CI/CD Workflows:
You need to set up GitHub Actions workflows that provide these status checks:
- `quality-assurance` (PHPStan, PHP CS Fixer, etc.)
- `security-analysis` (Security scanning)
- `package-validation` (Composer validation, tests)

The workflows should be in `.github/workflows/` directory.

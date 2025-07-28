# Cross-Platform GitHub Branch Protection Setup

This repository includes cross-platform scripts to set up GitHub branch protection rules that work on **Linux**, **macOS**, and **Windows**.

## 🚀 Quick Start

### Option 1: Auto-Detection (Recommended)

#### On Windows:
```cmd
.github\scripts\setup-branch-protection.bat
```

#### On Linux/macOS:
```bash
./.github/scripts/setup-branch-protection.sh
```

### Option 2: Manual Script Selection

#### Shell Script (Linux/macOS/Windows with Git Bash):
```bash
chmod +x .github/scripts/setup-branch-protection.sh
./.github/scripts/setup-branch-protection.sh
```

#### PowerShell Script (Windows):
```powershell
.\.github\scripts\setup-branch-protection.ps1
```

## 📋 Prerequisites

### Required on All Platforms:
1. **Git** - Version control system
2. **GitHub CLI** - Command line tool for GitHub

### GitHub CLI Installation:

#### macOS:
```bash
# Using Homebrew
brew install gh

# Using MacPorts
sudo port install gh
```

#### Linux (Ubuntu/Debian):
```bash
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null
sudo apt update && sudo apt install gh
```

#### Linux (CentOS/RHEL/Fedora):
```bash
sudo dnf install gh
```

#### Windows:
```cmd
# Using winget
winget install GitHub.cli

# Using Chocolatey
choco install gh

# Using Scoop
scoop install gh
```

Or download from: https://cli.github.com/

### Authentication:
After installing GitHub CLI, authenticate:
```bash
gh auth login
```

## 🛠️ What the Scripts Do

### Branch Protection Rules Applied:

#### Master Branch (Production):
- ✅ Requires 1 PR approval before merging
- ✅ Requires CI status checks: `quality-assurance`, `security-analysis`, `package-validation`
- ✅ No direct pushes (admin only)
- ✅ No force pushes or deletions
- ✅ Enforces admin compliance

#### Develop Branch (Integration):
- ✅ Requires CI status checks: `quality-assurance`, `security-analysis`
- ✅ Allows direct pushes for maintainers
- ✅ Auto-merge enabled for passing CI
- ✅ No force pushes or deletions

#### Repository Settings:
- ✅ Auto-merge enabled
- ✅ Master set as default branch

## 🔍 Available Scripts

| Script | Platform | Description |
|--------|----------|-------------|
| `setup-branch-protection.sh` | Linux/macOS/Git Bash | Cross-platform shell script |
| `setup-branch-protection.ps1` | Windows PowerShell | Windows-specific PowerShell script |
| `setup-branch-protection.bat` | Windows | Auto-detection launcher for Windows |
| `branch-cleanup-and-protection.sh` | Linux/macOS/Git Bash | Branch cleanup + protection setup |
| `branch-cleanup-and-protection.ps1` | Windows PowerShell | Branch cleanup + protection (Windows) |

## 🧪 Testing Your Setup

After running the setup script, test the protection rules:

```bash
# Create a test feature branch
git checkout develop
git checkout -b feature/test-protection
echo "test content" > test.txt
git add test.txt
git commit -m "Test branch protection"
git push origin feature/test-protection

# Create PR to develop (should auto-merge after CI passes)
gh pr create --base develop --title "Test Protection" --body "Testing branch protection rules"

# Create PR from develop to master (should require approval)
git checkout develop
git pull origin develop
gh pr create --base master --title "Release Test" --body "Testing master branch protection"
```

## 🆘 Troubleshooting

### Common Issues:

#### "GitHub CLI not found"
- Install GitHub CLI using the instructions above
- Restart your terminal after installation

#### "Not authenticated with GitHub"
- Run: `gh auth login`
- Follow the authentication prompts

#### "Permission denied" (Linux/macOS)
- Make scripts executable: `chmod +x .github/scripts/*.sh`

#### "Status checks not found"
- Ensure your CI workflows are set up and have run at least once
- Check that workflow names match: `quality-assurance`, `security-analysis`, `package-validation`

#### "Branch protection API error"
- Verify you have admin permissions on the repository
- Check that the branch exists and has commits

### Manual Setup Fallback:
If scripts fail, use the manual setup guide: [BRANCH_PROTECTION_SETUP.md](BRANCH_PROTECTION_SETUP.md)

## 📚 Additional Resources

- [GitHub Branch Protection Documentation](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/defining-the-mergeability-of-pull-requests/about-protected-branches)
- [GitHub CLI Documentation](https://cli.github.com/manual/)
- [Complete Manual Setup Guide](BRANCH_PROTECTION_SETUP.md)

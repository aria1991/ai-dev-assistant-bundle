#  Branch Protection Strategy

## Expert-Level Branch Protection with Developer-Friendly Practices

###  **Branching Strategy**
- **master**: Production-ready code only (protected)
- **develop**: Integration branch for features (lightly protected)
- **feature/***: Feature development branches
- **fix/***: Bug fix branches

###  **Branch Protection Rules**

#### **Master Branch Protection**
-  Require PR with 1 approval
-  All CI/CD checks must pass
-  No direct pushes (admins only)
-  No force pushes or deletions

#### **Develop Branch Protection**
-  CI/CD checks must pass  
-  Auto-merge enabled
-  Direct push allowed for maintainers
-  Fast iteration friendly

###  **Simple Developer Workflow**

#### **Feature Development**
```bash
git checkout develop
git checkout -b feature/your-feature
# Make changes
git push origin feature/your-feature
# Create PR to develop (auto-merges if CI passes)

###  **GitHub Settings Applied**

**Master Branch:**
- Require pull request before merging (1 approval)
- Require status checks: quality-assurance, security-analysis, package-validation
- Restrict pushes to admins
- No force pushes/deletions

**Develop Branch:**
- Require status checks: quality-assurance, security-analysis
- Allow direct pushes for maintainers
- Auto-merge enabled for passing CI

This balances enterprise security with developer productivity!

### **Setup Instructions**

#### **Cross-Platform Setup (Recommended)**
Works on Linux, macOS, and Windows:

**Quick Start:**
```bash
# Linux/macOS
make setup-protection

# Windows
.github\scripts\setup-branch-protection.bat

# Manual (any platform)
./.github/scripts/setup-branch-protection.sh  # Unix-like
.\.github\scripts\setup-branch-protection.ps1 # Windows PowerShell
```

📖 **Complete Guide:** [Cross-Platform Setup Instructions](.github/CROSS_PLATFORM_SETUP.md)

#### **Legacy PowerShell Setup (Windows Only)**
```powershell
# Run the automated setup script
.\.github\scripts\apply-branch-protection.ps1
```

#### **Manual Setup**
If you prefer manual setup, follow the detailed guide:
- 📖 [Complete Setup Guide](.github/BRANCH_PROTECTION_SETUP.md)

#### **Verification**
Test your protection rules:
```bash
# Test feature development workflow
git checkout develop
git checkout -b feature/test-protection
echo "test" > test.txt
git add test.txt
git commit -m "Test branch protection"
git push origin feature/test-protection
# Create PR to develop → should auto-merge after CI passes
# Create PR from develop to master → should require approval
``` 

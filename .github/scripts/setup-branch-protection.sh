#!/bin/bash
# Cross-Platform GitHub Branch Protection Setup Script
# Works on Linux, macOS, and Windows (with Git Bash or WSL)

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
GRAY='\033[0;37m'
NC='\033[0m' # No Color

echo -e "${CYAN}🔒 GitHub Branch Protection Setup${NC}"
echo -e "${YELLOW}Repository: aria1991/ai-dev-assistant-bundle${NC}"
echo ""

# Check if GitHub CLI is installed
if ! command -v gh &> /dev/null; then
    echo -e "${RED}❌ GitHub CLI (gh) is not installed.${NC}"
    echo -e "${YELLOW}Please install it from: https://cli.github.com/${NC}"
    echo ""
    echo -e "${GREEN}Installation instructions:${NC}"
    echo -e "${GRAY}# On macOS with Homebrew:${NC}"
    echo -e "${GRAY}brew install gh${NC}"
    echo ""
    echo -e "${GRAY}# On Ubuntu/Debian:${NC}"
    echo -e "${GRAY}curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg${NC}"
    echo -e "${GRAY}echo \"deb [arch=\$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main\" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null${NC}"
    echo -e "${GRAY}sudo apt update && sudo apt install gh${NC}"
    echo ""
    echo -e "${GRAY}# On Windows:${NC}"
    echo -e "${GRAY}Download from https://cli.github.com/ or use: winget install GitHub.cli${NC}"
    echo ""
    echo -e "${GREEN}After installation, run:${NC}"
    echo -e "${GRAY}  gh auth login${NC}"
    echo -e "${GRAY}  ./setup-branch-protection.sh${NC}"
    exit 1
fi

# Check if authenticated
if ! gh auth status &> /dev/null; then
    echo -e "${RED}❌ Not authenticated with GitHub${NC}"
    echo -e "${YELLOW}Please run: gh auth login${NC}"
    exit 1
fi

echo -e "${GREEN}✅ GitHub CLI is installed and authenticated${NC}"
echo ""

# Set repository context
REPO="aria1991/ai-dev-assistant-bundle"

echo -e "${CYAN}🛡️ Setting up Master Branch Protection...${NC}"

# Master branch protection (production-ready code only)
if gh api repos/$REPO/branches/master/protection \
    --method PUT \
    --field required_status_checks='{"strict":true,"checks":[{"context":"quality-assurance"},{"context":"security-analysis"},{"context":"package-validation"}]}' \
    --field enforce_admins=true \
    --field required_pull_request_reviews='{"required_approving_review_count":1,"dismiss_stale_reviews":true,"require_code_owner_reviews":false}' \
    --field restrictions='{"users":[],"teams":[],"apps":[]}' \
    --field allow_force_pushes=false \
    --field allow_deletions=false &> /dev/null; then
    echo -e "${GREEN}✅ Master branch protection configured${NC}"
else
    echo -e "${RED}❌ Failed to configure master branch protection${NC}"
fi

echo ""
echo -e "${CYAN}🛡️ Setting up Develop Branch Protection...${NC}"

# Develop branch protection (integration branch with auto-merge)
if gh api repos/$REPO/branches/develop/protection \
    --method PUT \
    --field required_status_checks='{"strict":true,"checks":[{"context":"quality-assurance"},{"context":"security-analysis"}]}' \
    --field enforce_admins=false \
    --field required_pull_request_reviews=null \
    --field restrictions=null \
    --field allow_force_pushes=false \
    --field allow_deletions=false &> /dev/null; then
    echo -e "${GREEN}✅ Develop branch protection configured${NC}"
else
    echo -e "${RED}❌ Failed to configure develop branch protection${NC}"
fi

echo ""
echo -e "${CYAN}🔧 Configuring Repository Settings...${NC}"

# Enable auto-merge for the repository
if gh api repos/$REPO --method PATCH --field allow_auto_merge=true &> /dev/null; then
    echo -e "${GREEN}✅ Auto-merge enabled${NC}"
else
    echo -e "${RED}❌ Failed to enable auto-merge${NC}"
fi

# Set default branch to master (if not already)
if gh api repos/$REPO --method PATCH --field default_branch=master &> /dev/null; then
    echo -e "${GREEN}✅ Default branch set to master${NC}"
else
    echo -e "${RED}❌ Failed to set default branch${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Branch Protection Setup Complete!${NC}"
echo ""
echo -e "${YELLOW}Summary of applied rules:${NC}"
echo -e "${GRAY}📋 Master Branch:${NC}"
echo -e "${GRAY}  • Requires 1 PR approval${NC}"
echo -e "${GRAY}  • Requires CI checks: quality-assurance, security-analysis, package-validation${NC}"
echo -e "${GRAY}  • No direct pushes (admins only)${NC}"
echo -e "${GRAY}  • No force pushes or deletions${NC}"
echo ""
echo -e "${GRAY}📋 Develop Branch:${NC}"
echo -e "${GRAY}  • Requires CI checks: quality-assurance, security-analysis${NC}"
echo -e "${GRAY}  • Direct pushes allowed for maintainers${NC}"
echo -e "${GRAY}  • Auto-merge enabled${NC}"
echo ""
echo -e "${CYAN}Next steps:${NC}"
echo -e "${GRAY}1. Verify the protection rules in GitHub web interface${NC}"
echo -e "${GRAY}2. Test the workflow with a feature branch${NC}"
echo -e "${GRAY}3. Create a test PR to verify auto-merge functionality${NC}"

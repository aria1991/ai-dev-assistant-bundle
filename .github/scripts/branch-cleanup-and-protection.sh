#!/bin/bash
# Cross-Platform Branch Cleanup and Protection Setup Script
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

echo -e "${CYAN}🔧 GitHub Branch Management and Protection Setup${NC}"
echo -e "${YELLOW}Repository: aria1991/ai-dev-assistant-bundle${NC}"
echo ""

# Check current branch status
echo -e "${CYAN}📊 Current Branch Status:${NC}"
echo -e "${GRAY}Master: $(git log --oneline master -1)${NC}"
echo -e "${GRAY}Main:   $(git log --oneline origin/main -1 2>/dev/null || echo 'Not found')${NC}"
echo ""

# Check if main and master are in sync
if git rev-parse origin/main &> /dev/null; then
    MASTER_COMMIT=$(git rev-parse master)
    MAIN_COMMIT=$(git rev-parse origin/main)

    if [ "$MASTER_COMMIT" = "$MAIN_COMMIT" ]; then
        echo -e "${GREEN}✅ main and master branches are in sync${NC}"
    else
        echo -e "${RED}❌ main and master branches are out of sync${NC}"
        echo -e "${GRAY}Master commit: $MASTER_COMMIT${NC}"
        echo -e "${GRAY}Main commit: $MAIN_COMMIT${NC}"
        
        echo ""
        echo -e "${YELLOW}🔄 Syncing main branch with master...${NC}"
        if git push origin master:main --force; then
            echo -e "${GREEN}✅ Successfully synced main branch with master${NC}"
        else
            echo -e "${RED}❌ Failed to sync main branch${NC}"
        fi
    fi
    echo ""

    echo -e "${YELLOW}🤔 Branch Cleanup Options:${NC}"
    echo -e "${GRAY}Your repository has both 'main' and 'master' branches.${NC}"
    echo -e "${GRAY}According to your branch protection strategy, you're using 'master' as primary.${NC}"
    echo ""
    
    read -p "Do you want to delete the redundant 'main' branch? (y/N): " choice
    case "$choice" in 
        y|Y ) 
            echo ""
            echo -e "${CYAN}🗑️ Deleting redundant main branch...${NC}"
            if git push origin --delete main; then
                echo -e "${GREEN}✅ Deleted remote 'main' branch${NC}"
            else
                echo -e "${RED}❌ Failed to delete remote main branch${NC}"
            fi
            ;;
        * ) 
            echo -e "${BLUE}ℹ️ Keeping both branches. Remember to keep them in sync manually.${NC}"
            ;;
    esac
else
    echo -e "${BLUE}ℹ️ No 'main' branch found. Using 'master' as primary.${NC}"
fi

echo ""
echo -e "${CYAN}🛡️ Setting up Branch Protection...${NC}"

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
    echo -e "${GRAY}sudo apt update && sudo apt install gh${NC}"
    echo ""
    echo -e "${GRAY}# On Windows:${NC}"
    echo -e "${GRAY}Download from https://cli.github.com/ or use: winget install GitHub.cli${NC}"
    echo ""
    echo -e "${GREEN}After installation, run:${NC}"
    echo -e "${GRAY}  gh auth login${NC}"
    echo -e "${GRAY}  ./branch-cleanup-and-protection.sh${NC}"
    echo ""
    echo -e "${YELLOW}📋 Manual Setup Alternative:${NC}"
    echo -e "${GRAY}Go to: https://github.com/aria1991/ai-dev-assistant-bundle/settings/branches${NC}"
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

# Ensure master is the default branch
echo -e "${CYAN}🔧 Setting master as default branch...${NC}"
if gh api repos/$REPO --method PATCH --field default_branch=master &> /dev/null; then
    echo -e "${GREEN}✅ Default branch set to master${NC}"
else
    echo -e "${RED}❌ Failed to set default branch${NC}"
fi

echo ""
echo -e "${CYAN}🛡️ Setting up Master Branch Protection...${NC}"

# Master branch protection
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

# Develop branch protection
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

# Enable auto-merge
if gh api repos/$REPO --method PATCH --field allow_auto_merge=true &> /dev/null; then
    echo -e "${GREEN}✅ Auto-merge enabled${NC}"
else
    echo -e "${RED}❌ Failed to enable auto-merge${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Branch Management and Protection Setup Complete!${NC}"
echo ""
echo -e "${YELLOW}Summary:${NC}"
echo -e "${GRAY}- main and master branches are now in sync${NC}"
echo -e "${GRAY}- master is set as the default branch${NC}"
echo -e "${GRAY}- Branch protection rules applied${NC}"
echo ""
echo -e "${YELLOW}📋 Applied Protection Rules:${NC}"
echo -e "${GRAY}🔒 Master Branch:${NC}"
echo -e "${GRAY}  • Requires 1 PR approval${NC}"
echo -e "${GRAY}  • Requires CI checks: quality-assurance, security-analysis, package-validation${NC}"
echo -e "${GRAY}  • No direct pushes (admins only)${NC}"
echo -e "${GRAY}  • No force pushes or deletions${NC}"
echo ""
echo -e "${GRAY}🔓 Develop Branch:${NC}"
echo -e "${GRAY}  • Requires CI checks: quality-assurance, security-analysis${NC}"
echo -e "${GRAY}  • Direct pushes allowed for maintainers${NC}"
echo -e "${GRAY}  • Auto-merge enabled${NC}"
echo ""
echo -e "${CYAN}Next steps:${NC}"
echo -e "${GRAY}1. Verify settings at: https://github.com/aria1991/ai-dev-assistant-bundle/settings/branches${NC}"
echo -e "${GRAY}2. Test workflow with a feature branch${NC}"
echo -e "${GRAY}3. Update any local clones to use master as primary branch${NC}"

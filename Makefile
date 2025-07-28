# Makefile for cross-platform GitHub repository management
# Works on Linux, macOS, and Windows (with make installed)

.PHONY: help setup-protection sync-branches test-protection clean install-deps

# Default target
help:
	@echo "GitHub Branch Protection Management"
	@echo "=================================="
	@echo ""
	@echo "Available targets:"
	@echo "  setup-protection  - Set up branch protection rules"
	@echo "  sync-branches     - Sync main and master branches"
	@echo "  test-protection   - Test branch protection with a feature branch"
	@echo "  install-deps      - Install required dependencies"
	@echo "  clean            - Clean up temporary files"
	@echo ""
	@echo "Cross-platform usage:"
	@echo "  Linux/macOS: make setup-protection"
	@echo "  Windows:     Use .github/scripts/setup-branch-protection.bat"

# Set up branch protection rules
setup-protection:
	@if command -v gh >/dev/null 2>&1; then \
		chmod +x .github/scripts/setup-branch-protection.sh; \
		./.github/scripts/setup-branch-protection.sh; \
	else \
		echo "GitHub CLI not found. Please install from: https://cli.github.com/"; \
		echo "Then run: gh auth login"; \
		exit 1; \
	fi

# Sync main and master branches
sync-branches:
	@echo "Syncing main and master branches..."
	@if git rev-parse --verify origin/main >/dev/null 2>&1; then \
		git push origin master:main --force; \
		echo "✅ Branches synced successfully"; \
	else \
		echo "ℹ️ No main branch found, using master only"; \
	fi

# Test branch protection by creating a test feature branch
test-protection:
	@echo "Testing branch protection rules..."
	@git checkout develop || (echo "❌ develop branch not found" && exit 1)
	@git pull origin develop
	@BRANCH_NAME="feature/test-protection-$$(date +%s)"; \
	git checkout -b "$$BRANCH_NAME"; \
	echo "# Test Protection" > test-protection.md; \
	echo "This file tests branch protection rules." >> test-protection.md; \
	echo "Created at: $$(date)" >> test-protection.md; \
	git add test-protection.md; \
	git commit -m "test: verify branch protection rules"; \
	git push origin "$$BRANCH_NAME"; \
	echo "✅ Test branch created: $$BRANCH_NAME"; \
	echo "Create a PR to test the protection rules"

# Install required dependencies
install-deps:
	@echo "Installing dependencies..."
	@if command -v brew >/dev/null 2>&1; then \
		echo "Installing GitHub CLI via Homebrew..."; \
		brew install gh; \
	elif command -v apt-get >/dev/null 2>&1; then \
		echo "Installing GitHub CLI on Ubuntu/Debian..."; \
		curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg; \
		echo "deb [arch=$$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null; \
		sudo apt update && sudo apt install gh; \
	elif command -v dnf >/dev/null 2>&1; then \
		echo "Installing GitHub CLI on Fedora/RHEL..."; \
		sudo dnf install gh; \
	else \
		echo "Please install GitHub CLI manually from: https://cli.github.com/"; \
		echo "Then run: gh auth login"; \
	fi

# Clean up temporary files
clean:
	@echo "Cleaning up..."
	@rm -f test-protection.md
	@git branch -D feature/test-protection-* 2>/dev/null || true
	@echo "✅ Cleanup complete"

# Show current branch status
status:
	@echo "Repository Status"
	@echo "================="
	@echo "Current branch: $$(git branch --show-current)"
	@echo "Remote origin: $$(git remote get-url origin)"
	@echo ""
	@echo "Recent commits:"
	@git log --oneline -5
	@echo ""
	@if command -v gh >/dev/null 2>&1; then \
		echo "GitHub CLI status:"; \
		gh auth status || echo "❌ Not authenticated"; \
	else \
		echo "❌ GitHub CLI not installed"; \
	fi

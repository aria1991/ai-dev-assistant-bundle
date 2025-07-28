# Branch Protection Strategy

## Protected Branches
- `main` - Primary development branch
- `master` - Packagist compatibility branch
- `release/*` - Release branches

## Protection Rules

### Main Branch (`main`)
-  Require pull request reviews before merging
-  Require status checks to pass before merging
-  Required status checks:
  - `validation`
  - `security`
  - `quality (8.2)`
  - `quality (8.3)`
  - `compatibility`
-  Require branches to be up to date before merging
-  Require linear history
-  Restrict pushes that create new files
-  Allow force pushes: **NO**
-  Allow deletions: **NO**

### Master Branch (`master`)
-  Require pull request reviews before merging
-  Require status checks to pass before merging
-  Required status checks:
  - `validation`
  - `security`
  - `quality (8.2)`
  - `quality (8.3)`
-  Require branches to be up to date before merging
-  Allow force pushes: **NO**
-  Allow deletions: **NO**

## Reviewer Requirements
- **Minimum reviewers**: 1
- **Dismiss stale reviews**: Yes
- **Require review from code owners**: Yes
- **Restrict reviews to code owners**: No

## Status Check Requirements
All CI/CD checks must pass:
1. Code Validation
2. Security Analysis
3. Quality Assurance (PHP 8.2 & 8.3)
4. Compatibility Testing
5. Performance Benchmarks (for PRs)

## Additional Security
- Enable vulnerability alerts
- Enable automated security fixes
- Enable dependency graph
- Enable secret scanning
- Enable code scanning (CodeQL)

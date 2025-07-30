# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v1.4.3] - 2025-07-30

### Fixed
- Corrected branch-alias in composer.json from 'dev-main' to 'dev-master'
- Fixed Packagist version display for better semantic versioning
- Updated development branch alias to 1.5-dev for future releases

## [v1.4.2] - 2025-07-30

### Fixed
- Added missing 'contents: write' permission to release workflow
- Updated to modern softprops/action-gh-release@v1 action
- Fixed "Resource not accessible by integration" error in GitHub Actions

## [v1.4.1] - 2025-07-30

### Fixed
- Removed incorrect REST API documentation from QUICK_START.md
- Updated service integration examples with proper dependency injection
- Corrected bundle capabilities description (console commands + services, not REST API)

### Changed
- Updated documentation to accurately reflect bundle architecture

## [v1.4.0] - 2025-07-29

### Fixed
- CI/CD pipeline compatibility with PHP 8.2-8.4 and Symfony 6.4+/7.0+
- PHPUnit configuration updated for v10+ compatibility  
- Improved error handling and debugging in GitHub Actions
- Added syntax validation step to CI pipeline
- Enhanced dependency resolution for dev tools
- WebProfiler integration service configuration

### Added
- Initial release of AI Development Assistant Bundle
- Multi-provider AI support (OpenAI, Anthropic Claude, Google AI)
- Security analysis for detecting vulnerabilities
- Performance analysis for optimization opportunities
- Code quality analysis for best practices
- Documentation analysis for completeness
- Injectable services for code analysis
- Console commands for CLI usage
- Caching system for analysis results
- Rate limiting for API protection
- Comprehensive configuration system
- PHPUnit test suite
- PHPStan static analysis
- PHP CS Fixer code style enforcement
- GitHub Actions CI/CD pipeline

### Changed
- N/A (initial release)

### Deprecated
- N/A (initial release)

### Removed
- N/A (initial release)

### Fixed
- N/A (initial release)

### Security
- Rate limiting implementation
- Input validation and sanitization
- File type restrictions for analysis

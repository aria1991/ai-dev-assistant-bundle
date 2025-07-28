# GitHub Tagging Best Practices

## Current Version
- **v1.0.0** - Initial stable release (July 28, 2025)

## Semantic Versioning Strategy

We follow [Semantic Versioning](https://semver.org/) (SemVer):

### Version Format: `MAJOR.MINOR.PATCH`

- **MAJOR** - Breaking changes, incompatible API changes
- **MINOR** - New features, backward compatible
- **PATCH** - Bug fixes, backward compatible

### Tag Naming Convention
- Use `v` prefix: `v1.0.0`, `v1.1.0`, `v2.0.0`
- Always use annotated tags with messages
- Include release notes in tag messages

## Release Workflow

### 1. Prepare Release
```bash
# Ensure you're on master branch
git checkout master
git pull origin master

# Update version in composer.json if needed
# Update CHANGELOG.md with new changes
```

### 2. Create Tag
```bash
# For patch release (bug fixes)
git tag -a v1.0.1 -m "Release v1.0.1: Fix critical security issue"

# For minor release (new features)
git tag -a v1.1.0 -m "Release v1.1.0: Add new analyzer features"

# For major release (breaking changes)
git tag -a v2.0.0 -m "Release v2.0.0: Major API redesign"
```

### 3. Push Tag
```bash
git push origin v1.0.1
```

### 4. Packagist Auto-Update
Tags automatically trigger Packagist updates via webhook.

## Next Planned Releases

### v1.0.1 (Patch)
- Bug fixes
- Documentation improvements
- Performance optimizations

### v1.1.0 (Minor)
- New analyzer types
- Enhanced AI provider support
- Additional configuration options

### v2.0.0 (Major)
- Breaking API changes
- New architecture
- PHP 9.0 support

## Branch Strategy Integration

- **master**: Production releases only
- **develop**: Integration branch for features
- **feature/***: Feature development branches

Tags are only created from **master** branch after merging from **develop**.

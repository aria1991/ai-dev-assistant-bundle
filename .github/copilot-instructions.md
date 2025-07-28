<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# AI Development Assistant Bundle - Copilot Instructions

This is a Symfony bundle project that provides AI-powered code analysis capabilities.

## Development Guidelines

- **Namespace**: All classes should use the `Aria1991\AIDevAssistantBundle` namespace
- **Coding Standards**: Follow Symfony coding standards and PSR-12
- **Architecture**: Use dependency injection, implement proper interfaces, and follow SOLID principles
- **AI Integration**: Support multiple AI providers (OpenAI, Anthropic, Google Gemini) with graceful fallbacks
- **Error Handling**: Implement comprehensive error handling with proper logging
- **Testing**: Write unit and integration tests for all functionality

## AI Provider Integration

- Use PSR-18 HTTP clients for API communication
- Implement rate limiting and retry logic
- Support environment-based configuration
- Provide fallback to static analysis when AI is unavailable

## Bundle Structure

- `src/` - Main source code
- `tests/` - Unit and integration tests  
- `config/` - Bundle configuration
- `docs/` - Documentation

When generating code, ensure it's production-ready, well-documented, and follows Symfony best practices.

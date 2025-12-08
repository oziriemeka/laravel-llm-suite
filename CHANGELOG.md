# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2024-12-08

### Added
- **Conversation Management** - Multi-turn chat with automatic context
  - `Llm::conversation()` to start or resume conversations
  - Session storage driver (stores in Laravel session)
  - Database storage driver (persistent storage with migration)
  - System prompt support per conversation
  - `Llm::conversations()` to list all conversation IDs
- **Token Usage Tracking** - Monitor token consumption
  - `TokenUsage` class with prompt, completion, and total tokens
  - Available via `$response->tokenUsage`
- **LM Studio Improvements**
  - Added `protocol` option (http/https)
  - Added `base_url` option for full URL override
- Database migration for conversation storage

### Changed
- Default conversation driver changed from `session` to `database`
- Updated all clients to return token usage in responses

## [0.1.2] - 2024-12-08

### Added
- LM Studio support for local LLM testing
  - Configurable host, port, timeout
  - `isAvailable()` method to check server status
  - `getAvailableModels()` method to list loaded models
- OpenAI `getAvailableModels()` and `isAvailable()` methods
- Package logo

### Changed
- Extracted hardcoded values to class constants across all clients
- Improved README documentation

## [0.1.1] - 2024-12-08

### Added
- Laravel 12.x support

### Fixed
- Composer dependency constraints for Laravel 12

## [0.1.0] - 2024-12-08

### Added
- Initial release
- **Chat API** - Unified interface for chat completions
  - OpenAI (GPT-4, GPT-4.1-mini, etc.)
  - Anthropic (Claude 3.5 Sonnet, etc.)
  - Dummy provider for testing
- **Image Generation** - OpenAI DALL-E support
- **Driver Pattern** - Switch providers with `Llm::using('provider')`
- Laravel service provider with auto-discovery
- Configuration file with environment variable support
- `Llm` facade with method hints
- Testing support with `Llm::fake()` and HTTP fakes
- Comprehensive documentation

[Unreleased]: https://github.com/OziriEmeka/laravel-llm-suite/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/OziriEmeka/laravel-llm-suite/compare/v0.1.2...v0.2.0
[0.1.2]: https://github.com/OziriEmeka/laravel-llm-suite/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/OziriEmeka/laravel-llm-suite/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/OziriEmeka/laravel-llm-suite/releases/tag/v0.1.0


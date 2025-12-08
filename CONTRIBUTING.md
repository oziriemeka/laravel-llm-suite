# Contributing to Laravel LLM Suite

Thank you for your interest in contributing to Laravel LLM Suite! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for everyone.

## How to Contribute

### Reporting Bugs

1. **Search existing issues** to avoid duplicates
2. **Create a new issue** with:
   - Clear, descriptive title
   - Steps to reproduce
   - Expected vs actual behavior
   - Laravel version, PHP version, package version
   - Relevant code snippets or error messages

### Suggesting Features

1. **Search existing issues** to see if it's been suggested
2. **Create a new issue** with:
   - Clear description of the feature
   - Use case / why it's needed
   - Proposed API or implementation (optional)

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch** from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Make your changes** following our coding standards
4. **Write/update tests** for your changes
5. **Update documentation** (README, docblocks)
6. **Commit with clear messages**:
   ```bash
   git commit -m "feat: Add support for XYZ"
   ```
7. **Push and create a Pull Request**

## Development Setup

### Requirements

- PHP 8.1+
- Composer
- Git

### Installation

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/laravel-llm-suite.git
cd laravel-llm-suite

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit
```

### Running Tests

```bash
# All tests
./vendor/bin/phpunit

# Specific test file
./vendor/bin/phpunit tests/Unit/LlmManagerTest.php

# With coverage
./vendor/bin/phpunit --coverage-html coverage
```

## Coding Standards

### PHP Style

- Follow **PSR-12** coding standard
- Use `declare(strict_types=1);` in all PHP files
- Use PHP 8.1+ features appropriately

### Code Structure

```php
<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Example;

class ExampleClass
{
    // 1. Constants
    protected const DEFAULT_VALUE = 'value';

    // 2. Properties
    protected string $property;

    // 3. Constructor
    public function __construct(string $property)
    {
        $this->property = $property;
    }

    // 4. Public methods
    public function doSomething(): string
    {
        return $this->helperMethod();
    }

    // 5. Protected/Private methods
    protected function helperMethod(): string
    {
        return self::DEFAULT_VALUE;
    }
}
```

### Constants for Magic Values

Always use class constants instead of hardcoded strings:

```php
// Good
protected const ENDPOINT_CHAT = '/chat/completions';
$response = $this->http()->post(self::ENDPOINT_CHAT, $payload);

// Bad
$response = $this->http()->post('/chat/completions', $payload);
```

### Type Hints

Always use type hints:

```php
public function process(string $input, array $options = []): Result
```

## Commit Messages

Format: `<type>: <description>`

| Type | Description |
|------|-------------|
| `feat` | New feature |
| `fix` | Bug fix |
| `docs` | Documentation changes |
| `refactor` | Code refactoring |
| `test` | Adding or updating tests |
| `chore` | Maintenance tasks |

Examples:
```
feat: Add Google Gemini provider
fix: Handle null response from API
docs: Add conversation examples to README
test: Add tests for TokenUsage class
```

## Adding a New Provider

1. Create client in `src/Clients/NewProvider/NewProviderClient.php`
2. Implement `ChatClient` and/or `ImageClient` interfaces
3. Register in `LlmManager::resolve()` method
4. Add configuration in `config/llm-suite.php`
5. Write tests in `tests/Unit/`
6. Update README with usage examples

## Documentation

- Update README.md for user-facing changes
- Add PHPDoc comments to all public methods
- Include `@param`, `@return`, and `@throws` tags

## Questions?

If you have questions about contributing, feel free to:
- Open a GitHub Discussion
- Create an issue with the "question" label

Thank you for contributing!


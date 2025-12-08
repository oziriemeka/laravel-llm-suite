# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.2.x   | :white_check_mark: |
| 0.1.x   | :white_check_mark: |
| < 0.1   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability within Laravel LLM Suite, please take the following steps:

### Do NOT

- Do not open a public GitHub issue for security vulnerabilities
- Do not discuss the vulnerability publicly until it has been addressed

### Do

1. **Email the maintainers directly** at [security@example.com] (replace with actual email)
2. Include the following information:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Any suggested fixes (optional)

### What to Expect

- **Acknowledgment:** Within 48 hours of your report
- **Initial Assessment:** Within 7 days
- **Resolution Timeline:** Depends on severity, typically within 30 days
- **Credit:** You will be credited in the security advisory (unless you prefer anonymity)

## Security Best Practices for Users

### API Keys

1. **Never commit API keys** to version control
2. Use environment variables:
   ```env
   OPENAI_API_KEY=sk-...
   ANTHROPIC_API_KEY=sk-ant-...
   ```
3. Use different API keys for development and production

### Configuration

1. In production, avoid storing sensitive configuration in plain text
2. Consider using Laravel's encrypted environment files
3. Restrict access to your `.env` file

### Database Storage

If using the database conversation driver:
1. Consider encrypting sensitive conversation data
2. Implement proper access controls
3. Set up appropriate data retention policies

## Known Security Considerations

### Token Usage

- API keys are transmitted to LLM providers
- Conversation history may contain sensitive data
- Consider implementing content filtering for user inputs

### Local LLM (LM Studio)

- Local LLM servers should not be exposed to the internet
- Use firewall rules to restrict access to localhost only


# Development Guide

This guide is for contributors and developers working on the lastfm-client library itself.

## 🧪 Testing

### Quick Commands

```bash
# Unit tests (fast, CI-compatible, no external dependencies)
composer test

# Integration tests (requires Last.fm API credentials)
composer test-integration

# All tests together (unit + integration)
composer test-all

# Code coverage (Unit Tests only - 100% target)
composer test-coverage

# Code coverage (All tests - requires credentials)
composer test-coverage-all
```

### Static Analysis & Code Quality

```bash
# Static analysis (PHPStan Level 8)
composer analyse

# Code style check (PSR-12)
composer cs

# Auto-fix code style
composer cs-fix
```

## 🔗 Integration Tests

Integration tests are **separated from the CI pipeline** to prevent:

- 🚫 Rate limiting (429 Too Many Requests)
- 🚫 Flaky builds due to network issues
- 🚫 Dependency on external API availability
- 🚫 Slow build times (2+ minutes vs. 0.4 seconds)

### Test Strategy

- **Unit Tests**: Fast, reliable, no external dependencies → **CI default** → **100% coverage**
- **Integration Tests**: Real API calls, rate-limited → **Manual execution** → **Full coverage with credentials**

### GitHub Secrets Required

To enable authenticated integration tests in CI/CD, add these secrets to your GitHub repository:

#### Repository Settings → Secrets and variables → Actions

| Secret Name          | Description                 | Where to get it                                               |
|----------------------|-----------------------------|---------------------------------------------------------------|
| `LASTFM_API_KEY`     | Your Last.fm API key        | [Last.fm API Account](https://www.last.fm/api/account/create) |
| `LASTFM_SECRET`      | Your Last.fm API secret     | [Last.fm API Account](https://www.last.fm/api/account/create) |
| `LASTFM_SESSION_KEY` | User session key (optional) | Authentication flow result                                    |

### Test Levels

#### 1. Public API Tests (Always Run)

- File: `tests/Integration/PublicApiIntegrationTest.php`
- No credentials required (runs without API keys)
- Tests public endpoints: artists, albums, tracks, charts
- Safe for forks and pull requests

#### 2. Authentication Levels Test (Conditional)

- File: `tests/Integration/AuthenticatedIntegrationTest.php`
- Requires API key and secret
- Tests authentication levels:
  - Level 1: No auth (public data)
  - Level 2: API key (basic authenticated calls)
  - Level 3: Session key (user-specific data)
  - Level 4: Write operations (scrobbling, when session is available)
  - Library methods (user library access)

### Local Development

```bash
# Set environment variables
export LASTFM_API_KEY="your-api-key"
export LASTFM_SECRET="your-api-secret" 
export LASTFM_SESSION_KEY="your-session-key"

# Run integration tests (public tests run without credentials, auth tests skip if no credentials)
composer test-integration

# Run all tests (unit + integration) with detailed output
composer test-all -- --testdox
```

### Safety Notes

- Public tests are safe for any environment
- Authentication tests will be skipped if secrets are missing
- No credentials are logged or exposed in the test output
- Tests use read-only operations only (no data modification)

## 🛠️ Development Workflow

1. Fork the repository
2. Create feature branch (`git checkout -b feature/name`)
3. Make changes with tests
4. Run test suite (`composer test-all`)
5. Check code quality (`composer analyse && composer cs`)
6. Commit changes (`git commit -m 'Add feature'`)
7. Push to branch (`git push origin feature/name`)
8. Open Pull Request

## 📋 Code Standards

- **PHP Version**: ^8.1
- **Code Style**: PSR-12 (enforced by PHP-CS-Fixer)
- **Static Analysis**: PHPStan Level 8
- **Test Coverage**: 100% lines, methods, and classes (Unit Tests)
- **Dependencies**: Minimal (only Guzzle required)

## 🔍 Architecture

The library consists of only four main classes:

1. **`LastFmClient`** - Main API client with method calls
2. **`LastFmClientFactory`** - Factory for creating authenticated clients
3. **`AuthHelper`** - Authentication and session management helper
4. **`ConfigCache`** - Service configuration cache

Simple, focused architecture with minimal dependencies.

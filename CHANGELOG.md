# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-09-23

### Added

- New `AuthHelper` class for streamlined Last.fm authentication workflows
- `LastFmClientFactory` with authentication methods (`createWithApiKey`, `createWithSession`, `createWithMobileAuth`)
- `ConfigCache` singleton for performance-optimized configuration management
- Comprehensive integration and unit test suite with 100% coverage
- Enhanced parameter validation with security and performance limits

### Changed

- **BREAKING**: Method names changed to verb-first convention (e.g. `artistGetInfo()` → `getArtistInfo()`)
- **BREAKING**: Enhanced parameter handling with named parameter support and camelCase conversion
- **BREAKING**: Restructured authentication flow with dedicated `setApiCredentials()` method
- **BREAKING**: No backward compatibility for old method names to ensure clean API design
- Improved error handling with more descriptive messages and proper exception types
- Performance optimizations including configuration caching and efficient type conversions

### Fixed

- Parameter validation edge cases and security vulnerabilities
- HTTP response stream handling and JSON error processing
- Authentication signature generation consistency

[2.0.0]: https://github.com/calliostro/lastfm-client/releases/tag/v2.0.0

## [1.0.0] - 2025-08-29

### Added

- Initial release of an ultra-lightweight Last.fm API client for PHP 8.1+
- Service-description-based architecture with only two classes
- Direct method mapping (`getTrackInfo()` → `track.getInfo`)
- Support for all 55+ Last.fm API endpoints (track, artist, album, user, chart, tag, geo, library, auth methods)
- Full authentication support (API key and OAuth session-based)
- Scrobbling and now playing functionality
- Comprehensive error handling and type safety
- IDE support with method documentation and Last.fm API links
- PHPStan Level 8 compatibility for maximum static analysis
- PSR-12 compliant code with 100% test coverage
- Complete authentication examples and setup documentation

[1.0.0]: https://github.com/calliostro/lastfm-client/releases/tag/v1.0.0

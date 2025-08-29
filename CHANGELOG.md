# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-08-29

### Added

- Initial release of an ultra-lightweight Last.fm API client for PHP 8.1+
- Service-description-based architecture with only two classes
- Direct method mapping (`trackGetInfo()` → `track.getInfo`)
- Support for all 60+ Last.fm API endpoints (track, artist, album, user, chart, tag, geo, library, auth methods)
- Full authentication support (API key and OAuth session-based)
- Scrobbling and now playing functionality
- Comprehensive error handling and type safety
- IDE support with method documentation and Last.fm API links
- PHPStan Level 8 compatibility for maximum static analysis
- PSR-12 compliant code with 100% test coverage
- Complete authentication examples and setup documentation

[1.0.0]: https://github.com/calliostro/lastfm-client/releases/tag/v1.0.0

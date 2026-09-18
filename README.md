# Last.fm API Client for PHP 8.1+

[![Package Version](https://img.shields.io/packagist/v/calliostro/lastfm-client.svg)](https://packagist.org/packages/calliostro/lastfm-client)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/lastfm-client.svg)](https://packagist.org/packages/calliostro/lastfm-client)
[![License](https://poser.pugx.org/calliostro/lastfm-client/license)](https://packagist.org/packages/calliostro/lastfm-client)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![Guzzle](https://img.shields.io/badge/guzzle-%5E7.0%20%7C%7C%20%5E8.0-orange.svg)](https://docs.guzzlephp.org/)
[![CI](https://github.com/calliostro/lastfm-client/actions/workflows/ci.yml/badge.svg)](https://github.com/calliostro/lastfm-client/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/calliostro/lastfm-client/graph/badge.svg?token=0SV4IXE9V1)](https://codecov.io/gh/calliostro/lastfm-client)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-PSR12-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

A lightweight, modern PHP client for the [Last.fm API](https://www.last.fm/api), supporting all 55+ endpoints with built-in resilience, session and mobile authentication, and PHP 8.1+ compatibility.

## 📦 Installation

```bash
composer require calliostro/lastfm-client
```

### Do You Need to Register?

**For ALL API calls:** Registration required

- [Register your application](https://www.last.fm/api/account/create) at Last.fm to get credentials
- **API Key needed for:** ALL methods (artist info, search, charts, etc.)

**For write operations:** Session authentication required

- **Session Key needed for:** scrobbling, loving tracks, personal collections, tagging

---

## 🚀 Quick Start

### Read-Only Data (API key required for all methods)

```php
use Calliostro\LastFm\LastFmClientFactory;

$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret');

$artist = $lastfm->getArtistInfo('Billie Eilish');          // Get artist info
$release = $lastfm->getAlbumInfo('The Weeknd', 'Dawn FM');  // Album info  
$charts = $lastfm->getTopArtistsChart();                    // Global charts
```

### Search with API Credentials

```php
use Calliostro\LastFm\LastFmClientFactory;

$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret');

// Positional parameters (traditional)
$results = $lastfm->searchArtists('Taylor Swift', 20);
$tracks = $lastfm->searchTracks('Anti-Hero', 'Taylor Swift');

// Named parameters (PHP 8.0+, recommended for clarity)
$results = $lastfm->searchArtists(artist: 'Taylor Swift', limit: 20);
$tracks = $lastfm->searchTracks(track: 'Anti-Hero', artist: 'Taylor Swift');
```

### Your Scrobbles (Session Authentication)

```php
use Calliostro\LastFm\LastFmClientFactory;

$lastfm = LastFmClientFactory::createWithSession('your-api-key', 'your-secret', 'your-session-key');

$collection = $lastfm->getUserRecentTracks('your-username');
$loved = $lastfm->getUserLovedTracks('your-username');

// Scrobble and love tracks with named parameters
$lastfm->scrobbleTrack(
    artist: 'Bad Bunny',
    track: 'Un Verano Sin Ti',
    timestamp: time()
);
```

### Multi-User Apps (Mobile Auth)

```php
use Calliostro\LastFm\LastFmClientFactory;

$lastfm = LastFmClientFactory::createWithMobileAuth('your-api-key', 'your-secret', 'your-username', 'your-password');

$identity = $lastfm->getUserInfo();
```

---

## ✨ Key Features

- **Simple Setup** – Works immediately with an API key, easy authentication for user actions.
- **Complete API Coverage** – All 55+ Last.fm API endpoints supported.
- **Built-in Resilience** – Automatic retries on `503 Service Temporarily Unavailable`, `429 Too Many Requests`, and connection errors with exponential backoff.
- **Clean Parameter API** – Natural method calls: `$client->getArtistInfo('Billie Eilish')` with PHP 8 named parameter support.
- **Lightweight Focus** – Minimal codebase with only essential dependencies (`guzzlehttp/guzzle: ^7.0 || ^8.0`).
- **Modern PHP Comfort** – Full IDE auto-completion, type safety, and PHPStan Level 8 clean.
- **Authentication Support** – Full API key, Session Key, and Mobile Authentication flows supported.
- **Well Tested** – Comprehensive test suite, PSR-12 compliant.
- **Future-Ready** – PHP 8.1–8.6 compatible.
- **Pure Guzzle** – Standard Guzzle 7/8 HTTP client without proprietary transport wrappers.

---

## 🎵 All Last.fm API Methods as Direct Calls

- **Album Methods** – `getAlbumInfo()`, `searchAlbums()`, `getAlbumTopTags()`, `addAlbumTags()`, `removeAlbumTag()`, `getAlbumTags()`
- **Artist Methods** – `getArtistInfo()`, `getArtistTopTracks()`, `getSimilarArtists()`, `searchArtists()`, `getArtistTopAlbums()`, `getArtistCorrection()`, `addArtistTags()`, `removeArtistTag()`, `getArtistTags()`, `getArtistTopTags()`
- **Track Methods** – `getTrackInfo()`, `searchTracks()`, `getSimilarTracks()`, `scrobbleTrack()`, `updateNowPlaying()`, `loveTrack()`, `unloveTrack()`, `getTrackCorrection()`, `addTrackTags()`, `removeTrackTag()`, `getTrackTags()`, `getTrackTopTags()`
- **User Methods** – `getUserInfo()`, `getUserRecentTracks()`, `getUserLovedTracks()`, `getUserTopArtists()`, `getUserTopTracks()`, `getUserTopAlbums()`, `getUserFriends()`, `getUserArtistTracks()`, `getUserPersonalTags()`, `getUserTopTags()`
- **Chart Methods** – `getTopArtistsChart()`, `getTopTracksChart()`, `getTopTagsChart()`
- **Geography Methods** – `getTopArtistsByCountry()`, `getTopTracksByCountry()`
- **Tag Methods** – `getTagInfo()`, `getSimilarTags()`, `getTagTopArtists()`, `getTagTopTracks()`, `getTagTopAlbums()`, `getTopTags()`, `getTagWeeklyChartList()`
- **Authentication Methods** – `getToken()`, `getSession()`, `getMobileSession()`
- **Library Methods** – `getLibraryArtists()`
- **User Charts** – `getUserWeeklyArtistChart()`, `getUserWeeklyAlbumChart()`, `getUserWeeklyTrackChart()`, `getUserWeeklyChartList()`

*All Last.fm API endpoints are supported — see the [Last.fm API Documentation](https://www.last.fm/api/) for complete parameter and response details.*

> [!NOTE]
> Some endpoints require session authentication (e.g., scrobbling, loved tracks, tagging) or specific permissions.

---

## 📋 Requirements

- **PHP** `^8.1`
- **guzzlehttp/guzzle** `^7.0 || ^8.0`

---

## ⚙️ Configuration

### Rate Limiting & Retries

Last.fm enforces rate limits and may occasionally return `503 Service Temporarily Unavailable` or `429 Too Many Requests`. By default (`auto_retry => true`, `max_retries => 3`), the client automatically retries temporary `503`, `429`, and connection failures using exponential backoff while respecting any `Retry-After` header.

You can customize or disable retries:

```php
use Calliostro\LastFm\LastFmClientFactory;

// Custom retry count
$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret', [
    'auto_retry' => true,   // Automatically wait and retry on 429/503 (default: true)
    'max_retries' => 5,     // Maximum number of retry attempts (default: 3)
]);

// Disable automatic retries (e.g. in tests or to handle exceptions immediately)
$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret', [
    'auto_retry' => false,
]);
```

### Advanced Configuration (Custom Guzzle handler, timeouts, headers)

```php
use Calliostro\LastFm\LastFmClientFactory;

$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret', [
    'timeout' => 30,
    'proxy' => 'http://proxy.example.com:8080',
    'verify' => true,
    'auto_retry' => true,
    'max_retries' => 3,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ],
]);
```

> [!NOTE]
> By default, the client uses `LastFmClient/2.1.0 +https://github.com/calliostro/lastfm-client` as its User-Agent. You can override this by providing custom headers in the configuration array.

---

## 🔐 Authentication

Get credentials at [Last.fm API Registration](https://www.last.fm/api/account/create).

### Quick Reference

| What you want to do         | Method                   | What you need                |
|-----------------------------|--------------------------|------------------------------|
| Get artist/track/chart info | `createWithApiKey()`     | API key + secret             |
| Search the database         | `createWithApiKey()`     | API key + secret             |
| Scrobble tracks             | `createWithSession()`    | API key + secret + session   |
| Access user collections     | `createWithSession()`    | API key + secret + session   |
| Mobile app                  | `createWithMobileAuth()` | API key + secret + user/pass |

### Complete Session Flow Example

**Step 1: authorize.php** – Redirect user to Last.fm

```php
<?php
// authorize.php

use Calliostro\LastFm\AuthHelper;

$apiKey = 'your-api-key';
$secret = 'your-secret';
$callbackUrl = 'https://yourapp.com/callback.php';

$auth = new AuthHelper($apiKey, $secret);

// For web apps, you can skip token generation and redirect directly:
$authUrl = "https://www.last.fm/api/auth/?api_key={$apiKey}&cb=" . urlencode($callbackUrl);

// For desktop apps, generate token first:
// $tokenData = $auth->getToken();
// $authUrl = $auth->getAuthorizationUrl($tokenData['token']);

header("Location: {$authUrl}");
exit;
```

**Step 2: callback.php** – Handle Last.fm callback

```php
<?php
// callback.php

require __DIR__ . '/vendor/autoload.php';

use Calliostro\LastFm\{AuthHelper, LastFmClientFactory};

$apiKey = 'your-api-key';
$secret = 'your-secret';
$token = $_GET['token'];

$auth = new AuthHelper($apiKey, $secret);
$sessionData = $auth->getSession($token);

$sessionKey = $sessionData['session']['key'];
$username = $sessionData['session']['name'];

// Store tokens for future use
$_SESSION['lastfm_session_key'] = $sessionKey;
$_SESSION['lastfm_username'] = $username;

$lastfm = LastFmClientFactory::createWithSession($apiKey, $secret, $sessionKey);
$user = $lastfm->getUserInfo();
echo "Hello " . $user['user']['name'];
```

---

## 🧪 Development & Testing Guide

See [DEVELOPMENT.md](DEVELOPMENT.md) for detailed setup instructions, test suite commands, static analysis, and contribution guidelines.

---

## 🤝 Contributing

Contributions are welcome! Please ensure all tests pass and coding standards are maintained:

```bash
composer cs-fix
composer analyse
composer test
```

---

## 📄 License

MIT License – see [LICENSE](LICENSE) file for details.

---

## ⚖️ Disclaimer

Last.fm is a registered trademark of CBS Interactive (or Paramount Global). This project is an independent, unofficial open-source library and is not affiliated with, endorsed by, or sponsored by Last.fm.

---

## 🙏 Acknowledgments

- [Last.fm](https://www.last.fm/) for providing the music data and scrobbling API.
- [Guzzle](https://docs.guzzlephp.org/) for HTTP transport.
- Sister projects: [`calliostro/musicbrainz-client`](https://github.com/calliostro/musicbrainz-client), [`calliostro/spotify-client`](https://github.com/calliostro/spotify-client), and [`calliostro/php-discogs-api`](https://github.com/calliostro/php-discogs-api).

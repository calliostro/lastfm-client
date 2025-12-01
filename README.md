# ⚡ Last.fm API Client for PHP 8.1+ – Lightweight with Maximum Developer Comfort

[![Package Version](https://img.shields.io/packagist/v/calliostro/lastfm-client.svg)](https://packagist.org/packages/calliostro/lastfm-client)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/lastfm-client.svg)](https://packagist.org/packages/calliostro/lastfm-client)
[![License](https://poser.pugx.org/calliostro/lastfm-client/license)](https://packagist.org/packages/calliostro/lastfm-client)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![Guzzle](https://img.shields.io/badge/guzzle-%5E6.5%7C%5E7.0-orange.svg)](https://docs.guzzlephp.org/)
[![CI](https://github.com/calliostro/lastfm-client/actions/workflows/ci.yml/badge.svg)](https://github.com/calliostro/lastfm-client/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/calliostro/lastfm-client/graph/badge.svg?token=0SV4IXE9V1)](https://codecov.io/gh/calliostro/lastfm-client)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-PSR12-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

> **🚀 MINIMAL YET POWERFUL!** Focused, lightweight Last.fm API client — as compact as possible while maintaining modern PHP comfort and clean APIs.

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

## 🚀 Quick Start

**Read-only data (API key required for all methods):**

```php
$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret');

$artist = $lastfm->getArtistInfo('Billie Eilish');          // Get artist info
$release = $lastfm->getAlbumInfo('The Weeknd', 'Dawn FM');  // Album info  
$charts = $lastfm->getTopArtistsChart();                    // Global charts
```

**Search with API credentials:**

```php
$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret');

// Positional parameters (traditional)
$results = $lastfm->searchArtists('Taylor Swift', 20);
$tracks = $lastfm->searchTracks('Anti-Hero', 'Taylor Swift');

// Named parameters (PHP 8.0+, recommended for clarity)
$results = $lastfm->searchArtists(artist: 'Taylor Swift', limit: 20);
$tracks = $lastfm->searchTracks(track: 'Anti-Hero', artist: 'Taylor Swift');
```

**Your scrobbles (session authentication):**

```php
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

**Multi-user apps (mobile auth):**

```php
$lastfm = LastFmClientFactory::createWithMobileAuth('your-api-key', 'your-secret', 'your-username', 'your-password');

$identity = $lastfm->getUserInfo();
```

## ✨ Key Features

- **Simple Setup** – Works immediately with an API key, easy authentication for advanced features
- **Complete API Coverage** – All 55+ Last.fm API endpoints supported
- **Clean Parameter API** – Natural method calls: `getArtistInfo('Billie Eilish')` with named parameter support
- **Lightweight Focus** – Minimal codebase with only essential dependencies
- **Modern PHP Comfort** – Full IDE support, type safety, PHPStan Level 8 without bloat
- **Secure Authentication** – Full session and mobile authentication support
- **Well Tested** – 100% test coverage, PSR-12 compliant
- **Future-Ready** – PHP 8.1–8.5 compatible (beta/dev testing)
- **Pure Guzzle** – Modern HTTP client, no custom transport layers

## 🎵 All Last.fm API Methods as Direct Calls

- **Album Methods** – getAlbumInfo(), searchAlbums(), getAlbumTopTags(), addAlbumTags(), removeAlbumTag(), getAlbumTags()
- **Artist Methods** – getArtistInfo(), getArtistTopTracks(), getSimilarArtists(), searchArtists(), getArtistTopAlbums(), getArtistCorrection(), addArtistTags(), removeArtistTag(), getArtistTags(), getArtistTopTags()
- **Track Methods** – getTrackInfo(), searchTracks(), getSimilarTracks(), scrobbleTrack(), updateNowPlaying(), loveTrack(), unloveTrack(), getTrackCorrection(), addTrackTags(), removeTrackTag(), getTrackTags(), getTrackTopTags()
- **User Methods** – getUserInfo(), getUserRecentTracks(), getUserLovedTracks(), getUserTopArtists(), getUserTopTracks(), getUserTopAlbums(), getUserFriends(), getUserArtistTracks(), getUserPersonalTags(), getUserTopTags()
- **Chart Methods** – getTopArtistsChart(), getTopTracksChart(), getTopTagsChart()
- **Geography Methods** – getTopArtistsByCountry(), getTopTracksByCountry()
- **Tag Methods** – getTagInfo(), getSimilarTags(), getTagTopArtists(), getTagTopTracks(), getTagTopAlbums(), getTopTags(), getTagWeeklyChartList()
- **Authentication Methods** – getToken(), getSession(), getMobileSession()
- **Library Methods** – getLibraryArtists()
- **User Charts** – getUserWeeklyArtistChart(), getUserWeeklyAlbumChart(), getUserWeeklyTrackChart(), getUserWeeklyChartList()

*All Last.fm API endpoints are supported with clean documentation — see [Last.fm API Documentation](https://www.last.fm/api/) for complete method reference*

> 💡 **Note:** Some endpoints require authentication (scrobbling, user libraries) or specific permissions.

## 📋 Requirements

- **php** ^8.1
- **guzzlehttp/guzzle** ^6.5 || ^7.0

## ⚙️ Configuration

### Configuration

**Simple (works out of the box):**

```php
use Calliostro\LastFm\LastFmClientFactory;

$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret');
```

**Advanced (middleware, custom options, etc.):**

```php
use Calliostro\LastFm\LastFmClientFactory;
use GuzzleHttp\{HandlerStack, Middleware};

$handler = HandlerStack::create();
$handler->push(Middleware::retry(
    fn ($retries, $request, $response) => $retries < 3 && $response?->getStatusCode() === 429,
    fn ($retries) => 1000 * 2 ** ($retries + 1) // Rate limit handling
));

$lastfm = LastFmClientFactory::createWithApiKey('your-api-key', 'your-secret', [
    'timeout' => 30,
    'handler' => $handler,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ]
]);
```

> 💡 **Note:** By default, the client uses `LastFmClient/2.0.0 +https://github.com/calliostro/lastfm-client` as User-Agent. You can override this by setting custom headers as shown above.

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

## 🤝 Contributing

Contributions are welcome! See [DEVELOPMENT.md](DEVELOPMENT.md) for detailed setup instructions, testing guide, and development workflow.

## 📄 License

MIT License – see [LICENSE](LICENSE) file.

## 🙏 Acknowledgments

- [Last.fm](https://www.last.fm/) for providing the excellent music data and scrobbling API
- [Guzzle](https://docs.guzzlephp.org/) for the robust HTTP client
- The PHP community for continuous inspiration

---

> ⭐ **Star this repo if you find it useful!**

# ⚡ Last.fm Client for PHP 8.1+ – Ultra-Lightweight

[![Latest Stable Version](https://img.shields.io/packagist/v/calliostro/lastfm-client.svg)](https://packagist.org/packages/calliostro/lastfm-client)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/lastfm-client.svg)](https://packagist.org/packages/calliostro/lastfm-client)
[![License](https://img.shields.io/packagist/l/calliostro/lastfm-client.svg)](https://packagist.org/packages/calliostro/lastfm-client)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![Guzzle](https://img.shields.io/badge/guzzle-%5E6.5%7C%5E7.0-orange.svg)](https://docs.guzzlephp.org/)
[![CI](https://img.shields.io/github/actions/workflow/status/calliostro/lastfm-client/ci.yml.svg)](https://github.com/calliostro/lastfm-client/actions)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)](https://github.com/calliostro/lastfm-client)

> **🚀 ONLY 2 CLASSES!** The most lightweight Last.fm API client for PHP. Zero bloats, maximum performance.

An **ultra-minimalist** Last.fm API client that proves you don't need 20+ classes to build a great API client. Built with modern PHP 8.1+ features, service descriptions, and powered by Guzzle.

## 📦 Installation

```bash
composer require calliostro/lastfm-client
```

**Important:** You need to [register your application](https://www.last.fm/api/account/create) at Last.fm to get your API key and secret. All API calls require a valid API key.

**Symfony Users:** For easier integration, there's also a [Symfony Bundle](https://github.com/calliostro/last-fm-client-bundle) available (currently supports only pre-configured sessions, but sufficient if you don't need dynamic authentication or scrobbling).

## 🚀 Quick Start

### Basic Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Calliostro\LastFm\ClientFactory;

// Basic client for read-only operations
$lastfm = ClientFactory::create('your-api-key', 'your-secret');

// Fetch track information
$track = $lastfm->trackGetInfo([
    'artist' => 'Coldplay',
    'track' => 'Viva La Vida'
]);

$artist = $lastfm->artistGetInfo([
    'artist' => 'Ed Sheeran'
]);

echo "Track: " . $track['track']['name'] . "\n";
echo "Artist: " . $artist['artist']['name'] . "\n";
```

### Scrobbling and Now Playing

```php
// Authenticated client for write operations (scrobbling, loving tracks)
$lastfm = ClientFactory::createWithAuth(
    'your-api-key',
    'your-secret',
    'session-key'
);

$lastfm->trackUpdateNowPlaying([
    'artist' => 'Linkin Park',
    'track' => 'In the End'
]);

$lastfm->trackScrobble([
    'artist' => 'Linkin Park',
    'track' => 'In the End',
    'timestamp' => time()
]);

// Mark tracks as loved/unloved
$lastfm->trackLove(['artist' => 'Linkin Park', 'track' => 'In the End']);
$lastfm->trackUnlove(['artist' => 'Eminem', 'track' => 'Lose Yourself']);
```

### Discovery and Charts

```php
// Find similar artists and top tracks
$similar = $lastfm->artistGetSimilar(['artist' => 'Imagine Dragons']);
$topTracks = $lastfm->artistGetTopTracks(['artist' => 'Adele']);

// Fetch user listening history
$recentTracks = $lastfm->userGetRecentTracks(['user' => 'username']);
$topArtists = $lastfm->chartGetTopArtists(['limit' => 10]);

// Explore music by genre/tag
$rockTracks = $lastfm->tagGetTopTracks(['tag' => 'rock']);
```

## ✨ Key Features

- **Ultra-Lightweight** – Only 2 classes, ~130 lines of logic + service descriptions
- **Complete API Coverage** – All 60+ Last.fm API endpoints supported
- **Direct API Calls** – `$client->trackGetInfo()` maps to `track.getInfo`, no abstractions
- **Type Safe + IDE Support** – Full PHP 8.1+ types, PHPStan Level 8, method autocomplete
- **Future-Ready** – PHP 8.5 compatible (beta/dev testing)
- **Pure Guzzle** – Modern HTTP client, no custom transport layers
- **Well Tested** – 100% test coverage, PSR-12 compliant
- **Secure Authentication** – Full OAuth and API key support

## 🎵 All Last.fm API Methods as Direct Calls

- **Track Methods** – trackGetInfo(), trackScrobble(), trackUpdateNowPlaying(), trackLove(), trackUnlove()  
- **Artist Methods** – artistGetInfo(), artistGetTopTracks(), artistGetSimilar(), artistSearch()  
- **User Methods** – userGetInfo(), userGetRecentTracks(), userGetLovedTracks(), userGetTopArtists()  
- **Chart Methods** – chartGetTopArtists(), chartGetTopTracks()  
- **Album Methods** – albumGetInfo(), albumSearch()  
- **Tag Methods** – tagGetInfo(), tagGetTopTracks(), tagGetTopTags()  
- **Auth Methods** – authGetToken(), authGetSession()  
- **Geo Methods** – geoGetTopArtists(), geoGetTopTracks()  
- **Library Methods** – libraryGetArtists()  

*All 60+ Last.fm API endpoints are supported with clean documentation — see [Last.fm API Documentation](https://www.last.fm/api) for complete method reference*

## 📋 Requirements

- php ^8.1
- guzzlehttp/guzzle ^6.5 || ^7.0

## 🔧 Advanced Configuration

### Option 1: Simple Configuration (Recommended)

For basic customizations like timeout or User-Agent, use the ClientFactory:

```php
use Calliostro\LastFm\ClientFactory;

$lastfm = ClientFactory::create('your-api-key', 'your-secret', [
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ]
]);
```

### Option 2: Advanced Guzzle Configuration

For advanced HTTP client features (middleware, interceptors, etc.), create your own Guzzle client:

```php
use GuzzleHttp\Client;
use Calliostro\LastFm\LastFmApiClient;

$httpClient = new Client([
    'timeout' => 30,
    'connect_timeout' => 10,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ]
]);

// Direct usage
$lastfm = new LastFmApiClient('your-api-key', 'your-secret', $httpClient);

// Or via ClientFactory
$lastfm = ClientFactory::create('your-api-key', 'your-secret', $httpClient);
```

> **💡 Note:** By default, the client uses `LastFmClient/1.0 (+https://github.com/calliostro/lastfm-client)` as User-Agent. You can override this by setting custom headers as shown above.

## 🔐 Authentication

Last.fm supports different authentication flows:

### Web Application Authentication (Recommended)

For web applications, use the simplified flow where Last.fm generates the token automatically:

> **💡 Quick reminder:** Make sure you've configured the callback URL (e.g., `https://yourapp.com/lastfm/callback`) in your Last.fm application settings, otherwise the redirect won't work!

```php
// Step 1: Redirect the user to Last.fm
$callbackUrl = 'https://yourapp.com/lastfm/callback';
$authUrl = "https://www.last.fm/api/auth/?api_key=your-api-key&cb=" . urlencode($callbackUrl);

// Step 2: User authorizes at Last.fm (redirect to $authUrl)
// Step 3: Last.fm redirects back with token parameter

// Step 4: In your callback, get a session key with the received token
$token = $_GET['token']; // Token from Last.fm callback
$session = $lastfm->authGetSession(['token' => $token]);
$sessionKey = $session['session']['key'];

// Step 5: Use an authenticated client
$authenticatedClient = ClientFactory::createWithAuth('your-api-key', 'your-secret', $sessionKey);
```

### Desktop Application Authentication

For desktop/mobile applications, you may need to generate a token first:

```php
// Step 1: Get a token (for desktop apps)
$token = $lastfm->authGetToken();
$authUrl = "https://www.last.fm/api/auth/?api_key=your-api-key&token=" . $token['token'];

// Step 2: User authorizes at $authUrl
// Step 3: Get a session key
$session = $lastfm->authGetSession(['token' => $token['token']]);
$sessionKey = $session['session']['key'];
```

### Complete Web Application Example

```php
<?php
// authorize.php

$apiKey = 'your-api-key';
$callbackUrl = 'https://yourapp.com/callback.php';
$authUrl = "https://www.last.fm/api/auth/?api_key={$apiKey}&cb=" . urlencode($callbackUrl);

header("Location: {$authUrl}");
exit;
```

```php
<?php
// callback.php

require __DIR__ . '/vendor/autoload.php';

use Calliostro\LastFm\ClientFactory;

$apiKey = 'your-api-key';
$secret = 'your-secret';
$token = $_GET['token'];

$lastfm = ClientFactory::create($apiKey, $secret);
$session = $lastfm->authGetSession(['token' => $token]);
$sessionKey = $session['session']['key'];
$username = $session['session']['name'];

// Store $sessionKey and $username for future use (database, session, cache, etc.)

$lastfm = ClientFactory::createWithAuth($apiKey, $secret, $sessionKey);
$lastfm->trackScrobble([
    'artist' => 'Adele',
    'track' => 'Rolling in the Deep', 
    'timestamp' => time()
]);
```

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Check code style:

```bash
composer cs
```

## 📖 API Documentation Reference

For complete API documentation including all available parameters, visit the [Last.fm API Documentation](https://www.last.fm/api).

### Popular Methods

#### Track Methods

- `trackGetInfo($params)` – Get track information
- `trackSearch($params)` – Search for tracks
- `trackScrobble($params)` – Scrobble a track (auth required)
- `trackUpdateNowPlaying($params)` – Update now playing (auth required)
- `trackLove($params)` – Love a track (auth required)
- `trackUnlove($params)` – Remove love from track (auth required)

#### Artist Methods

- `artistGetInfo($params)` – Get artist information
- `artistGetTopTracks($params)` – Get artist's top tracks
- `artistGetSimilar($params)` – Get similar artists
- `artistSearch($params)` – Search for artists

#### User Methods

- `userGetInfo($params)` – Get user profile information
- `userGetRecentTracks($params)` – Get user's recent tracks
- `userGetLovedTracks($params)` – Get user's loved tracks
- `userGetTopArtists($params)` – Get user's top artists

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure your code follows PSR-12 standards and includes tests.

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Last.fm](https://last.fm) for providing the excellent music data API
- [Guzzle](https://docs.guzzlephp.org/) for the robust HTTP client
- The PHP community for continuous inspiration

---

> **⭐ Star this repo** if you find it useful! It helps others discover this lightweight solution.

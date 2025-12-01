# Upgrade Guide: v2.x to v2.0

This guide helps you upgrade your Last.fm API client to newer versions.

## 📋 Overview

Version 2.0 introduces significant improvements to authentication, parameter handling, and overall developer experience while maintaining backward compatibility for most use cases.

## ⚠️ Breaking Changes

### 1. Authentication Setup

**Before (v1.x):**

```php
$client = new LastFmClient(['api_key' => 'your-key']);
```

**After (v2.0):**

```php
// For read-only methods (search, charts, getInfo, etc.)
$client = LastFmClientFactory::createWithApiKey('your-key', 'your-secret');

// For write methods (scrobbling, loving tracks)
$client = LastFmClientFactory::createWithSession('your-key', 'your-secret', 'session-key');
```

### 2. Method Names Changed to Verb-First Convention

**Before (v1.x):**

```php
$client->artistGetInfo('Taylor Swift');
$client->trackGetInfo('Taylor Swift', 'Anti-Hero');
$client->albumGetInfo('Taylor Swift', 'Midnights');
$client->userGetTopTracks('username');
$client->trackScrobble('Artist', 'Track', time());
$client->trackLove('Artist', 'Track');
```

**After (v2.0):**

```php
$client->getArtistInfo('Taylor Swift');
$client->getTrackInfo('Taylor Swift', 'Anti-Hero');
$client->getAlbumInfo('Taylor Swift', 'Midnights');
$client->getUserTopTracks('username');
$client->scrobbleTrack('Artist', 'Track', time());
$client->loveTrack('Artist', 'Track');
```

### 3. Authentication Credentials

**Before (v1.x):**
Authentication was handled manually through parameters.

**After (v2.0):**

```php
$client = new LastFmClient();
$client->setApiCredentials('api-key', 'api-secret', 'session-key');
```

## ✨ New Features

### 1. Named Parameters Support

```php
// Both work in v2.0:
$client->getTrackInfo('Dua Lipa', 'Physical');  // Positional
$client->getTrackInfo(artist: 'Dua Lipa', track: 'Physical');  // Named
```

### 2. Authentication Helper

```php
$auth = new AuthHelper('api-key', 'api-secret');

// Get authorization token
$token = $auth->getToken();

// Generate authorization URL
$url = $auth->getAuthorizationUrl($token['token']);

// Get session after user authorization
$session = $auth->getSession($token['token']);
```

### 3. Enhanced Error Handling

More descriptive error messages and proper exception types:

```php
try {
    $result = $client->getArtistInfo('NonExistent');
} catch (RuntimeException $e) {
    echo "API Error: " . $e->getMessage();
}
```

## 🔄 Migration Steps

1. **Update your dependencies:**

   ```bash
   composer update calliostro/lastfm-client
   ```

2. **Replace direct instantiation with factory methods:**
   - Use `LastFmClientFactory::createWithApiKey()` for read-only methods (most methods)
   - Use `LastFmClientFactory::createWithSession()` for write methods (scrobbling, loving, etc.)

3. **Update authentication setup:**
   - Move API credentials to factory method or `setApiCredentials()`
   - Use new `AuthHelper` for authentication flows

4. **Update all method names:**
   - Use the migration examples above as reference
   - Replace all old method names with verb-first equivalents
   - Test your integration to ensure all calls work correctly

## 📝 Complete Method Migration Examples

### Database Methods

**Before (v1.x):**

```php
$client->artistGetInfo('Taylor Swift');
$client->artistGetTopTracks('Taylor Swift');
$client->albumGetInfo('Taylor Swift', 'Midnights');
$client->trackGetInfo('Taylor Swift', 'Anti-Hero');
$client->tagGetInfo('pop');
$client->userGetInfo('username');
$client->userGetTopTracks('username');
```

**After (v2.0):**

```php
$client->getArtistInfo('Taylor Swift');
$client->getArtistTopTracks('Taylor Swift');
$client->getAlbumInfo('Taylor Swift', 'Midnights');
$client->getTrackInfo('Taylor Swift', 'Anti-Hero');
$client->getTagInfo('pop');
$client->getUserInfo('username');
$client->getUserTopTracks('username');
```

### Search Methods

**Before (v1.x):**

```php
$client->artistSearch('Taylor Swift');
$client->albumSearch('Midnights');
$client->trackSearch('Anti-Hero');
```

**After (v2.0):**

```php
$client->searchArtists('Taylor Swift');
$client->searchAlbums('Midnights');
$client->searchTracks('Anti-Hero');
```

### Write Operations

**Before (v1.x):**

```php
$client->trackScrobble('Artist', 'Track', time());
$client->trackLove('Artist', 'Track');
$client->trackUnlove('Artist', 'Track');
$client->trackUpdateNowPlaying('Artist', 'Track');
$client->artistAddTags('Artist', ['rock', 'pop']);
```

**After (v2.0):**

```php
$client->scrobbleTrack('Artist', 'Track', time());
$client->loveTrack('Artist', 'Track');
$client->unloveTrack('Artist', 'Track');
$client->updateNowPlayingTrack('Artist', 'Track');
$client->addArtistTags('Artist', ['rock', 'pop']);
```

## 📌 Breaking Changes Summary

- **No backward compatibility** – All method names have changed
- **Verb-first naming**: `artistGetInfo()` → `getArtistInfo()`
- **Consistent conventions**: `get*`, `search*`, `add*`, `update*`, etc.
- Return data structures remain unchanged
- Error handling is enhanced but compatible

## 🛠️ Migration Helper

Use search and replace to update method names in your codebase:

```bash
# Find old method calls
grep -r "artistGet\|trackGet\|albumGet\|userGet\|trackLove\|trackScrobble" /path/to/your/project

# Replace common patterns (backup your files first!)
sed -i 's/artistGetInfo(/getArtistInfo(/g' /path/to/your/project/*.php
sed -i 's/trackGetInfo(/getTrackInfo(/g' /path/to/your/project/*.php
sed -i 's/albumGetInfo(/getAlbumInfo(/g' /path/to/your/project/*.php
sed -i 's/userGetTopTracks(/getUserTopTracks(/g' /path/to/your/project/*.php
sed -i 's/trackLove(/loveTrack(/g' /path/to/your/project/*.php
sed -i 's/trackScrobble(/scrobbleTrack(/g' /path/to/your/project/*.php
```

## ❓ Need Help?

If you encounter issues during the upgrade:

1. Use the migration examples above as reference
2. Review the enhanced error messages for guidance  
3. Open an issue if you find compatibility problems

# 📦 Upgrading from Legacy Versions

If you're upgrading from the legacy `calliostro/lastfm-client` package, please refer to the v1.0.0 migration guide in the archived repository.

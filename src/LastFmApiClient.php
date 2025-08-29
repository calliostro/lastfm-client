<?php

declare(strict_types=1);

namespace Calliostro\LastFm;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Minimalist Last.fm client using service descriptions
 *
 * Album methods:
 * @method array<string, mixed> albumGetInfo(array $params = []) Get album information — <a href="https://www.last.fm/api/show/album.getInfo">https://www.last.fm/api/show/album.getInfo</a>
 * @method array<string, mixed> albumGetTags(array $params = []) Get tags for an album — <a href="https://www.last.fm/api/show/album.getTags">https://www.last.fm/api/show/album.getTags</a>
 * @method array<string, mixed> albumGetTopTags(array $params = []) Get top tags for an album — <a href="https://www.last.fm/api/show/album.getTopTags">https://www.last.fm/api/show/album.getTopTags</a>
 * @method array<string, mixed> albumSearch(array $params = []) Search for albums — <a href="https://www.last.fm/api/show/album.search">https://www.last.fm/api/show/album.search</a>
 * @method array<string, mixed> albumAddTags(array $params = []) Add tags to an album (requires auth) — <a href="https://www.last.fm/api/show/album.addTags">https://www.last.fm/api/show/album.addTags</a>
 * @method array<string, mixed> albumRemoveTag(array $params = []) Remove a tag from an album (requires auth) — <a href="https://www.last.fm/api/show/album.removeTag">https://www.last.fm/api/show/album.removeTag</a>
 *
 * Artist methods:
 * @method array<string, mixed> artistGetInfo(array $params = []) Get artist information — <a href="https://www.last.fm/api/show/artist.getInfo">https://www.last.fm/api/show/artist.getInfo</a>
 * @method array<string, mixed> artistGetSimilar(array $params = []) Get similar artists — <a href="https://www.last.fm/api/show/artist.getSimilar">https://www.last.fm/api/show/artist.getSimilar</a>
 * @method array<string, mixed> artistGetTags(array $params = []) Get tags for an artist — <a href="https://www.last.fm/api/show/artist.getTags">https://www.last.fm/api/show/artist.getTags</a>
 * @method array<string, mixed> artistGetTopAlbums(array $params = []) Get top albums for an artist — <a href="https://www.last.fm/api/show/artist.getTopAlbums">https://www.last.fm/api/show/artist.getTopAlbums</a>
 * @method array<string, mixed> artistGetTopTags(array $params = []) Get top tags for an artist — <a href="https://www.last.fm/api/show/artist.getTopTags">https://www.last.fm/api/show/artist.getTopTags</a>
 * @method array<string, mixed> artistGetTopTracks(array $params = []) Get top tracks for an artist — <a href="https://www.last.fm/api/show/artist.getTopTracks">https://www.last.fm/api/show/artist.getTopTracks</a>
 * @method array<string, mixed> artistSearch(array $params = []) Search for artists — <a href="https://www.last.fm/api/show/artist.search">https://www.last.fm/api/show/artist.search</a>
 * @method array<string, mixed> artistGetCorrection(array $params = []) Get correction for an artist name — <a href="https://www.last.fm/api/show/artist.getCorrection">https://www.last.fm/api/show/artist.getCorrection</a>
 * @method array<string, mixed> artistAddTags(array $params = []) Add tags to an artist (requires auth) — <a href="https://www.last.fm/api/show/artist.addTags">https://www.last.fm/api/show/artist.addTags</a>
 * @method array<string, mixed> artistRemoveTag(array $params = []) Remove a tag from an artist (requires auth) — <a href="https://www.last.fm/api/show/artist.removeTag">https://www.last.fm/api/show/artist.removeTag</a>
 *
 * Auth methods:
 * @method array<string, mixed> authGetMobileSession(array $params = []) Get mobile session (requires signature) — <a href="https://www.last.fm/api/show/auth.getMobileSession">https://www.last.fm/api/show/auth.getMobileSession</a>
 * @method array<string, mixed> authGetSession(array $params = []) Get session (requires signature) — <a href="https://www.last.fm/api/show/auth.getSession">https://www.last.fm/api/show/auth.getSession</a>
 * @method array<string, mixed> authGetToken(array $params = []) Get auth token (requires signature) — <a href="https://www.last.fm/api/show/auth.getToken">https://www.last.fm/api/show/auth.getToken</a>
 *
 * Chart methods:
 * @method array<string, mixed> chartGetTopArtists(array $params = []) Get top artists from the charts — <a href="https://www.last.fm/api/show/chart.getTopArtists">https://www.last.fm/api/show/chart.getTopArtists</a>
 * @method array<string, mixed> chartGetTopTags(array $params = []) Get top tags from charts — <a href="https://www.last.fm/api/show/chart.getTopTags">https://www.last.fm/api/show/chart.getTopTags</a>
 * @method array<string, mixed> chartGetTopTracks(array $params = []) Get top tracks from the charts — <a href="https://www.last.fm/api/show/chart.getTopTracks">https://www.last.fm/api/show/chart.getTopTracks</a>
 *
 * Geo methods:
 * @method array<string, mixed> geoGetTopArtists(array $params = []) Get top artists by country — <a href="https://www.last.fm/api/show/geo.getTopArtists">https://www.last.fm/api/show/geo.getTopArtists</a>
 * @method array<string, mixed> geoGetTopTracks(array $params = []) Get top tracks by country — <a href="https://www.last.fm/api/show/geo.getTopTracks">https://www.last.fm/api/show/geo.getTopTracks</a>
 *
 * Library methods:
 * @method array<string, mixed> libraryGetArtists(array $params = []) Get artists from the user's library (requires auth) — <a href="https://www.last.fm/api/show/library.getArtists">https://www.last.fm/api/show/library.getArtists</a>
 *
 * Tag methods:
 * @method array<string, mixed> tagGetInfo(array $params = []) Get tag information — <a href="https://www.last.fm/api/show/tag.getInfo">https://www.last.fm/api/show/tag.getInfo</a>
 * @method array<string, mixed> tagGetSimilar(array $params = []) Get similar tags — <a href="https://www.last.fm/api/show/tag.getSimilar">https://www.last.fm/api/show/tag.getSimilar</a>
 * @method array<string, mixed> tagGetTopAlbums(array $params = []) Get top albums for a tag — <a href="https://www.last.fm/api/show/tag.getTopAlbums">https://www.last.fm/api/show/tag.getTopAlbums</a>
 * @method array<string, mixed> tagGetTopArtists(array $params = []) Get top artists for a tag — <a href="https://www.last.fm/api/show/tag.getTopArtists">https://www.last.fm/api/show/tag.getTopArtists</a>
 * @method array<string, mixed> tagGetTopTags(array $params = []) Get top tags — <a href="https://www.last.fm/api/show/tag.getTopTags">https://www.last.fm/api/show/tag.getTopTags</a>
 * @method array<string, mixed> tagGetTopTracks(array $params = []) Get top tracks for a tag — <a href="https://www.last.fm/api/show/tag.getTopTracks">https://www.last.fm/api/show/tag.getTopTracks</a>
 * @method array<string, mixed> tagGetWeeklyChartList(array $params = []) Get a weekly chart list for a tag — <a href="https://www.last.fm/api/show/tag.getWeeklyChartList">https://www.last.fm/api/show/tag.getWeeklyChartList</a>
 *
 * Track methods:
 * @method array<string, mixed> trackAddTags(array $params = []) Add tags to a track (requires auth) — <a href="https://www.last.fm/api/show/track.addTags">https://www.last.fm/api/show/track.addTags</a>
 * @method array<string, mixed> trackGetCorrection(array $params = []) Get correction for a track — <a href="https://www.last.fm/api/show/track.getCorrection">https://www.last.fm/api/show/track.getCorrection</a>
 * @method array<string, mixed> trackGetInfo(array $params = []) Get track information — <a href="https://www.last.fm/api/show/track.getInfo">https://www.last.fm/api/show/track.getInfo</a>
 * @method array<string, mixed> trackGetSimilar(array $params = []) Get similar tracks — <a href="https://www.last.fm/api/show/track.getSimilar">https://www.last.fm/api/show/track.getSimilar</a>
 * @method array<string, mixed> trackGetTags(array $params = []) Get tags for a track — <a href="https://www.last.fm/api/show/track.getTags">https://www.last.fm/api/show/track.getTags</a>
 * @method array<string, mixed> trackGetTopTags(array $params = []) Get top tags for a track — <a href="https://www.last.fm/api/show/track.getTopTags">https://www.last.fm/api/show/track.getTopTags</a>
 * @method array<string, mixed> trackLove(array $params = []) Love a track (requires auth) — <a href="https://www.last.fm/api/show/track.love">https://www.last.fm/api/show/track.love</a>
 * @method array<string, mixed> trackRemoveTag(array $params = []) Remove a tag from a track (requires auth) — <a href="https://www.last.fm/api/show/track.removeTag">https://www.last.fm/api/show/track.removeTag</a>
 * @method array<string, mixed> trackScrobble(array $params = []) Scrobble a track (requires auth) — <a href="https://www.last.fm/api/show/track.scrobble">https://www.last.fm/api/show/track.scrobble</a>
 * @method array<string, mixed> trackSearch(array $params = []) Search for tracks — <a href="https://www.last.fm/api/show/track.search">https://www.last.fm/api/show/track.search</a>
 * @method array<string, mixed> trackUnlove(array $params = []) Unlove a track (requires auth) — <a href="https://www.last.fm/api/show/track.unlove">https://www.last.fm/api/show/track.unlove</a>
 * @method array<string, mixed> trackUpdateNowPlaying(array $params = []) Update now playing track (requires auth) — <a href="https://www.last.fm/api/show/track.updateNowPlaying">https://www.last.fm/api/show/track.updateNowPlaying</a>
 *
 * User methods:
 * @method array<string, mixed> userGetFriends(array $params = []) Get user's friends — <a href="https://www.last.fm/api/show/user.getFriends">https://www.last.fm/api/show/user.getFriends</a>
 * @method array<string, mixed> userGetInfo(array $params = []) Get user information — <a href="https://www.last.fm/api/show/user.getInfo">https://www.last.fm/api/show/user.getInfo</a>
 * @method array<string, mixed> userGetLovedTracks(array $params = []) Get user's loved tracks — <a href="https://www.last.fm/api/show/user.getLovedTracks">https://www.last.fm/api/show/user.getLovedTracks</a>
 * @method array<string, mixed> userGetPersonalTags(array $params = []) Get user's personal tags — <a href="https://www.last.fm/api/show/user.getPersonalTags">https://www.last.fm/api/show/user.getPersonalTags</a>
 * @method array<string, mixed> userGetRecentTracks(array $params = []) Get a user's recent tracks — <a href="https://www.last.fm/api/show/user.getRecentTracks">https://www.last.fm/api/show/user.getRecentTracks</a>
 * @method array<string, mixed> userGetTopAlbums(array $params = []) Get user's top albums — <a href="https://www.last.fm/api/show/user.getTopAlbums">https://www.last.fm/api/show/user.getTopAlbums</a>
 * @method array<string, mixed> userGetTopArtists(array $params = []) Get user's top artists — <a href="https://www.last.fm/api/show/user.getTopArtists">https://www.last.fm/api/show/user.getTopArtists</a>
 * @method array<string, mixed> userGetTopTags(array $params = []) Get user's top tags — <a href="https://www.last.fm/api/show/user.getTopTags">https://www.last.fm/api/show/user.getTopTags</a>
 * @method array<string, mixed> userGetTopTracks(array $params = []) Get user's top tracks — <a href="https://www.last.fm/api/show/user.getTopTracks">https://www.last.fm/api/show/user.getTopTracks</a>
 * @method array<string, mixed> userGetWeeklyAlbumChart(array $params = []) Get a user's weekly album chart — <a href="https://www.last.fm/api/show/user.getWeeklyAlbumChart">https://www.last.fm/api/show/user.getWeeklyAlbumChart</a>
 * @method array<string, mixed> userGetWeeklyArtistChart(array $params = []) Get a user's weekly artist chart — <a href="https://www.last.fm/api/show/user.getWeeklyArtistChart">https://www.last.fm/api/show/user.getWeeklyArtistChart</a>
 * @method array<string, mixed> userGetWeeklyChartList(array $params = []) Get a user's weekly chart list — <a href="https://www.last.fm/api/show/user.getWeeklyChartList">https://www.last.fm/api/show/user.getWeeklyChartList</a>
 * @method array<string, mixed> userGetWeeklyTrackChart(array $params = []) Get a user's weekly track chart — <a href="https://www.last.fm/api/show/user.getWeeklyTrackChart">https://www.last.fm/api/show/user.getWeeklyTrackChart</a>
 */
class LastFmApiClient
{
    private GuzzleClient $client;

    /** @var array<string, mixed> */
    private array $config;

    private ?string $secret;
    private ?string $sessionKey = null;

    /**
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public function __construct(
        private readonly string $apiKey,
        ?string $secret = null,
        array|GuzzleClient $optionsOrClient = [],
    ) {
        $this->secret = $secret;

        // Load service configuration
        $this->config = require __DIR__ . '/../resources/service.php';

        // Create or use the provided Guzzle client
        if ($optionsOrClient instanceof GuzzleClient) {
            $this->client = $optionsOrClient;
        } else {
            $clientOptions = array_merge($this->config['client']['options'], $optionsOrClient);
            $this->client = new GuzzleClient($clientOptions);
        }
    }

    public function setSessionKey(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    /**
     * Magic method to call Last.fm API operations
     *
     * Examples:
     * - albumGetInfo(['artist' => 'Radiohead', 'album' => 'OK Computer'])
     * - trackSearch(['track' => 'Creep', 'artist' => 'Radiohead'])
     * - userGetRecentTracks(['user' => 'username', 'limit' => 10])
     *
     * @param array<int, mixed> $arguments
     * @return array<string, mixed>
     */
    public function __call(string $name, array $arguments): array
    {
        // Convert camelCase method name to operation name (e.g., trackGetInfo -> track.getInfo)
        $operationName = $this->convertMethodToOperation($name);

        if (!isset($this->config['operations'][$operationName])) {
            throw new \InvalidArgumentException("Unknown operation: {$operationName}");
        }

        $params = $arguments[0] ?? [];
        return $this->callOperation($operationName, $params);
    }

    private function convertMethodToOperation(string $methodName): string
    {
        // Convert trackGetInfo to track.getInfo
        $parts = preg_split('/(?=[A-Z])/', $methodName, -1, PREG_SPLIT_NO_EMPTY);

        if ($parts === false || count($parts) < 2) {
            throw new \InvalidArgumentException("Invalid method name: {$methodName}");
        }

        $service = strtolower($parts[0]);
        $method = lcfirst(implode('', array_slice($parts, 1)));

        return $service . '.' . $method;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function callOperation(string $operationName, array $params): array
    {
        $operation = $this->config['operations'][$operationName];

        // Add required parameters
        $params['method'] = $operationName;
        $params['api_key'] = $this->apiKey;
        $params['format'] = 'json';

        // Add a session key if required
        if (isset($operation['requiresAuth']) && $operation['requiresAuth'] && $this->sessionKey) {
            $params['sk'] = $this->sessionKey;
        }

        // Add a signature if required
        if (isset($operation['requiresSignature']) && $operation['requiresSignature'] && $this->secret) {
            $params['api_sig'] = $this->generateSignature($params);
        } elseif (isset($operation['requiresAuth']) && $operation['requiresAuth'] && $this->secret) {
            $params['api_sig'] = $this->generateSignature($params);
        }

        try {
            $httpMethod = $operation['httpMethod'] ?? 'GET';

            if ($httpMethod === 'POST') {
                $response = $this->client->post('', ['form_params' => $params]);
            } else {
                $response = $this->client->get('', ['query' => $params]);
            }

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
            }

            if (!is_array($data)) {
                throw new \RuntimeException('Expected array response from API');
            }

            if (isset($data['error'])) {
                throw new \RuntimeException($data['message'] ?? 'API Error', $data['error']);
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new \RuntimeException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function generateSignature(array $params): string
    {
        unset($params['format']); // Remove format from signature
        ksort($params);

        $signature = '';
        foreach ($params as $key => $value) {
            $signature .= $key . $value;
        }
        $signature .= $this->secret;

        return md5($signature);
    }
}

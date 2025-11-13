<?php

declare(strict_types=1);

namespace Calliostro\LastFm;

use DateTimeInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Ultra-lightweight Last.fm API client with smart parameter handling
 *
 * Album methods (parameter types based on official Last.fm API documentation):
 * @method array<string, mixed> addAlbumTags(string $artist, string $album, string $tags) Add tags to an album (Authentication required) — <a href="https://www.last.fm/api/show/album.addTags">https://www.last.fm/api/show/album.addTags</a>
 * @method array<string, mixed> getAlbumInfo(?string $artist = null, ?string $album = null, ?string $mbid = null, ?int $autocorrect = null, ?string $username = null, ?string $lang = null) Get album information — <a href="https://www.last.fm/api/show/album.getInfo">https://www.last.fm/api/show/album.getInfo</a>
 * @method array<string, mixed> getAlbumTags(string $user, ?string $artist = null, ?string $album = null, ?string $mbid = null, ?int $autocorrect = null) Get user's tags for an album (Authentication required) — <a href="https://www.last.fm/api/show/album.getTags">https://www.last.fm/api/show/album.getTags</a>
 * @method array<string, mixed> getAlbumTopTags(?string $artist = null, ?string $album = null, ?string $mbid = null, ?int $autocorrect = null) Get top tags for an album — <a href="https://www.last.fm/api/show/album.getTopTags">https://www.last.fm/api/show/album.getTopTags</a>
 * @method array<string, mixed> removeAlbumTag(string $artist, string $album, string $tag) Remove tag from the album (Authentication required) — <a href="https://www.last.fm/api/show/album.removeTag">https://www.last.fm/api/show/album.removeTag</a>
 * @method array<string, mixed> searchAlbums(string $album, ?int $limit = null, ?int $page = null) Search for albums — <a href="https://www.last.fm/api/show/album.search">https://www.last.fm/api/show/album.search</a>
 *
 * Artist methods:
 * @method array<string, mixed> addArtistTags(string $artist, string $tags) Add tags to an artist (Authentication required) — <a href="https://www.last.fm/api/show/artist.addTags">https://www.last.fm/api/show/artist.addTags</a>
 * @method array<string, mixed> getArtistCorrection(string $artist) Get correction for artist name — <a href="https://www.last.fm/api/show/artist.getCorrection">https://www.last.fm/api/show/artist.getCorrection</a>
 * @method array<string, mixed> getArtistInfo(?string $artist = null, ?string $mbid = null, ?string $lang = null, ?int $autocorrect = null, ?string $username = null) Get artist information — <a href="https://www.last.fm/api/show/artist.getInfo">https://www.last.fm/api/show/artist.getInfo</a>
 * @method array<string, mixed> getSimilarArtists(?string $artist = null, ?string $mbid = null, ?int $autocorrect = null, ?int $limit = null) Get similar artists — <a href="https://www.last.fm/api/show/artist.getSimilar">https://www.last.fm/api/show/artist.getSimilar</a>
 * @method array<string, mixed> getArtistTags(string $user, ?string $artist = null, ?string $mbid = null, ?int $autocorrect = null) Get user's tags for artist (Authentication required) — <a href="https://www.last.fm/api/show/artist.getTags">https://www.last.fm/api/show/artist.getTags</a>
 * @method array<string, mixed> getArtistTopAlbums(?string $artist = null, ?string $mbid = null, ?int $autocorrect = null, ?int $page = null, ?int $limit = null) Get top albums for artist — <a href="https://www.last.fm/api/show/artist.getTopAlbums">https://www.last.fm/api/show/artist.getTopAlbums</a>
 * @method array<string, mixed> getArtistTopTags(?string $artist = null, ?string $mbid = null, ?int $autocorrect = null) Get top tags for artist — <a href="https://www.last.fm/api/show/artist.getTopTags">https://www.last.fm/api/show/artist.getTopTags</a>
 * @method array<string, mixed> getArtistTopTracks(?string $artist = null, ?string $mbid = null, ?int $autocorrect = null, ?int $page = null, ?int $limit = null) Get top tracks for artist — <a href="https://www.last.fm/api/show/artist.getTopTracks">https://www.last.fm/api/show/artist.getTopTracks</a>
 * @method array<string, mixed> removeArtistTag(string $artist, string $tag) Remove tag from artist (Authentication required) — <a href="https://www.last.fm/api/show/artist.removeTag">https://www.last.fm/api/show/artist.removeTag</a>
 * @method array<string, mixed> searchArtists(string $artist, ?int $limit = null, ?int $page = null) Search for artists — <a href="https://www.last.fm/api/show/artist.search">https://www.last.fm/api/show/artist.search</a>
 *
 * Chart methods:
 * @method array<string, mixed> getTopArtistsChart(?int $page = null, ?int $limit = null) Get top artists chart — <a href="https://www.last.fm/api/show/chart.getTopArtists">https://www.last.fm/api/show/chart.getTopArtists</a>
 * @method array<string, mixed> getTopTagsChart(?int $page = null, ?int $limit = null) Get top tags chart — <a href="https://www.last.fm/api/show/chart.getTopTags">https://www.last.fm/api/show/chart.getTopTags</a>
 * @method array<string, mixed> getTopTracksChart(?int $page = null, ?int $limit = null) Get top tracks chart — <a href="https://www.last.fm/api/show/chart.getTopTracks">https://www.last.fm/api/show/chart.getTopTracks</a>
 *
 * Geography methods:
 * @method array<string, mixed> getTopArtistsByCountry(string $country, ?int $page = null, ?int $limit = null) Get top artists by country — <a href="https://www.last.fm/api/show/geo.getTopArtists">https://www.last.fm/api/show/geo.getTopArtists</a>
 * @method array<string, mixed> getTopTracksByCountry(string $country, ?int $page = null, ?int $limit = null) Get top tracks by country — <a href="https://www.last.fm/api/show/geo.getTopTracks">https://www.last.fm/api/show/geo.getTopTracks</a>
 *
 * Library methods:
 * @method array<string, mixed> getLibraryArtists(string $user, ?int $page = null, ?int $limit = null) Get user's library artists (Authentication required) — <a href="https://www.last.fm/api/show/library.getArtists">https://www.last.fm/api/show/library.getArtists</a>
 *
 * Tag methods:
 * @method array<string, mixed> getTagInfo(string $tag, ?string $lang = null) Get tag information — <a href="https://www.last.fm/api/show/tag.getInfo">https://www.last.fm/api/show/tag.getInfo</a>
 * @method array<string, mixed> getSimilarTags(string $tag) Get similar tags — <a href="https://www.last.fm/api/show/tag.getSimilar">https://www.last.fm/api/show/tag.getSimilar</a>
 * @method array<string, mixed> getTagTopAlbums(string $tag, ?int $page = null, ?int $limit = null) Get top albums for tag — <a href="https://www.last.fm/api/show/tag.getTopAlbums">https://www.last.fm/api/show/tag.getTopAlbums</a>
 * @method array<string, mixed> getTagTopArtists(string $tag, ?int $page = null, ?int $limit = null) Get top artists for tag — <a href="https://www.last.fm/api/show/tag.getTopArtists">https://www.last.fm/api/show/tag.getTopArtists</a>
 * @method array<string, mixed> getTopTags(?int $page = null, ?int $limit = null) Get top tags — <a href="https://www.last.fm/api/show/tag.getTopTags">https://www.last.fm/api/show/tag.getTopTags</a>
 * @method array<string, mixed> getTagTopTracks(string $tag, ?int $page = null, ?int $limit = null) Get top tracks for tag — <a href="https://www.last.fm/api/show/tag.getTopTracks">https://www.last.fm/api/show/tag.getTopTracks</a>
 * @method array<string, mixed> getTagWeeklyChartList(string $tag) Get a weekly chart list for tag — <a href="https://www.last.fm/api/show/tag.getWeeklyChartList">https://www.last.fm/api/show/tag.getWeeklyChartList</a>
 *
 * Track methods:
 * @method array<string, mixed> addTrackTags(string $artist, string $track, string $tags) Add tags to a track (Authentication required) — <a href="https://www.last.fm/api/show/track.addTags">https://www.last.fm/api/show/track.addTags</a>
 * @method array<string, mixed> getTrackCorrection(string $artist, string $track) Get correction for track — <a href="https://www.last.fm/api/show/track.getCorrection">https://www.last.fm/api/show/track.getCorrection</a>
 * @method array<string, mixed> getTrackInfo(?string $artist = null, ?string $track = null, ?string $mbid = null, ?int $autocorrect = null, ?string $username = null) Get track information — <a href="https://www.last.fm/api/show/track.getInfo">https://www.last.fm/api/show/track.getInfo</a>
 * @method array<string, mixed> getSimilarTracks(?string $artist = null, ?string $track = null, ?string $mbid = null, ?int $autocorrect = null, ?int $limit = null) Get similar tracks — <a href="https://www.last.fm/api/show/track.getSimilar">https://www.last.fm/api/show/track.getSimilar</a>
 * @method array<string, mixed> getTrackTags(string $user, ?string $artist = null, ?string $track = null, ?string $mbid = null, ?int $autocorrect = null) Get user's tags for track (Authentication required) — <a href="https://www.last.fm/api/show/track.getTags">https://www.last.fm/api/show/track.getTags</a>
 * @method array<string, mixed> getTrackTopTags(?string $artist = null, ?string $track = null, ?string $mbid = null, ?int $autocorrect = null) Get top tags for track — <a href="https://www.last.fm/api/show/track.getTopTags">https://www.last.fm/api/show/track.getTopTags</a>
 * @method array<string, mixed> loveTrack(string $artist, string $track) Love a track (Authentication required) — <a href="https://www.last.fm/api/show/track.love">https://www.last.fm/api/show/track.love</a>
 * @method array<string, mixed> removeTrackTag(string $artist, string $track, string $tag) Remove tag from the track (Authentication required) — <a href="https://www.last.fm/api/show/track.removeTag">https://www.last.fm/api/show/track.removeTag</a>
 * @method array<string, mixed> scrobbleTrack(string $artist, string $track, int $timestamp, ?string $album = null, ?int $trackNumber = null, ?string $mbid = null, ?string $albumArtist = null, ?int $duration = null, ?string $streamId = null, ?int $chosenByUser = null, ?string $context = null) Scrobble a single track (Authentication required) — <a href="https://www.last.fm/api/show/track.scrobble">https://www.last.fm/api/show/track.scrobble</a>
 * @method array<string, mixed> searchTracks(string $track, ?string $artist = null, ?int $limit = null, ?int $page = null) Search for tracks — <a href="https://www.last.fm/api/show/track.search">https://www.last.fm/api/show/track.search</a>
 * @method array<string, mixed> unloveTrack(string $artist, string $track) Unlove a track (Authentication required) — <a href="https://www.last.fm/api/show/track.unlove">https://www.last.fm/api/show/track.unlove</a>
 * @method array<string, mixed> updateNowPlaying(string $artist, string $track, ?string $album = null, ?int $trackNumber = null, ?string $mbid = null, ?int $duration = null, ?string $albumArtist = null, ?string $context = null) Update now playing track (Authentication required) — <a href="https://www.last.fm/api/show/track.updateNowPlaying">https://www.last.fm/api/show/track.updateNowPlaying</a>
 *
 * User methods:
 * @method array<string, mixed> getUserArtistTracks(string $user, string $artist, ?int $startTimestamp = null, ?int $page = null, ?int $endTimestamp = null) Get user's tracks by artist — <a href="https://www.last.fm/api/show/user.getArtistTracks">https://www.last.fm/api/show/user.getArtistTracks</a>
 * @method array<string, mixed> getUserFriends(string $user, ?int $recenttracks = null, ?int $page = null, ?int $limit = null) Get user's friends — <a href="https://www.last.fm/api/show/user.getFriends">https://www.last.fm/api/show/user.getFriends</a>
 * @method array<string, mixed> getUserInfo(?string $user = null) Get user information — <a href="https://www.last.fm/api/show/user.getInfo">https://www.last.fm/api/show/user.getInfo</a>
 * @method array<string, mixed> getUserLovedTracks(string $user, ?int $page = null, ?int $limit = null) Get user's loved tracks — <a href="https://www.last.fm/api/show/user.getLovedTracks">https://www.last.fm/api/show/user.getLovedTracks</a>
 * @method array<string, mixed> getUserPersonalTags(string $user, string $tag, string $taggingtype, ?int $page = null, ?int $limit = null) Get user's personal tags — <a href="https://www.last.fm/api/show/user.getPersonalTags">https://www.last.fm/api/show/user.getPersonalTags</a>
 * @method array<string, mixed> getUserRecentTracks(string $user, ?int $page = null, ?int $limit = null, ?int $extended = null, ?int $from = null, ?int $to = null) Get user's recent tracks — <a href="https://www.last.fm/api/show/user.getRecentTracks">https://www.last.fm/api/show/user.getRecentTracks</a>
 * @method array<string, mixed> getUserTopAlbums(string $user, ?string $period = null, ?int $page = null, ?int $limit = null) Get user's top albums — <a href="https://www.last.fm/api/show/user.getTopAlbums">https://www.last.fm/api/show/user.getTopAlbums</a>
 * @method array<string, mixed> getUserTopArtists(string $user, ?string $period = null, ?int $page = null, ?int $limit = null) Get user's top artists — <a href="https://www.last.fm/api/show/user.getTopArtists">https://www.last.fm/api/show/user.getTopArtists</a>
 * @method array<string, mixed> getUserTopTags(string $user, ?int $limit = null) Get user's top tags — <a href="https://www.last.fm/api/show/user.getTopTags">https://www.last.fm/api/show/user.getTopTags</a>
 * @method array<string, mixed> getUserTopTracks(string $user, ?string $period = null, ?int $page = null, ?int $limit = null) Get user's top tracks — <a href="https://www.last.fm/api/show/user.getTopTracks">https://www.last.fm/api/show/user.getTopTracks</a>
 * @method array<string, mixed> getUserWeeklyAlbumChart(string $user, ?int $from = null, ?int $to = null) Get a user's weekly album chart — <a href="https://www.last.fm/api/show/user.getWeeklyAlbumChart">https://www.last.fm/api/show/user.getWeeklyAlbumChart</a>
 * @method array<string, mixed> getUserWeeklyArtistChart(string $user, ?int $from = null, ?int $to = null) Get a user's weekly artist chart — <a href="https://www.last.fm/api/show/user.getWeeklyArtistChart">https://www.last.fm/api/show/user.getWeeklyArtistChart</a>
 * @method array<string, mixed> getUserWeeklyChartList(string $user) Get a user's weekly chart list — <a href="https://www.last.fm/api/show/user.getWeeklyChartList">https://www.last.fm/api/show/user.getWeeklyChartList</a>
 * @method array<string, mixed> getUserWeeklyTrackChart(string $user, ?int $from = null, ?int $to = null) Get a user's weekly track chart — <a href="https://www.last.fm/api/show/user.getWeeklyTrackChart">https://www.last.fm/api/show/user.getWeeklyTrackChart</a>
 */
final class LastFmClient
{
    // Performance constants for validation limits
    private const MAX_URI_LENGTH = 2048;
    private const MAX_PLACEHOLDERS = 50;
    private const PARAM_NAME_PATTERN = '/^[a-zA-Z][a-zA-Z0-9_]*(?:\[[0-9]+])?$/';

    private GuzzleClient $client;

    /** @var array<string, mixed> */
    private array $config;

    private ?string $apiKey = null;
    private ?string $apiSecret = null;
    private ?string $sessionKey = null;

    /**
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public function __construct(array|GuzzleClient $optionsOrClient = [])
    {
        // Load service configuration (cached for performance)
        $this->config = ConfigCache::get();

        // Create or use the provided Guzzle client
        if ($optionsOrClient instanceof GuzzleClient) {
            $this->client = $optionsOrClient;
        } else {
            $clientOptions = array_merge([
                'base_uri' => $this->config['baseUrl'],
                'headers' => [
                    'User-Agent' => $this->config['client']['options']['headers']['User-Agent']
                ]
            ], $optionsOrClient);
            $this->client = new GuzzleClient($clientOptions);
        }
    }

    /**
     * Set API credentials for authentication
     */
    public function setApiCredentials(?string $apiKey = null, ?string $apiSecret = null, ?string $sessionKey = null): void
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->sessionKey = $sessionKey;
    }

    /**
     * Magic method to call Last.fm API operations with intelligent parameter mapping
     *
     * Examples:
     * - artistGetInfo('Dua Lipa') // Maps to ['artist' => 'Dua Lipa']
     * - trackSearch('Physical', 'Dua Lipa') // Maps to ['track' => 'Physical', 'artist' => 'Dua Lipa']
     * - userGetRecentTracks('username', 50, 1) // All positional parameters
     * - trackScrobble('Dua Lipa', 'Physical', time()) // artist, track, timestamp
     *
     * @param array<int, mixed> $arguments
     * @return array<string, mixed>
     * @throws RuntimeException If API operation fails or returns invalid data
     * @throws InvalidArgumentException If method parameters are invalid
     * @throws GuzzleException If HTTP request fails
     */
    public function __call(string $method, array $arguments): array
    {
        $params = $this->buildParamsFromArguments($method, $arguments);
        return $this->callOperation($method, $params);
    }

    /**
     * Build parameters from positional/named arguments with intelligent mapping
     *
     * @param array<int|string, mixed> $arguments
     * @return array<string, mixed>
     */
    private function buildParamsFromArguments(string $method, array $arguments): array
    {
        if (empty($arguments)) {
            return [];
        }

        $operationName = $this->convertMethodToOperation($method);

        if (!isset($this->config['operations'][$operationName]['parameters'])) {
            return [];
        }

        // Handle single associative array argument (convenience feature)
        if (count($arguments) === 1 && isset($arguments[0]) && is_array($arguments[0]) && $this->isAssociativeArray($arguments[0])) {
            return $this->convertAssociativeArrayParams($arguments[0]);
        }

        $parameterNames = array_keys($this->config['operations'][$operationName]['parameters']);
        $params = [];
        $allowedCamelParams = $this->getAllowedCamelCaseParams($operationName);
        $maxParams = count($parameterNames);

        // Handle both positional AND named parameters (mixed support)
        foreach ($arguments as $key => $value) {
            if (is_string($key)) {
                // Named parameter
                if (in_array($key, $allowedCamelParams, true)) {
                    // Convert to snake_case for internal use
                    $snakeKey = $this->convertCamelToSnake($key);
                    $params[$snakeKey] = $value;
                } else {
                    // PHP-native behavior: throw Error for unknown named parameters
                    throw new \Error("Unknown named parameter \$$key");
                }
            } else {
                // Positional parameter
                if ($key < $maxParams && isset($parameterNames[$key])) {
                    $params[$parameterNames[$key]] = $value;
                }
            }
        }

        // Validate required parameters and null values
        // Check if we have any named parameters for validation
        $hasNamedParams = false;
        foreach ($arguments as $key => $value) {
            if (is_string($key)) {
                $hasNamedParams = true;
                break;
            }
        }

        if ($hasNamedParams) {
            $this->validateRequiredParameters($operationName, $params, $arguments);
        }

        return $params;
    }

    /**
     * Convert method name to operation name
     * In v2.0, we use verb-first camelCase directly, no conversion needed
     */
    private function convertMethodToOperation(string $method): string
    {
        // Last.fm uses verb-first camelCase keys directly
        // getArtistInfo -> getArtistInfo (no conversion needed)
        return $method;
    }

    /**
     * Get allowed camelCase parameters from PHPDoc for operation
     *
     * @return array<string>
     */
    private function getAllowedCamelCaseParams(string $operationName): array
    {
        // Map snake_case internal parameters to camelCase PHPDoc parameters
        if (!isset($this->config['operations'][$operationName]['parameters'])) {
            return [];
        }

        $snakeParams = array_keys($this->config['operations'][$operationName]['parameters']);
        $camelParams = [];

        foreach ($snakeParams as $snakeParam) {
            if (is_string($snakeParam)) {
                $camelParams[] = $this->convertSnakeToCamel($snakeParam);
            }
        }

        return $camelParams;
    }

    /**
     * Convert snake_case parameter names to camelCase
     * Optimized for performance with early returns
     */
    private function convertSnakeToCamel(string $snakeCase): string
    {
        // Fast path for strings without underscores
        if (!str_contains($snakeCase, '_')) {
            return $snakeCase;
        }

        return lcfirst(str_replace('_', '', ucwords($snakeCase, '_')));
    }

    /**
     * Convert camelCase parameter names to snake_case
     * Optimized for performance with early returns
     */
    private function convertCamelToSnake(string $camelCase): string
    {
        // Fast path for empty strings or already snake_case
        if ($camelCase === '' || !preg_match('/[A-Z]/', $camelCase)) {
            return $camelCase;
        }

        $result = preg_replace('/([a-z])([A-Z])/', '$1_$2', $camelCase);
        return strtolower($result ?? $camelCase);
    }

    /**
     * Validate required parameters and null values
     *
     * @param array<string, mixed> $params
     * @param array<int|string, mixed> $originalNamedArgs
     */
    private function validateRequiredParameters(string $operationName, array $params, array $originalNamedArgs): void
    {
        if (!isset($this->config['operations'][$operationName]['parameters'])) {
            return;
        }

        $parameterConfig = $this->config['operations'][$operationName]['parameters'];

        // Check for missing required parameters
        foreach ($parameterConfig as $paramName => $paramConfig) {
            if (($paramConfig['required'] ?? false) && !array_key_exists($paramName, $params)) {
                // Convert snake_case to camelCase for user-friendly error message
                $camelName = $this->convertSnakeToCamel($paramName);
                throw new \InvalidArgumentException("Required parameter $camelName is missing");
            }
        }

        // Check for required parameters with null values in named arguments
        foreach ($originalNamedArgs as $key => $value) {
            if (is_string($key) && $value === null) {
                // Convert camelCase to snake_case to check in config
                $snakeKey = $this->convertCamelToSnake($key);
                if (isset($parameterConfig[$snakeKey]) && ($parameterConfig[$snakeKey]['required'] ?? false)) {
                    throw new \InvalidArgumentException("Parameter $key is required but null was provided");
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws RuntimeException If API operation fails or returns invalid data
     * @throws InvalidArgumentException If method parameters are invalid
     * @throws GuzzleException If HTTP request fails
     */
    private function callOperation(string $method, array $params): array
    {
        $operationName = $this->convertMethodToOperation($method);

        if (!isset($this->config['operations'][$operationName])) {
            throw new RuntimeException("Unknown operation: $operationName");
        }

        $operation = $this->config['operations'][$operationName];

        // Check if the operation requires authentication and we don't have a session key
        if (($operation['requiresAuth'] ?? false) && $this->sessionKey === null) {
            throw new RuntimeException(
                "Operation '$operationName' requires authentication. " .
                "Please provide a session key using setApiCredentials() or use LastFmClientFactory::createWithSession()."
            );
        }

        $httpMethod = $operation['httpMethod'] ?? 'GET';
        $lastFmMethod = $operation['method'] ?? '';

        // Last.fm API always uses the root URL with a method parameter
        $uri = '';

        // Always add the method parameter and format for Last.fm API
        $params['method'] = $lastFmMethod;
        $params['format'] = 'json'; // Force JSON format

        // Add an API key if available
        if ($this->apiKey !== null) {
            $params['api_key'] = $this->apiKey;
        }

        // Convert array parameters to strings and remove nulls FIRST
        $convertedQueryParams = $this->convertArrayParamsToString($params);

        // Validate parameters for security and performance
        $this->validateParameters($convertedQueryParams);

        // Add an authentication signature if we have an API secret
        if ($this->apiSecret !== null) {
            // Add a session key if available for authenticated requests
            if ($this->sessionKey !== null) {
                $convertedQueryParams['sk'] = $this->sessionKey;
            }

            // Generate signature for authenticated requests (after null removal)
            $convertedQueryParams['api_sig'] = $this->generateSignature($convertedQueryParams, $this->apiSecret);
        }

        if ($httpMethod === 'POST') {
            $response = $this->client->post($uri, ['form_params' => $convertedQueryParams]);
        } else {
            $response = $this->client->get($uri, ['query' => $convertedQueryParams]);
        }

        $body = $response->getBody();
        $body->rewind(); // Ensure we're at the beginning of the stream
        $content = $body->getContents();

        if (empty($content)) {
            throw new RuntimeException('Empty response body received');
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'Invalid JSON response: ' . json_last_error_msg() . ' (Content: ' . substr($content, 0, 100) . ')'
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException('Expected array response from API');
        }

        if (isset($data['error'])) {
            throw new RuntimeException($data['message'] ?? 'API Error', $data['error']);
        }

        return $data;
    }

    /**
     * Convert parameter value to string with proper type handling
     * Optimized for common cases (strings/ints) first
     *
     * @throws InvalidArgumentException If value cannot be converted to string
     */
    private function convertParameterToString(mixed $value): string
    {
        // Fast path for most common types
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (string)$value;
        }

        return match (true) {
            is_null($value) => '',
            is_bool($value) => $value ? '1' : '0',
            is_float($value) => number_format($value, 2, '.', ''),
            $value instanceof DateTimeInterface => $value->format(DateTimeInterface::ATOM),
            is_object($value) && method_exists($value, '__toString') => (string)$value,
            is_array($value) => throw new InvalidArgumentException('Invalid parameter type: arrays not supported'),
            is_object($value) => throw new InvalidArgumentException('Invalid parameter type'),
            default => throw new InvalidArgumentException('Unsupported parameter type: ' . gettype($value))
        };
    }



    /**
     * Convert array parameters to strings more efficiently than array_map
     * Skip null values to avoid sending empty parameters to Last.fm API
     *
     * @param array<string, mixed> $params
     * @return array<string, string>
     */
    private function convertArrayParamsToString(array $params): array
    {
        if (empty($params)) {
            return [];
        }

        $converted = [];
        foreach ($params as $key => $value) {
            // Skip null values - don't send them to Last.fm API at all
            if ($value !== null) {
                $converted[$key] = $this->convertParameterToString($value);
            }
        }

        return $converted;
    }

    /**
     * Validate parameters for security and performance
     * Based on performance constants for validation limits
     *
     * @param array<string, mixed> $params
     * @throws InvalidArgumentException If parameters fail validation
     */
    private function validateParameters(array $params): void
    {
        // Check maximum number of parameters (performance limit)
        if (count($params) > self::MAX_PLACEHOLDERS) {
            throw new InvalidArgumentException(
                'Too many parameters: ' . count($params) . '. Maximum allowed: ' . self::MAX_PLACEHOLDERS
            );
        }

        // Validate parameter names to prevent injection
        foreach ($params as $key => $value) {
            // Only validate string keys (skip numeric array indices)
            if (is_string($key) && !preg_match(self::PARAM_NAME_PATTERN, $key)) {
                throw new InvalidArgumentException('Invalid parameter name: ' . $key);
            }
        }

        // Build estimated query string to check URI length limit
        $queryString = '';
        foreach ($params as $key => $value) {
            $queryString .= urlencode($key) . '=' . urlencode($this->convertParameterToString($value)) . '&';
        }

        // Check if the resulting query string would exceed URI length limits
        $estimatedUriLength = strlen($this->config['baseUrl']) + strlen($queryString);
        if ($estimatedUriLength > self::MAX_URI_LENGTH) {
            throw new InvalidArgumentException(
                'Request URI too long: ' . $estimatedUriLength . '. Maximum allowed: ' . self::MAX_URI_LENGTH
            );
        }
    }

    /**
     * Generate Last.fm API signature
     *
     * @param array<string, mixed> $params
     */
    private function generateSignature(array $params, string $secret): string
    {
        // Remove api_sig and format from params for signature generation
        unset($params['api_sig'], $params['format']);

        // Sort parameters alphabetically by key
        ksort($params);

        // Create signature string
        $sigString = '';
        foreach ($params as $key => $value) {
            $sigString .= $key . $value;
        }
        $sigString .= $secret;

        return md5($sigString);
    }





    /**
     * Check if an array is associative (has string keys)
     *
     * @param array<mixed, mixed> $array
     */
    private function isAssociativeArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        // If any key is a string, treat as associative
        foreach (array_keys($array) as $key) {
            if (is_string($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert associative array parameters to snake_case format
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function convertAssociativeArrayParams(array $params): array
    {
        $converted = [];
        foreach ($params as $key => $value) {
            // Convert camelCase to snake_case for Last.fm API
            $snakeKey = $this->convertCamelToSnake($key);

            // Handle array values that should be comma-separated (like tags)
            if (is_array($value)) {
                $converted[$snakeKey] = implode(',', $value);
            } else {
                $converted[$snakeKey] = $value;
            }
        }

        return $converted;
    }


}

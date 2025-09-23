<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Base class for unit tests with common helper methods
 */
abstract class UnitTestCase extends TestCase
{
    /**
     * Helper method to safely encode JSON for Response body
     *
     * @param array<string, mixed> $data
     */
    protected function jsonEncode(array $data): string
    {
        return json_encode($data) ?: '{}';
    }

    /**
     * Assert that the response contains required artist fields
     *
     * @param array<string, mixed> $artist
     */
    protected function assertValidArtistResponse(array $artist): void
    {
        $this->assertValidResponse($artist);
        $this->assertArrayHasKey('name', $artist);
        $this->assertIsString($artist['name']);
        $this->assertNotEmpty($artist['name']);
    }

    /**
     * Assert that response contains valid basic structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertValidResponse(array $response): void
    {
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }

    /**
     * Assert that the response contains required track fields
     *
     * @param array<string, mixed> $track
     */
    protected function assertValidTrackResponse(array $track): void
    {
        $this->assertValidResponse($track);
        $this->assertArrayHasKey('name', $track);
        $this->assertIsString($track['name']);
        $this->assertNotEmpty($track['name']);
    }

    /**
     * Assert that the response contains required album fields
     *
     * @param array<string, mixed> $album
     */
    protected function assertValidAlbumResponse(array $album): void
    {
        $this->assertValidResponse($album);
        $this->assertArrayHasKey('name', $album);
        $this->assertIsString($album['name']);
        $this->assertNotEmpty($album['name']);
    }

    /**
     * Assert that response contains required search result structure
     *
     * @param array<string, mixed> $searchResults
     */
    protected function assertValidSearchResponse(array $searchResults): void
    {
        $this->assertValidResponse($searchResults);
        $this->assertArrayHasKey('results', $searchResults);
        $this->assertIsArray($searchResults['results']);
    }

    /**
     * Assert that the response contains required user info fields
     *
     * @param array<string, mixed> $user
     */
    protected function assertValidUserResponse(array $user): void
    {
        $this->assertValidResponse($user);
        $this->assertArrayHasKey('name', $user);
        $this->assertIsString($user['name']);
        $this->assertNotEmpty($user['name']);
    }

    /**
     * Assert that response contains required session fields
     *
     * @param array<string, mixed> $session
     */
    protected function assertValidSessionResponse(array $session): void
    {
        $this->assertValidResponse($session);
        $this->assertArrayHasKey('session', $session);
        $this->assertIsArray($session['session']);
        $this->assertArrayHasKey('name', $session['session']);
        $this->assertArrayHasKey('key', $session['session']);
        $this->assertIsString($session['session']['name']);
        $this->assertIsString($session['session']['key']);
        $this->assertNotEmpty($session['session']['name']);
        $this->assertNotEmpty($session['session']['key']);
    }

    /**
     * Assert that response contains required top tracks structure
     *
     * @param array<string, mixed> $topTracks
     */
    protected function assertValidTopTracksResponse(array $topTracks): void
    {
        $this->assertValidResponse($topTracks);
        $this->assertArrayHasKey('toptracks', $topTracks);
        $this->assertIsArray($topTracks['toptracks']);
        $this->assertArrayHasKey('track', $topTracks['toptracks']);
        $this->assertIsArray($topTracks['toptracks']['track']);
    }

    /**
     * Assert that response contains required top artists structure
     *
     * @param array<string, mixed> $topArtists
     */
    protected function assertValidTopArtistsResponse(array $topArtists): void
    {
        $this->assertValidResponse($topArtists);
        $this->assertArrayHasKey('topartists', $topArtists);
        $this->assertIsArray($topArtists['topartists']);
        $this->assertArrayHasKey('artist', $topArtists['topartists']);
        $this->assertIsArray($topArtists['topartists']['artist']);
    }

    /**
     * Assert that response contains required recent tracks structure
     *
     * @param array<string, mixed> $recentTracks
     */
    protected function assertValidRecentTracksResponse(array $recentTracks): void
    {
        $this->assertValidResponse($recentTracks);
        $this->assertArrayHasKey('recenttracks', $recentTracks);
        $this->assertIsArray($recentTracks['recenttracks']);
        $this->assertArrayHasKey('track', $recentTracks['recenttracks']);
        $this->assertIsArray($recentTracks['recenttracks']['track']);
    }

    /**
     * Assert that response contains required tag info structure
     *
     * @param array<string, mixed> $tag
     */
    protected function assertValidTagResponse(array $tag): void
    {
        $this->assertValidResponse($tag);
        $this->assertArrayHasKey('name', $tag);
        $this->assertIsString($tag['name']);
        $this->assertNotEmpty($tag['name']);
    }

    /**
     * Assert that the API signature contains a valid format
     */
    protected function assertValidApiSignature(string $signature): void
    {
        $this->assertIsString($signature);
        $this->assertEquals(32, strlen($signature)); // MD5 hash is always 32 characters
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $signature);
    }

    /**
     * Assert that an API key is present in parameters
     *
     * @param array<string, mixed> $params
     */
    protected function assertHasApiKey(array $params): void
    {
        $this->assertArrayHasKey('api_key', $params);
        $this->assertIsString($params['api_key']);
        $this->assertNotEmpty($params['api_key']);
    }

    /**
     * Assert that a session key is present in parameters
     *
     * @param array<string, mixed> $params
     */
    protected function assertHasSessionKey(array $params): void
    {
        $this->assertArrayHasKey('sk', $params);
        $this->assertIsString($params['sk']);
        $this->assertNotEmpty($params['sk']);
    }

    /**
     * Assert that response contains Last.fm API error structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertValidApiErrorResponse(array $response): void
    {
        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertIsInt($response['error']);
        $this->assertIsString($response['message']);
        $this->assertNotEmpty($response['message']);
    }

    /**
     * Assert that the response contains pagination info
     *
     * @param array<string, mixed> $response
     */
    protected function assertValidPaginationResponse(array $response): void
    {
        $this->assertArrayHasKey('@attr', $response);
        $this->assertIsArray($response['@attr']);
        $this->assertArrayHasKey('page', $response['@attr']);
        $this->assertArrayHasKey('total', $response['@attr']);
        $this->assertArrayHasKey('totalPages', $response['@attr']);
        $this->assertArrayHasKey('perPage', $response['@attr']);
    }

    /**
     * Assert that track has required playcount information
     *
     * @param array<string, mixed> $track
     */
    protected function assertTrackHasPlaycount(array $track): void
    {
        $this->assertArrayHasKey('playcount', $track);
        $this->assertTrue(
            is_string($track['playcount']) || is_int($track['playcount']),
            'Playcount should be string or integer'
        );
    }

    /**
     * Assert that the artist has required listener information
     *
     * @param array<string, mixed> $artist
     */
    protected function assertArtistHasListeners(array $artist): void
    {
        $this->assertArrayHasKey('listeners', $artist);
        $this->assertTrue(
            is_string($artist['listeners']) || is_int($artist['listeners']),
            'Listeners should be string or integer'
        );
    }

    /**
     * Assert that timestamp is valid Unix timestamp
     *
     * @param mixed $timestamp
     */
    protected function assertValidTimestamp(mixed $timestamp): void
    {
        $this->assertTrue(
            is_int($timestamp) || (is_string($timestamp) && ctype_digit($timestamp)),
            'Timestamp should be integer or numeric string'
        );

        if (is_string($timestamp)) {
            $timestamp = (int) $timestamp;
        }

        $this->assertGreaterThan(0, $timestamp);
        $this->assertLessThanOrEqual(time() + 86400, $timestamp); // Allow up to 1 day in the future
    }

    /**
     * Assert that MBID (MusicBrainz Identifier) format is valid
     */
    protected function assertValidMbid(string $mbid): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $mbid,
            'MBID should be in UUID format'
        );
    }

    /**
     * Assert that URL is a valid Last.fm image URL
     */
    protected function assertValidLastFmImageUrl(string $url): void
    {
        $this->assertIsString($url);
        $this->assertTrue(
            str_contains($url, 'lastfm') || str_contains($url, 'last.fm') || empty($url),
            'Image URL should be from Last.fm domain or empty'
        );

        if (!empty($url)) {
            $this->assertMatchesRegularExpression('/^https?:\/\//', $url, 'Image URL should start with http or https');
        }
    }

    /**
     * Assert that response contains valid chart structure
     *
     * @param array<string, mixed> $chartData
     */
    protected function assertValidChartResponse(array $chartData): void
    {
        $this->assertValidResponse($chartData);

        // Chart responses can have various structures but should contain ranking info
        $hasRankingData = array_key_exists('artists', $chartData) ||
                         array_key_exists('tracks', $chartData) ||
                         array_key_exists('albums', $chartData) ||
                         array_key_exists('tags', $chartData);

        $this->assertTrue($hasRankingData, 'Chart response should contain ranking data');
    }

    /**
     * Assert that the scrobble response is valid
     *
     * @param array<string, mixed> $response
     */
    protected function assertValidScrobbleResponse(array $response): void
    {
        $this->assertValidResponse($response);
        $this->assertArrayHasKey('scrobbles', $response);
        $this->assertIsArray($response['scrobbles']);
        $this->assertArrayHasKey('@attr', $response['scrobbles']);
        $this->assertArrayHasKey('accepted', $response['scrobbles']['@attr']);
        $this->assertArrayHasKey('ignored', $response['scrobbles']['@attr']);
    }

    /**
     * Assert that the love / unlove response is valid
     *
     * @param array<string, mixed> $response
     */
    protected function assertValidLoveResponse(array $response): void
    {
        $this->assertValidResponse($response);
        $this->assertArrayHasKey('lfm', $response);
        $this->assertArrayHasKey('status', $response['lfm']);
        $this->assertEquals('ok', $response['lfm']['status']);
    }

    /**
     * Create a mock Last.fm artist response
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function createMockArtistResponse(array $overrides = []): array
    {
        $default = [
            'artist' => [
                'name' => 'Test Artist',
                'mbid' => '12345678-1234-1234-1234-123456789abc',
                'url' => 'https://www.last.fm/music/Test+Artist',
                'image' => [
                    [
                        '#text' => 'https://lastfm.freetls.fastly.net/i/u/34s/artist.png',
                        'size' => 'small'
                    ]
                ],
                'streamable' => '0',
                'ontour' => '0',
                'playcount' => '1000000',
                'listeners' => '50000',
                'bio' => [
                    'published' => 'Mon, 01 Jan 2024 00:00:00 +0000',
                    'summary' => 'Test Artist biography summary',
                    'content' => 'Full biography content here'
                ]
            ]
        ];

        // Use array_replace_recursive to properly override nested values
        return array_replace_recursive($default, $overrides);
    }

    /**
     * Create a mock Last.fm track response
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function createMockTrackResponse(array $overrides = []): array
    {
        $default = [
            'track' => [
                'name' => 'Test Track',
                'mbid' => '12345678-1234-1234-1234-123456789abc',
                'url' => 'https://www.last.fm/music/Test+Artist/_/Test+Track',
                'duration' => '240000',
                'streamable' => [
                    '#text' => '0',
                    'fulltrack' => '0'
                ],
                'listeners' => '25000',
                'playcount' => '500000',
                'artist' => [
                    'name' => 'Test Artist',
                    'mbid' => '87654321-4321-4321-4321-cba987654321',
                    'url' => 'https://www.last.fm/music/Test+Artist'
                ],
                'album' => [
                    'artist' => 'Test Artist',
                    'title' => 'Test Album',
                    'mbid' => '11111111-2222-3333-4444-555555555555',
                    'url' => 'https://www.last.fm/music/Test+Artist/Test+Album'
                ]
            ]
        ];

        // Use array_replace_recursive to properly override nested values
        return array_replace_recursive($default, $overrides);
    }

    /**
     * Create a mock Last.fm search response
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function createMockSearchResponse(array $overrides = []): array
    {
        $default = [
            'results' => [
                'opensearch:Query' => [
                    '#text' => '',
                    'role' => 'request',
                    'searchTerms' => 'test',
                    'startPage' => '1'
                ],
                'opensearch:totalResults' => '100',
                'opensearch:startIndex' => '0',
                'opensearch:itemsPerPage' => '30',
                'artistmatches' => [
                    'artist' => [
                        [
                            'name' => 'Test Artist 1',
                            'listeners' => '50000',
                            'mbid' => '11111111-1111-1111-1111-111111111111',
                            'url' => 'https://www.last.fm/music/Test+Artist+1'
                        ],
                        [
                            'name' => 'Test Artist 2',
                            'listeners' => '25000',
                            'mbid' => '22222222-2222-2222-2222-222222222222',
                            'url' => 'https://www.last.fm/music/Test+Artist+2'
                        ]
                    ]
                ],
                '@attr' => [
                    'for' => 'test'
                ]
            ]
        ];

        return array_replace_recursive($default, $overrides);
    }

    /**
     * Create a mock authentication token response
     *
     * @param string $token
     * @return array<string, mixed>
     */
    protected function createMockTokenResponse(string $token = 'test_token_123'): array
    {
        return [
            'token' => $token
        ];
    }

    /**
     * Create a mock session response
     *
     * @param string $username
     * @param string $sessionKey
     * @return array<string, mixed>
     */
    protected function createMockSessionResponse(
        string $username = 'test_user',
        string $sessionKey = 'test_session_key_123'
    ): array {
        return [
            'session' => [
                'name' => $username,
                'key' => $sessionKey,
                'subscriber' => 0
            ]
        ];
    }
}

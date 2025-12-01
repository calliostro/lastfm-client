<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Integration;

use Calliostro\LastFm\LastFmClient;
use Calliostro\LastFm\LastFmClientFactory;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Base class for integration tests with shared utilities and configuration
 */
abstract class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Add delay between tests to respect API rate limits
        // Last.fm API typically allows 5 requests per second per IP
        sleep(5);

        // Ensure required environment variables are available for tests
        if (!$this->hasRequiredCredentials()) {
            $this->markTestSkipped('Last.fm API credentials not available. Set LASTFM_API_KEY, LASTFM_SECRET, and LASTFM_SESSION_KEY environment variables.');
        }
    }

    /**
     * Check if the required API credentials are available in environment variables
     */
    protected function hasRequiredCredentials(): bool
    {
        return !empty(getenv('LASTFM_API_KEY')) && !empty(getenv('LASTFM_SECRET'));
    }

    /**
     * Check if a session key is available for authenticated tests
     */
    protected function hasSessionKey(): bool
    {
        return !empty(getenv('LASTFM_SESSION_KEY'));
    }

    /**
     * Get API key from environment variables
     */
    protected function getApiKey(): string
    {
        return getenv('LASTFM_API_KEY') ?: '';
    }

    /**
     * Get API secret from environment variables
     */
    protected function getApiSecret(): string
    {
        return getenv('LASTFM_SECRET') ?: '';
    }

    /**
     * Get session key from environment variables
     */
    protected function getSessionKey(): string
    {
        return getenv('LASTFM_SESSION_KEY') ?: '';
    }

    /**
     * Create a basic Last.fm client with API key authentication
     */
    protected function createClient(): LastFmClient
    {
        return LastFmClientFactory::createWithApiKey($this->getApiKey(), $this->getApiSecret());
    }

    /**
     * Create a Last.fm client with session authentication for protected operations
     */
    protected function createAuthenticatedClient(): LastFmClient
    {
        if (!$this->hasSessionKey()) {
            $this->markTestSkipped('Session key not available for authenticated tests. Set LASTFM_SESSION_KEY environment variable.');
        }

        return LastFmClientFactory::createWithSession(
            $this->getApiKey(),
            $this->getApiSecret(),
            $this->getSessionKey()
        );
    }

    /**
     * Create a client with custom Guzzle options for specific test needs
     *
     * @param array<string, mixed> $options
     */
    protected function createCustomClient(array $options = []): LastFmClient
    {
        $guzzleClient = new Client(array_merge([
            'timeout' => 30,
            'connect_timeout' => 10,
        ], $options));

        return LastFmClientFactory::createWithApiKey(
            $this->getApiKey(),
            $this->getApiSecret(),
            $guzzleClient
        );
    }

    /**
     * Skip test if session authentication is not available
     */
    protected function skipIfNoSession(): void
    {
        if (!$this->hasSessionKey()) {
            $this->markTestSkipped('Session key required for this test. Set LASTFM_SESSION_KEY environment variable.');
        }
    }

    /**
     * Assert that a Last.fm API response has the expected structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertLastFmResponseStructure(array $response, string $expectedKey): void
    {
        $this->assertIsArray($response);
        $this->assertArrayHasKey($expectedKey, $response);
        $this->assertIsArray($response[$expectedKey]);
    }

    /**
     * Assert that an artist response has the expected structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertArtistResponse(array $response): void
    {
        $this->assertLastFmResponseStructure($response, 'artist');
        $this->assertArrayHasKey('name', $response['artist']);
        $this->assertIsString($response['artist']['name']);
    }

    /**
     * Assert that a track response has the expected structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertTrackResponse(array $response): void
    {
        $this->assertLastFmResponseStructure($response, 'track');
        $this->assertArrayHasKey('name', $response['track']);
        $this->assertArrayHasKey('artist', $response['track']);
        $this->assertIsString($response['track']['name']);
    }

    /**
     * Assert that an album response has the expected structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertAlbumResponse(array $response): void
    {
        $this->assertLastFmResponseStructure($response, 'album');
        $this->assertArrayHasKey('name', $response['album']);
        $this->assertArrayHasKey('artist', $response['album']);
        $this->assertIsString($response['album']['name']);
    }

    /**
     * Assert that a search response has the expected structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertSearchResponse(array $response): void
    {
        $this->assertLastFmResponseStructure($response, 'results');
        $this->assertArrayHasKey('opensearch:Query', $response['results']);
        $this->assertArrayHasKey('opensearch:totalResults', $response['results']);
    }

    /**
     * Get a well-known artist for testing purposes
     */
    protected function getTestArtist(): string
    {
        return 'Taylor Swift';
    }

    /**
     * Get a well-known track for testing purposes
     */
    protected function getTestTrack(): string
    {
        return 'Anti-Hero';
    }

    /**
     * Get a well-known album for testing purposes
     */
    protected function getTestAlbum(): string
    {
        return 'Midnights';
    }

    /**
     * Get a well-known user for testing purposes
     */
    protected function getTestUser(): string
    {
        return 'lastfm'; // Official Last.fm account - stable for testing
    }

    /**
     * Get a well-known tag for testing purposes
     */
    protected function getTestTag(): string
    {
        return 'rock';
    }

    /**
     * Get a test timestamp for scrobbling (24 hours ago)
     */
    protected function getTestTimestamp(): int
    {
        return time() - 86400; // 24 hours ago
    }
}

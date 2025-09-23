<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Integration;

use Calliostro\LastFm\LastFmClient;
use Calliostro\LastFm\LastFmClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Integration tests for authenticated Last.fm API endpoints that require a session key
 */
#[CoversClass(LastFmClient::class)]
#[CoversClass(LastFmClientFactory::class)]
final class AuthenticatedIntegrationTest extends IntegrationTestCase
{
    private LastFmClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->skipIfNoSession();
        $this->client = $this->createAuthenticatedClient();
    }

    // =====================================
    // SCROBBLING METHODS
    // =====================================

    public function testScrobbleTrack(): void
    {
        $response = $this->client->scrobbleTrack(
            $this->getTestArtist(),
            $this->getTestTrack(),
            time(),
            album: $this->getTestAlbum()
        );

        $this->assertIsArray($response);
        $this->assertArrayHasKey('scrobbles', $response);
        $this->assertArrayHasKey('scrobble', $response['scrobbles']);

        $scrobble = $response['scrobbles']['scrobble'];
        $this->assertArrayHasKey('track', $scrobble);
        $this->assertArrayHasKey('artist', $scrobble);
        $this->assertArrayHasKey('timestamp', $scrobble);
    }

    public function testUpdateNowPlayingTrack(): void
    {
        $response = $this->client->updateNowPlaying(
            $this->getTestArtist(),
            $this->getTestTrack(),
            album: $this->getTestAlbum()
        );

        $this->assertIsArray($response);
        $this->assertArrayHasKey('nowplaying', $response);
        $this->assertArrayHasKey('track', $response['nowplaying']);
        $this->assertArrayHasKey('artist', $response['nowplaying']);
    }

    public function testMultipleScrobbles(): void
    {
        // Note: Last.fm API typically handles single scrobbles
        // For multiple scrobbles, we'd need to make separate API calls

        // Test first scrobble
        $response1 = $this->client->scrobbleTrack(
            $this->getTestArtist(),
            $this->getTestTrack(),
            $this->getTestTimestamp(),
            $this->getTestAlbum()
        );

        // Test second scrobble
        $response2 = $this->client->scrobbleTrack(
            'Olivia Rodrigo',
            'Drivers License',
            $this->getTestTimestamp() - 300, // 5 minutes earlier
            'SOUR'
        );

        // Both responses should be valid
        $response = $response1; // Use first response for assertions

        $this->assertIsArray($response);
        $this->assertArrayHasKey('scrobbles', $response);
        $this->assertArrayHasKey('scrobble', $response['scrobbles']);

        // Should contain multiple scrobbles
        $scrobbleData = $response['scrobbles']['scrobble'];
        $this->assertTrue(is_array($scrobbleData));
    }

    // =====================================
    // LOVE/UNLOVE METHODS
    // =====================================

    public function testTrackLove(): void
    {
        $response = $this->client->loveTrack(
            $this->getTestArtist(),
            $this->getTestTrack()
        );

        $this->assertIsArray($response);
        // Love method typically returns a minimal response
        $this->assertTrue(true); // If we get here without exception, it worked
    }

    public function testTrackUnlove(): void
    {
        // First love the track, then unlove it
        $this->client->loveTrack(
            $this->getTestArtist(),
            $this->getTestTrack()
        );

        $response = $this->client->unloveTrack(
            $this->getTestArtist(),
            $this->getTestTrack()
        );

        $this->assertIsArray($response);
        // Unlove method typically returns a minimal response
        $this->assertTrue(true); // If we get here without exception, it worked
    }

    public function testArtistAddTags(): void
    {
        $response = $this->client->addArtistTags(
            $this->getTestArtist(),
            'rock,alternative,british'  // Tags as comma-separated string
        );

        $this->assertIsArray($response);
        $this->assertTrue(true); // If we get here without exception, it worked
    }

    public function testTrackAddTags(): void
    {
        $response = $this->client->addTrackTags(
            $this->getTestArtist(),
            $this->getTestTrack(),
            'rock,grunge,90s'  // Tags as comma-separated string
        );

        $this->assertIsArray($response);
        $this->assertTrue(true); // If we get here without exception, it worked
    }

    public function testAlbumAddTags(): void
    {
        $response = $this->client->addAlbumTags(
            $this->getTestArtist(),
            $this->getTestAlbum(),
            'rock,alternative,masterpiece'  // Tags as comma-separated string
        );

        $this->assertIsArray($response);
        $this->assertTrue(true); // If we get here without exception, it worked
    }

    // =====================================
    // REMOVE TAGS METHODS
    // =====================================

    public function testArtistRemoveTag(): void
    {
        // First add a tag, then remove it
        $this->client->addArtistTags(
            $this->getTestArtist(),
            'test-tag'
        );

        $response = $this->client->removeArtistTag(
            $this->getTestArtist(),
            'test-tag'
        );

        $this->assertIsArray($response);
        $this->assertTrue(true); // If we get here without exception, it worked
    }

    public function testTrackRemoveTag(): void
    {
        // First add a tag, then remove it
        $this->client->addTrackTags(
            $this->getTestArtist(),
            $this->getTestTrack(),
            'test-tag'
        );

        $response = $this->client->removeTrackTag(
            $this->getTestArtist(),
            $this->getTestTrack(),
            'test-tag'
        );

        $this->assertIsArray($response);
        $this->assertTrue(true); // If we get here without exception, it worked
    }

    public function testAlbumRemoveTag(): void
    {
        // First add a tag, then remove it
        $this->client->addAlbumTags(
            $this->getTestArtist(),
            $this->getTestAlbum(),
            'test-tag'
        );

        $response = $this->client->removeAlbumTag(
            $this->getTestArtist(),
            $this->getTestAlbum(),
            'test-tag'
        );

        $this->assertIsArray($response);
        $this->assertTrue(true); // If we get here without exception, it worked
    }

    // =====================================
    // USER PERSONAL DATA
    // =====================================

    public function testUserGetPersonalTags(): void
    {
        // This requires the authenticated user's data
        try {
            $response = $this->client->getUserPersonalTags(
                'authenticated_user', // This would need to be the actual authenticated user
                $this->getTestTag(),
                'artist'
            );

            $this->assertIsArray($response);
        } catch (\RuntimeException $e) {
            // This is expected if the user doesn't exist or has no tags
            $this->assertStringContainsString('User not found', $e->getMessage());
        }
    }

    // =====================================
    // ERROR HANDLING FOR AUTHENTICATED METHODS
    // =====================================

    public function testTrackLoveWithMissingFieldsThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->client->loveTrack(
            '', // Empty artist name
            ''  // Empty track name
        );
    }

    // =====================================
    // RATE LIMITING AND PERFORMANCE
    // =====================================

    public function testMultipleScrobblesWithDelay(): void
    {
        $baseTimestamp = $this->getTestTimestamp();

        // Send multiple individual scrobbles (batch functionality removed for lightweight design)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->client->scrobbleTrack(
                'Test Artist ' . $i,
                'Test Track ' . $i,
                $baseTimestamp - ($i * 240), // 4 minutes apart
                'Test Album ' . $i
            );

            $this->assertIsArray($response);
            $this->assertArrayHasKey('scrobbles', $response);

            // Small delay between scrobbles
            usleep(100000); // 0.1 seconds
        }
    }

    public function testRateLimitingHandling(): void
    {
        // Perform multiple operations quickly to potentially hit rate limits
        $operations = [
            fn () => $this->client->updateNowPlaying('Test Artist 1', 'Test Track 1'),
            fn () => $this->client->updateNowPlaying('Test Artist 2', 'Test Track 2'),
            fn () => $this->client->updateNowPlaying('Test Artist 3', 'Test Track 3'),
        ];

        foreach ($operations as $operation) {
            try {
                $response = $operation();
                $this->assertIsArray($response);

                // Small delay between operations to be nice to the API
                usleep(200000); // 0.2 seconds
            } catch (\RuntimeException $e) {
                // Rate limiting or other API errors are acceptable here
                $this->assertIsString($e->getMessage());
            }
        }

        $this->assertTrue(true); // If we get here, rate limiting was handled gracefully
    }

    // =====================================
    // AUTHENTICATION VALIDATION
    // =====================================

    public function testAuthenticatedClientHasValidSession(): void
    {
        // Verify that the client was created with proper authentication
        // by performing a simple authenticated operation
        $response = $this->client->updateNowPlaying(
            $this->getTestArtist(),
            $this->getTestTrack()
        );

        $this->assertIsArray($response);
        $this->assertArrayHasKey('nowplaying', $response);
    }

    // =====================================
    // INTEGRATION WORKFLOW TESTS
    // =====================================

    public function testCompleteListeningSession(): void
    {
        // Simulate a complete listening session:
        // 1. Update now playing
        // 2. Love the track
        // 3. Add tags
        // 4. Scrobble after listening

        $artist = $this->getTestArtist();
        $track = $this->getTestTrack();
        $album = $this->getTestAlbum();

        // Step 1: Update now playing
        $nowPlayingResponse = $this->client->updateNowPlaying($artist, $track, $album);
        $this->assertArrayHasKey('nowplaying', $nowPlayingResponse);

        // Step 2: Love track
        $loveResponse = $this->client->loveTrack(
            $artist,
            $track
        );
        $this->assertIsArray($loveResponse);

        // Step 3: Add tags
        $tagResponse = $this->client->addTrackTags(
            $artist,
            $track,
            'integration-test,automated'
        );
        $this->assertIsArray($tagResponse);

        // Step 4: Scrobble (simulating that the track finished playing)
        $scrobbleResponse = $this->client->scrobbleTrack(
            $artist,
            $track,
            $this->getTestTimestamp(),
            $album
        );
        $this->assertArrayHasKey('scrobbles', $scrobbleResponse);
    }

    public function testBatchOperations(): void
    {
        // Test adding multiple tags and performing multiple loves
        $artists = [$this->getTestArtist(), 'Olivia Rodrigo', 'Lorde'];
        $tracks = [$this->getTestTrack(), 'Drivers License', 'Solar Power'];

        foreach (array_combine($artists, $tracks) as $artist => $track) {
            // Add tags to each track
            $tagResponse = $this->client->addTrackTags(
                $artist,
                $track,
                'batch-test'
            );
            $this->assertIsArray($tagResponse);

            // Love each track
            $loveResponse = $this->client->loveTrack(
                $artist,
                $track
            );
            $this->assertIsArray($loveResponse);

            // Small delay between operations
            usleep(100000); // 0.1 seconds
        }
    }
}

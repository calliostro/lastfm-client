<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use Calliostro\LastFm\LastFmClient;
use Calliostro\LastFm\LastFmClientFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Test authentication error handling for methods that require session keys
 */
final class AuthenticationErrorTest extends TestCase
{
    private LastFmClient $client;

    protected function setUp(): void
    {
        // Create client without session key (only API key + secret)
        $this->client = LastFmClientFactory::createWithApiKey('test_key', 'test_secret');
    }

    public function testUpdateNowPlayingRequiresAuthentication(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Operation 'updateNowPlaying' requires authentication. " .
            "Please provide a session key using setApiCredentials() or use LastFmClientFactory::createWithSession()."
        );

        $this->client->updateNowPlaying('Artist', 'Track');
    }

    public function testScrobbleTrackRequiresAuthentication(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Operation 'scrobbleTrack' requires authentication. " .
            "Please provide a session key using setApiCredentials() or use LastFmClientFactory::createWithSession()."
        );

        $this->client->scrobbleTrack('Artist', 'Track', time());
    }

    public function testLoveTrackRequiresAuthentication(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Operation 'loveTrack' requires authentication. " .
            "Please provide a session key using setApiCredentials() or use LastFmClientFactory::createWithSession()."
        );

        $this->client->loveTrack('Artist', 'Track');
    }

    public function testUnloveTrackRequiresAuthentication(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Operation 'unloveTrack' requires authentication. " .
            "Please provide a session key using setApiCredentials() or use LastFmClientFactory::createWithSession()."
        );

        $this->client->unloveTrack('Artist', 'Track');
    }

    public function testAddTrackTagsRequiresAuthentication(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Operation 'addTrackTags' requires authentication. " .
            "Please provide a session key using setApiCredentials() or use LastFmClientFactory::createWithSession()."
        );

        $this->client->addTrackTags('Artist', 'Track', 'tag1,tag2');
    }

    public function testRemoveTrackTagRequiresAuthentication(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Operation 'removeTrackTag' requires authentication. " .
            "Please provide a session key using setApiCredentials() or use LastFmClientFactory::createWithSession()."
        );

        $this->client->removeTrackTag('Artist', 'Track', 'tag');
    }

    public function testPublicMethodsDoNotRequireAuthentication(): void
    {
        // This should not throw an authentication error (though it may fail for other reasons)
        try {
            $this->client->getArtistInfo('Dua Lipa');
        } catch (RuntimeException $e) {
            // Make sure it's not the authentication error
            $this->assertStringNotContainsString('requires authentication', $e->getMessage());
        }
    }
}

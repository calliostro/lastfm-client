<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use Calliostro\LastFm\LastFmClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test for the null parameter handling fix that resolves Error Code 6
 * Previously, null parameters were converted to empty strings and sent to Last.fm,
 * causing "Invalid parameters" errors. Now they are properly omitted.
 */
#[CoversClass(LastFmClient::class)]
final class NullParameterHandlingTest extends TestCase
{
    private LastFmClient $client;
    /** @var ReflectionClass<LastFmClient> */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->client = new LastFmClient();
        $this->client->setApiCredentials('test_api_key', 'test_api_secret', 'test_session_key');
        $this->reflection = new ReflectionClass($this->client);
    }

    public function testNullParametersAreOmittedFromRequest(): void
    {
        // Access the private method that converts parameters
        $convertMethod = $this->reflection->getMethod('convertArrayParamsToString');
        $convertMethod->setAccessible(true);

        // Test parameters with null values (the original problem case)
        $params = [
            'artist' => 'Test Artist',
            'track' => 'Test Track',
            'album' => 'Test Album',
            'trackNumber' => null,  // This was causing the problem
            'mbid' => null,         // This was causing the problem
            'duration' => 240,
            'albumArtist' => null,  // This was causing the problem
        ];

        $convertedParams = $convertMethod->invoke($this->client, $params);

        // Verify that null parameters are completely omitted
        $this->assertArrayNotHasKey('trackNumber', $convertedParams);
        $this->assertArrayNotHasKey('track_number', $convertedParams);
        $this->assertArrayNotHasKey('mbid', $convertedParams);
        $this->assertArrayNotHasKey('albumArtist', $convertedParams);
        $this->assertArrayNotHasKey('album_artist', $convertedParams);

        // Verify that non-null parameters are still included
        $this->assertArrayHasKey('artist', $convertedParams);
        $this->assertArrayHasKey('track', $convertedParams);
        $this->assertArrayHasKey('album', $convertedParams);
        $this->assertArrayHasKey('duration', $convertedParams);

        // Verify values are correct
        $this->assertEquals('Test Artist', $convertedParams['artist']);
        $this->assertEquals('Test Track', $convertedParams['track']);
        $this->assertEquals('Test Album', $convertedParams['album']);
        $this->assertEquals('240', $convertedParams['duration']);
    }

    public function testEmptyStringParametersAreStillIncluded(): void
    {
        // Access the private method that converts parameters
        $convertMethod = $this->reflection->getMethod('convertArrayParamsToString');
        $convertMethod->setAccessible(true);

        // Test parameters with empty string values (should be kept)
        $params = [
            'artist' => 'Test Artist',
            'track' => 'Test Track',
            'album' => '',  // Empty string should be kept, not omitted
        ];

        $convertedParams = $convertMethod->invoke($this->client, $params);

        // Verify that empty strings are kept (they're valid for Last.fm)
        $this->assertArrayHasKey('album', $convertedParams);
        $this->assertEquals('', $convertedParams['album']);
    }

    public function testParameterBuildingWithNullValues(): void
    {
        // Test the complete parameter building process
        $buildParamsMethod = $this->reflection->getMethod('buildParamsFromArguments');
        $buildParamsMethod->setAccessible(true);

        // Simulate the exact call that was causing Error Code 6
        $arguments = [
            'artist' => 'Test Artist',
            'track' => 'Test Track',
            'album' => 'Test Album',
            'trackNumber' => null,
            'mbid' => null,
            'duration' => 240,
        ];

        $builtParams = $buildParamsMethod->invoke($this->client, 'updateNowPlaying', $arguments);

        // The buildParamsFromArguments should still include nulls (they get filtered later)
        $this->assertArrayHasKey('track_number', $builtParams);
        $this->assertNull($builtParams['track_number']);
        $this->assertArrayHasKey('mbid', $builtParams);
        $this->assertNull($builtParams['mbid']);

        // But when converted, nulls should be omitted
        $convertMethod = $this->reflection->getMethod('convertArrayParamsToString');
        $convertMethod->setAccessible(true);
        $convertedParams = $convertMethod->invoke($this->client, $builtParams);

        $this->assertArrayNotHasKey('track_number', $convertedParams);
        $this->assertArrayNotHasKey('mbid', $convertedParams);
    }
}

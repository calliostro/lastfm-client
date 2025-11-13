<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Integration;

use Calliostro\LastFm\LastFmClient;
use Calliostro\LastFm\LastFmClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Integration tests for public Last.fm API endpoints that don't require authentication
 */
#[CoversClass(LastFmClient::class)]
final class PublicApiIntegrationTest extends IntegrationTestCase
{
    private LastFmClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
    }

    // =====================================
    // ARTIST METHODS
    // =====================================

    public function testGetArtistInfo(): void
    {
        $response = $this->client->getArtistInfo($this->getTestArtist());

        $this->assertArtistResponse($response);
        $this->assertEquals($this->getTestArtist(), $response['artist']['name']);
        $this->assertArrayHasKey('bio', $response['artist']);
        $this->assertArrayHasKey('stats', $response['artist']);
    }

    public function testGetArtistInfoWithMbid(): void
    {
        // Using Taylor Swift's MusicBrainz ID
        $mbid = '20244d07-534f-4eff-b4d4-930878889970';
        $response = $this->client->getArtistInfo(mbid: $mbid);

        $this->assertArtistResponse($response);
        $this->assertEquals($this->getTestArtist(), $response['artist']['name']);
    }

    public function testGetArtistTopTracks(): void
    {
        $response = $this->client->getArtistTopTracks($this->getTestArtist());

        $this->assertLastFmResponseStructure($response, 'toptracks');
        $this->assertArrayHasKey('track', $response['toptracks']);
        $this->assertIsArray($response['toptracks']['track']);
        $this->assertNotEmpty($response['toptracks']['track']);

        // Check first track structure
        $firstTrack = $response['toptracks']['track'][0];
        $this->assertArrayHasKey('name', $firstTrack);
        $this->assertArrayHasKey('playcount', $firstTrack);
        $this->assertArrayHasKey('listeners', $firstTrack);
    }

    public function testArtistGetTopAlbums(): void
    {
        $response = $this->client->getArtistTopAlbums($this->getTestArtist());

        $this->assertLastFmResponseStructure($response, 'topalbums');
        $this->assertArrayHasKey('album', $response['topalbums']);
        $this->assertIsArray($response['topalbums']['album']);
        $this->assertNotEmpty($response['topalbums']['album']);

        // Check first album structure
        $firstAlbum = $response['topalbums']['album'][0];
        $this->assertArrayHasKey('name', $firstAlbum);
        $this->assertArrayHasKey('playcount', $firstAlbum);
    }

    public function testArtistGetSimilar(): void
    {
        $response = $this->client->getSimilarArtists($this->getTestArtist());

        $this->assertLastFmResponseStructure($response, 'similarartists');
        $this->assertArrayHasKey('artist', $response['similarartists']);
        $this->assertIsArray($response['similarartists']['artist']);
    }

    public function testArtistSearch(): void
    {
        $response = $this->client->searchArtists($this->getTestArtist());

        $this->assertSearchResponse($response);
        $this->assertArrayHasKey('artistmatches', $response['results']);
        $this->assertArrayHasKey('artist', $response['results']['artistmatches']);
        $this->assertIsArray($response['results']['artistmatches']['artist']);
    }

    // =====================================
    // TRACK METHODS
    // =====================================

    public function testTrackGetInfo(): void
    {
        $response = $this->client->getTrackInfo($this->getTestArtist(), $this->getTestTrack());

        $this->assertTrackResponse($response);
        $this->assertEquals($this->getTestTrack(), $response['track']['name']);
        $this->assertEquals($this->getTestArtist(), $response['track']['artist']['name']);
        $this->assertArrayHasKey('album', $response['track']);
    }

    public function testTrackGetInfoWithMbid(): void
    {
        // Using Anti-Hero's MusicBrainz ID
        $mbid = '6bb9e766-97a5-440f-9173-99515493d2d4';
        $response = $this->client->getTrackInfo(mbid: $mbid);

        $this->assertTrackResponse($response);
        $this->assertEquals($this->getTestTrack(), $response['track']['name']);
        $this->assertEquals($this->getTestArtist(), $response['track']['artist']['name']);
    }

    public function testTrackGetSimilar(): void
    {
        $response = $this->client->getSimilarTracks($this->getTestArtist(), $this->getTestTrack());

        $this->assertLastFmResponseStructure($response, 'similartracks');
        $this->assertArrayHasKey('track', $response['similartracks']);
        $this->assertIsArray($response['similartracks']['track']);
    }

    public function testTrackSearch(): void
    {
        $response = $this->client->searchTracks($this->getTestTrack());

        $this->assertSearchResponse($response);
        $this->assertArrayHasKey('trackmatches', $response['results']);
        $this->assertArrayHasKey('track', $response['results']['trackmatches']);
        $this->assertIsArray($response['results']['trackmatches']['track']);
    }

    public function testTrackGetTopTags(): void
    {
        $response = $this->client->getTrackTopTags($this->getTestArtist(), $this->getTestTrack());

        $this->assertLastFmResponseStructure($response, 'toptags');
        $this->assertArrayHasKey('tag', $response['toptags']);
        $this->assertIsArray($response['toptags']['tag']);
    }

    // =====================================
    // ALBUM METHODS
    // =====================================

    public function testAlbumGetInfo(): void
    {
        $response = $this->client->getAlbumInfo($this->getTestArtist(), $this->getTestAlbum());

        $this->assertAlbumResponse($response);
        $this->assertEquals($this->getTestAlbum(), $response['album']['name']);
        $this->assertEquals($this->getTestArtist(), $response['album']['artist']);
        $this->assertArrayHasKey('tracks', $response['album']);
    }

    public function testAlbumGetInfoWithMbid(): void
    {
        // Using Midnights' MusicBrainz ID
        $mbid = '4101202a-1aca-4756-8c07-752dca888722';
        $response = $this->client->getAlbumInfo(mbid: $mbid);

        $this->assertAlbumResponse($response);
        $this->assertEquals($this->getTestAlbum(), $response['album']['name']);
        $this->assertEquals($this->getTestArtist(), $response['album']['artist']);
    }

    public function testAlbumSearch(): void
    {
        $response = $this->client->searchAlbums($this->getTestAlbum());

        $this->assertSearchResponse($response);
        $this->assertArrayHasKey('albummatches', $response['results']);
        $this->assertArrayHasKey('album', $response['results']['albummatches']);
        $this->assertIsArray($response['results']['albummatches']['album']);
    }

    public function testAlbumGetTopTags(): void
    {
        $response = $this->client->getAlbumTopTags($this->getTestArtist(), $this->getTestAlbum());

        $this->assertLastFmResponseStructure($response, 'toptags');
        $this->assertArrayHasKey('tag', $response['toptags']);
        $this->assertIsArray($response['toptags']['tag']);
    }

    // =====================================
    // USER METHODS (Public)
    // =====================================

    public function testUserGetInfo(): void
    {
        $response = $this->client->getUserInfo($this->getTestUser());

        $this->assertLastFmResponseStructure($response, 'user');
        $this->assertEquals(strtolower($this->getTestUser()), strtolower($response['user']['name']));
        $this->assertArrayHasKey('realname', $response['user']);
        $this->assertArrayHasKey('playcount', $response['user']);
    }

    public function testUserGetRecentTracks(): void
    {
        $response = $this->client->getUserRecentTracks($this->getTestUser(), limit: 10);

        $this->assertLastFmResponseStructure($response, 'recenttracks');
        $this->assertArrayHasKey('track', $response['recenttracks']);
        $this->assertIsArray($response['recenttracks']['track']);
        $this->assertLessThanOrEqual(10, count($response['recenttracks']['track']));
    }

    public function testUserGetTopTracks(): void
    {
        $response = $this->client->getUserTopTracks($this->getTestUser(), limit: 10);

        $this->assertLastFmResponseStructure($response, 'toptracks');
        $this->assertArrayHasKey('track', $response['toptracks']);
        $this->assertIsArray($response['toptracks']['track']);
    }

    public function testUserGetTopArtists(): void
    {
        $response = $this->client->getUserTopArtists($this->getTestUser(), limit: 10);

        $this->assertLastFmResponseStructure($response, 'topartists');
        $this->assertArrayHasKey('artist', $response['topartists']);
        $this->assertIsArray($response['topartists']['artist']);
    }

    public function testUserGetTopAlbums(): void
    {
        $response = $this->client->getUserTopAlbums($this->getTestUser(), limit: 10);

        $this->assertLastFmResponseStructure($response, 'topalbums');
        $this->assertArrayHasKey('album', $response['topalbums']);
        $this->assertIsArray($response['topalbums']['album']);
    }

    // =====================================
    // TAG METHODS
    // =====================================

    public function testTagGetInfo(): void
    {
        $response = $this->client->getTagInfo($this->getTestTag());

        $this->assertLastFmResponseStructure($response, 'tag');
        $this->assertEquals($this->getTestTag(), $response['tag']['name']);
        $this->assertArrayHasKey('wiki', $response['tag']);
    }

    public function testTagGetTopTracks(): void
    {
        $response = $this->client->getTagTopTracks($this->getTestTag(), limit: 10);

        $this->assertLastFmResponseStructure($response, 'tracks');
        $this->assertArrayHasKey('track', $response['tracks']);
        $this->assertIsArray($response['tracks']['track']);
    }

    public function testTagGetTopArtists(): void
    {
        $response = $this->client->getTagTopArtists($this->getTestTag(), limit: 10);

        $this->assertLastFmResponseStructure($response, 'topartists');
        $this->assertArrayHasKey('artist', $response['topartists']);
        $this->assertIsArray($response['topartists']['artist']);
    }

    public function testTagGetTopAlbums(): void
    {
        $response = $this->client->getTagTopAlbums($this->getTestTag(), limit: 10);

        $this->assertLastFmResponseStructure($response, 'albums');
        $this->assertArrayHasKey('album', $response['albums']);
        $this->assertIsArray($response['albums']['album']);
    }

    // =====================================
    // GEO METHODS
    // =====================================

    public function testGeoGetTopTracks(): void
    {
        $response = $this->client->getTopTracksByCountry('United States', limit: 10);

        $this->assertLastFmResponseStructure($response, 'tracks');
        $this->assertArrayHasKey('track', $response['tracks']);
        $this->assertIsArray($response['tracks']['track']);
        $this->assertArrayHasKey('@attr', $response['tracks']);
        $this->assertEquals('United States', $response['tracks']['@attr']['country']);
    }

    public function testGeoGetTopArtists(): void
    {
        $response = $this->client->getTopArtistsByCountry('United Kingdom', limit: 10);

        $this->assertLastFmResponseStructure($response, 'topartists');
        $this->assertArrayHasKey('artist', $response['topartists']);
        $this->assertIsArray($response['topartists']['artist']);
        $this->assertArrayHasKey('@attr', $response['topartists']);
        $this->assertEquals('United Kingdom', $response['topartists']['@attr']['country']);
    }

    // =====================================
    // CHART METHODS
    // =====================================

    public function testChartGetTopTracks(): void
    {
        $response = $this->client->getTopTracksChart(limit: 10);

        $this->assertLastFmResponseStructure($response, 'tracks');
        $this->assertArrayHasKey('track', $response['tracks']);
        $this->assertIsArray($response['tracks']['track']);
        $this->assertLessThanOrEqual(10, count($response['tracks']['track']));
    }

    public function testChartGetTopArtists(): void
    {
        $response = $this->client->getTopArtistsChart(limit: 10);

        $this->assertLastFmResponseStructure($response, 'artists');
        $this->assertArrayHasKey('artist', $response['artists']);
        $this->assertIsArray($response['artists']['artist']);
        $this->assertLessThanOrEqual(10, count($response['artists']['artist']));
    }

    public function testChartGetTopTags(): void
    {
        $response = $this->client->getTopTagsChart(limit: 10);

        $this->assertLastFmResponseStructure($response, 'tags');
        $this->assertArrayHasKey('tag', $response['tags']);
        $this->assertIsArray($response['tags']['tag']);
        $this->assertLessThanOrEqual(10, count($response['tags']['tag']));
    }

    // =====================================
    // LIBRARY METHODS (Public)
    // =====================================

    public function testLibraryGetArtists(): void
    {
        $response = $this->client->getLibraryArtists($this->getTestUser(), limit: 10);

        $this->assertLastFmResponseStructure($response, 'artists');
        $this->assertArrayHasKey('artist', $response['artists']);
        $this->assertIsArray($response['artists']['artist']);
    }

    // =====================================
    // ERROR HANDLING
    // =====================================

    public function testNonExistentArtistReturnsError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The artist you supplied could not be found');

        $this->client->getArtistInfo('NonExistentArtist123456789');
    }

    public function testInvalidParametersThrowException(): void
    {
        $this->expectException(\RuntimeException::class);

        // Try to get track info without required parameters
        $this->client->getTrackInfo('', '');
    }

    // =====================================
    // PAGINATION AND LIMITS
    // =====================================

    public function testPaginationParameters(): void
    {
        $page1 = $this->client->getUserTopTracks($this->getTestUser(), limit: 5, page: 1);
        $page2 = $this->client->getUserTopTracks($this->getTestUser(), limit: 5, page: 2);

        $this->assertLastFmResponseStructure($page1, 'toptracks');
        $this->assertLastFmResponseStructure($page2, 'toptracks');

        // Pages should contain different results
        if (!empty($page1['toptracks']['track']) && !empty($page2['toptracks']['track'])) {
            $this->assertNotEquals(
                $page1['toptracks']['track'][0]['name'] ?? '',
                $page2['toptracks']['track'][0]['name'] ?? ''
            );
        }
    }

    public function testLimitParameter(): void
    {
        $limited = $this->client->getArtistTopTracks($this->getTestArtist(), limit: 3);

        $this->assertLastFmResponseStructure($limited, 'toptracks');
        $this->assertArrayHasKey('track', $limited['toptracks']);
        $this->assertLessThanOrEqual(3, count($limited['toptracks']['track']));
    }

    // =====================================
    // TIME PERIOD PARAMETERS
    // =====================================

    public function testTimePeriodParameters(): void
    {
        $overall = $this->client->getUserTopTracks($this->getTestUser(), period: 'overall', limit: 5);
        $week = $this->client->getUserTopTracks($this->getTestUser(), period: '7day', limit: 5);

        $this->assertLastFmResponseStructure($overall, 'toptracks');
        $this->assertLastFmResponseStructure($week, 'toptracks');
    }

    // =====================================
    // AUTOCORRECT PARAMETERS
    // =====================================

    public function testAutocorrectParameter(): void
    {
        // Test with a deliberately misspelled artist name
        $response = $this->client->getArtistInfo('taylor swif', autocorrect: 1);

        $this->assertArtistResponse($response);
        // Should still return valid artist info due to autocorrection
        $this->assertIsString($response['artist']['name']);
        // With autocorrect, it should return "Taylor Swift"
        $this->assertEquals('Taylor Swift', $response['artist']['name']);
    }
}

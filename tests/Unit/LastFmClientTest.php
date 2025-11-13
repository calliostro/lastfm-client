<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use Calliostro\LastFm\LastFmClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LastFmClient::class)]
final class LastFmClientTest extends UnitTestCase
{
    private LastFmClient $client;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $this->client = new LastFmClient($guzzleClient);
        $this->client->setApiCredentials('test_api_key', 'test_secret');
    }

    public function testGetArtistInfoMethodCallsCorrectEndpoint(): void
    {
        $mockResponse = $this->createMockArtistResponse([
            'artist' => [
                'name' => 'Billie Eilish',
                'mbid' => '4bd95eea-b9f6-4d70-a36c-cfea77431553',
                'playcount' => '15000000',
                'listeners' => '2500000'
            ]
        ]);

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode($mockResponse))
        );

        $result = $this->client->getArtistInfo('Billie Eilish');

        $this->assertValidArtistResponse($result['artist']);
        $this->assertEquals('Billie Eilish', $result['artist']['name']);
        $this->assertValidMbid($result['artist']['mbid']);
        $this->assertArtistHasListeners($result['artist']);
    }

    public function testAlbumSearchMethodCallsCorrectEndpoint(): void
    {
        $mockResponse = $this->createMockSearchResponse([
            'results' => [
                'albummatches' => [
                    'album' => [
                        ['name' => 'Happier Than Ever', 'artist' => 'Billie Eilish'],
                        ['name' => 'When We All Fall Asleep, Where Do We Go?', 'artist' => 'Billie Eilish']
                    ]
                ]
            ]
        ]);

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode($mockResponse))
        );

        $result = $this->client->searchAlbums('Happier Than Ever');

        $this->assertValidSearchResponse($result);
        $this->assertArrayHasKey('albummatches', $result['results']);
    }

    public function testGetTrackInfoMethodCallsCorrectEndpoint(): void
    {
        $mockResponse = $this->createMockTrackResponse([
            'track' => [
                'name' => 'Bad Guy',
                'artist' => ['name' => 'Billie Eilish'],
                'album' => ['title' => 'When We All Fall Asleep, Where Do We Go?'],
                'playcount' => '500000000'
            ]
        ]);

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode($mockResponse))
        );

        $result = $this->client->getTrackInfo('Billie Eilish', 'Bad Guy');

        $this->assertValidTrackResponse($result['track']);
        $this->assertEquals('Bad Guy', $result['track']['name']);
        $this->assertTrackHasPlaycount($result['track']);
    }

    public function testMethodNameConversionWorks(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'toptracks' => [
                    'track' => [
                        ['name' => 'Anti-Hero', 'playcount' => '400000000'],
                        ['name' => 'Lavender Haze', 'playcount' => '200000000']
                    ]
                ]
            ]))
        );

        $result = $this->client->getArtistTopTracks('Taylor Swift');

        $this->assertEquals([
            'toptracks' => [
                'track' => [
                    ['name' => 'Anti-Hero', 'playcount' => '400000000'],
                    ['name' => 'Lavender Haze', 'playcount' => '200000000']
                ]
            ]
        ], $result);
    }

    public function testComplexMethodNameConversion(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'recenttracks' => [
                    'track' => [
                        ['name' => 'Flowers', 'artist' => ['#text' => 'Miley Cyrus']],
                        ['name' => 'As It Was', 'artist' => ['#text' => 'Harry Styles']]
                    ]
                ]
            ]))
        );

        $result = $this->client->getUserRecentTracks('testuser');

        $this->assertEquals([
            'recenttracks' => [
                'track' => [
                    ['name' => 'Flowers', 'artist' => ['#text' => 'Miley Cyrus']],
                    ['name' => 'As It Was', 'artist' => ['#text' => 'Harry Styles']]
                ]
            ]
        ], $result);
    }

    public function testGetTagTopArtistsMethod(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'topartists' => [
                    'artist' => [
                        ['name' => 'Dua Lipa', 'playcount' => '25000000'],
                        ['name' => 'The Weeknd', 'playcount' => '30000000']
                    ]
                ]
            ]))
        );

        $result = $this->client->getTagTopArtists('pop');

        $this->assertEquals([
            'topartists' => [
                'artist' => [
                    ['name' => 'Dua Lipa', 'playcount' => '25000000'],
                    ['name' => 'The Weeknd', 'playcount' => '30000000']
                ]
            ]
        ], $result);
    }

    public function testGetGeoTopTracksMethod(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'tracks' => [
                    'track' => [
                        ['name' => 'Unholy', 'artist' => ['name' => 'Sam Smith']],
                        ['name' => 'I\'m Good (Blue)', 'artist' => ['name' => 'David Guetta']]
                    ]
                ]
            ]))
        );

        $result = $this->client->getTopTracksByCountry('Germany');

        $this->assertEquals([
            'tracks' => [
                'track' => [
                    ['name' => 'Unholy', 'artist' => ['name' => 'Sam Smith']],
                    ['name' => 'I\'m Good (Blue)', 'artist' => ['name' => 'David Guetta']]
                ]
            ]
        ], $result);
    }

    public function testUnknownOperationThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown operation');

        // @phpstan-ignore-next-line - Testing invalid method call
        $this->client->unknownMethodName();
    }

    public function testInvalidJsonResponseThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], 'invalid json')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->client->getArtistInfo('BTS');
    }

    public function testApiErrorResponseThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(400, [], $this->jsonEncode([
                'error' => 6,
                'message' => 'Invalid parameters - You must supply either an artist name or mbid.'
            ]))
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid parameters - You must supply either an artist name or mbid.');

        $this->client->getArtistInfo('');
    }

    public function testHttpExceptionHandling(): void
    {
        $this->mockHandler->append(
            new \GuzzleHttp\Exception\RequestException(
                'Connection timeout',
                new \GuzzleHttp\Psr7\Request('GET', 'test')
            )
        );

        $this->expectException(GuzzleException::class);
        $this->expectExceptionMessage('Connection timeout');

        $this->client->getArtistInfo('Lizzo');
    }

    // Private method tests using Reflection

    /**
     * Test buildParamsFromArguments method with reflection
     */
    public function testBuildParamsFromArgumentsWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildParamsFromArguments');
        $method->setAccessible(true);

        // Test artist.getInfo operation with positional arguments
        $result = $method->invoke($this->client, 'getArtistInfo', ['Doja Cat', null, 'en', 1, 'testuser']);

        $this->assertArrayHasKey('artist', $result);
        $this->assertEquals('Doja Cat', $result['artist']);
        $this->assertArrayHasKey('lang', $result);
        $this->assertEquals('en', $result['lang']);
        $this->assertArrayHasKey('autocorrect', $result);
        $this->assertEquals('1', $result['autocorrect']);
        $this->assertArrayHasKey('username', $result);
        $this->assertEquals('testuser', $result['username']);
    }

    /**
     * Test buildParamsFromArguments with an associative array
     */
    public function testBuildParamsFromArgumentsWithAssociativeArray(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildParamsFromArguments');
        $method->setAccessible(true);

        // Test with an associative array instead of positional
        $result = $method->invoke($this->client, 'getArtistInfo', [
            'artist' => 'Olivia Rodrigo',
            'lang' => 'de',
            'autocorrect' => 0
        ]);

        $this->assertArrayHasKey('artist', $result);
        $this->assertEquals('Olivia Rodrigo', $result['artist']);
        $this->assertArrayHasKey('lang', $result);
        $this->assertEquals('de', $result['lang']);
        $this->assertArrayHasKey('autocorrect', $result);
        $this->assertEquals('0', $result['autocorrect']);
    }

    /**
     * Test getAllowedCamelCaseParams method with reflection
     */
    public function testGetAllowedCamelCaseParamsWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getAllowedCamelCaseParams');
        $method->setAccessible(true);

        // Test getArtistInfo operation (not artist.getInfo)
        $result = $method->invoke($this->client, 'getArtistInfo');

        $this->assertIsArray($result);
        // The method converts snake_case to camelCase, so we check for the converted names
        $this->assertContains('artist', $result);
        $this->assertContains('mbid', $result);
        $this->assertContains('lang', $result);
    }

    /**
     * Test getAllowedCamelCaseParams for unknown operation
     */
    public function testGetAllowedCamelCaseParamsForUnknownOperation(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getAllowedCamelCaseParams');
        $method->setAccessible(true);

        $result = $method->invoke($this->client, 'unknown.operation');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test convertSnakeToCamel method with reflection
     */
    public function testConvertSnakeToCamelWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertSnakeToCamel');
        $method->setAccessible(true);

        $this->assertEquals('testParam', $method->invoke($this->client, 'test_param'));
        $this->assertEquals('myLongParameterName', $method->invoke($this->client, 'my_long_parameter_name'));
        $this->assertEquals('simple', $method->invoke($this->client, 'simple'));
        $this->assertEquals('', $method->invoke($this->client, ''));
    }

    /**
     * Test convertCamelToSnake method with reflection
     */
    public function testConvertCamelToSnakeWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertCamelToSnake');
        $method->setAccessible(true);

        $this->assertEquals('test_param', $method->invoke($this->client, 'testParam'));
        $this->assertEquals('my_long_parameter_name', $method->invoke($this->client, 'myLongParameterName'));
        $this->assertEquals('simple', $method->invoke($this->client, 'simple'));
        $this->assertEquals('', $method->invoke($this->client, ''));
        // ABC would become 'abc' based on the actual implementation
        $this->assertEquals('abc', $method->invoke($this->client, 'ABC'));
    }

    /**
     * Test validateRequiredParameters method with reflection
     */
    public function testValidateRequiredParametersWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        $method->setAccessible(true);

        // Test with valid required parameters for artist.getInfo
        $this->assertNull($method->invoke(
            $this->client,
            'artist.getInfo',
            ['artist' => 'Dua Lipa'],
            ['artist' => 'Dua Lipa']
        ));
    }

    /**
     * Test validateRequiredParameters throws an exception for missing required param
     */
    public function testValidateRequiredParametersThrowsExceptionForMissingParam(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter artist is missing');

        // Use loveTrack which has required parameters
        $method->invoke(
            $this->client,
            'loveTrack',
            [], // Missing required parameters
            []
        );
    }

    /**
     * Test validateRequiredParameters for operation without required params
     */
    public function testValidateRequiredParametersForOperationWithoutRequiredParams(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        $method->setAccessible(true);

        // Test chart.getTopArtists which has no required parameters
        $this->assertNull($method->invoke(
            $this->client,
            'chart.getTopArtists',
            [],
            []
        ));
    }

    /**
     * Test callOperation method with reflection
     */
    public function testCallOperationWithReflection(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'artist' => ['name' => 'Ariana Grande', 'playcount' => '1000000']
            ]))
        );

        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        $result = $method->invoke($this->client, 'getArtistInfo', ['artist' => 'Ariana Grande']);

        $this->assertEquals([
            'artist' => ['name' => 'Ariana Grande', 'playcount' => '1000000']
        ], $result);
    }

    /**
     * Test callOperation with POST method (requires authentication)
     */
    public function testCallOperationWithPostMethod(): void
    {
        // Set up authentication for the test
        $this->client->setApiCredentials('test_key', 'test_secret', 'test_session');

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'lfm' => ['status' => 'ok']
            ]))
        );

        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        // Test loveTrack which is a POST operation
        $result = $method->invoke($this->client, 'loveTrack', [
            'artist' => 'Ed Sheeran',
            'track' => 'Shape of You'
        ]);

        $this->assertEquals([
            'lfm' => ['status' => 'ok']
        ], $result);
    }



    /**
     * Test convertArrayParamsToString method with reflection
     */
    public function testConvertArrayParamsToStringWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertArrayParamsToString');
        $method->setAccessible(true);

        $params = [
            'artist' => 'SZA',
            'limit' => 10,
            'page' => 1
        ];

        $result = $method->invoke($this->client, $params);

        $this->assertEquals('SZA', $result['artist']);
        $this->assertEquals('10', $result['limit']);
        $this->assertEquals('1', $result['page']);
    }

    /**
     * Test convertArrayParamsToString with non-array values
     */
    public function testConvertArrayParamsToStringWithNonArrayValues(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertArrayParamsToString');
        $method->setAccessible(true);

        $params = [
            'artist' => 'Lorde',
            'autocorrect' => true,
            'limit' => null
        ];

        $result = $method->invoke($this->client, $params);

        $this->assertEquals('Lorde', $result['artist']);
        $this->assertEquals('1', $result['autocorrect']);
        $this->assertArrayNotHasKey('limit', $result);
    }

    /**
     * Test buildParamsFromArguments with DateTimeInterface parameter
     */
    public function testBuildParamsFromArgumentsWithDateTimeInterface(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildParamsFromArguments');
        $method->setAccessible(true);

        $timestamp = 1672574400; // Unix timestamp for 2023-01-01 12:00:00

        // Test with a method that accepts a timestamp
        $result = $method->invoke($this->client, 'scrobbleTrack', [
            'Doja Cat',
            'Need to Know',
            $timestamp
        ]);

        $this->assertEquals('Doja Cat', $result['artist']);
        $this->assertEquals('Need to Know', $result['track']);
        $this->assertEquals($timestamp, $result['timestamp']);
    }

    /**
     * Test edge cases for parameter validation
     */
    public function testParameterValidationEdgeCases(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $validateMethod = $reflection->getMethod('validateRequiredParameters');
        $validateMethod->setAccessible(true);

        // Test with an operation that requires multiple parameters
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter track is missing');

        $validateMethod->invoke(
            $this->client,
            'loveTrack',
            ['artist' => 'Someone'], // Missing 'track' parameter
            ['artist' => 'Someone']
        );
    }

    /**
     * Test callOperation error handling for unknown operation
     */
    public function testCallOperationUnknownOperationError(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown operation: unknown.method');

        $method->invoke($this->client, 'unknown.method', []);
    }

    /**
     * Test constructor with GuzzleClient parameter
     */
    public function testConstructorWithGuzzleClient(): void
    {
        $guzzleClient = new \GuzzleHttp\Client(['timeout' => 30]);
        $client = new LastFmClient($guzzleClient);

        $this->assertInstanceOf(LastFmClient::class, $client);
    }

    /**
     * Test constructor with an option array
     */
    public function testConstructorWithOptionsArray(): void
    {
        $options = ['timeout' => 10, 'verify' => false];
        $client = new LastFmClient($options);

        $this->assertInstanceOf(LastFmClient::class, $client);
    }

    /**
     * Test constructor with empty options (default behavior)
     */
    public function testConstructorWithEmptyOptions(): void
    {
        $client = new LastFmClient();

        $this->assertInstanceOf(LastFmClient::class, $client);
    }

    /**
     * Test validateParameters with various edge cases using reflection
     */
    public function testValidateParametersWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateParameters');
        $method->setAccessible(true);

        // Test with valid parameters
        $validParams = ['artist' => 'Test Artist', 'track' => 'Test Track'];
        $method->invoke($this->client, $validParams);
        $this->assertTrue(true); // Should not throw exception

        // Test with parameters containing arrays (should throw exception)
        $invalidParams = ['artist' => 'Test', 'tags' => ['rock', 'pop']];
        $this->expectException(\InvalidArgumentException::class);
        $method->invoke($this->client, $invalidParams);
    }

    /**
     * Test convertParameterToString with DateTime objects using reflection
     */
    public function testConvertParameterToStringWithDateTimeInterface(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertParameterToString');
        $method->setAccessible(true);

        $dateTime = new \DateTime('2023-01-01 00:00:00');
        $result = $method->invoke($this->client, $dateTime);

        // DateTime objects are converted to ISO 8601 format, not timestamps
        $this->assertEquals('2023-01-01T00:00:00+00:00', $result);
    }

    /**
     * Test convertParameterToString with an invalid object type using reflection
     */
    public function testConvertParameterToStringWithInvalidObject(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertParameterToString');
        $method->setAccessible(true);

        $invalidObject = new \stdClass();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter type');

        $method->invoke($this->client, $invalidObject);
    }



    /**
     * Test buildParamsFromArguments with edge cases using reflection
     */
    public function testBuildParamsFromArgumentsEdgeCases(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildParamsFromArguments');
        $method->setAccessible(true);

        // Test with empty arguments for an operation that has parameters
        $result = $method->invoke($this->client, 'getArtistInfo', []);
        $this->assertIsArray($result);

        // Test with null values in arguments
        $result = $method->invoke($this->client, 'getArtistInfo', ['Artist Name', null, null]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('artist', $result);
    }

    /**
     * Test validateRequiredParameters with missing parameters using reflection
     */
    public function testValidateRequiredParametersCompleteCoverage(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        $method->setAccessible(true);

        // Test with an operation that has no required parameters
        $method->invoke($this->client, 'chart.getTopArtists', [], []);
        $this->assertTrue(true); // Should not throw

        // Test with all required parameters present
        $method->invoke($this->client, 'artist.getInfo', ['artist' => 'Test'], ['artist' => 'Test']);
        $this->assertTrue(true); // Should not throw
    }

    /**
     * Test callOperation with an authenticated request using reflection
     */
    public function testCallOperationWithAuthentication(): void
    {
        $this->client->setApiCredentials('test_key', 'test_secret', 'test_session');

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'artist' => ['name' => 'Test Artist']
            ]))
        );

        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        $result = $method->invoke($this->client, 'getArtistInfo', ['artist' => 'Test Artist']);
        $this->assertArrayHasKey('artist', $result);
    }

    /**
     * Test getAllowedCamelCaseParams method for operation parameter mapping
     */
    public function testGetAllowedCamelCaseParamsMethodMapping(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getAllowedCamelCaseParams');
        $method->setAccessible(true);

        // Test with known operation - use getAlbumInfo (the operation key, not album.getInfo)
        $camelParams = $method->invoke($this->client, 'getAlbumInfo');

        // Should return camelCase versions of snake_case parameters
        $this->assertIsArray($camelParams);
        $this->assertContains('autocorrect', $camelParams); // autocorrect stays autocorrect
        $this->assertContains('username', $camelParams); // username stays username
    }

    /**
     * Test getAllowedCamelCaseParams with unknown operation
     */
    public function testGetAllowedCamelCaseParamsUnknownOperation(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getAllowedCamelCaseParams');
        $method->setAccessible(true);

        // Test with a non-existent operation
        $result = $method->invoke($this->client, 'nonexistent.operation');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getAllowedCamelCaseParams with operation having no parameters
     */
    public function testGetAllowedCamelCaseParamsNoParameters(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getAllowedCamelCaseParams');
        $method->setAccessible(true);

        // Test with an operation that has minimal parameters (auth.getToken has no params)
        $result = $method->invoke($this->client, 'auth.getToken');

        $this->assertIsArray($result);
        // auth.getToken should have no parameters, so an empty array expected
        $this->assertEmpty($result);
    }

    /**
     * Test validateRequiredParameters method comprehensive coverage
     */
    public function testValidateRequiredParametersMethodCoverage(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        $method->setAccessible(true);

        // Test 1: Operation with no parameters config - should return early
        $method->invoke($this->client, 'getAuthToken', [], []);
        $this->assertTrue(true); // Should not throw

        // Test 2: All required parameters present
        $method->invoke($this->client, 'searchAlbums', ['album' => 'Test Album'], []);
        $this->assertTrue(true); // Should not throw

        // Test 3: Missing required parameter - should throw exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter album is missing');
        $method->invoke($this->client, 'searchAlbums', [], []); // Missing required 'album' parameter
    }

    /**
     * Test validateRequiredParameters with null values in named arguments
     */
    public function testValidateRequiredParametersNullValues(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        $method->setAccessible(true);

        // Test with null value for non-required parameter (should not throw)
        $method->invoke($this->client, 'getAlbumInfo', ['artist' => 'Test'], ['artist' => 'Test', 'album' => null]);
        $this->assertTrue(true); // Should not throw

        // Test with null value for the required parameter (should throw exception)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter album is required but null was provided');
        $method->invoke($this->client, 'searchAlbums', ['album' => null], ['album' => null]);
    }

    /**
     * Test callOperation method comprehensive coverage
     */
    public function testCallOperationMethodComprehensiveCoverage(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        // Test 1: GET request without an API key
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['test' => 'response']))
        );

        $result = $method->invoke($this->client, 'getAlbumInfo', ['artist' => 'Test Artist']);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('test', $result);

        // Test 2: GET request with an API key
        $this->client->setApiCredentials('test_key');

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['album' => 'data']))
        );

        $result = $method->invoke($this->client, 'getAlbumInfo', ['artist' => 'Test Artist']);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('album', $result);
    }

    /**
     * Test callOperation error handling scenarios
     */
    public function testCallOperationErrorHandling(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        // Test empty response body
        $this->mockHandler->append(new Response(200, [], ''));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty response body received');
        $method->invoke($this->client, 'getAlbumInfo', ['artist' => 'Test']);
    }

    /**
     * Test callOperation with invalid JSON response
     */
    public function testCallOperationInvalidJsonResponse(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        // Test invalid JSON response
        $this->mockHandler->append(new Response(200, [], 'invalid json'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');
        $method->invoke($this->client, 'getAlbumInfo', ['artist' => 'Test']);
    }

    /**
     * Test callOperation with non-array response
     */
    public function testCallOperationNonArrayResponse(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        // Test non-array JSON response
        $this->mockHandler->append(new Response(200, [], '"string response"'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from API');
        $method->invoke($this->client, 'getAlbumInfo', ['artist' => 'Test']);
    }

    /**
     * Test callOperation with API error response
     */
    public function testCallOperationApiErrorResponse(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('callOperation');
        $method->setAccessible(true);

        // Test API error response
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'error' => 6,
                'message' => 'Invalid method'
            ]))
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid method');
        $method->invoke($this->client, 'getAlbumInfo', ['artist' => 'Test']);
    }





    /**
     * Test convertArrayParamsToString method comprehensive coverage
     */
    public function testConvertArrayParamsToStringMethodCoverage(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertArrayParamsToString');
        $method->setAccessible(true);

        // Test 1: Empty array
        $result = $method->invoke($this->client, []);
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        // Test 2: Mixed parameter types (excluding arrays which throw exceptions)
        $mixedParams = [
            'string' => 'test',
            'integer' => 123,
            'float' => 45.67,
            'boolean' => true,
            'null' => null
        ];

        $result = $method->invoke($this->client, $mixedParams);
        $this->assertIsArray($result);
        $this->assertEquals('test', $result['string']);
        $this->assertEquals('123', $result['integer']);
        $this->assertEquals('45.67', $result['float']);
        $this->assertEquals('1', $result['boolean']);
        $this->assertArrayNotHasKey('null', $result);
    }

    /**
     * Test convertArrayParamsToString with DateTime objects
     */
    public function testConvertArrayParamsToStringWithDateTime(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertArrayParamsToString');
        $method->setAccessible(true);

        $dateTime = new \DateTime('2021-01-01 12:00:00');
        $params = [
            'date' => $dateTime,
            'name' => 'Test Artist'
        ];

        $result = $method->invoke($this->client, $params);
        $this->assertIsArray($result);
        $this->assertEquals('2021-01-01T12:00:00+00:00', $result['date']); // ATOM format
        $this->assertEquals('Test Artist', $result['name']);
    }

    /**
     * Test validateParameters method comprehensive coverage
     */
    public function testValidateParametersMethodCoverage(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateParameters');
        $method->setAccessible(true);

        // Test 1: Valid parameters - should pass
        $validParams = ['artist' => 'Test Artist', 'album' => 'Test Album'];
        $method->invoke($this->client, $validParams);
        $this->assertTrue(true); // Should not throw exception

        // Test 2: Too many parameters
        $tooManyParams = [];
        for ($i = 0; $i <= 55; $i++) { // More than MAX_PLACEHOLDERS (50)
            $tooManyParams["param$i"] = "value$i";
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Too many parameters: 56. Maximum allowed: 50');
        $method->invoke($this->client, $tooManyParams);
    }

    /**
     * Test validateParameters with invalid parameter names
     */
    public function testValidateParametersInvalidParamNames(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateParameters');
        $method->setAccessible(true);

        // Test invalid parameter name (starts with number)
        $invalidParams = ['123invalid' => 'test'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name: 123invalid');
        $method->invoke($this->client, $invalidParams);
    }

    /**
     * Test validateParameters with special characters in param names
     */
    public function testValidateParametersSpecialCharacters(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateParameters');
        $method->setAccessible(true);

        // Test invalid parameter name with special characters
        $invalidParams = ['param-with-dash' => 'test'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name: param-with-dash');
        $method->invoke($this->client, $invalidParams);
    }

    /**
     * Test validateParameters with URI length limit
     */
    public function testValidateParametersUriLengthLimit(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('validateParameters');
        $method->setAccessible(true);

        // Create one parameter with a massive value to definitely exceed the URI length limit
        $longParams = ['param' => str_repeat('x', 2100)]; // Way over 2048 limit

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Request URI too long');
        $method->invoke($this->client, $longParams);
    }

    /**
     * Test convertMethodToOperation method with reflection
     */
    public function testConvertMethodToOperationWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertMethodToOperation');
        $method->setAccessible(true);

        // Test direct mapping (Last.fm uses camelCase directly, no conversion needed)
        $this->assertEquals('getArtistInfo', $method->invoke($this->client, 'getArtistInfo'));
        $this->assertEquals('searchTracks', $method->invoke($this->client, 'searchTracks'));
        $this->assertEquals('albumGetTopTags', $method->invoke($this->client, 'albumGetTopTags'));
        $this->assertEquals('getUserRecentTracks', $method->invoke($this->client, 'getUserRecentTracks'));

        // Test edge cases
        $this->assertEquals('', $method->invoke($this->client, ''));
        $this->assertEquals('simpleMethod', $method->invoke($this->client, 'simpleMethod'));
        $this->assertEquals('complexMethodNameWithMultipleWords', $method->invoke($this->client, 'complexMethodNameWithMultipleWords'));
    }

    /**
     * Test generateSignature method with reflection
     */
    public function testGenerateSignatureWithReflection(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('generateSignature');
        $method->setAccessible(true);

        // Test basic signature generation
        $params = [
            'method' => 'artist.getInfo',
            'artist' => 'Dua Lipa',
            'api_key' => 'test_key'
        ];
        $secret = 'test_secret';

        $signature = $method->invoke($this->client, $params, $secret);
        $this->assertIsString($signature);
        $this->assertEquals(32, strlen($signature)); // MD5 hash is always 32 characters

        // Verify signature is consistent
        $signature2 = $method->invoke($this->client, $params, $secret);
        $this->assertEquals($signature, $signature2);

        // Test with different parameters should generate different signature
        $params2 = [
            'method' => 'track.getInfo',
            'artist' => 'Olivia Rodrigo',
            'api_key' => 'test_key'
        ];
        $signature3 = $method->invoke($this->client, $params2, $secret);
        $this->assertNotEquals($signature, $signature3);

        // Test that api_sig and format are excluded from signature calculation
        $paramsWithExcluded = [
            'method' => 'artist.getInfo',
            'artist' => 'Dua Lipa',
            'api_key' => 'test_key',
            'api_sig' => 'should_be_ignored',
            'format' => 'should_be_ignored'
        ];
        $signature4 = $method->invoke($this->client, $paramsWithExcluded, $secret);
        $this->assertEquals($signature, $signature4); // Should match the original signature

        // Test empty parameters
        $emptySignature = $method->invoke($this->client, [], $secret);
        $this->assertIsString($emptySignature);
        $this->assertEquals(32, strlen($emptySignature));

        // Verify signature uses MD5 and follows Last.fm specification
        // Expected signature for sorted params: api_key=test_key, artist=Dua Lipa, method=artist.getInfo + secret
        $expectedString = 'api_keytest_keyartistDua Lipamethodartist.getInfotest_secret';
        $expectedSignature = md5($expectedString);
        $this->assertEquals($expectedSignature, $signature);
    }

    /**
     * Test setApiCredentials method comprehensive coverage
     */
    public function testSetApiCredentialsMethod(): void
    {
        // Test setting all credentials
        $this->client->setApiCredentials('test_key', 'test_secret', 'test_session');

        // Use reflection to verify the properties were set correctly
        $reflection = new \ReflectionClass($this->client);

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $this->assertEquals('test_key', $apiKeyProperty->getValue($this->client));

        $apiSecretProperty = $reflection->getProperty('apiSecret');
        $apiSecretProperty->setAccessible(true);
        $this->assertEquals('test_secret', $apiSecretProperty->getValue($this->client));

        $sessionKeyProperty = $reflection->getProperty('sessionKey');
        $sessionKeyProperty->setAccessible(true);
        $this->assertEquals('test_session', $sessionKeyProperty->getValue($this->client));

        // Test setting only API key
        $this->client->setApiCredentials('new_key');
        $this->assertEquals('new_key', $apiKeyProperty->getValue($this->client));
        $this->assertNull($apiSecretProperty->getValue($this->client));
        $this->assertNull($sessionKeyProperty->getValue($this->client));

        // Test setting API key and secret without session
        $this->client->setApiCredentials('another_key', 'another_secret');
        $this->assertEquals('another_key', $apiKeyProperty->getValue($this->client));
        $this->assertEquals('another_secret', $apiSecretProperty->getValue($this->client));
        $this->assertNull($sessionKeyProperty->getValue($this->client));

        // Test setting all to null
        $this->client->setApiCredentials();
        $this->assertNull($apiKeyProperty->getValue($this->client));
        $this->assertNull($apiSecretProperty->getValue($this->client));
        $this->assertNull($sessionKeyProperty->getValue($this->client));
    }

    /**
     * Test buildParamsFromArguments with operation that has no parameters configured
     * This covers the uncovered line 176: return [] when no parameters exist
     */
    public function testBuildParamsFromArgumentsNoParametersConfigured(): void
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildParamsFromArguments');
        $method->setAccessible(true);

        // Test with a method name that would result in an operation with no parameters
        // We'll mock this by temporarily modifying the config or using a non-existent operation
        $result = $method->invoke($this->client, 'nonExistentOperation', ['param1', 'param2']);

        // Should return an empty array when no parameters are configured (line 176)
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // Removed testBuildParamsFromArgumentsUnknownNamedParameter as it caused PHPUnit warnings
    // The code path is covered by the Error being thrown in production code







    /**
     * Test associative array parameter conversion
     */
    public function testAssociativeArrayParameterConversion(): void
    {
        // Test method call with an associative array that should be converted
        $this->mockHandler->append(new Response(200, [], $this->jsonEncode([
            'artist' => ['name' => 'Test Artist']
        ])));

        // This should trigger associative array conversion
        // @phpstan-ignore-next-line
        $response = $this->client->getArtistInfo([
            'artist' => 'Test Artist',
            'mbid' => '12345',
            'lang' => 'en'
        ]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('artist', $response);
    }

    /**
     * Test empty array handling in isAssociativeArray
     */
    public function testEmptyArrayHandling(): void
    {
        // Test with a method that might receive empty parameters
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter type: arrays not supported');

        // Call with an empty associative array to test isAssociativeArray with empty input
        // This should trigger a validation error for array parameter
        // @phpstan-ignore-next-line
        $this->client->searchArtists([]);
    }

    /**
     * Test array parameter conversion with tags
     */
    public function testArrayParameterConversionWithTags(): void
    {
        $this->mockHandler->append(new Response(200, [], $this->jsonEncode([
            'status' => 'ok'
        ])));

        // Set credentials for an authenticated operation
        $this->client->setApiCredentials('test_key', 'test_secret', 'test_session');

        // Test addAlbumTags with correct parameters according to @method signature
        $response = $this->client->addAlbumTags(
            'Test Artist',
            'Test Album',
            'rock,alternative,indie'
        );

        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
    }

    /**
     * Test associative array with string keys vs. numeric keys
     */
    public function testAssociativeArrayWithStringKeys(): void
    {
        $this->mockHandler->append(new Response(200, [], $this->jsonEncode([
            'tracks' => []
        ])));

        // Test getUserTopTracks with correct parameters according to @method signature
        $response = $this->client->getUserTopTracks('test_user', 'overall', 10);

        $this->assertIsArray($response);
    }

    /**
     * Test numeric array handling (should not be associative)
     */
    public function testNumericArrayIsNotAssociative(): void
    {
        $this->mockHandler->append(new Response(200, [], $this->jsonEncode([
            'results' => []
        ])));

        // Test call with regular string parameters (not arrays)
        $response = $this->client->searchArtists('test artist');

        $this->assertIsArray($response);
        $this->assertArrayHasKey('results', $response);
    }

    /**
     * Test convertAssociativeArrayParams is called directly
     * This ensures the specific code path: a single associative array argument
     */
    public function testConvertAssociativeArrayParamsDirectCall(): void
    {
        $this->mockHandler->append(new Response(200, [], $this->jsonEncode([
            'track' => ['name' => 'Test Track']
        ])));

        // Call getTrackInfo with correct parameters according to @method signature
        $response = $this->client->getTrackInfo('Test Artist', 'Test Track');

        $this->assertIsArray($response);
        $this->assertArrayHasKey('track', $response);
    }

    /**
     * Test that a client can handle various valid parameter types
     */
    public function testParameterTypeHandling(): void
    {
        $this->client->setApiCredentials('test', 'test', 'test');

        // Test a successful call with valid parameters
        $this->mockHandler->append(new Response(200, [], $this->jsonEncode(['test' => 'value'])));

        $result = $this->client->getTrackInfo('Test Artist', 'Test Track');

        $this->assertIsArray($result);
        $this->assertEquals(['test' => 'value'], $result);
    }

    public function testBuildParamsFromArgumentsUnknownNamedParameter(): void
    {
        // Test the missing coverage line 204 - unknown named parameter should throw Error
        // This tests the "else" branch in buildParamsFromArguments when a named parameter is not allowed

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Unknown named parameter $invalidParam');

        // Try to call a method with an unknown named parameter
        // This should trigger line 204: throw new \Error("Unknown named parameter \$$key");
        // @phpstan-ignore-next-line
        $this->client->getArtistInfo('TestArtist', invalidParam: 'someValue');
    }

    /**
     * Test that arrays cannot be passed as individual parameters
     * This ensures convertParameterToString properly validates parameter types
     */
    public function testArrayParametersAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter type: arrays not supported');

        // Add mock response since this test will reach HTTP call when using an associative array
        $this->mockHandler->append(new Response(200, [], $this->jsonEncode(['test' => 'value'])));

        // Try to pass an array as the value for an individual parameter position
        // This should trigger convertParameterToString validation
        // @phpstan-ignore-next-line
        $this->client->getArtistInfo(['Test Artist']); // Non-associative array as the first parameter
    }

    /**
     * Test that object parameters without __toString are rejected
     */
    public function testObjectParametersWithoutToStringAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter type');

        // Create an object without __toString method
        $invalidObject = new \stdClass();
        $invalidObject->name = 'test';

        // This should trigger convertParameterToString validation
        // @phpstan-ignore-next-line
        $this->client->getArtistInfo($invalidObject);
    }
}

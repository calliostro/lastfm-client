<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use Calliostro\LastFm\AuthHelper;
use Calliostro\LastFm\LastFmClient;
use Calliostro\LastFm\LastFmClientFactory;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Http\Message\ResponseInterface;
use ReflectionMethod;
use RuntimeException;

#[CoversClass(LastFmClientFactory::class)]
#[UsesClass(LastFmClient::class)]
#[UsesClass(AuthHelper::class)]
final class LastFmClientFactoryTest extends UnitTestCase
{
    public function testCreateReturnsClient(): void
    {
        $client = LastFmClientFactory::create();

        $this->assertInstanceOf(LastFmClient::class, $client);
    }

    public function testCreateWithGuzzleClient(): void
    {
        $guzzle = new GuzzleClient();
        $client = LastFmClientFactory::create($guzzle);

        $this->assertInstanceOf(LastFmClient::class, $client);
    }

    public function testFactoryMethods(): void
    {
        $client1 = LastFmClientFactory::createWithApiKey('test-api-key', 'test-secret');
        $this->assertInstanceOf(LastFmClient::class, $client1);

        $client2 = LastFmClientFactory::createWithSession('test-api-key', 'test-secret', 'test-session-key');
        $this->assertInstanceOf(LastFmClient::class, $client2);

        // Test with custom options
        $client3 = LastFmClientFactory::createWithApiKey('test-api-key', 'test-secret', ['timeout' => 30]);
        $this->assertInstanceOf(LastFmClient::class, $client3);

        $client4 = LastFmClientFactory::createWithSession('test-api-key', 'test-secret', 'test-session', ['timeout' => 30]);
        $this->assertInstanceOf(LastFmClient::class, $client4);

        // Test with GuzzleClient passed to factory methods
        $guzzle = new GuzzleClient();
        $clientGuzzle = LastFmClientFactory::createWithApiKey('test-api-key', 'test-secret', $guzzle);
        $this->assertInstanceOf(LastFmClient::class, $clientGuzzle);

        $clientGuzzleSession = LastFmClientFactory::createWithSession('test-api-key', 'test-secret', 'test-session', $guzzle);
        $this->assertInstanceOf(LastFmClient::class, $clientGuzzleSession);

        // Test createWithMobileAuth success path
        $mockAuthHelper = $this->createMockAuthHelper();

        $client5 = LastFmClientFactory::createWithMobileAuth('test-api-key', 'test-secret', 'test-user', 'test-pass', [], $mockAuthHelper);
        $this->assertInstanceOf(LastFmClient::class, $client5);

        // Test createWithMobileAuth with options
        $client6 = LastFmClientFactory::createWithMobileAuth('test-api-key', 'test-secret', 'test-user', 'test-pass', ['timeout' => 30], $mockAuthHelper);
        $this->assertInstanceOf(LastFmClient::class, $client6);
    }

    public function testCreateWithMobileAuthMissingSessionKey(): void
    {
        // Test error case: Use a mock AuthHelper that returns data without a session key
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode([
                'session' => ['name' => 'test-user'] // Missing 'key' field
            ]) ?: '')
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new GuzzleClient([
            'handler' => $handlerStack,
            'base_uri' => 'https://ws.audioscrobbler.com/2.0/'
        ]);
        $mockAuthHelper = new AuthHelper('test-api-key', 'test-secret', $guzzleClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Session key not found in response');

        LastFmClientFactory::createWithMobileAuth('test-api-key', 'test-secret', 'test-user', 'test-pass', [], $mockAuthHelper);
    }

    public function testRetryMiddlewareRetriesOn503AndSucceeds(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"artist": {"name": "Test Artist"}}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtistInfo('Test Artist');

        $this->assertSame('Test Artist', $result['artist']['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testRetryMiddlewareRetriesOn429AndSucceeds(): void
    {
        $mock = new MockHandler([
            new Response(429, ['Content-Type' => 'application/json'], '{"error": 29, "message": "Rate Limit Exceeded"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"artist": {"name": "Test Artist"}}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtistInfo('Test Artist');

        $this->assertSame('Test Artist', $result['artist']['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testRetryMiddlewareRetriesOnConnectException(): void
    {
        $mock = new MockHandler([
            new ConnectException('Connection timed out', new Request('GET', 'https://ws.audioscrobbler.com/2.0/')),
            new Response(200, ['Content-Type' => 'application/json'], '{"artist": {"name": "Test Artist"}}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtistInfo('Test Artist');

        $this->assertSame('Test Artist', $result['artist']['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testRetryMiddlewareRetriesOnServerException(): void
    {
        $request = new Request('GET', 'https://ws.audioscrobbler.com/2.0/');
        $response503 = new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}');
        $mock = new MockHandler([
            new ServerException('Server error', $request, $response503),
            new Response(200, ['Content-Type' => 'application/json'], '{"artist": {"name": "Test Artist"}}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtistInfo('Test Artist');

        $this->assertSame('Test Artist', $result['artist']['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testRetryMiddlewareFailsWhenRetriesExhausted(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'max_retries' => 2,
            'retry_delay' => static fn (): int => 0,
        ]);

        $this->expectException(ServerException::class);
        $client->getArtistInfo('Test Artist');
    }

    public function testRetryMiddlewareDoesNotRetryOn400(): void
    {
        $mock = new MockHandler([
            new Response(400, ['Content-Type' => 'application/json'], '{"error": 6, "message": "Invalid parameters"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"artist": {"name": "Test Artist"}}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);

        $this->expectException(ClientException::class);
        $client->getArtistInfo('Test Artist');
    }

    public function testAutoRetryDisabledThrowsImmediatelyOn503(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"artist": {"name": "Test Artist"}}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'auto_retry' => false,
        ]);

        $this->expectException(ServerException::class);
        $client->getArtistInfo('Test Artist');
    }

    public function testMaxRetriesZeroThrowsImmediatelyOn503(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"artist": {"name": "Test Artist"}}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'max_retries' => 0,
        ]);

        $this->expectException(ServerException::class);
        $client->getArtistInfo('Test Artist');
    }

    public function testMaxRetriesCustomCountSucceeds(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"error": 11, "message": "Service Temporarily Unavailable"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"artist": {"name": "Test Artist"}}'),
        ]);

        $client = LastFmClientFactory::create([
            'handler' => $mock,
            'auto_retry' => true,
            'max_retries' => 3,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtistInfo('Test Artist');

        $this->assertSame('Test Artist', $result['artist']['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testDefaultRetryDelayCalculatesBackoff(): void
    {
        $this->assertSame(1000, $this->invokeDefaultRetryDelay(1));
        $this->assertSame(2000, $this->invokeDefaultRetryDelay(2));
        $this->assertSame(4000, $this->invokeDefaultRetryDelay(3));
    }

    public function testDefaultRetryDelayWithNumericRetryAfter(): void
    {
        $response = new Response(503, ['Retry-After' => '5']);

        $this->assertSame(5000, $this->invokeDefaultRetryDelay(1, $response));
    }

    public function testDefaultRetryDelayWithZeroNumericRetryAfter(): void
    {
        $response = new Response(503, ['Retry-After' => '0']);

        $this->assertSame(1000, $this->invokeDefaultRetryDelay(1, $response));
    }

    public function testDefaultRetryDelayWithHttpDateRetryAfter(): void
    {
        $futureTime = time() + 10;
        $httpDate = gmdate('D, d M Y H:i:s \G\M\T', $futureTime);
        $response = new Response(503, ['Retry-After' => $httpDate]);

        $delay = $this->invokeDefaultRetryDelay(1, $response);
        $this->assertGreaterThan(0, $delay);
        $this->assertLessThanOrEqual(10000, $delay);
    }

    public function testDefaultRetryDelayWithInvalidOrPastHttpDateRetryAfter(): void
    {
        $pastTime = time() - 10;
        $httpDate = gmdate('D, d M Y H:i:s \G\M\T', $pastTime);
        $response = new Response(503, ['Retry-After' => $httpDate]);

        $this->assertSame(1000, $this->invokeDefaultRetryDelay(1, $response));
    }

    private function invokeDefaultRetryDelay(int $retries, ?ResponseInterface $response = null): int
    {
        $reflection = new ReflectionMethod(LastFmClientFactory::class, 'defaultRetryDelay');

        return (int) $reflection->invoke(null, $retries, $response);
    }

    /**
     * Create a mock AuthHelper that returns a successful session
     */
    private function createMockAuthHelper(): AuthHelper
    {
        $mockHandler = new MockHandler([
            // Multiple responses for getMobileSession calls - used in test
            new Response(200, [], json_encode([
                'session' => ['key' => 'test-session-key']
            ]) ?: ''),
            new Response(200, [], json_encode([
                'session' => ['key' => 'test-session-key']
            ]) ?: ''),
            new Response(200, [], json_encode([
                'session' => ['key' => 'test-session-key']
            ]) ?: '')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new GuzzleClient([
            'handler' => $handlerStack,
            'base_uri' => 'https://ws.audioscrobbler.com/2.0/'
        ]);

        return new AuthHelper('test-api-key', 'test-secret', $guzzleClient);
    }
}

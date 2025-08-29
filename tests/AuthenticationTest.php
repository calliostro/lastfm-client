<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Calliostro\LastFm\LastFmApiClient;
use Calliostro\LastFm\ClientFactory;

class AuthenticationTest extends TestCase
{
    private function createMockedClient(array $responses): LastFmApiClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        $reflection = new \ReflectionClass(LastFmApiClient::class);
        $client = $reflection->newInstanceWithoutConstructor();

        // Set private properties
        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $apiKeyProperty->setValue($client, 'test-api-key');

        $secretProperty = $reflection->getProperty('secret');
        $secretProperty->setAccessible(true);
        $secretProperty->setValue($client, 'test-secret');

        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($client, require __DIR__ . '/../resources/service.php');

        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($client, new Client(['handler' => $handlerStack]));

        return $client;
    }

    /**
     * Test the correct web application authentication flow
     *
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::generateSignature
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testWebApplicationAuthFlow(): void
    {
        // Mock response for auth.getSession call
        $sessionResponse = [
            'session' => [
                'name' => 'testuser',
                'key' => 'test-session-key-123',
                'subscriber' => 0
            ]
        ];

        $client = $this->createMockedClient([
            new Response(200, [], json_encode($sessionResponse))
        ]);

        // This simulates the correct flow:
        // 1. User is redirected to: https://www.last.fm/api/auth/?api_key=YOUR_KEY&cb=CALLBACK_URL
        // 2. Last.fm generates a token and redirects it back with a token parameter
        // 3. We call auth.getSession with the received token

        $result = $client->authGetSession(['token' => 'token-from-lastfm-callback']);

        $this->assertEquals('testuser', $result['session']['name']);
        $this->assertEquals('test-session-key-123', $result['session']['key']);
    }

    /**
     * Test that authGetToken is still available but not needed for web app flow
     *
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::generateSignature
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testAuthGetTokenStillWorks(): void
    {
        $tokenResponse = [
            'token' => 'generated-token-123'
        ];

        $client = $this->createMockedClient([
            new Response(200, [], json_encode($tokenResponse))
        ]);

        // While not needed for web app flow, this method should still work
        // (e.g., for desktop applications)
        $result = $client->authGetToken();

        $this->assertEquals('generated-token-123', $result['token']);
    }

    /**
     * Test signature generation for auth methods
     *
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient::generateSignature
     */
    public function testSignatureGeneration(): void
    {
        $client = new LastFmApiClient('test-api-key', 'test-secret');

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('generateSignature');
        $method->setAccessible(true);

        // Test signature generation for auth.getSession
        $params = [
            'method' => 'auth.getSession',
            'api_key' => 'test-api-key',
            'token' => 'test-token',
            'format' => 'json' // This should be excluded from the signature
        ];

        $signature = $method->invoke($client, $params);

        // Expected: api_keytest-api-keymethodauth.getSessiontokentest-tokentest-secret
        // MD5 of: api_keytest-api-keymethodauth.getSessiontokentest-tokentest-secret
        $expected = md5('api_keytest-api-keymethodauth.getSessiontokentest-tokentest-secret');

        $this->assertEquals($expected, $signature);
    }

    /**
     * Test complete authenticated client creation
     *
     * @covers \Calliostro\LastFm\ClientFactory::createWithAuth
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient::setSessionKey
     */
    public function testAuthenticatedClientCreation(): void
    {
        $client = ClientFactory::createWithAuth(
            'test-api-key',
            'test-secret',
            'test-session-key'
        );

        $this->assertInstanceOf(LastFmApiClient::class, $client);
    }

    /**
     * Test method name to operation conversion
     *
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     */
    public function testMethodToOperationConversion(): void
    {
        $client = new LastFmApiClient('test-api-key', 'test-secret');

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('convertMethodToOperation');
        $method->setAccessible(true);

        // Test various method name conversions
        $this->assertEquals('track.getInfo', $method->invoke($client, 'trackGetInfo'));
        $this->assertEquals('artist.getTopTracks', $method->invoke($client, 'artistGetTopTracks'));
        $this->assertEquals('user.getRecentTracks', $method->invoke($client, 'userGetRecentTracks'));
        $this->assertEquals('auth.getSession', $method->invoke($client, 'authGetSession'));
    }

    /**
     * Test invalid method name handling
     *
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     */
    public function testInvalidMethodName(): void
    {
        $client = new LastFmApiClient('test-api-key', 'test-secret');

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('convertMethodToOperation');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid method name: invalid');

        $method->invoke($client, 'invalid');
    }

    /**
     * Test non-array response handling
     *
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     */
    public function testNonArrayResponse(): void
    {
        // Mock response that returns a string instead of an array
        $client = $this->createMockedClient([
            new Response(200, [], '"just a string"') // Valid JSON but not an array
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from API');

        $client->trackGetInfo(['artist' => 'Test', 'track' => 'Test']);
    }
}

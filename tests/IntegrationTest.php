<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Calliostro\LastFm\LastFmApiClient;

class IntegrationTest extends TestCase
{
    private function createMockedClient(array $responses): LastFmApiClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        // Override the service configuration to use our mocked handler
        $reflection = new \ReflectionClass(LastFmApiClient::class);
        $constructor = $reflection->getConstructor();

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
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testTrackGetInfoWithMockedResponse(): void
    {
        $mockResponse = [
            'track' => [
                'name' => 'Shape of You',
                'artist' => ['name' => 'Ed Sheeran'],
                'playcount' => '1000000'
            ]
        ];

        $client = $this->createMockedClient([
            new Response(200, [], json_encode($mockResponse))
        ]);

        $result = $client->trackGetInfo([
            'artist' => 'Ed Sheeran',
            'track' => 'Shape of You'
        ]);

        $this->assertEquals('Shape of You', $result['track']['name']);
        $this->assertEquals('Ed Sheeran', $result['track']['artist']['name']);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::generateSignature
     * @covers \Calliostro\LastFm\LastFmApiClient::setSessionKey
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testAuthenticatedCallWithSignature(): void
    {
        $mockResponse = ['status' => 'ok'];

        $client = $this->createMockedClient([
            new Response(200, [], json_encode($mockResponse))
        ]);

        $client->setSessionKey('test-session');

        $result = $client->trackScrobble([
            'artist' => 'Coldplay',
            'track' => 'Viva La Vida',
            'timestamp' => time()
        ]);

        $this->assertEquals('ok', $result['status']);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testErrorHandling(): void
    {
        $errorResponse = [
            'error' => 6,
            'message' => 'No user with that name was found'
        ];

        $client = $this->createMockedClient([
            new Response(200, [], json_encode($errorResponse))
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No user with that name was found');

        $client->userGetInfo(['user' => 'nonexistent']);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testInvalidJsonResponse(): void
    {
        $client = $this->createMockedClient([
            new Response(200, [], 'invalid json')
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $client->trackGetInfo(['artist' => 'Test', 'track' => 'Test']);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     */
    public function testHttpException(): void
    {
        $client = $this->createMockedClient([
            new \GuzzleHttp\Exception\ConnectException('Connection failed', new \GuzzleHttp\Psr7\Request('GET', '/'))
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection failed');

        $client->trackGetInfo(['artist' => 'Test', 'track' => 'Test']);
    }
}

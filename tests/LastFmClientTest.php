<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests;

use PHPUnit\Framework\TestCase;
use Calliostro\LastFm\ClientFactory;
use Calliostro\LastFm\LastFmApiClient;

class LastFmClientTest extends TestCase
{
    /**
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testFactoryCreatesClient(): void
    {
        $client = ClientFactory::create('test-api-key', 'test-secret');

        $this->assertInstanceOf(LastFmApiClient::class, $client);
    }

    /**
     * @covers \Calliostro\LastFm\ClientFactory::createWithAuth
     * @covers \Calliostro\LastFm\LastFmApiClient::setSessionKey
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testFactoryWithAuth(): void
    {
        $client = ClientFactory::createWithAuth('test-api-key', 'test-secret', 'session-key');

        $this->assertInstanceOf(LastFmApiClient::class, $client);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testInvalidOperation(): void
    {
        $client = ClientFactory::create('test-api-key', 'test-secret');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown operation: invalid.method');

        $client->invalidMethod([]);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testMethodToOperationConversion(): void
    {
        $client = ClientFactory::create('test-api-key', 'test-secret');

        // Test that the client has the __call method for magic method handling
        $this->assertTrue(method_exists($client, '__call'));

        // Test invalid method name handling
        $this->expectException(\InvalidArgumentException::class);
        $client->invalidMethodName([]);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::setSessionKey
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testSessionKeyCanBeSet(): void
    {
        $client = ClientFactory::create('test-api-key', 'test-secret');

        // Test that we can set a session key
        $client->setSessionKey('test-session-key');

        // This should not throw an exception
        $this->assertTrue(method_exists($client, 'setSessionKey'));
    }

    /**
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testClientWithCustomOptions(): void
    {
        $options = [
            'timeout' => 30,
            'headers' => ['User-Agent' => 'TestClient/1.0']
        ];

        $client = ClientFactory::create('test-api-key', 'test-secret', $options);

        $this->assertInstanceOf(LastFmApiClient::class, $client);
    }

    /**
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     */
    public function testFactoryAcceptsGuzzleClient(): void
    {
        $guzzleClient = new \GuzzleHttp\Client([
            'timeout' => 45,
            'headers' => ['User-Agent' => 'CustomClient/2.0']
        ]);

        $client = ClientFactory::create('test-api-key', 'test-secret', $guzzleClient);

        $this->assertInstanceOf(LastFmApiClient::class, $client);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     */
    public function testDirectConstructorWithGuzzleClient(): void
    {
        $guzzleClient = new \GuzzleHttp\Client([
            'timeout' => 45,
            'headers' => ['User-Agent' => 'CustomClient/2.0']
        ]);

        $client = new LastFmApiClient('test-api-key', 'test-secret', $guzzleClient);

        $this->assertInstanceOf(LastFmApiClient::class, $client);
    }
}

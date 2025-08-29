<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests;

use PHPUnit\Framework\TestCase;
use Calliostro\LastFm\ClientFactory;
use Calliostro\LastFm\LastFmApiClient;

class ServiceDescriptorTest extends TestCase
{
    /**
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testFactoryCreatesLastFmClient(): void
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
        $client = ClientFactory::createWithAuth(
            'test-api-key',
            'test-secret',
            'test-session-key'
        );

        $this->assertInstanceOf(LastFmApiClient::class, $client);
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testClientHasMagicCallMethod(): void
    {
        $client = ClientFactory::create('test-api-key', 'test-secret');

        // Test that the client has the __call method for dynamic method calls
        $this->assertTrue(method_exists($client, '__call'));
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testInvalidMethodThrowsException(): void
    {
        $client = ClientFactory::create('test-api-key', 'test-secret');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown operation: invalid.method');

        $client->invalidMethod([]);
    }

    /**
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient
     */
    public function testFactoryWithOptions(): void
    {
        $options = [
            'timeout' => 60,
            'headers' => ['User-Agent' => 'TestApp/1.0']
        ];

        $client = ClientFactory::create('test-api-key', 'test-secret', $options);

        $this->assertInstanceOf(LastFmApiClient::class, $client);
    }
}

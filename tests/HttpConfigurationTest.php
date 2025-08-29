<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests;

use PHPUnit\Framework\TestCase;
use Calliostro\LastFm\ClientFactory;
use Calliostro\LastFm\LastFmApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

class HttpConfigurationTest extends TestCase
{
    private array $requests = [];
    private MockHandler $mockHandler;
    private HandlerStack $handlerStack;

    protected function setUp(): void
    {
        $this->requests = [];
        $history = Middleware::history($this->requests);

        $this->mockHandler = new MockHandler([
            new Response(200, [], json_encode(['test' => 'response']))
        ]);

        $this->handlerStack = HandlerStack::create($this->mockHandler);
        $this->handlerStack->push($history);
    }

    /**
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     */
    public function testCustomUserAgentViaOptions(): void
    {
        $client = ClientFactory::create('test-api-key', 'test-secret', [
            'handler' => $this->handlerStack,
            'headers' => [
                'User-Agent' => 'TestApp/1.0 (+https://test.com)',
            ]
        ]);

        $client->trackGetInfo(['artist' => 'Test', 'track' => 'Test']);

        $this->assertCount(1, $this->requests);
        $request = $this->requests[0]['request'];
        $this->assertEquals(
            'TestApp/1.0 (+https://test.com)',
            $request->getHeaderLine('User-Agent')
        );
    }

    /**
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     */
    public function testCustomUserAgentViaGuzzleClient(): void
    {
        $guzzleClient = new Client([
            'handler' => $this->handlerStack,
            'headers' => [
                'User-Agent' => 'GuzzleApp/2.0 (+https://guzzle.test)',
                'X-Custom' => 'custom-value'
            ]
        ]);

        $client = ClientFactory::create('test-api-key', 'test-secret', $guzzleClient);
        $client->artistGetInfo(['artist' => 'Test']);

        $this->assertCount(1, $this->requests);
        $request = $this->requests[0]['request'];

        $this->assertEquals(
            'GuzzleApp/2.0 (+https://guzzle.test)',
            $request->getHeaderLine('User-Agent')
        );

        $this->assertEquals(
            'custom-value',
            $request->getHeaderLine('X-Custom')
        );
    }

    /**
     * @covers \Calliostro\LastFm\ClientFactory::create
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     */
    public function testDefaultUserAgent(): void
    {
        $client = ClientFactory::create('test-api-key', 'test-secret', [
            'handler' => $this->handlerStack,
        ]);

        $client->chartGetTopArtists(['limit' => 10]);

        $this->assertCount(1, $this->requests);
        $request = $this->requests[0]['request'];

        $this->assertEquals(
            'LastFmClient/1.0 (+https://github.com/calliostro/lastfm-client)',
            $request->getHeaderLine('User-Agent')
        );
    }

    /**
     * @covers \Calliostro\LastFm\LastFmApiClient::__construct
     * @covers \Calliostro\LastFm\LastFmApiClient::__call
     * @covers \Calliostro\LastFm\LastFmApiClient::callOperation
     * @covers \Calliostro\LastFm\LastFmApiClient::convertMethodToOperation
     */
    public function testDirectConstructorWithGuzzleClient(): void
    {
        $guzzleClient = new Client([
            'handler' => $this->handlerStack,
            'headers' => [
                'User-Agent' => 'DirectConstruct/1.0',
            ]
        ]);

        $client = new LastFmApiClient('test-api-key', 'test-secret', $guzzleClient);
        $client->userGetInfo(['user' => 'testuser']);

        $this->assertCount(1, $this->requests);
        $request = $this->requests[0]['request'];

        $this->assertEquals(
            'DirectConstruct/1.0',
            $request->getHeaderLine('User-Agent')
        );
    }
}

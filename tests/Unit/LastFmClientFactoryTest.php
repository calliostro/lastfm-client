<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use Calliostro\LastFm\LastFmClient;
use Calliostro\LastFm\LastFmClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(LastFmClientFactory::class)]
#[UsesClass(LastFmClient::class)]
final class LastFmClientFactoryTest extends UnitTestCase
{
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
        $mockHandler = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'session' => ['name' => 'test-user'] // Missing 'key' field
            ]) ?: '')
        ]);
        $handlerStack = \GuzzleHttp\HandlerStack::create($mockHandler);
        $guzzleClient = new \GuzzleHttp\Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://ws.audioscrobbler.com/2.0/'
        ]);
        $mockAuthHelper = new \Calliostro\LastFm\AuthHelper('test-api-key', 'test-secret', $guzzleClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Session key not found in response');

        LastFmClientFactory::createWithMobileAuth('test-api-key', 'test-secret', 'test-user', 'test-pass', [], $mockAuthHelper);
    }

    /**
     * Create a mock AuthHelper that returns a successful session
     */
    private function createMockAuthHelper(): \Calliostro\LastFm\AuthHelper
    {
        $mockHandler = new \GuzzleHttp\Handler\MockHandler([
            // Multiple responses for getMobileSession calls - used in test
            new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'session' => ['key' => 'test-session-key']
            ]) ?: ''),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'session' => ['key' => 'test-session-key']
            ]) ?: ''),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'session' => ['key' => 'test-session-key']
            ]) ?: '')
        ]);

        $handlerStack = \GuzzleHttp\HandlerStack::create($mockHandler);
        $guzzleClient = new \GuzzleHttp\Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://ws.audioscrobbler.com/2.0/'
        ]);

        return new \Calliostro\LastFm\AuthHelper('test-api-key', 'test-secret', $guzzleClient);
    }
}

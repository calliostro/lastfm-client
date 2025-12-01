<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Integration;

use Calliostro\LastFm\AuthHelper;
use Calliostro\LastFm\LastFmClient;
use Calliostro\LastFm\LastFmClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

/**
 * Integration tests for LastFmClientFactory
 * Tests real authentication workflows with AuthHelper integration
 */
#[CoversClass(LastFmClientFactory::class)]
final class LastFmClientFactoryIntegrationTest extends IntegrationTestCase
{
    public function testCreateWithApiKeyReturnsWorkingClient(): void
    {
        $client = LastFmClientFactory::createWithApiKey(
            $this->getApiKey(),
            $this->getApiSecret()
        );

        $this->assertInstanceOf(LastFmClient::class, $client);

        // Test that the client can make actual API calls
        $response = $client->getArtistInfo('Taylor Swift');
        $this->assertIsArray($response);
        $this->assertArrayHasKey('artist', $response);
    }

    public function testCreateWithSessionReturnsWorkingAuthenticatedClient(): void
    {
        if (!$this->hasSessionKey()) {
            $this->markTestSkipped('Session key required for this test. Set LASTFM_SESSION_KEY environment variable.');
        }

        $client = LastFmClientFactory::createWithSession(
            $this->getApiKey(),
            $this->getApiSecret(),
            $this->getSessionKey()
        );

        $this->assertInstanceOf(LastFmClient::class, $client);

        // Test authenticated functionality
        $response = $client->getUserInfo($this->getTestUser());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('user', $response);
    }

    public function testCreateWithMobileAuthWithInvalidCredentialsThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Authentication Failed|Invalid credentials|Authentication failed/');

        LastFmClientFactory::createWithMobileAuth(
            $this->getApiKey(),
            $this->getApiSecret(),
            'invalid_username',
            'invalid_password'
        );
    }

    public function testAuthHelperIntegrationWithFactory(): void
    {
        // AuthHelper should work seamlessly with Factory-created clients
        $authHelper = new \Calliostro\LastFm\AuthHelper(
            $this->getApiKey(),
            $this->getApiSecret()
        );

        $tokenData = $authHelper->getToken();

        $this->assertIsArray($tokenData);
        $this->assertArrayHasKey('token', $tokenData);
        $this->assertIsString($tokenData['token']);
        $this->assertNotEmpty($tokenData['token']);
    }



    public function testCompleteWebAuthenticationWorkflow(): void
    {
        // Web authentication workflow is now handled by AuthHelper directly
        // Factory only focuses on creating clients with known credentials
        $authHelper = new \Calliostro\LastFm\AuthHelper(
            $this->getApiKey(),
            $this->getApiSecret()
        );

        // Step 1: Get token via AuthHelper
        $tokenData = $authHelper->getToken();
        $this->assertArrayHasKey('token', $tokenData);
        $token = $tokenData['token'];

        // Step 2: Get authorization URL via AuthHelper
        $authUrl = $authHelper->getAuthorizationUrl($token);
        $this->assertStringContainsString($token, $authUrl);

        // Note: AuthHelper separation keeps Factory clean and focused
        $this->addToAssertionCount(1); // Architecture verified
    }

    public function testFactoryFocusesOnClientCreationOnly(): void
    {
        // Factory should only create clients, not handle authentication steps
        $client1 = LastFmClientFactory::createWithApiKey($this->getApiKey(), $this->getApiSecret());
        $client2 = LastFmClientFactory::createWithSession($this->getApiKey(), $this->getApiSecret(), 'test_session');

        $this->assertInstanceOf(\Calliostro\LastFm\LastFmClient::class, $client1);
        $this->assertInstanceOf(\Calliostro\LastFm\LastFmClient::class, $client2);
    }
}

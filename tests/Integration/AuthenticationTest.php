<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Integration;

use Calliostro\LastFm\AuthHelper;
use Calliostro\LastFm\ConfigCache;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Integration tests for Last.fm authentication flow and methods
 */
#[CoversClass(AuthHelper::class)]
final class AuthenticationTest extends IntegrationTestCase
{
    private AuthHelper $authHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authHelper = new AuthHelper($this->getApiKey(), $this->getApiSecret());
    }

    // =====================================
    // TOKEN GENERATION
    // =====================================

    public function testGetTokenReturnsValidToken(): void
    {
        $response = $this->authHelper->getToken();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('token', $response);
        $this->assertIsString($response['token']);
        $this->assertNotEmpty($response['token']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $response['token']);
    }

    public function testGetAuthorizationUrlReturnsValidUrl(): void
    {
        $tokenResponse = $this->authHelper->getToken();
        $token = $tokenResponse['token'];

        $url = $this->authHelper->getAuthorizationUrl($token);

        $this->assertIsString($url);
        $this->assertStringStartsWith('https://www.last.fm/api/auth/', $url);
        $this->assertStringContainsString('api_key=' . $this->getApiKey(), $url);
        $this->assertStringContainsString('token=' . $token, $url);

        // Verify URL is properly formatted
        $parsedUrl = parse_url($url);
        $this->assertNotFalse($parsedUrl, 'URL should be valid');
        $this->assertEquals('www.last.fm', $parsedUrl['host'] ?? '');
        $this->assertEquals('/api/auth/', $parsedUrl['path'] ?? '');

        parse_str($parsedUrl['query'] ?? '', $queryParams);
        $this->assertEquals($this->getApiKey(), $queryParams['api_key']);
        $this->assertEquals($token, $queryParams['token']);
    }

    // =====================================
    // SIGNATURE GENERATION
    // =====================================

    public function testGenerateSignatureWithRealParameters(): void
    {
        $params = [
            'method' => 'auth.getToken',
            'api_key' => $this->getApiKey(),
        ];

        $signature = $this->authHelper->generateSignature($params);

        $this->assertIsString($signature);
        $this->assertEquals(32, strlen($signature));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $signature);

        // Verify signature is consistent
        $signature2 = $this->authHelper->generateSignature($params);
        $this->assertEquals($signature, $signature2);
    }

    public function testAddAuthParamsWithRealCredentials(): void
    {
        $params = [
            'method' => 'track.getInfo',
            'artist' => 'Dua Lipa',
            'track' => 'Physical',
        ];

        $result = $this->authHelper->addAuthParams($params);

        $this->assertArrayHasKey('api_key', $result);
        $this->assertArrayHasKey('api_sig', $result);
        $this->assertEquals($this->getApiKey(), $result['api_key']);
        $this->assertEquals('track.getInfo', $result['method']);
        $this->assertEquals('Dua Lipa', $result['artist']);
        $this->assertEquals('Physical', $result['track']);

        // Verify signature is valid
        $this->assertIsString($result['api_sig']);
        $this->assertEquals(32, strlen($result['api_sig']));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $result['api_sig']);
    }

    public function testAddAuthParamsWithSession(): void
    {
        if (!$this->hasSessionKey()) {
            $this->markTestSkipped('Session key not available for session auth test.');
        }

        $params = [
            'method' => 'track.love',
            'artist' => 'Dua Lipa',
            'track' => 'Physical',
        ];

        $result = $this->authHelper->addAuthParams($params, $this->getSessionKey());

        $this->assertArrayHasKey('api_key', $result);
        $this->assertArrayHasKey('api_sig', $result);
        $this->assertArrayHasKey('sk', $result);
        $this->assertEquals($this->getApiKey(), $result['api_key']);
        $this->assertEquals($this->getSessionKey(), $result['sk']);

        // Verify signature is valid
        $this->assertIsString($result['api_sig']);
        $this->assertEquals(32, strlen($result['api_sig']));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $result['api_sig']);
    }

    // =====================================
    // CONSTRUCTOR VARIATIONS
    // =====================================

    public function testAuthHelperWithDefaultGuzzleClient(): void
    {
        $authHelper = new AuthHelper($this->getApiKey(), $this->getApiSecret());
        $this->assertInstanceOf(AuthHelper::class, $authHelper);

        // Verify it works by getting a token
        $response = $authHelper->getToken();
        $this->assertArrayHasKey('token', $response);
    }

    public function testAuthHelperWithCustomGuzzleClient(): void
    {
        $config = ConfigCache::get();
        $customClient = new Client([
            'base_uri' => $config['baseUrl'],
            'timeout' => 60,
            'connect_timeout' => 30,
        ]);

        $authHelper = new AuthHelper($this->getApiKey(), $this->getApiSecret(), $customClient);
        $this->assertInstanceOf(AuthHelper::class, $authHelper);

        // Verify it works by getting a token
        $response = $authHelper->getToken();
        $this->assertArrayHasKey('token', $response);
    }

    // =====================================
    // ERROR HANDLING
    // =====================================

    public function testInvalidApiKeyThrowsException(): void
    {
        $authHelper = new AuthHelper('invalid_key', 'invalid_secret');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid API key');

        $authHelper->getToken();
    }

    public function testGetSessionWithInvalidTokenThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unauthorized Token - This token has not been issued');

        $this->authHelper->getSession('invalid_token_123');
    }

    public function testGetMobileSessionWithInvalidCredentialsThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Authentication Failed');

        $this->authHelper->getMobileSession('invalid_user', 'invalid_password');
    }

    // =====================================
    // MOBILE SESSION (if credentials available)
    // =====================================

    public function testGetMobileSessionRequiresValidCredentials(): void
    {
        // This test requires actual Last.fm username/password which we won't have in CI.
        // So we test that it properly throws an exception with invalid credentials.
        $this->expectException(\RuntimeException::class);

        $this->authHelper->getMobileSession('nonexistent_user_12345', 'wrong_password');
    }

    // =====================================
    // SESSION VALIDATION (if session available)
    // =====================================

    public function testSessionKeyValidation(): void
    {
        if (!$this->hasSessionKey()) {
            $this->markTestSkipped('Session key not available for validation test.');
        }

        // Verify the session key format is valid
        $sessionKey = $this->getSessionKey();
        $this->assertIsString($sessionKey);
        $this->assertNotEmpty($sessionKey);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $sessionKey);
    }

    // =====================================
    // INTEGRATION WITH REAL API
    // =====================================

    public function testCompleteAuthWorkflow(): void
    {
        // Step 1: Get token
        $tokenResponse = $this->authHelper->getToken();
        $token = $tokenResponse['token'];

        // Step 2: Get authorization URL
        $authUrl = $this->authHelper->getAuthorizationUrl($token);
        $this->assertStringContainsString($token, $authUrl);

        // Step 3: Verify signature generation works with the token
        $params = [
            'method' => 'auth.getSession',
            'api_key' => $this->getApiKey(),
            'token' => $token,
        ];

        $signature = $this->authHelper->generateSignature($params);
        $this->assertIsString($signature);
        $this->assertEquals(32, strlen($signature));

        // Note: We can't complete the session creation without manual authorization
        // which requires user interaction, so this is as far as we can test automatically
    }

    public function testSignatureConsistency(): void
    {
        $params = [
            'method' => 'track.scrobble',
            'artist' => 'Dua Lipa',
            'track' => 'Physical',
            'timestamp' => (string) time(),
        ];

        // Generate signature multiple times - should be identical
        $sig1 = $this->authHelper->generateSignature($params);
        $sig2 = $this->authHelper->generateSignature($params);
        $sig3 = $this->authHelper->generateSignature($params);

        $this->assertEquals($sig1, $sig2);
        $this->assertEquals($sig2, $sig3);

        // Change a parameter slightly and verify signature changes
        $params['artist'] = 'dua lipa'; // lowercase
        $sig4 = $this->authHelper->generateSignature($params);
        $this->assertNotEquals($sig1, $sig4);
    }

    // =====================================
    // PERFORMANCE AND RELIABILITY
    // =====================================

    public function testMultipleTokenRequests(): void
    {
        // Request multiple tokens to ensure API can handle repeated requests
        $tokens = [];
        for ($i = 0; $i < 3; $i++) {
            $response = $this->authHelper->getToken();
            $tokens[] = $response['token'];

            // Small delay to avoid hitting rate limits
            usleep(100000); // 0.1 seconds
        }

        // All tokens should be different
        $this->assertEquals(3, count(array_unique($tokens)));

        // All tokens should have the correct format
        foreach ($tokens as $token) {
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $token);
        }
    }

    public function testNetworkErrorHandling(): void
    {
        // Create a client with an invalid timeout to simulate network issues
        $faultyClient = new Client([
            'timeout' => 0.001, // Very short timeout
            'base_uri' => 'https://ws.audioscrobbler.com/2.0/',
        ]);

        $authHelper = new AuthHelper($this->getApiKey(), $this->getApiSecret(), $faultyClient);

        $this->expectException(\GuzzleHttp\Exception\ConnectException::class);

        $authHelper->getToken();
    }
}

<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use Calliostro\LastFm\AuthHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(AuthHelper::class)]
final class AuthHelperTest extends UnitTestCase
{
    private AuthHelper $authHelper;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'http://ws.audioscrobbler.com/2.0/'
        ]);

        // Use environment variables if available, otherwise fall back to test values
        $apiKey = $_ENV['LASTFM_API_KEY'] ?? 'test_api_key';
        $secret = $_ENV['LASTFM_SECRET'] ?? 'test_secret';

        $this->authHelper = new AuthHelper($apiKey, $secret, $guzzleClient);
    }

    public function testGetTokenReturnsValidToken(): void
    {
        $expectedToken = 'abc123def456';
        $mockResponse = $this->createMockTokenResponse($expectedToken);

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode($mockResponse))
        );

        $result = $this->authHelper->getToken();

        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($expectedToken, $result['token']);
    }

    public function testGetTokenThrowsExceptionOnApiError(): void
    {
        $this->mockHandler->append(
            new Response(400, [], $this->jsonEncode([
                'error' => 6,
                'message' => 'Invalid API key'
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid API key');

        $this->authHelper->getToken();
    }

    public function testGetTokenThrowsExceptionOnHttpError(): void
    {
        $this->mockHandler->append(
            new RequestException(
                'Connection failed',
                new Request('GET', 'test')
            )
        );

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Connection failed');

        $this->authHelper->getToken();
    }

    public function testGetAuthorizationUrlReturnsValidUrl(): void
    {
        $token = 'abc123def456';
        $apiKey = $_ENV['LASTFM_API_KEY'] ?? 'test_api_key';
        $expectedUrl = 'http://www.last.fm/api/auth/?api_key=' . $apiKey . '&token=' . $token;

        $url = $this->authHelper->getAuthorizationUrl($token);

        $this->assertEquals($expectedUrl, $url);
    }

    public function testGetSessionReturnsValidSession(): void
    {
        $token = 'abc123def456';
        $expectedSessionKey = 'session_key_123';
        $expectedUsername = 'test_user';
        $mockResponse = $this->createMockSessionResponse($expectedUsername, $expectedSessionKey);

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode($mockResponse))
        );

        $result = $this->authHelper->getSession($token);

        $this->assertValidSessionResponse($result);
        $this->assertEquals($expectedUsername, $result['session']['name']);
        $this->assertEquals($expectedSessionKey, $result['session']['key']);
    }

    public function testGetSessionThrowsExceptionOnInvalidToken(): void
    {
        $this->mockHandler->append(
            new Response(400, [], $this->jsonEncode([
                'error' => 14,
                'message' => 'Invalid token'
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->authHelper->getSession('invalid_token');
    }

    public function testGetMobileSessionReturnsValidSession(): void
    {
        $username = 'test_user';
        $password = 'test_password';
        $expectedSessionKey = 'mobile_session_key_123';

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'session' => [
                    'name' => $username,
                    'key' => $expectedSessionKey,
                    'subscriber' => 1
                ]
            ]))
        );

        $result = $this->authHelper->getMobileSession($username, $password);

        $this->assertArrayHasKey('session', $result);
        $this->assertEquals($username, $result['session']['name']);
        $this->assertEquals($expectedSessionKey, $result['session']['key']);
    }

    public function testGetMobileSessionThrowsExceptionOnInvalidCredentials(): void
    {
        $this->mockHandler->append(
            new Response(403, [], $this->jsonEncode([
                'error' => 4,
                'message' => 'Authentication Failed'
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Authentication Failed');

        $this->authHelper->getMobileSession('invalid_user', 'invalid_password');
    }

    public function testGenerateSignatureWithBasicParameters(): void
    {
        $apiKey = $_ENV['LASTFM_API_KEY'] ?? 'test_api_key';
        $secret = $_ENV['LASTFM_SECRET'] ?? 'test_secret';

        $params = [
            'method' => 'auth.getToken',
            'api_key' => $apiKey,
            'format' => 'json'
        ];

        // Expected signature calculation (the format is removed from signature):
        // api_key{$apiKey}methodauth.getToken{$secret}
        $expectedString = 'api_key' . $apiKey . 'methodauth.getToken' . $secret;
        $expectedSignature = md5($expectedString);

        $signature = $this->authHelper->generateSignature($params);

        $this->assertValidApiSignature($signature);
        $this->assertEquals($expectedSignature, $signature);
    }

    public function testGenerateSignatureWithComplexParameters(): void
    {
        $apiKey = $_ENV['LASTFM_API_KEY'] ?? 'test_api_key';

        $params = [
            'method' => 'track.scrobble',
            'api_key' => $apiKey,
            'sk' => 'session_key',
            'artist' => 'Bad Bunny',
            'track' => 'Monaco',
            'timestamp' => '1632947200'
        ];

        // Parameters should be sorted alphabetically by key
        $signature = $this->authHelper->generateSignature($params);

        $this->assertValidApiSignature($signature);
    }

    public function testAddAuthParamsWithoutSession(): void
    {
        $apiKey = $_ENV['LASTFM_API_KEY'] ?? 'test_api_key';

        $params = [
            'method' => 'artist.getInfo',
            'artist' => 'Taylor Swift'
        ];

        $result = $this->authHelper->addAuthParams($params);

        $this->assertArrayHasKey('api_key', $result);
        $this->assertArrayHasKey('api_sig', $result);
        $this->assertEquals($apiKey, $result['api_key']);
        $this->assertEquals('artist.getInfo', $result['method']);
        $this->assertEquals('Taylor Swift', $result['artist']);
        // Note: addAuthParams doesn't add 'format' parameter
    }

    public function testAddAuthParamsWithSession(): void
    {
        $apiKey = $_ENV['LASTFM_API_KEY'] ?? 'test_api_key';

        $params = [
            'method' => 'track.love',
            'artist' => 'Billie Eilish',
            'track' => 'bad guy'
        ];

        $sessionKey = 'session_key_123';

        $result = $this->authHelper->addAuthParams($params, $sessionKey);

        $this->assertArrayHasKey('api_key', $result);
        $this->assertArrayHasKey('api_sig', $result);
        $this->assertArrayHasKey('sk', $result);
        $this->assertEquals($apiKey, $result['api_key']);
        $this->assertEquals($sessionKey, $result['sk']);
        // Note: addAuthParams doesn't add 'format' parameter
    }

    public function testConstructorWithDefaultGuzzleClient(): void
    {
        // Test constructor without providing Guzzle client (uses default)
        $authHelper = new AuthHelper('api_key', 'api_secret');

        $this->assertInstanceOf(AuthHelper::class, $authHelper);

        // Verify the constructor actually initializes properly by calling a method
        $url = $authHelper->getAuthorizationUrl('test_token');
        $this->assertStringContainsString('api_key', $url);
    }

    public function testConstructorWithCustomGuzzleClient(): void
    {
        $customClient = new Client(['timeout' => 30]);
        $authHelper = new AuthHelper('api_key', 'api_secret', $customClient);

        $this->assertInstanceOf(AuthHelper::class, $authHelper);

        // Verify the constructor stores the custom client properly
        $url = $authHelper->getAuthorizationUrl('test_token');
        $this->assertStringContainsString('api_key', $url);
    }

    public function testInvalidJsonResponseThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], 'invalid json response')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->authHelper->getToken();
    }

    public function testMissingTokenInResponseThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'success' => true
                // Missing 'token' field
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Token not found in response');

        $this->authHelper->getToken();
    }

    public function testGetSessionWithHttpError(): void
    {
        $this->mockHandler->append(
            new RequestException(
                'Network error',
                new Request('POST', 'test')
            )
        );

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Network error');

        $this->authHelper->getSession('token123');
    }

    public function testGetMobileSessionWithHttpError(): void
    {
        $this->mockHandler->append(
            new RequestException(
                'Server timeout',
                new Request('POST', 'test')
            )
        );

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Server timeout');

        $this->authHelper->getMobileSession('user', 'password');
    }

    public function testGenerateSignatureWithEmptyParameters(): void
    {
        $secret = $_ENV['LASTFM_SECRET'] ?? 'test_secret';
        $params = [];

        // With empty params, the signature should be MD5 of just the secret
        $expectedSignature = md5($secret);

        $signature = $this->authHelper->generateSignature($params);

        $this->assertEquals($expectedSignature, $signature);
    }

    public function testGenerateSignatureWithSpecialCharacters(): void
    {
        $params = [
            'method' => 'track.scrobble',
            'artist' => 'Björk & Ólafur Arnalds',
            'track' => 'Unión',
            'album' => 'Medúlla'
        ];

        $signature = $this->authHelper->generateSignature($params);

        $this->assertIsString($signature);
        $this->assertEquals(32, strlen($signature));
    }

    public function testGenerateSignatureRemovesFormatAndApiSig(): void
    {
        $params = [
            'method' => 'track.scrobble',
            'artist' => 'Test Artist',
            'api_sig' => 'should_be_removed',
            'format' => 'json'
        ];

        $signature = $this->authHelper->generateSignature($params);

        $this->assertIsString($signature);
        $this->assertEquals(32, strlen($signature));

        // Verify the signature is different from if we included api_sig and format
        $paramsWithoutExclusion = [
            'method' => 'track.scrobble',
            'artist' => 'Test Artist',
            'api_sig' => 'should_be_removed',
            'format' => 'json'
        ];
        $signatureWithInclusion = md5('api_sigshould_be_removedartistTest Artistformatjsonmethodtrack.scrobbletest_secret');

        $this->assertNotEquals($signatureWithInclusion, $signature);
    }

    public function testAddAuthParamsOverwritesExistingApiKey(): void
    {
        $apiKey = $_ENV['LASTFM_API_KEY'] ?? 'test_api_key';

        $params = [
            'method' => 'track.scrobble',
            'api_key' => 'old_key'
        ];

        $result = $this->authHelper->addAuthParams($params);

        $this->assertEquals($apiKey, $result['api_key']);
        $this->assertArrayHasKey('api_sig', $result);
    }

    public function testGetTokenWithNonArrayResponse(): void
    {
        // Test the "Expected array response from API" error path
        $this->mockHandler->append(
            new Response(200, [], '"not_an_array"')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from API');

        $this->authHelper->getToken();
    }

    public function testGetSessionWithNonArrayResponse(): void
    {
        // Test the "Expected array response from API" error path
        $this->mockHandler->append(
            new Response(200, [], '"not_an_array"')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from API');

        $this->authHelper->getSession('token');
    }

    public function testGetSessionMissingSessionInResponse(): void
    {
        // Test the "Session not found in response" error path
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'success' => true
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Session not found in response');

        $this->authHelper->getSession('token');
    }

    public function testGetMobileSessionWithNonArrayResponse(): void
    {
        // Test the "Expected array response from API" error path
        $this->mockHandler->append(
            new Response(200, [], '"not_an_array"')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from API');

        $this->authHelper->getMobileSession('user', 'password');
    }

    public function testGetMobileSessionMissingSessionInResponse(): void
    {
        // Test the "Session not found in response" error path
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'success' => true
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Session not found in response');

        $this->authHelper->getMobileSession('user', 'password');
    }

    public function testGetTokenWithErrorResponseWithoutMessage(): void
    {
        // Test error response where a message is not provided (triggers ?? 'API Error')
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'error' => 6
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('API Error');

        $this->authHelper->getToken();
    }

    public function testGetSessionWithErrorResponseWithoutMessage(): void
    {
        // Test error response where a message is not provided (triggers ?? 'API Error')
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'error' => 14
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('API Error');

        $this->authHelper->getSession('invalid_token');
    }

    public function testGetMobileSessionWithErrorResponseWithoutMessage(): void
    {
        // Test error response where a message is not provided (triggers ?? 'API Error')
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'error' => 4
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('API Error');

        $this->authHelper->getMobileSession('user', 'password');
    }

    public function testGetSessionWithMalformedJsonResponse(): void
    {
        // Test actual malformed JSON to trigger json_last_error_msg()
        $this->mockHandler->append(
            new Response(200, [], '{"incomplete": json}')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response:');

        $this->authHelper->getSession('token');
    }

    public function testGetMobileSessionWithMalformedJsonResponse(): void
    {
        // Test actual malformed JSON to trigger json_last_error_msg()
        $this->mockHandler->append(
            new Response(200, [], '{"incomplete": json}')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response:');

        $this->authHelper->getMobileSession('user', 'password');
    }

    public function testGetMobileSessionMissingSessionKeyInResponse(): void
    {
        // Test the "Session key not found in response" error path
        // Session exists but key is empty/missing
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'session' => [
                    'name' => 'test_user',
                    'subscriber' => 0
                    // 'key' is intentionally missing
                ]
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Session key not found in response');

        $this->authHelper->getMobileSession('user', 'password');
    }
}

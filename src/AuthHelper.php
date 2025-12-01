<?php

declare(strict_types=1);

namespace Calliostro\LastFm;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

/**
 * Authentication helper for Last.fm API
 * Handles API key authentication and session management
 */
final class AuthHelper
{
    private GuzzleClient $client;
    private string $apiKey;
    private string $apiSecret;

    public function __construct(string $apiKey, string $apiSecret, ?GuzzleClient $client = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        if ($client === null) {
            $config = ConfigCache::get();
            $this->client = new GuzzleClient([
                'base_uri' => $config['baseUrl'],
                'headers' => $config['client']['options']['headers']
            ]);
        } else {
            $this->client = $client;
        }
    }

    /**
     * Get authentication token for web authentication
     *
     * @return array{token: string}
     * @throws RuntimeException If an authentication token cannot be obtained
     * @throws GuzzleException If HTTP request fails
     */
    public function getToken(): array
    {
        $params = [
            'method' => 'auth.getToken',
            'api_key' => $this->apiKey,
            'format' => 'json'
        ];

        $params['api_sig'] = $this->generateSignature($params);

        $response = $this->client->get('', ['query' => $params]);

        $body = $response->getBody()->getContents();
        $data = $this->parseJsonResponse($body);

        if (!isset($data['token'])) {
            throw new RuntimeException('Token not found in response');
        }

        return ['token' => $data['token']];
    }

    /**
     * Get the authorization URL for web authentication
     *
     * @param string $token The token obtained from getToken()
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(string $token): string
    {
        return "https://www.last.fm/api/auth/?api_key={$this->apiKey}&token={$token}";
    }

    /**
     * Exchange token for a session key after user authorization
     *
     * @param string $token The authorized token
     * @return array{session: array{name: string, key: string, subscriber: string}}
     * @throws RuntimeException If a session cannot be obtained
     * @throws GuzzleException If HTTP request fails
     */
    public function getSession(string $token): array
    {
        $params = [
            'method' => 'auth.getSession',
            'api_key' => $this->apiKey,
            'token' => $token,
            'format' => 'json'
        ];

        $params['api_sig'] = $this->generateSignature($params);

        $response = $this->client->get('', ['query' => $params]);

        $body = $response->getBody()->getContents();
        $data = $this->parseJsonResponse($body);

        if (!isset($data['session'])) {
            throw new RuntimeException('Session not found in response');
        }

        return $data;
    }

    /**
     * Get a mobile session using username and password
     *
     * @param string $username User's Last.fm username
     * @param string $password User's Last.fm password
     * @return array{session: array{name: string, key: string, subscriber: string}}
     * @throws RuntimeException If a mobile session cannot be obtained
     * @throws GuzzleException If HTTP request fails
     */
    public function getMobileSession(string $username, string $password): array
    {
        $params = [
            'method' => 'auth.getMobileSession',
            'api_key' => $this->apiKey,
            'username' => $username,
            'password' => $password,
            'format' => 'json'
        ];

        $params['api_sig'] = $this->generateSignature($params);

        $response = $this->client->post('', ['form_params' => $params]);

        $body = $response->getBody()->getContents();
        $data = $this->parseJsonResponse($body);

        if (!isset($data['session'])) {
            throw new RuntimeException('Session not found in response');
        }

        if (empty($data['session']['key'])) {
            throw new RuntimeException('Session key not found in response');
        }

        return $data;
    }

    /**
     * Generate API signature for Last.fm requests
     *
     * @param array<string, mixed> $params
     * @return string
     */
    public function generateSignature(array $params): string
    {
        // Remove api_sig and format from params for signature generation
        unset($params['api_sig'], $params['format']);

        // Sort parameters alphabetically by key
        ksort($params);

        // Create signature string
        $sigString = '';
        foreach ($params as $key => $value) {
            $sigString .= $key . $value;
        }
        $sigString .= $this->apiSecret;

        return md5($sigString);
    }

    /**
     * Parse and validate JSON response from Last.fm API
     *
     * @param string $body Raw response body
     * @return array<string, mixed> Parsed response data
     * @throws RuntimeException If JSON is invalid or API returns an error
     */
    private function parseJsonResponse(string $body): array
    {
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new RuntimeException('Expected array response from API');
        }

        if (isset($data['error'])) {
            throw new RuntimeException($data['message'] ?? 'API Error', $data['error']);
        }

        return $data;
    }

    /**
     * Add authentication parameters to request params
     *
     * @param array<string, mixed> $params
     * @param string|null $sessionKey Optional session key for authenticated requests
     * @return array<string, mixed>
     */
    public function addAuthParams(array $params, ?string $sessionKey = null): array
    {
        $params['api_key'] = $this->apiKey;

        if ($sessionKey !== null) {
            $params['sk'] = $sessionKey;
        }

        $params['api_sig'] = $this->generateSignature($params);

        return $params;
    }
}

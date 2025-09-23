<?php

declare(strict_types=1);

namespace Calliostro\LastFm;

use GuzzleHttp\Client as GuzzleClient;
use RuntimeException;

/**
 * Factory for creating Last.fm clients with proper authentication
 * Clean, focused factory with only essential creation methods
 */
final class LastFmClientFactory
{
    /**
     * Create a client authenticated with API key and secret
     * Required for read-only authenticated methods
     *
     * @param string $apiKey Your Last.fm API key
     * @param string $apiSecret Your Last.fm API secret
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function createWithApiKey(
        string $apiKey,
        string $apiSecret,
        array|GuzzleClient $optionsOrClient = []
    ): LastFmClient {
        // If GuzzleClient is passed directly, return it as-is
        if ($optionsOrClient instanceof GuzzleClient) {
            return new LastFmClient($optionsOrClient);
        }

        $config = ConfigCache::get();

        // Last.fm uses the API key in query parameters, not headers
        $clientOptions = array_merge($optionsOrClient, [
            'base_uri' => $config['baseUrl'],
        ]);

        // Create a client and set API credentials
        $client = new LastFmClient(new GuzzleClient($clientOptions));
        $client->setApiCredentials($apiKey, $apiSecret);

        return $client;
    }

    /**
     * Create a client authenticated with a session key
     * Required for write methods like scrobbling, loving tracks, etc.
     *
     * @param string $apiKey Your Last.fm API key
     * @param string $apiSecret Your Last.fm API secret
     * @param string $sessionKey Session key obtained through authentication flow
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function createWithSession(
        string $apiKey,
        string $apiSecret,
        string $sessionKey,
        array|GuzzleClient $optionsOrClient = []
    ): LastFmClient {
        // If GuzzleClient is passed directly, return it as-is
        if ($optionsOrClient instanceof GuzzleClient) {
            return new LastFmClient($optionsOrClient);
        }

        $config = ConfigCache::get();

        $clientOptions = array_merge($optionsOrClient, [
            'base_uri' => $config['baseUrl'],
        ]);

        // Create a client and set authentication credentials
        $client = new LastFmClient(new GuzzleClient($clientOptions));
        $client->setApiCredentials($apiKey, $apiSecret, $sessionKey);

        return $client;
    }

    /**
     * Create LastFmClient with mobile authentication
     *
     * @param string $apiKey Last.fm API key
     * @param string $apiSecret Last.fm API secret
     * @param string $username User's Last.fm username
     * @param string $password User's Last.fm password
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     * @param AuthHelper|null $authHelper Optional AuthHelper for testing
     * @throws RuntimeException If a mobile session cannot be obtained
     * @throws \GuzzleHttp\Exception\GuzzleException If HTTP request fails
     * @internal The $authHelper parameter is for testing purposes only
     */
    public static function createWithMobileAuth(
        string $apiKey,
        string $apiSecret,
        string $username,
        string $password,
        array|GuzzleClient $optionsOrClient = [],
        ?AuthHelper $authHelper = null
    ): LastFmClient {
        // Use provided AuthHelper or create new one
        $authHelper = $authHelper ?? new AuthHelper($apiKey, $apiSecret);
        $sessionData = $authHelper->getMobileSession($username, $password);

        // Create an authenticated client using a session key
        return self::createWithSession(
            $apiKey,
            $apiSecret,
            $sessionData['session']['key'],
            $optionsOrClient
        );
    }

}

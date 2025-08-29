<?php

declare(strict_types=1);

namespace Calliostro\LastFm;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Simple factory for creating Last.fm clients
 */
class ClientFactory
{
    /**
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function create(
        string $apiKey,
        ?string $secret = null,
        array|GuzzleClient $optionsOrClient = []
    ): LastFmApiClient {
        return new LastFmApiClient($apiKey, $secret, $optionsOrClient);
    }

    /**
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function createWithAuth(
        string $apiKey,
        string $secret,
        string $sessionKey,
        array|GuzzleClient $optionsOrClient = []
    ): LastFmApiClient {
        $client = new LastFmApiClient($apiKey, $secret, $optionsOrClient);
        $client->setSessionKey($sessionKey);

        return $client;
    }
}

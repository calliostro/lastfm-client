<?php

declare(strict_types=1);

namespace Calliostro\LastFm;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Factory for creating Last.fm clients with proper authentication and rate-limit handling
 * Clean, focused factory with only essential creation methods
 */
final class LastFmClientFactory
{
    /**
     * Create a basic unauthenticated Last.fm client
     *
     * @param array<string, mixed>|GuzzleClient $optionsOrClient Client options (timeout, auto_retry, max_retries, etc.) or pre-configured Guzzle client
     */
    public static function create(array|GuzzleClient $optionsOrClient = []): LastFmClient
    {
        if ($optionsOrClient instanceof GuzzleClient) {
            return new LastFmClient($optionsOrClient);
        }

        $options = $optionsOrClient;
        self::configureHandler($options);

        $config = ConfigCache::get();
        $clientOptions = array_merge([
            'base_uri' => $config['baseUrl'],
        ], $options);

        return new LastFmClient(new GuzzleClient($clientOptions));
    }

    /**
     * Create a client authenticated with API key and secret
     * Required for read-only authenticated methods
     *
     * @param string $apiKey Your Last.fm API key
     * @param string $apiSecret Your Last.fm API secret
     * @param array<string, mixed>|GuzzleClient $optionsOrClient Guzzle client options (timeout, proxy, auto_retry, etc.) or pre-configured Guzzle client
     */
    public static function createWithApiKey(
        string $apiKey,
        string $apiSecret,
        array|GuzzleClient $optionsOrClient = []
    ): LastFmClient {
        $client = self::create($optionsOrClient);
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
     * @param array<string, mixed>|GuzzleClient $optionsOrClient Guzzle client options (timeout, proxy, auto_retry, etc.) or pre-configured Guzzle client
     */
    public static function createWithSession(
        string $apiKey,
        string $apiSecret,
        string $sessionKey,
        array|GuzzleClient $optionsOrClient = []
    ): LastFmClient {
        $client = self::create($optionsOrClient);
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
     * @param array<string, mixed>|GuzzleClient $optionsOrClient Guzzle client options (timeout, proxy, auto_retry, etc.) or pre-configured Guzzle client
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

        // No additional validation needed: AuthHelper::getMobileSession() already validates
        // the session key exists and throws RuntimeException if missing
        return self::createWithSession(
            $apiKey,
            $apiSecret,
            $sessionData['session']['key'],
            $optionsOrClient
        );
    }

    /**
     * Configures the Guzzle HandlerStack with retry middleware in client options.
     *
     * @param array<string, mixed> $options
     */
    private static function configureHandler(array &$options): void
    {
        if (isset($options['handler']) && $options['handler'] instanceof HandlerStack) {
            return;
        }

        $handler = $options['handler'] ?? null;
        $stack = $handler !== null ? HandlerStack::create($handler) : HandlerStack::create();

        $autoRetry = (bool) ($options['auto_retry'] ?? true);
        $maxRetries = (int) ($options['max_retries'] ?? 3);

        if ($autoRetry && $maxRetries > 0) {
            $stack->push(Middleware::retry(
                static function (
                    int $retries,
                    RequestInterface $request,
                    ?ResponseInterface $response = null,
                    mixed $reason = null,
                ) use ($maxRetries): bool {
                    if ($retries >= $maxRetries) {
                        return false;
                    }

                    if ($reason instanceof ConnectException) {
                        return true;
                    }

                    if ($response === null && $reason instanceof BadResponseException) {
                        $response = $reason->getResponse();
                    }

                    return $response !== null && in_array($response->getStatusCode(), [429, 503], true);
                },
                $options['retry_delay'] ?? static fn (int $retries, ?ResponseInterface $response = null): int => self::defaultRetryDelay($retries, $response),
            ), 'lastfm_retry');
        }

        $options['handler'] = $stack;
    }

    /**
     * Calculates the retry delay in milliseconds.
     * Respects Retry-After header (seconds or HTTP-date) if provided,
     * otherwise applies exponential backoff (1s, 2s, etc.).
     */
    private static function defaultRetryDelay(int $retries, ?ResponseInterface $response = null): int
    {
        if ($response !== null && $response->hasHeader('Retry-After')) {
            $retryAfter = $response->getHeaderLine('Retry-After');
            if (is_numeric($retryAfter) && (int) $retryAfter > 0) {
                return (int) $retryAfter * 1000;
            }

            $time = strtotime($retryAfter);
            if ($time !== false) {
                $diff = $time - time();
                if ($diff > 0) {
                    return $diff * 1000;
                }
            }
        }

        // Exponential backoff: 1000ms for 1st retry, 2000ms for 2nd retry, etc.
        return 1000 * (2 ** ($retries - 1));
    }
}

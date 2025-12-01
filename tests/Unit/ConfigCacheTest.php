<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use Calliostro\LastFm\ConfigCache;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConfigCache::class)]
final class ConfigCacheTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        // Clear the cache after each test to ensure a clean state
        ConfigCache::clear();
    }

    public function testGetReturnsConfigurationArray(): void
    {
        $config = ConfigCache::get();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('baseUrl', $config);
        $this->assertArrayHasKey('operations', $config);
        $this->assertArrayHasKey('client', $config);
    }

    public function testGetReturnsSameInstanceOnMultipleCalls(): void
    {
        $config1 = ConfigCache::get();
        $config2 = ConfigCache::get();

        // Should be identical (same reference due to caching)
        $this->assertSame($config1, $config2);
    }

    public function testConfigContainsExpectedBaseUrl(): void
    {
        $config = ConfigCache::get();

        $this->assertEquals('https://ws.audioscrobbler.com/2.0/', $config['baseUrl']);
    }

    public function testConfigContainsExpectedOperations(): void
    {
        $config = ConfigCache::get();

        $operations = $config['operations'];

        $this->assertIsArray($operations);

        // Test some expected Last.fm operations
        $this->assertArrayHasKey('getArtistInfo', $operations);
        $this->assertArrayHasKey('searchAlbums', $operations);
        $this->assertArrayHasKey('getTrackInfo', $operations);
        $this->assertArrayHasKey('getUserRecentTracks', $operations);

        // Verify operation structure
        $artistOperation = $operations['getArtistInfo'];
        $this->assertIsArray($artistOperation);
        $this->assertArrayHasKey('httpMethod', $artistOperation);
        $this->assertArrayHasKey('parameters', $artistOperation);
        $this->assertEquals('GET', $artistOperation['httpMethod']);
    }

    public function testConfigContainsClientConfiguration(): void
    {
        $config = ConfigCache::get();

        $clientConfig = $config['client'];

        $this->assertIsArray($clientConfig);
        $this->assertArrayHasKey('options', $clientConfig);

        $options = $clientConfig['options'];
        $this->assertArrayHasKey('headers', $options);

        $headers = $options['headers'];
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertStringContainsString('LastFmClient', $headers['User-Agent']);
    }

    public function testClearRemovesCache(): void
    {
        // Get config to initialize cache
        $config1 = ConfigCache::get();

        // Clear cache
        ConfigCache::clear();

        // Get config again - should be a new instance
        $config2 = ConfigCache::get();

        // Content should be identical but different reference
        $this->assertEquals($config1, $config2);

        // Note: We can't easily test different references in static context,
        // but the clear() method should reset the internal state
        $this->assertIsArray($config2);
    }

    public function testConfigContainsValidParameterDefinitions(): void
    {
        $config = ConfigCache::get();
        $operations = $config['operations'];

        // Test getArtistInfo operation parameters
        $artistGetInfo = $operations['getArtistInfo'];
        $parameters = $artistGetInfo['parameters'];

        $this->assertIsArray($parameters);
        $this->assertArrayHasKey('artist', $parameters);
        $this->assertArrayHasKey('mbid', $parameters);

        // Test parameter structure
        $artistParam = $parameters['artist'];
        $this->assertIsArray($artistParam);
        $this->assertArrayHasKey('required', $artistParam);
    }

    public function testConfigContainsHttpMethodsForOperations(): void
    {
        $config = ConfigCache::get();
        $operations = $config['operations'];

        $validHttpMethods = ['GET', 'POST', 'PUT', 'DELETE'];

        foreach ($operations as $operationName => $operation) {
            $this->assertArrayHasKey(
                'httpMethod',
                $operation,
                "Operation '{$operationName}' must have httpMethod"
            );
            $this->assertContains(
                $operation['httpMethod'],
                $validHttpMethods,
                "Operation '{$operationName}' has invalid HTTP method"
            );
        }
    }

    public function testConfigContainsRequiredAuthOperations(): void
    {
        $config = ConfigCache::get();
        $operations = $config['operations'];

        // Some operations should require authentication
        $authRequiredOps = ['scrobbleTrack', 'loveTrack', 'unloveTrack'];

        foreach ($authRequiredOps as $opName) {
            if (isset($operations[$opName])) {
                $this->assertArrayHasKey('requiresAuth', $operations[$opName]);
                $this->assertTrue(
                    $operations[$opName]['requiresAuth'],
                    "Operation '{$opName}' should require authentication"
                );
            }
        }
    }

    public function testConfigParameterLimitsAreValid(): void
    {
        $config = ConfigCache::get();
        $operations = $config['operations'];

        $hasValidations = false;

        foreach ($operations as $operationName => $operation) {
            $parameters = $operation['parameters'] ?? [];

            foreach ($parameters as $paramName => $paramConfig) {
                if (isset($paramConfig['maxLength'])) {
                    $this->assertIsInt($paramConfig['maxLength']);
                    $this->assertGreaterThan(0, $paramConfig['maxLength']);
                    $hasValidations = true;
                }

                if (isset($paramConfig['minLength'])) {
                    $this->assertIsInt($paramConfig['minLength']);
                    $this->assertGreaterThanOrEqual(0, $paramConfig['minLength']);
                    $hasValidations = true;
                }
            }
        }

        // If no validation limits exist, just verify the structure is valid
        if (!$hasValidations) {
            $this->assertIsArray($operations);
            $this->assertNotEmpty($operations);
        }
    }

    public function testConfigServiceFileExists(): void
    {
        // The service.php file should exist and be loadable
        $servicePath = __DIR__ . '/../../resources/service.php';

        $this->assertFileExists($servicePath, 'service.php file must exist');

        // Should return an array when included
        $serviceConfig = include $servicePath;
        $this->assertIsArray($serviceConfig, 'service.php must return an array');
    }

    public function testPerformanceOfCachedAccess(): void
    {
        // First access (cache miss)
        $start1 = microtime(true);
        $config1 = ConfigCache::get();
        $end1 = microtime(true);

        // Second access (cache hit)
        $start2 = microtime(true);
        $config2 = ConfigCache::get();
        $end2 = microtime(true);

        $firstAccessTime = $end1 - $start1;
        $secondAccessTime = $end2 - $start2;

        // Cached access should be significantly faster
        $this->assertLessThan($firstAccessTime, $secondAccessTime);

        // Both should return the same data
        $this->assertEquals($config1, $config2);
    }

    public function testClearAndReloadCycle(): void
    {
        // Load config
        $config1 = ConfigCache::get();
        $this->assertIsArray($config1);

        // Clear and reload
        ConfigCache::clear();
        $config2 = ConfigCache::get();

        // Should have the same content
        $this->assertEquals($config1, $config2);
        $this->assertArrayHasKey('baseUrl', $config2);
        $this->assertArrayHasKey('operations', $config2);
    }
}

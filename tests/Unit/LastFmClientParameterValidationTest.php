<?php

declare(strict_types=1);

namespace Calliostro\LastFm\Tests\Unit;

use Calliostro\LastFm\LastFmClient;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionMethod;

#[CoversClass(LastFmClient::class)]
final class LastFmClientParameterValidationTest extends UnitTestCase
{
    private LastFmClient $client;
    private ReflectionMethod $validateParametersMethod;
    private ReflectionMethod $convertParameterMethod;

    protected function setUp(): void
    {
        $this->client = new LastFmClient();

        $reflection = new ReflectionClass($this->client);
        $this->validateParametersMethod = $reflection->getMethod('validateParameters');
        $this->validateParametersMethod->setAccessible(true);

        $this->convertParameterMethod = $reflection->getMethod('convertParameterToString');
        $this->convertParameterMethod->setAccessible(true);
    }

    #[Test]
    public function validateParameters_WithValidParameterNames_DoesNotThrowException(): void
    {
        $validParams = ['artist' => 'Billie Eilish', 'album' => 'Happier Than Ever', 'limit' => '50'];

        // Should not throw any exception
        $this->validateParametersMethod->invoke($this->client, $validParams);

        $this->assertTrue(true); // If we reach here, no exception was thrown
    }

    #[Test]
    public function validateParameters_WithInvalidParameterName_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name');

        $invalidParams = ['invalid-param-with-dashes' => 'value'];
        $this->validateParametersMethod->invoke($this->client, $invalidParams);
    }

    #[Test]
    public function validateParameters_WithTooManyParameters_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Too many parameters');

        $tooManyParams = [];
        for ($i = 0; $i < 51; $i++) {
            $tooManyParams["param$i"] = "value$i";
        }
        $this->validateParametersMethod->invoke($this->client, $tooManyParams);
    }

    #[Test]
    public function validateParameters_WithVeryLongParameterValue_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Request URI too long');

        $veryLongValue = str_repeat('x', 10000); // Very long value to exceed URI limit
        $this->validateParametersMethod->invoke($this->client, ['artist' => $veryLongValue]);
    }

    #[Test]
    public function convertParameterToString_WithString_ReturnsString(): void
    {
        $result = $this->convertParameterMethod->invoke($this->client, 'Bad Bunny');
        $this->assertEquals('Bad Bunny', $result);
    }

    #[Test]
    public function convertParameterToString_WithInteger_ReturnsStringifiedInteger(): void
    {
        $result = $this->convertParameterMethod->invoke($this->client, 42);
        $this->assertEquals('42', $result);
    }

    #[Test]
    public function convertParameterToString_WithFloat_ReturnsStringifiedFloat(): void
    {
        $result = $this->convertParameterMethod->invoke($this->client, 3.14);
        $this->assertEquals('3.14', $result);
    }

    #[Test]
    public function convertParameterToString_WithBooleanTrue_ReturnsOne(): void
    {
        $result = $this->convertParameterMethod->invoke($this->client, true);
        $this->assertEquals('1', $result);
    }

    #[Test]
    public function convertParameterToString_WithBooleanFalse_ReturnsZero(): void
    {
        $result = $this->convertParameterMethod->invoke($this->client, false);
        $this->assertEquals('0', $result);
    }

    #[Test]
    public function convertParameterToString_WithNull_ReturnsEmptyString(): void
    {
        $result = $this->convertParameterMethod->invoke($this->client, null);
        $this->assertEquals('', $result);
    }

    #[Test]
    public function convertParameterToString_WithArray_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter type');

        $this->convertParameterMethod->invoke($this->client, ['array', 'value']);
    }

    #[Test]
    public function convertParameterToString_WithObject_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter type');

        $this->convertParameterMethod->invoke($this->client, new \stdClass());
    }

    #[Test]
    public function validateParameters_WithEmptyArray_DoesNotThrowException(): void
    {
        // Empty parameter array should be valid
        $this->validateParametersMethod->invoke($this->client, []);

        $this->assertTrue(true); // If we reach here, no exception was thrown
    }
}

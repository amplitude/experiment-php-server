<?php

namespace AmplitudeExperiment\Test\Util;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * PSR-7 / PSR-17 helpers for tests. Uses php-http/discovery so the same
 * test code runs against any installed PSR-7 implementation (Guzzle's
 * psr7, nyholm/psr7, etc.) — required for the CI matrix that exercises
 * multiple PSR-18 stacks.
 */
class Psr7TestUtil
{
    public static function request(string $method, string $uri = 'https://example.test'): RequestInterface
    {
        return Psr17FactoryDiscovery::findRequestFactory()->createRequest($method, $uri);
    }

    /**
     * @param array<string, string> $headers
     */
    public static function response(int $status = 200, array $headers = [], ?string $body = null): ResponseInterface
    {
        $response = Psr17FactoryDiscovery::findResponseFactory()->createResponse($status);
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        if ($body !== null) {
            $stream = Psr17FactoryDiscovery::findStreamFactory()->createStream($body);
            $response = $response->withBody($stream);
        }
        return $response;
    }

    public static function clientException(string $message = 'Simulated transport error'): ClientExceptionInterface
    {
        return new class($message) extends RuntimeException implements ClientExceptionInterface {
        };
    }
}

<?php

namespace AmplitudeExperiment\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Resolves the PSR-18 client and PSR-17 factories used by SDK clients.
 *
 * When the consumer supplies an explicit PSR-18 client, it is used
 * verbatim — the SDK does not auto-wrap with {@link RetryingClient},
 * which avoids layered-retry amplification (N x M total attempts) for
 * users whose own client already implements retry. When no client is
 * supplied, a PSR-18 implementation is discovered and wrapped with
 * {@link RetryingClient} so default users retain v1 retry-on-transport-
 * error behavior.
 */
class HttpClientFactory
{
    public static function resolveClient(?ClientInterface $explicit, ?RetryConfig $retryConfig): ClientInterface
    {
        if ($explicit !== null) {
            return $explicit;
        }
        return new RetryingClient(
            Psr18ClientDiscovery::find(),
            $retryConfig ?? new RetryConfig()
        );
    }

    public static function resolveRequestFactory(?RequestFactoryInterface $explicit): RequestFactoryInterface
    {
        return $explicit ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    public static function resolveStreamFactory(?StreamFactoryInterface $explicit): StreamFactoryInterface
    {
        return $explicit ?? Psr17FactoryDiscovery::findStreamFactory();
    }
}

<?php

namespace AmplitudeExperiment\Http;

use AmplitudeExperiment\Exception\MissingHttpImplementationException;
use Http\Discovery\Exception\DiscoveryFailedException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Resolves the PSR-18 client and PSR-17 factories used by SDK clients.
 *
 * When a consumer supplies an explicit instance, it is used verbatim — no
 * auto-wrap with {@link RetryingClient}, which avoids layered-retry
 * amplification (N x M attempts) for clients that already implement retry.
 * Anything left null is auto-discovered via php-http/discovery; the
 * discovered PSR-18 client is wrapped in {@link RetryingClient} so default
 * users retain v1 retry-on-transport-error behavior.
 *
 * Resolution is eager and shared: a single discovery failure produces one
 * {@link MissingHttpImplementationException} regardless of which slot was
 * the proximate cause, since in practice all three resolutions fail
 * together when no PSR-18 / PSR-17 implementation is installed.
 */
class HttpClientFactory
{
    /**
     * @return array{0: ClientInterface, 1: RequestFactoryInterface, 2: StreamFactoryInterface}
     * @throws MissingHttpImplementationException when no PSR-18 / PSR-17 implementation can be discovered.
     */
    public static function resolveAll(
        ?ClientInterface $client,
        ?RequestFactoryInterface $requestFactory,
        ?RetryConfig $retryConfig
    ): array {
        try {
            $resolvedClient = $client ?? new RetryingClient(
                Psr18ClientDiscovery::find(),
                $retryConfig ?? new RetryConfig()
            );
            $resolvedRequestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
            $resolvedStreamFactory = Psr17FactoryDiscovery::findStreamFactory();
        } catch (DiscoveryFailedException $e) {
            throw new MissingHttpImplementationException(
                'Amplitude Experiment SDK could not discover a PSR-18 / PSR-17 implementation. '
                . 'Install a supported HTTP client or pass instances via the config builder. '
                . 'See: https://github.com/amplitude/experiment-php-server',
                0,
                $e
            );
        }
        return [$resolvedClient, $resolvedRequestFactory, $resolvedStreamFactory];
    }
}

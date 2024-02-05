<?php

namespace AmplitudeExperiment\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Interface for the HTTP clients set in {@link RemoteEvaluationConfig} and {@link LocalEvaluationConfig}.
 */
interface HttpClientInterface
{
    /**
     * Return the underlying PSR HTTP Client
     */
    public function getClient(): ClientInterface;
    /**
     * Return a PSR Request to be sent by the underlying PSR HTTP Client
     * @param string $method The HTTP method to use
     * @param string $uri The URI to send the request to
     * @param string|null $body The body of the request
     */
    public function createRequest(string $method, string $uri, ?string $body = null) : RequestInterface;
}

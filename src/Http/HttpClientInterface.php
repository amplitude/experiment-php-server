<?php

namespace AmplitudeExperiment\Http;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

interface HttpClientInterface
{
    /**
     * Return the underlying PSR HTTP Client
     */
    public function getClient(): ClientInterface;
    /**
     * Return a PSR Request to be sent by the underlying PSR HTTP Client
     */
    public function createRequest(string $method, string $uri, ?string $body = null) : RequestInterface;
}

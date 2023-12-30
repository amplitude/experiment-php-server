<?php

namespace AmplitudeExperiment\Http;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

interface FetchClientInterface
{
    /**
     * return a Psr Client
     */
    public function getClient(): ClientInterface;
    /**
     * return a Psr Request to be sent by the client
     */
    public function createRequest(string $method, string $uri, ?string $body = null) : RequestInterface;
}

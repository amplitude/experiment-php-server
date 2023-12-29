<?php

namespace AmplitudeExperiment\Http;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

interface FetchClientInterface
{
    /**
     * return a Psr ClientInterface
     */
    public function getClient(): ClientInterface;
    /**
     * return a Psr RequestInterface to be sent by the client
     */
    public function createRequest(string $method, string $uri) : RequestInterface;
}

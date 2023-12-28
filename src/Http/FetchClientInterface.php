<?php

namespace AmplitudeExperiment\Http;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

interface FetchClientInterface
{
    public function getClient(): ClientInterface;
    public function createRequest(string $method, string $uri) : RequestInterface;
}

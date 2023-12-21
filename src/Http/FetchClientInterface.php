<?php

namespace AmplitudeExperiment\Http;


use Psr\Http\Client\ClientInterface;

interface FetchClientInterface
{
    public function getClient(): ClientInterface;
    public function createRequest(string $method, string $uri);
}

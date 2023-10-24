<?php

namespace AmplitudeExperiment\Remote;

class RemoteEvaluationConfigBuilder
{
    protected bool $debug = false;
    protected string $serverUrl = 'https://api.lab.amplitude.com';
    protected int $fetchTimeoutMillis = 10000;
    protected int $fetchRetries = 8;
    protected int $fetchRetryBackoffMinMillis = 500;
    protected int $fetchRetryBackoffMaxMillis = 10000;
    protected float $fetchRetryBackoffScalar = 1.5;
    protected int $fetchRetryTimeoutMillis = 10000;

    public function __construct()
    {
    }

    public function debug(bool $debug): RemoteEvaluationConfigBuilder
    {
        $this->debug = $debug;
        return $this;
    }

    public function serverUrl(string $serverUrl): RemoteEvaluationConfigBuilder
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    public function fetchTimeoutMillis(int $fetchTimeoutMillis): RemoteEvaluationConfigBuilder
    {
        $this->fetchTimeoutMillis = $fetchTimeoutMillis;
        return $this;
    }

    public function fetchRetries(int $fetchRetries): RemoteEvaluationConfigBuilder
    {
        $this->fetchRetries = $fetchRetries;
        return $this;
    }

    public function fetchRetryBackoffMinMillis(int $fetchRetryBackoffMinMillis): RemoteEvaluationConfigBuilder
    {
        $this->fetchRetryBackoffMinMillis = $fetchRetryBackoffMinMillis;
        return $this;
    }

    public function fetchRetryBackoffMaxMillis(int $fetchRetryBackoffMaxMillis): RemoteEvaluationConfigBuilder
    {
        $this->fetchRetryBackoffMaxMillis = $fetchRetryBackoffMaxMillis;
        return $this;
    }

    public function fetchRetryBackoffScalar(float $fetchRetryBackoffScalar): RemoteEvaluationConfigBuilder
    {
        $this->fetchRetryBackoffScalar = $fetchRetryBackoffScalar;
        return $this;
    }

    public function fetchRetryTimeoutMillis(int $fetchRetryTimeoutMillis): RemoteEvaluationConfigBuilder
    {
        $this->fetchRetryTimeoutMillis = $fetchRetryTimeoutMillis;
        return $this;
    }

    public function build(): RemoteEvaluationConfig
    {
        return new RemoteEvaluationConfig(
            $this->debug,
            $this->serverUrl,
            $this->fetchTimeoutMillis,
            $this->fetchRetries,
            $this->fetchRetryBackoffMinMillis,
            $this->fetchRetryBackoffMaxMillis,
            $this->fetchRetryBackoffScalar,
            $this->fetchRetryTimeoutMillis,
        );
    }
}

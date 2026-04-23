<?php

namespace AmplitudeExperiment\Test\Logger;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Amplitude\AmplitudeConfig;
use AmplitudeExperiment\Local\LocalEvaluationClient;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use AmplitudeExperiment\Logger\DefaultLogger;
use AmplitudeExperiment\Remote\RemoteEvaluationClient;
use AmplitudeExperiment\Remote\RemoteEvaluationConfig;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;

class ClientDefaultLoggerTest extends TestCase
{
    private const API_KEY = 'test-api-key-ignored';

    /**
     * @dataProvider clientKindProvider
     */
    public function testDefaultsToNullLogger(string $kind): void
    {
        $client = self::buildWithoutLogger($kind);
        $this->assertInstanceOf(NullLogger::class, $this->readLogger($client));
    }

    /**
     * @dataProvider clientKindProvider
     */
    public function testCustomLoggerIsRespected(string $kind): void
    {
        $logger = new DefaultLogger();
        $client = self::buildWithLogger($kind, $logger);
        $this->assertSame($logger, $this->readLogger($client));
    }

    public static function clientKindProvider(): array
    {
        return [
            'Amplitude' => ['Amplitude'],
            'LocalEvaluationClient' => ['LocalEvaluationClient'],
            'RemoteEvaluationClient' => ['RemoteEvaluationClient'],
        ];
    }

    private static function buildWithoutLogger(string $kind): object
    {
        switch ($kind) {
            case 'Amplitude':
                return new Amplitude(self::API_KEY);
            case 'LocalEvaluationClient':
                return new LocalEvaluationClient(self::API_KEY);
            case 'RemoteEvaluationClient':
                return new RemoteEvaluationClient(self::API_KEY);
        }
        throw new \InvalidArgumentException("Unknown client kind: $kind");
    }

    private static function buildWithLogger(string $kind, LoggerInterface $logger): object
    {
        switch ($kind) {
            case 'Amplitude':
                return new Amplitude(
                    self::API_KEY,
                    AmplitudeConfig::builder()->logger($logger)->build()
                );
            case 'LocalEvaluationClient':
                return new LocalEvaluationClient(
                    self::API_KEY,
                    LocalEvaluationConfig::builder()->logger($logger)->build()
                );
            case 'RemoteEvaluationClient':
                return new RemoteEvaluationClient(
                    self::API_KEY,
                    RemoteEvaluationConfig::builder()->logger($logger)->build()
                );
        }
        throw new \InvalidArgumentException("Unknown client kind: $kind");
    }

    private function readLogger(object $client): LoggerInterface
    {
        $prop = (new ReflectionClass($client))->getProperty('logger');
        $prop->setAccessible(true);
        /** @var LoggerInterface $logger */
        $logger = $prop->getValue($client);
        return $logger;
    }
}

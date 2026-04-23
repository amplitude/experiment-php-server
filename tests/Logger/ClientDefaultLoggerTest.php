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
     * @dataProvider clientProvider
     */
    public function testDefaultsToNullLogger(callable $build): void
    {
        $this->assertInstanceOf(NullLogger::class, $this->readLogger($build(null)));
    }

    /**
     * @dataProvider clientProvider
     */
    public function testCustomLoggerIsRespected(callable $build): void
    {
        $logger = new DefaultLogger();
        $this->assertSame($logger, $this->readLogger($build($logger)));
    }

    public static function clientProvider(): array
    {
        return [
            'Amplitude' => [
                fn(?LoggerInterface $l) => new Amplitude(
                    self::API_KEY,
                    $l ? AmplitudeConfig::builder()->logger($l)->build() : null
                ),
            ],
            'LocalEvaluationClient' => [
                fn(?LoggerInterface $l) => new LocalEvaluationClient(
                    self::API_KEY,
                    $l ? LocalEvaluationConfig::builder()->logger($l)->build() : null
                ),
            ],
            'RemoteEvaluationClient' => [
                fn(?LoggerInterface $l) => new RemoteEvaluationClient(
                    self::API_KEY,
                    $l ? RemoteEvaluationConfig::builder()->logger($l)->build() : null
                ),
            ],
        ];
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

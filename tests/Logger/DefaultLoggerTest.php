<?php

namespace AmplitudeExperiment\Test\Logger;

use AmplitudeExperiment\Logger\DefaultLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class DefaultLoggerTest extends TestCase
{
    private string $tmpFile;
    private string $originalErrorLog;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'amp_log_test_');
        $this->originalErrorLog = ini_get('error_log') ?: '';
        ini_set('error_log', $this->tmpFile);
    }

    protected function tearDown(): void
    {
        ini_set('error_log', $this->originalErrorLog);
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    private function getLoggedMessages(): array
    {
        $contents = file_get_contents($this->tmpFile);
        if ($contents === false || $contents === '') {
            return [];
        }
        return array_values(array_filter(
            explode("\n", trim($contents)),
            fn(string $line) => $line !== ''
        ));
    }

    public function testImplementsLoggerInterface(): void
    {
        $this->assertInstanceOf(LoggerInterface::class, new DefaultLogger());
    }

    public function testDefaultMinLevelIsError(): void
    {
        $logger = new DefaultLogger();
        $logger->error('should log');
        $logger->warning('should not log');
        $messages = $this->getLoggedMessages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('should log', $messages[0]);
    }

    /**
     * @dataProvider levelFilteringProvider
     */
    public function testLevelFiltering(string $minLevel, string $logLevel, bool $shouldLog): void
    {
        $logger = new DefaultLogger($minLevel);
        $logger->log($logLevel, 'test message');
        $messages = $this->getLoggedMessages();

        if ($shouldLog) {
            $this->assertCount(1, $messages);
            $this->assertStringContainsString('test message', $messages[0]);
            $this->assertStringContainsString("AmplitudeExperiment.$logLevel", $messages[0]);
        } else {
            $this->assertEmpty($messages);
        }
    }

    public static function levelFilteringProvider(): array
    {
        return [
            'error at error level logs' => [LogLevel::ERROR, LogLevel::ERROR, true],
            'critical at error level logs' => [LogLevel::ERROR, LogLevel::CRITICAL, true],
            'warning at error level filtered' => [LogLevel::ERROR, LogLevel::WARNING, false],
            'debug at error level filtered' => [LogLevel::ERROR, LogLevel::DEBUG, false],
            'debug at debug level logs' => [LogLevel::DEBUG, LogLevel::DEBUG, true],
            'info at debug level logs' => [LogLevel::DEBUG, LogLevel::INFO, true],
            'emergency always logs' => [LogLevel::ERROR, LogLevel::EMERGENCY, true],
            'info at warning level filtered' => [LogLevel::WARNING, LogLevel::INFO, false],
            'warning at warning level logs' => [LogLevel::WARNING, LogLevel::WARNING, true],
        ];
    }

    public function testMessageFormat(): void
    {
        $logger = new DefaultLogger(LogLevel::DEBUG);
        $logger->log(LogLevel::INFO, 'hello world');
        $messages = $this->getLoggedMessages();
        $this->assertCount(1, $messages);
        $this->assertMatchesRegularExpression(
            '/\[\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}\] AmplitudeExperiment\.info: hello world/',
            $messages[0]
        );
    }

    public function testInvalidMinLevelThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DefaultLogger('invalid');
    }

    public function testInvalidLogLevelThrows(): void
    {
        $logger = new DefaultLogger();
        $this->expectException(InvalidArgumentException::class);
        $logger->log('invalid', 'test');
    }

    public function testStringableMessage(): void
    {
        $logger = new DefaultLogger(LogLevel::DEBUG);
        $stringable = new class {
            public function __toString(): string
            {
                return 'stringable message';
            }
        };
        $logger->log(LogLevel::DEBUG, $stringable);
        $messages = $this->getLoggedMessages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('stringable message', $messages[0]);
    }
}

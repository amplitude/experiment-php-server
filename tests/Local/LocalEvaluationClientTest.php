<?php

namespace AmplitudeExperiment\Test\Local;

use AmplitudeExperiment\Experiment;
use AmplitudeExperiment\Local\EvaluateOptions;
use AmplitudeExperiment\Local\LocalEvaluationClient;
use AmplitudeExperiment\Local\LocalEvaluationConfig;
use AmplitudeExperiment\Logger\LogLevel;
use AmplitudeExperiment\User;
use PHPUnit\Framework\TestCase;

class LocalEvaluationClientTest extends TestCase
{
    private string $apiKey = 'server-qz35UwzJ5akieoAdIgzM4m9MIiOLXLoz';
    private User $testUser;
    private LocalEvaluationClient $client;

    public function __construct()
    {
        parent::__construct();
        $this->testUser = User::builder()
            ->userId('test_user')
            ->deviceId('test_device')
            ->build();
        $experiment = new Experiment();
        $config = LocalEvaluationConfig::builder()->logLevel(LogLevel::DEBUG)->build();
        $this->client = $experiment->initializeLocal($this->apiKey, $config);
    }

    public function setUp(): void
    {
        $this->client->refreshFlagConfigs();
    }

    public function testEvaluateAllFlags()
    {
        $variants = $this->client->evaluate($this->testUser);
        $variant = $variants['sdk-local-evaluation-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testEvaluateOneFlagSuccess()
    {
        $variants = $this->client->evaluate($this->testUser, ["sdk-local-evaluation-ci-test"]);
        $variant = $variants['sdk-local-evaluation-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testEvaluateWithDependenciesWithFlagKeysSuccess()
    {
        $variants = $this->client->evaluate($this->testUser, ['sdk-ci-local-dependencies-test']);
        $variant = $variants['sdk-ci-local-dependencies-test'];
        $this->assertEquals("control", $variant->key);
        $this->assertEquals(null, $variant->payload);
    }

    public function testEvaluateWithDependenciesWithUnknownFlagKeysNoVariant()
    {
        $variants = $this->client->evaluate($this->testUser, ['does-not-exist']);
        $this->assertFalse(isset($variants['sdk-ci-local-dependencies-test']));
    }

    public function testEvaluateWithDependenciesVariantHeldOut()
    {
        $variants = $this->client->evaluate($this->testUser);
        $variant = $variants['sdk-ci-local-dependencies-test-holdout'];
        $this->assertEquals("off", $variant->key);
        $this->assertEquals(null, $variant->payload);
        $this->assertTrue($variant->metadata["default"]);
    }

    public function testGetFlagConfigs()
    {
        $flagConfigs = $this->client->getFlagConfigs();
        $bootstrapClient = new LocalEvaluationClient('', LocalEvaluationConfig::builder()->bootstrap($flagConfigs)->build());
        $variants = $bootstrapClient->evaluate($this->testUser);
        $variant = $variants['sdk-local-evaluation-ci-test'];
        $this->assertEquals("on", $variant->key);
        $this->assertEquals("payload", $variant->payload);
    }

    public function testEvaluateWithTracksExposureTracksNonDefaultVariants()
    {
        // Mock the amplitude client's logEvent method
        $trackedEvents = [];
        $reflection = new \ReflectionClass($this->client);
        $exposureServiceProperty = $reflection->getProperty('exposureService');
        $exposureServiceProperty->setAccessible(true);
        $exposureService = $exposureServiceProperty->getValue($this->client);
        
        $trackingProviderReflection = new \ReflectionClass($exposureService);
        $trackingProviderProperty = $trackingProviderReflection->getProperty('exposureTrackingProvider');
        $trackingProviderProperty->setAccessible(true);
        $trackingProvider = $trackingProviderProperty->getValue($exposureService);
        
        $amplitudeReflection = new \ReflectionClass($trackingProvider);
        $amplitudeProperty = $amplitudeReflection->getProperty('amplitude');
        $amplitudeProperty->setAccessible(true);
        $amplitude = $amplitudeProperty->getValue($trackingProvider);
        
        $amplitudeMock = $this->createMock(get_class($amplitude));
        $amplitudeMock->expects($this->atLeastOnce())
            ->method('logEvent')
            ->willReturnCallback(function ($event) use (&$trackedEvents) {
                $trackedEvents[] = $event;
            });
        $amplitudeProperty->setValue($trackingProvider, $amplitudeMock);
        
        // Perform evaluation with tracksExposure=true
        $options = new EvaluateOptions(null, true);
        $variants = $this->client->evaluate($this->testUser, ['sdk-local-evaluation-ci-test'], $options);
        
        // Verify that logEvent was called
        $this->assertGreaterThan(0, count($trackedEvents), 'Amplitude logEvent should be called when tracksExposure is true');
        
        // Count non-default variants
        $nonDefaultVariants = array_filter($variants, function ($variant) {
            return !($variant->metadata && isset($variant->metadata['default']) && $variant->metadata['default']);
        });
        
        // Verify that we have one event per non-default variant
        $this->assertEquals(count($nonDefaultVariants), count($trackedEvents),
            'Expected ' . count($nonDefaultVariants) . ' exposure events, got ' . count($trackedEvents));
        
        // Verify each event has the correct structure
        $trackedFlagKeys = [];
        foreach ($trackedEvents as $event) {
            $this->assertEquals('[Experiment] Exposure', $event->eventType);
            $this->assertEquals($this->testUser->userId, $event->userId);
            $flagKey = $event->eventProperties['[Experiment] Flag Key'] ?? null;
            $this->assertNotNull($flagKey, 'Event should have flag key');
            $trackedFlagKeys[] = $flagKey;
            // Verify the variant is not default
            $variant = $variants[$flagKey] ?? null;
            $this->assertNotNull($variant, "Variant for {$flagKey} should exist");
            $this->assertFalse($variant->metadata && isset($variant->metadata['default']) && $variant->metadata['default'],
                "Variant for {$flagKey} should not be default");
        }
        
        // Verify all non-default variants were tracked
        $this->assertEquals(array_keys($nonDefaultVariants), $trackedFlagKeys,
            'All non-default variants should be tracked');
    }
}

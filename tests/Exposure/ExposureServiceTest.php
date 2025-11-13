<?php

namespace AmplitudeExperiment\Test\Exposure;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Exposure\DefaultExposureFilter;
use AmplitudeExperiment\Exposure\Exposure;
use AmplitudeExperiment\Exposure\ExposureService;
use AmplitudeExperiment\Exposure\DefaultExposureTrackingProvider;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ExposureServiceTest extends TestCase
{
    public function testLogEventCalledInAmplitude()
    {
        $exposureFilter = new DefaultExposureFilter(new ArrayAdapter(0, true, 0, 100));
        $mockAmp = $this->getMockBuilder(Amplitude::class)
            ->setConstructorArgs([''])
            ->onlyMethods(['logEvent'])
            ->getMock();
        $results = [
            'flag-key-1' => new Variant('on')
        ];
        $exposureTrackingProvider = new DefaultExposureTrackingProvider($mockAmp);
        $service = new ExposureService($exposureTrackingProvider, $exposureFilter);
        $mockAmp->expects($this->once())->method('logEvent');
        $service->track(new Exposure(User::builder()->userId('user')->build(), $results));
    }

    public function testToEventsCreatesOneEventPerFlag()
    {
        $user = User::builder()->userId('user')->deviceId('device')->build();
        $basic = new Variant('control', 'control', null, null, [
            'segmentName' => 'All Other Users',
            'flagType' => 'experiment',
            'flagVersion' => 10,
            'default' => false
        ]);
        $differentValue = new Variant('on', 'control', null, null, [
            'segmentName' => 'All Other Users',
            'flagType' => 'experiment',
            'flagVersion' => 10,
            'default' => false
        ]);
        $default = new Variant('off', null, null, null, [
            'segmentName' => 'All Other Users',
            'flagType' => 'experiment',
            'flagVersion' => 10,
            'default' => true
        ]);
        $mutex = new Variant('slot-1', 'slot-1', null, null, [
            'segmentName' => 'All Other Users',
            'flagType' => 'mutual-exclusion-group',
            'flagVersion' => 10,
            'default' => false
        ]);
        $holdout = new Variant('holdout', 'holdout', null, null, [
            'segmentName' => 'All Other Users',
            'flagType' => 'holdout-group',
            'flagVersion' => 10,
            'default' => false
        ]);
        $partialMetadata = new Variant('on', 'on', null, null, [
            'segmentName' => 'All Other Users',
            'flagType' => 'release'
        ]);
        $emptyMetadata = new Variant('on', 'on');
        $emptyVariant = new Variant();
        $results = [
            'basic' => $basic,
            'different_value' => $differentValue,
            'default' => $default,
            'mutex' => $mutex,
            'holdout' => $holdout,
            'partial_metadata' => $partialMetadata,
            'empty_metadata' => $emptyMetadata,
            'empty_variant' => $emptyVariant
        ];
        $exposure = new Exposure($user, $results);
        $events = $exposure->toEvents();
        // Should exclude default (default=true) only
        // basic, different_value, mutex, holdout, partial_metadata, empty_metadata, empty_variant = 7 events
        $this->assertCount(7, $events);
        
        foreach ($events as $event) {
            $this->assertEquals('[Experiment] Exposure', $event->eventType);
            $this->assertEquals($user->userId, $event->userId);
            $this->assertEquals($user->deviceId, $event->deviceId);
            
            $flagKey = $event->eventProperties['[Experiment] Flag Key'];
            $this->assertArrayHasKey($flagKey, $results);
            $variant = $results[$flagKey];
            
            // Validate event properties
            if ($variant->key) {
                $this->assertEquals($variant->key, $event->eventProperties['[Experiment] Variant']);
            } elseif ($variant->value) {
                $this->assertEquals($variant->value, $event->eventProperties['[Experiment] Variant']);
            }
            if ($variant->metadata) {
                $this->assertEquals($variant->metadata, $event->eventProperties['metadata']);
            }
            
            // Validate user properties
            $flagType = $variant->metadata['flagType'] ?? null;
            if ($flagType === 'mutual-exclusion-group') {
                $this->assertEquals([], $event->userProperties['$set']);
                $this->assertEquals([], $event->userProperties['$unset']);
            } else {
                $isDefault = $variant->metadata['default'] ?? false;
                if ($isDefault) {
                    $this->assertEquals([], $event->userProperties['$set']);
                    $this->assertArrayHasKey("[Experiment] {$flagKey}", $event->userProperties['$unset']);
                } else {
                    if ($variant->key) {
                        $this->assertEquals($variant->key, $event->userProperties['$set']["[Experiment] {$flagKey}"]);
                    } elseif ($variant->value) {
                        $this->assertEquals($variant->value, $event->userProperties['$set']["[Experiment] {$flagKey}"]);
                    }
                    $this->assertEquals([], $event->userProperties['$unset']);
                }
            }
        }
    }
}


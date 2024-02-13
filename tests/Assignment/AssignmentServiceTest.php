<?php

namespace AmplitudeExperiment\Test\Assignment;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Assignment\Assignment;
use AmplitudeExperiment\Assignment\AssignmentFilter;
use AmplitudeExperiment\Assignment\AssignmentService;
use AmplitudeExperiment\Assignment\DefaultAssignmentTrackingProvider;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use PHPUnit\Framework\TestCase;
use const AmplitudeExperiment\Assignment\DAY_MILLIS;
use function AmplitudeExperiment\hashCode;

require_once __DIR__ . '/../../src/Util.php';

class AssignmentServiceTest extends TestCase
{
    public function testAssignmentToEventAsExpected()
    {
        $user = User::builder()->userId('user')->deviceId('device')->build();
        $results = [
            'basic' => new Variant(
                'control', 'control', null, null,
                ['segmentName' => 'All Other Users', 'flagType' => 'experiment', 'flagVersion' => 10, 'default' => false]
            ),
            'different_value' => new Variant(
                'on', 'control', null, null,
                ['segmentName' => 'All Other Users', 'flagType' => 'experiment', 'flagVersion' => 10, 'default' => false]
            ),
            'default' => new Variant(
                'off', null, null, null,
                ['segmentName' => 'All Other Users', 'flagType' => 'experiment', 'flagVersion' => 10, 'default' => true]
            ),
            'mutex' => new Variant(
                'slot-1', 'slot-1', null, null,
                ['segmentName' => 'All Other Users', 'flagType' => 'mutual-exclusion-group', 'flagVersion' => 10, 'default' => false]
            ),
            'holdout' => new Variant('holdout', 'holdout', null, null,
                ['segmentName' => 'All Other Users', 'flagType' => 'holdout-group', 'flagVersion' => 10, 'default' => false]
            ),
            'partial_metadata' => new Variant('on', 'on', null, null,
                ['segmentName' => 'All Other Users', 'flagType' => 'release']
            ),
            'empty_metadata' => new Variant('on', 'on'),
            'empty_variant' => new Variant()
        ];

        $assignment = new Assignment($user, $results, 'apiKey', 10);
        $event = $assignment->toEvent();

        $this->assertEquals($user->userId, $event->userId);
        $this->assertEquals($user->deviceId, $event->deviceId);
        $this->assertEquals('[Experiment] Assignment', $event->eventType);

        $eventProperties = $event->eventProperties;
        $this->assertEquals('control', $eventProperties['basic.variant']);
        $this->assertEquals('v10 rule:All Other Users', $eventProperties['basic.details']);
        $this->assertEquals('on', $eventProperties['different_value.variant']);
        $this->assertEquals('v10 rule:All Other Users', $eventProperties['different_value.details']);
        $this->assertEquals('off', $eventProperties['default.variant']);
        $this->assertEquals('v10 rule:All Other Users', $eventProperties['default.details']);
        $this->assertEquals('slot-1', $eventProperties['mutex.variant']);
        $this->assertEquals('v10 rule:All Other Users', $eventProperties['mutex.details']);
        $this->assertEquals('holdout', $eventProperties['holdout.variant']);
        $this->assertEquals('v10 rule:All Other Users', $eventProperties['holdout.details']);
        $this->assertEquals('on', $eventProperties['partial_metadata.variant']);
        $this->assertEquals('on', $eventProperties['empty_metadata.variant']);

        $userProperties = $event->userProperties;
        $setProperties = $userProperties['$set'];
        $this->assertEquals('control', $setProperties['[Experiment] basic']);
        $this->assertEquals('on', $setProperties['[Experiment] different_value']);
        $this->assertEquals('holdout', $setProperties['[Experiment] holdout']);
        $this->assertEquals('on', $setProperties['[Experiment] partial_metadata']);
        $this->assertEquals('on', $setProperties['[Experiment] empty_metadata']);
        $unsetProperties = $userProperties['$unset'];
        $this->assertEquals('-', $unsetProperties['[Experiment] default']);

        $canonicalization = 'user device basic control default off different_value on empty_metadata on holdout holdout mutex slot-1 partial_metadata on ';
        $expected = "user device " . hashCode($canonicalization) . ' ' . floor($assignment->timestamp / DAY_MILLIS);
        $this->assertEquals($expected, $event->insertId);
        $expectedPayload = json_encode(["api_key" => 'apiKey', "events" => [$event], "options" => ["min_id_length" => 10]]);
        $this->assertEquals($expectedPayload, $assignment->toJSONPayload());
    }

    public function testlogEventCalledInAmplitude()
    {
        $assignmentFilter = new AssignmentFilter(1);
        $mockAmp = $this->getMockBuilder(Amplitude::class)
            ->setConstructorArgs([''])
            ->onlyMethods(['logEvent'])
            ->getMock();
        $results = [
            'flag-key-1' => new Variant('on')
        ];
        $assigmentTrackingProvider = new DefaultAssignmentTrackingProvider($mockAmp);
        $service = new AssignmentService($assigmentTrackingProvider, $assignmentFilter);
        $mockAmp->expects($this->once())->method('logEvent');
        $service->track(new Assignment(User::builder()->userId('user')->build(), $results));
    }
}



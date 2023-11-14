<?php

namespace AmplitudeExperiment\Test\Assignment;


use AmplitudeExperiment\Assignment\Assignment;
use AmplitudeExperiment\Assignment\AssignmentService;
use AmplitudeExperiment\User;
use PHPUnit\Framework\TestCase;
use const AmplitudeExperiment\Assignment\DAY_SECS;
use function AmplitudeExperiment\hashCode;

require_once __DIR__ . '/../../src/Util.php';

class AssignmentServiceTest extends TestCase
{
    public function testAssignmentToEventAsExpected()
    {
        // Mock ExperimentUser and results
        $user = User::builder()->userId('user')->deviceId('device')->build();
        $results = [
            'flag-key-1' => [
                'value' => 'on',
            ],
            'flag-key-2' => [
                'value' => 'control',
                'metadata' => [
                    'default' => true,
                ],
            ],
        ];

        // Create Assignment object
        $assignment = new Assignment($user, $results);

        // Convert Assignment to Event
        $event = AssignmentService::toEvent($assignment);

        // Assertions
        $this->assertEquals($user->userId, $event->userId);
        $this->assertEquals($user->deviceId, $event->deviceId);
        $this->assertEquals('[Experiment] Assignment', $event->eventType);

        $eventProperties = $event->eventProperties;
        $this->assertCount(2, $eventProperties);
        $this->assertEquals('on', $eventProperties['flag-key-1.variant']);
        $this->assertEquals('control', $eventProperties['flag-key-2.variant']);

        $userProperties = $event->userProperties;
        $this->assertCount(2, $userProperties);
        $this->assertCount(1, $userProperties['$set']);
        $this->assertCount(1, $userProperties['$unset']);

        $canonicalization = 'user device flag-key-1 on flag-key-2 control';
        $expected = 'user device ' . hashCode($canonicalization) . ' ' . floor($assignment->timestamp / DAY_SECS);
        $this->assertEquals($expected, $event->insertId);
    }
}



<?php

namespace AmplitudeExperiment\Test\Assignment;

use AmplitudeExperiment\Amplitude\Amplitude;
use AmplitudeExperiment\Assignment\Assignment;
use AmplitudeExperiment\Assignment\DefaultAssignmentFilter;
use AmplitudeExperiment\Assignment\AssignmentService;
use AmplitudeExperiment\Assignment\DefaultAssignmentTrackingProvider;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class AssignmentServiceTest extends TestCase
{
    public function testlogEventCalledInAmplitude()
    {
        $assignmentFilter = new DefaultAssignmentFilter(new ArrayAdapter(0, true, 0, 100));
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



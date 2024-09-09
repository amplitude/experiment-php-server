<?php

namespace AmplitudeExperiment\Test\Assignment;

use AmplitudeExperiment\Assignment\Assignment;
use AmplitudeExperiment\Assignment\DefaultAssignmentFilter;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Util.php';

class AssignmentFilterTest extends TestCase
{
    public function testSingleAssignment()
    {
        $user = User::builder()->userId('user')->build();
        $results = [
            'flag-key-1' => new Variant('on'),
            'flag-key-2' => new Variant('control', null, null, null, ['default' => true])
        ];

        $filter = new DefaultAssignmentFilter(100);
        $assignment = new Assignment($user, $results);
        $this->assertTrue($filter->shouldTrack($assignment));
    }

    public function testDuplicateAssignment()
    {
        $user = User::builder()->userId('user')->build();
        $results = [
            'flag-key-1' => new Variant('on'),
            'flag-key-2' => new Variant('control', null, null, null, ['default' => true])
        ];

        $filter = new DefaultAssignmentFilter(100);
        $assignment1 = new Assignment($user, $results);
        $assignment2 = new Assignment($user, $results);
        $filter->shouldTrack($assignment1);
        $this->assertFalse($filter->shouldTrack($assignment2));
    }

    public function testSameUserDifferentResults()
    {
        $user = User::builder()->userId('user')->build();
        $results1 = [
            'flag-key-1' => new Variant('on'),
            'flag-key-2' => new Variant('control', null, null, null, ['default' => true])
        ];

        $results2 = [
            'flag-key-1' => new Variant('control'),
            'flag-key-2' => new Variant('on')
        ];

        $filter = new DefaultAssignmentFilter(100);
        $assignment1 = new Assignment($user, $results1);
        $assignment2 = new Assignment($user, $results2);
        $this->assertTrue($filter->shouldTrack($assignment1));
        $this->assertTrue($filter->shouldTrack($assignment2));
    }

    public function testSameResultDifferentUser()
    {
        $user1 = User::builder()->userId('user')->build();
        $user2 = User::builder()->userId('different-user')->build();
        $results = [
            'flag-key-1' => new Variant('on'),
            'flag-key-2' => new Variant('control', null, null, null, ['default' => true])
        ];

        $filter = new DefaultAssignmentFilter(100);
        $assignment1 = new Assignment($user1, $results);
        $assignment2 = new Assignment($user2, $results);
        $this->assertTrue($filter->shouldTrack($assignment1));
        $this->assertTrue($filter->shouldTrack($assignment2));
    }

    public function testEmptyResult()
    {
        $user1 = User::builder()->userId('user')->build();
        $user2 = User::builder()->userId('different-user')->build();

        $filter = new DefaultAssignmentFilter(100);
        $assignment1 = new Assignment($user1, []);
        $assignment2 = new Assignment($user1, []);
        $assignment3 = new Assignment($user2, []);
        $this->assertFalse($filter->shouldTrack($assignment1));
        $this->assertFalse($filter->shouldTrack($assignment2));
        $this->assertFalse($filter->shouldTrack($assignment3));
    }

    public function testDuplicateAssignmentsDifferentResultOrdering()
    {
        $user = User::builder()->userId('user')->build();
        $result1 = new Variant('on');

        $result2 =
            new Variant('control', null, null, null, ['default' => true]);

        $results1 = [
            'flag-key-1' => $result1,
            'flag-key-2' => $result2,
        ];

        $results2 = [
            'flag-key-2' => $result2,
            'flag-key-1' => $result1,
        ];

        $filter = new DefaultAssignmentFilter(100);
        $assignment1 = new Assignment($user, $results1);
        $assignment2 = new Assignment($user, $results2);
        $this->assertTrue($filter->shouldTrack($assignment1));
        $this->assertFalse($filter->shouldTrack($assignment2));
    }

    public function testLruReplacement()
    {
        $user1 = User::builder()->userId('user1')->build();
        $user2 = User::builder()->userId('user2')->build();
        $user3 = User::builder()->userId('user3')->build();
        $results = [
            'flag-key-1' => new Variant('on'),
            'flag-key-2' => new Variant('control', null, null, null, ['default' => true])
        ];

        $filter = new DefaultAssignmentFilter(2);
        $assignment1 = new Assignment($user1, $results);
        $assignment2 = new Assignment($user2, $results);
        $assignment3 = new Assignment($user3, $results);
        $this->assertTrue($filter->shouldTrack($assignment1));
        $this->assertTrue($filter->shouldTrack($assignment2));
        $this->assertTrue($filter->shouldTrack($assignment3));
        $this->assertTrue($filter->shouldTrack($assignment1));
    }

    public function testTtlBasedEviction()
    {
        $user1 = User::builder()->userId('user')->build();
        $user2 = User::builder()->userId('different-user')->build();
        $results = [
            'flag-key-1' => new Variant('on'),
            'flag-key-2' => new Variant('control', null, null, null, ['default' => true])
        ];

        $filter = new DefaultAssignmentFilter(100, 1000);
        $assignment1 = new Assignment($user1, $results);
        $assignment2 = new Assignment($user2, $results);
        $this->assertTrue($filter->shouldTrack($assignment1));
        sleep(1);
        $this->assertTrue($filter->shouldTrack($assignment2));
    }
}

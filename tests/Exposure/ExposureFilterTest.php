<?php

namespace AmplitudeExperiment\Test\Exposure;

use AmplitudeExperiment\Exposure\DefaultExposureFilter;
use AmplitudeExperiment\Exposure\Exposure;
use AmplitudeExperiment\User;
use AmplitudeExperiment\Variant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ExposureFilterTest extends TestCase
{
    public function testSingleExposure()
    {
        $filter = new DefaultExposureFilter(new ArrayAdapter(0, true, 0, 100));
        $user = User::builder()->userId('user')->deviceId('device')->build();
        $results = [
            'flag-key-1' => new Variant('on', 'on'),
            'flag-key-2' => new Variant('control', 'control')
        ];
        $exposure = new Exposure($user, $results);
        $this->assertTrue($filter->shouldTrack($exposure));
    }

    public function testDuplicateExposure()
    {
        $filter = new DefaultExposureFilter(new ArrayAdapter(0, true, 0, 100));
        $user = User::builder()->userId('user')->deviceId('device')->build();
        $results = [
            'flag-key-1' => new Variant('on', 'on'),
            'flag-key-2' => new Variant('control', 'control')
        ];
        $exposure1 = new Exposure($user, $results);
        $exposure2 = new Exposure($user, $results);
        $this->assertTrue($filter->shouldTrack($exposure1));
        $this->assertFalse($filter->shouldTrack($exposure2));
    }

    public function testSameUserDifferentResults()
    {
        $filter = new DefaultExposureFilter(new ArrayAdapter(0, true, 0, 100));
        $user = User::builder()->userId('user')->deviceId('device')->build();
        $results1 = [
            'flag-key-1' => new Variant('on', 'on'),
            'flag-key-2' => new Variant('control', 'control')
        ];
        $results2 = [
            'flag-key-1' => new Variant('control', 'control'),
            'flag-key-2' => new Variant('on', 'on')
        ];
        $exposure1 = new Exposure($user, $results1);
        $exposure2 = new Exposure($user, $results2);
        $this->assertTrue($filter->shouldTrack($exposure1));
        $this->assertTrue($filter->shouldTrack($exposure2));
    }

    public function testSameResultsDifferentUser()
    {
        $filter = new DefaultExposureFilter(new ArrayAdapter(0, true, 0, 100));
        $user1 = User::builder()->userId('user')->deviceId('device')->build();
        $user2 = User::builder()->userId('different user')->deviceId('device')->build();
        $results = [
            'flag-key-1' => new Variant('on', 'on'),
            'flag-key-2' => new Variant('control', 'control')
        ];
        $exposure1 = new Exposure($user1, $results);
        $exposure2 = new Exposure($user2, $results);
        $this->assertTrue($filter->shouldTrack($exposure1));
        $this->assertTrue($filter->shouldTrack($exposure2));
    }

    public function testEmptyResults()
    {
        $filter = new DefaultExposureFilter(new ArrayAdapter(0, true, 0, 100));
        $user1 = User::builder()->userId('user')->deviceId('device')->build();
        $user2 = User::builder()->userId('different user')->deviceId('device')->build();
        $exposure1 = new Exposure($user1, []);
        $exposure2 = new Exposure($user1, []);
        $exposure3 = new Exposure($user2, []);
        $this->assertFalse($filter->shouldTrack($exposure1));
        $this->assertFalse($filter->shouldTrack($exposure2));
        $this->assertFalse($filter->shouldTrack($exposure3));
    }

    public function testDuplicateExposuresWithDifferentOrdering()
    {
        $filter = new DefaultExposureFilter(new ArrayAdapter(0, true, 0, 100));
        $user = User::builder()->userId('user')->deviceId('device')->build();
        $results1 = [
            'flag-key-1' => new Variant('on', 'on'),
            'flag-key-2' => new Variant('control', 'control')
        ];
        $results2 = [
            'flag-key-2' => new Variant('control', 'control'),
            'flag-key-1' => new Variant('on', 'on')
        ];
        $exposure1 = new Exposure($user, $results1);
        $exposure2 = new Exposure($user, $results2);
        $this->assertTrue($filter->shouldTrack($exposure1));
        $this->assertFalse($filter->shouldTrack($exposure2));
    }
}


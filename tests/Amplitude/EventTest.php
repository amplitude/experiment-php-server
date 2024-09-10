<?php

declare(strict_types=1);

namespace AmplitudeExperiment\Test\Amplitude;

use AmplitudeExperiment\Amplitude\Event;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    public function testToAndFromArray(): void
    {
        $event = new Event(
            'eventType',
            ['eventProperty' => 'eventValue'],
            ['userProperty' => 'userValue'],
            'userId',
            'deviceId',
            'insertId',
            1234567890
        );

        $this->assertEquals(
            [
                'event_type' => 'eventType',
                'event_properties' => ['eventProperty' => 'eventValue'],
                'user_properties' => ['userProperty' => 'userValue'],
                'user_id' => 'userId',
                'device_id' => 'deviceId',
                'insert_id' => 'insertId',
                'time' => 1234567890
            ],
            $event->toArray()
        );

        $this->assertEquals(
            $event,
            Event::fromArray($event->toArray())
        );
    }
}

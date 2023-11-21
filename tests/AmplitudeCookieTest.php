<?php

namespace AmplitudeExperiment\Test;

use AmplitudeExperiment\AmplitudeCookie;
use Exception;
use PHPUnit\Framework\TestCase;

class AmplitudeCookieTest extends TestCase
{
    public function testCookieName()
    {
        $this->expectException(Exception::class);
        AmplitudeCookie::cookieName('');

        $this->assertEquals('amp_123456', AmplitudeCookie::cookieName('1234567'));
        $this->assertEquals('AMP_1234567890', AmplitudeCookie::cookieName('1234567890', true));
    }

    public function testParse()
    {
        $this->assertEquals(['deviceId' => 'deviceId', 'userId' => null], AmplitudeCookie::parse('deviceId...1f1gkeib1.1f1gkeib1.dv.1ir.20q'));
        $this->assertEquals(['deviceId' => 'deviceId', 'userId' => null], AmplitudeCookie::parse('JTdCJTIyZGV2aWNlSWQlMjIlM0ElMjJkZXZpY2VJZCUyMiU3RA==', true));
        $this->assertEquals([], AmplitudeCookie::parse('invalidcookie', true));
        $this->assertEquals(['deviceId' => 'deviceId', 'userId' => 'test@amplitude.com'], AmplitudeCookie::parse('deviceId.dGVzdEBhbXBsaXR1ZGUuY29t..1f1gkeib1.1f1gkeib1.dv.1ir.20q'));
        $this->assertEquals(['deviceId' => 'deviceId', 'userId' => 'test@amplitude.com'], AmplitudeCookie::parse('JTdCJTIydXNlcklkJTIyJTNBJTIydGVzdCU0MGFtcGxpdHVkZS5jb20lMjIlMkMlMjJkZXZpY2VJZCUyMiUzQSUyMmRldmljZUlkJTIyJTdE', true));
        $this->assertEquals(['deviceId' => 'deviceId', 'userId' => 'c÷>'], AmplitudeCookie::parse('deviceId.Y8O3Pg==..1f1gkeib1.1f1gkeib1.dv.1ir.20q'));
    }

    public function testGenerate()
    {
        $this->assertEquals('deviceId..........', AmplitudeCookie::generate('deviceId'));
        $this->assertEquals('JTdCJTIyZGV2aWNlSWQlMjIlM0ElMjJkZXZpY2VJZCUyMiU3RA==', AmplitudeCookie::generate('deviceId', true));
    }
}


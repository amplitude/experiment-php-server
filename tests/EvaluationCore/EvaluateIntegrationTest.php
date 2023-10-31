<?php

namespace AmplitudeExperiment\Test\EvaluationCore;

use AmplitudeExperiment\EvaluationCore\EvaluationEngine;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class EvaluateIntegrationTest extends TestCase
{
    private EvaluationEngine $engine;
    private $flags;

    /**
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        $this->engine = new EvaluationEngine();
        $this->flags = $this->getFlags('server-NgJxxvg8OGwwBsWVXqyxQbdiflbhvugy');
    }

    public function testTestOff()
    {
        $user = $this->userContext('user_id', 'device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-off'];
        $this->assertEquals('off', $result['key']);
    }

    public function testTestOn()
    {
        $user = $this->userContext('user_id', 'device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-on'];
        $this->assertEquals('on', $result['key']);
    }

    // Opinionated Segment Tests

    public function testTestIndividualInclusionsMatchUserId()
    {
        $user = $this->userContext('user_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-individual-inclusions'];
        $this->assertEquals('on', $result['key']);
        $this->assertEquals('individual-inclusions', $result['metadata']['segmentName']);
    }

    public function testTestIndividualInclusionsMatchDeviceId()
    {
        $user = $this->userContext(null, 'device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-individual-inclusions'];
        $this->assertEquals('on', $result['key']);
        $this->assertEquals('individual-inclusions', $result['metadata']['segmentName']);
    }

    public function testTestIndividualInclusionsNoMatchUserId()
    {
        $user = $this->userContext('not_user_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-individual-inclusions'];
        $this->assertEquals('off', $result['key']);
    }

    public function testTestIndividualInclusionsNoMatchDeviceId()
    {
        $user = $this->userContext(null, 'not_device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-individual-inclusions'];
        $this->assertEquals('off', $result['key']);
    }

    public function testTestFlagDependenciesOn()
    {
        $user = $this->userContext('user_id', 'device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-flag-dependencies-on'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestFlagDependenciesOff()
    {
        $user = $this->userContext('user_id', 'device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-flag-dependencies-off'];
        $this->assertEquals('off', $result['key']);
        $this->assertEquals('flag-dependencies', $result['metadata']['segmentName']);
    }

    public function testTestStickyBucketingOn()
    {
        $user = $this->userContext('user_id', 'device_id', null, ['[Experiment] test-sticky-bucketing' => 'on']);
        $result = $this->engine->evaluate($user, $this->flags)['test-sticky-bucketing'];
        $this->assertEquals('on', $result['key']);
        $this->assertEquals('sticky-bucketing', $result['metadata']['segmentName']);
    }

    public function testTestStickyBucketingOff()
    {
        $user = $this->userContext('user_id', 'device_id', null, ['[Experiment] test-sticky-bucketing' => 'off']);
        $result = $this->engine->evaluate($user, $this->flags)['test-sticky-bucketing'];
        $this->assertEquals('off', $result['key']);
        $this->assertEquals('All Other Users', $result['metadata']['segmentName']);
    }

    public function testTestStickyBucketingNonVariant()
    {
        $user = $this->userContext('user_id', 'device_id', null, ['[Experiment] test-sticky-bucketing' => 'not-a-variant']);
        $result = $this->engine->evaluate($user, $this->flags)['test-sticky-bucketing'];
        $this->assertEquals('off', $result['key']);
        $this->assertEquals('All Other Users', $result['metadata']['segmentName']);
    }

    public function testTestExperiment()
    {
        $user = $this->userContext('user_id', 'device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-experiment'];
        $this->assertEquals('on', $result['key']);
        $this->assertEquals('exp-1', $result['metadata']['experimentKey']);
    }

    public function testTestFlag()
    {
        $user = $this->userContext('user_id', 'device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-flag'];
        $this->assertEquals('on', $result['key']);
        $this->assertArrayNotHasKey('experimentKey', $result['metadata']);
    }

    public function testTestMultipleConditionsAndValuesAllMatch()
    {
        $user = $this->userContext('user_id', 'device_id', null, [
            'key-1' => 'value-1',
            'key-2' => 'value-2',
            'key-3' => 'value-3',
        ]);
        $result = $this->engine->evaluate($user, $this->flags)['test-multiple-conditions-and-values'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestMultipleConditionsAndValuesSomeMatch()
    {
        $user = $this->userContext('user_id', 'device_id', null, [
            'key-1' => 'value-1',
            'key-2' => 'value-2',
        ]);
        $result = $this->engine->evaluate($user, $this->flags)['test-multiple-conditions-and-values'];
        $this->assertEquals('off', $result['key']);
    }

    public function testTestAmplitudePropertyTargeting()
    {
        $user = $this->userContext('user_id', 'device_id', null, ['key-1' => 'value-1']);
        $result = $this->engine->evaluate($user, $this->flags)['test-amplitude-property-targeting'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestCohortTargetingOn()
    {
        $user = $this->userContext(null, null, null, null, ['u0qtvwla', '12345678']);
        $result = $this->engine->evaluate($user, $this->flags)['test-cohort-targeting'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestCohortTargetingOff()
    {
        $user = $this->userContext(null, null, null, null, ['12345678', '87654321']);
        $result = $this->engine->evaluate($user, $this->flags)['test-cohort-targeting'];
        $this->assertEquals('off', $result['key']);
    }

    public function testTestGroupNameTargeting()
    {
        $user = $this->groupContext('org name', 'amplitude');
        $result = $this->engine->evaluate($user, $this->flags)['test-group-name-targeting'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestGroupPropertyTargeting()
    {
        $user = $this->groupContext('org name', 'amplitude', ['org plan' => 'enterprise2']);
        $result = $this->engine->evaluate($user, $this->flags)['test-group-property-targeting'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestAmplitudeIdBucketing()
    {
        $user = $this->userContext(null, null, '1234567890');
        $result = $this->engine->evaluate($user, $this->flags)['test-amplitude-id-bucketing'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestUserIdBucketing()
    {
        $user = $this->userContext('user_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-user-id-bucketing'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestDeviceIdBucketing()
    {
        $user = $this->userContext(null, 'device_id');
        $result = $this->engine->evaluate($user, $this->flags)['test-device-id-bucketing'];
        $this->assertEquals('on', $result['key']);
    }

    public function testTestCustomUserPropertyBucketing()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $result = $this->engine->evaluate($user, $this->flags)['test-custom-user-property-bucketing'];
        $this->assertEquals('on', $result['key']);
    }

    public function testGroupNameBucketing()
    {
        $user = $this->groupContext('org name', 'amplitude');
        $result = $this->engine->evaluate($user, $this->flags)['test-group-name-bucketing'];
        $this->assertEquals('on', $result['key']);
    }

    public function testGroupPropertyBucketing()
    {
        $user = $this->groupContext('org name', 'amplitude', ['org plan' => 'enterprise2']);
        $result = $this->engine->evaluate($user, $this->flags)['test-group-name-bucketing'];
        $this->assertEquals('on', $result['key']);
    }

    public function testOnePercentAllocation()
    {
        $on = 0;
        for ($i = 0; $i < 10000; $i++) {
            $user = $this->userContext(null, (string)($i + 1));
            $result = $this->engine->evaluate($user, $this->flags)['test-1-percent-allocation'];
            if ($result['key'] === 'on') {
                $on++;
            }
        }
        $this->assertEquals(107, $on);
    }

    public function testFiftyPercentAllocation()
    {
        $on = 0;
        for ($i = 0; $i < 10000; $i++) {
            $user = $this->userContext(null, (string)($i + 1));
            $result = $this->engine->evaluate($user, $this->flags)['test-50-percent-allocation'];
            if ($result['key'] === 'on') {
                $on++;
            }
        }
        $this->assertEquals(5009, $on);
    }

    public function testNinetyNinePercentAllocation()
    {
        $on = 0;
        for ($i = 0; $i < 10000; $i++) {
            $user = $this->userContext(null, (string)($i + 1));
            $result = $this->engine->evaluate($user, $this->flags)['test-99-percent-allocation'];
            if ($result['key'] === 'on') {
                $on++;
            }
        }
        $this->assertEquals(9900, $on);
    }

    public function testOnePercentDistribution()
    {
        $control = 0;
        $treatment = 0;
        for ($i = 0; $i < 10000; $i++) {
            $user = $this->userContext(null, (string)($i + 1));
            $result = $this->engine->evaluate($user, $this->flags)['test-1-percent-distribution'];
            if ($result['key'] === 'control') {
                $control++;
            } elseif ($result['key'] === 'treatment') {
                $treatment++;
            }
        }
        $this->assertEquals(106, $control);
        $this->assertEquals(9894, $treatment);
    }

    public function testFiftyPercentDistribution()
    {
        $control = 0;
        $treatment = 0;
        for ($i = 0; $i < 10000; $i++) {
            $user = $this->userContext(null, (string)($i + 1));
            $result = $this->engine->evaluate($user, $this->flags)['test-50-percent-distribution'];
            if ($result['key'] === 'control') {
                $control++;
            } elseif ($result['key'] === 'treatment') {
                $treatment++;
            }
        }
        $this->assertEquals(4990, $control);
        $this->assertEquals(5010, $treatment);
    }

    public function testNinetyNinePercentDistribution()
    {
        $control = 0;
        $treatment = 0;
        for ($i = 0; $i < 10000; $i++) {
            $user = $this->userContext(null, (string)($i + 1));
            $result = $this->engine->evaluate($user, $this->flags)['test-99-percent-distribution'];
            if ($result['key'] === 'control') {
                $control++;
            } elseif ($result['key'] === 'treatment') {
                $treatment++;
            }
        }
        $this->assertEquals(9909, $control);
        $this->assertEquals(91, $treatment);
    }

    public function testMultipleDistributions()
    {
        $a = 0;
        $b = 0;
        $c = 0;
        $d = 0;
        for ($i = 0; $i < 10000; $i++) {
            $user = $this->userContext(null, (string)($i + 1));
            $result = $this->engine->evaluate($user, $this->flags)['test-multiple-distributions'];
            if ($result['key'] === 'a') {
                $a++;
            } elseif ($result['key'] === 'b') {
                $b++;
            } elseif ($result['key'] === 'c') {
                $c++;
            } elseif ($result['key'] === 'd') {
                $d++;
            }
        }
        $this->assertEquals(2444, $a);
        $this->assertEquals(2634, $b);
        $this->assertEquals(2447, $c);
        $this->assertEquals(2475, $d);
    }

    public function testIs()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $result = $this->engine->evaluate($user, $this->flags)['test-is'];
        $this->assertEquals('on', $result['key']);
    }

    public function testIsNot()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $result = $this->engine->evaluate($user, $this->flags)['test-is-not'];
        $this->assertEquals('on', $result['key']);
    }

    public function testContains()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $result = $this->engine->evaluate($user, $this->flags)['test-contains'];
        $this->assertEquals('on', $result['key']);
    }

    public function testDoesNotContain()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $result = $this->engine->evaluate($user, $this->flags)['test-does-not-contain'];
        $this->assertEquals('on', $result['key']);
    }

    public function testLess()
    {
        $user = $this->userContext(null, null, null, ['key' => '-1']);
        $result = $this->engine->evaluate($user, $this->flags)['test-less'];
        $this->assertEquals('on', $result['key']);
    }

    public function testLessOrEqual()
    {
        $user = $this->userContext(null, null, null, ['key' => '0']);
        $result = $this->engine->evaluate($user, $this->flags)['test-less-or-equal'];
        $this->assertEquals('on', $result['key']);
    }

    public function testGreater()
    {
        $user = $this->userContext(null, null, null, ['key' => '1']);
        $result = $this->engine->evaluate($user, $this->flags)['test-greater'];
        $this->assertEquals('on', $result['key']);
    }

    public function testGreaterOrEqual()
    {
        $user = $this->userContext(null, null, null, ['key' => '0']);
        $result = $this->engine->evaluate($user, $this->flags)['test-greater-or-equal'];
        $this->assertEquals('on', $result['key']);
    }

    public function testVersionLess()
    {
        $user = $this->freeformUserContext(['version' => '1.9.0']);
        $result = $this->engine->evaluate($user, $this->flags)['test-version-less'];
        $this->assertEquals('on', $result['key']);
    }

    public function testVersionLessOrEqual()
    {
        $user = $this->freeformUserContext(['version' => '1.10.0']);
        $result = $this->engine->evaluate($user, $this->flags)['test-version-less-or-equal'];
        $this->assertEquals('on', $result['key']);
    }

    public function testVersionGreater()
    {
        $user = $this->freeformUserContext(['version' => '1.10.0']);
        $result = $this->engine->evaluate($user, $this->flags)['test-version-greater'];
        $this->assertEquals('on', $result['key']);
    }

    public function testVersionGreaterOrEqual()
    {
        $user = $this->freeformUserContext(['version' => '1.9.0']);
        $result = $this->engine->evaluate($user, $this->flags)['test-version-greater-or-equal'];
        $this->assertEquals('on', $result['key']);
    }

    public function testSetIs()
    {
        $user = $this->userContext(null, null, null, ['key' => ['1', '2', '3']]);
        $result = $this->engine->evaluate($user, $this->flags)['test-set-is'];
        $this->assertEquals('on', $result['key']);
    }

    public function testSetIsNot()
    {
        $user = $this->userContext(null, null, null, ['key' => ['1', '2']]);
        $result = $this->engine->evaluate($user, $this->flags)['test-set-is-not'];
        $this->assertEquals('on', $result['key']);
    }

    public function testSetContains()
    {
        $user = $this->userContext(null, null, null, ['key' => ['1', '2', '3', '4']]);
        $result = $this->engine->evaluate($user, $this->flags)['test-set-contains'];
        $this->assertEquals('on', $result['key']);
    }

    public function testSetDoesNotContain()
    {
        $user = $this->userContext(null, null, null, ['key' => ['1', '2', '4']]);
        $result = $this->engine->evaluate($user, $this->flags)['test-set-does-not-contain'];
        $this->assertEquals('on', $result['key']);
    }

    public function testSetContainsAny()
    {
        $user = $this->userContext(null, null, null, null, ['u0qtvwla', '12345678']);
        $result = $this->engine->evaluate($user, $this->flags)['test-set-contains-any'];
        $this->assertEquals('on', $result['key']);
    }

    public function testSetDoesNotContainAny()
    {
        $user = $this->userContext(null, null, null, null, ['12345678', '87654321']);
        $result = $this->engine->evaluate($user, $this->flags)['test-set-does-not-contain-any'];
        $this->assertEquals('on', $result['key']);
    }

    public function testGlobMatch()
    {
        $user = $this->userContext(null, null, null, ['key' => '/path/1/2/3/end']);
        $result = $this->engine->evaluate($user, $this->flags)['test-glob-match'];
        $this->assertEquals('on', $result['key']);
    }

    public function testGlobDoesNotMatch()
    {
        $user = $this->userContext(null, null, null, ['key' => '/path/1/2/3']);
        $result = $this->engine->evaluate($user, $this->flags)['test-glob-does-not-match'];
        $this->assertEquals('on', $result['key']);
    }

    public function testIsWithBooleans()
    {
        $user = $this->userContext(null, null, null, ['true' => 'TRUE', 'false' => 'FALSE']);
        $result = $this->engine->evaluate($user, $this->flags)['test-is-with-booleans'];
        $this->assertEquals('on', $result['key']);
        $user = $this->userContext(null, null, null, ['true' => 'True', 'false' => 'False']);
        $result = $this->engine->evaluate($user, $this->flags)['test-is-with-booleans'];
        $this->assertEquals('on', $result['key']);
        $user = $this->userContext(null, null, null, ['true' => 'true', 'false' => 'false']);
        $result = $this->engine->evaluate($user, $this->flags)['test-is-with-booleans'];
        $this->assertEquals('on', $result['key']);
    }

    private function userContext($userId = null, $deviceId = null, $amplitudeId = null, $userProperties = [], $cohortIds = []): array
    {
        return [
            'user' => [
                'user_id' => $userId,
                'device_id' => $deviceId,
                'amplitude_id' => $amplitudeId,
                'user_properties' => $userProperties,
                'cohort_ids' => $cohortIds,
            ],
        ];
    }

    private function freeformUserContext($user): array
    {
        return [
            'user' => $user,
        ];
    }

    private function groupContext($groupType, $groupName, $groupProperties = []): array
    {
        return [
            'groups' => [
                $groupType => [
                    'group_name' => $groupName,
                    'group_properties' => $groupProperties,
                ],
            ],
        ];
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function getFlags($deploymentKey)
    {
        $serverUrl = 'https://api.lab.amplitude.com';
        $client = new Client();

        $response = $client->request('GET', "{$serverUrl}/sdk/v2/flags?eval_mode=remote", [
            'headers' => [
                'Authorization' => "Api-Key $deploymentKey",
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception("Response error {$response->getStatusCode()}");
        }

        return json_decode($response->getBody(), true);
    }
}

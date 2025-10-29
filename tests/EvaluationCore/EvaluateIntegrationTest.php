<?php

namespace AmplitudeExperiment\Test\EvaluationCore;

use AmplitudeExperiment\EvaluationCore\EvaluationEngine;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationFlag;
use AmplitudeExperiment\Variant;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use function AmplitudeExperiment\Flag\createFlagsFromArray;

class EvaluateIntegrationTest extends TestCase
{
    private EvaluationEngine $engine;

    /**
     * @var EvaluationFlag[]
     */
    private array $flags;

    /**
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        $this->engine = new EvaluationEngine();
        $rawFlags = $this->getFlags('server-NgJxxvg8OGwwBsWVXqyxQbdiflbhvugy');
        $this->flags = createFlagsFromArray($rawFlags);
    }

    public function testOff()
    {
        $user = $this->userContext('user_id', 'device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-off'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('off', $variant->key);
    }

    public function testOn()
    {
        $user = $this->userContext('user_id', 'device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-on'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testIndividualInclusionsMatchUserId()
    {
        $user = $this->userContext('user_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-individual-inclusions'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
        $this->assertEquals('individual-inclusions', $variant->metadata['segmentName']);
    }

    public function testIndividualInclusionsMatchDeviceId()
    {
        $user = $this->userContext(null, 'device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-individual-inclusions'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
        $this->assertEquals('individual-inclusions', $variant->metadata['segmentName']);
    }

    public function testIndividualInclusionsNoMatchUserId()
    {
        $user = $this->userContext('not_user_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-individual-inclusions'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('off', $variant->key);
    }

    public function testIndividualInclusionsNoMatchDeviceId()
    {
        $user = $this->userContext(null, 'not_device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-individual-inclusions'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('off', $variant->key);
    }

    public function testFlagDependenciesOn()
    {
        $user = $this->userContext('user_id', 'device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-flag-dependencies-on'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
         $this->assertEquals('on', $variant->key);
    }

    public function testFlagDependenciesOff()
    {
        $user = $this->userContext('user_id', 'device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-flag-dependencies-off'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('off', $variant->key);
        $this->assertEquals('flag-dependencies', $variant->metadata['segmentName']);
    }

    public function testStickyBucketingOn()
    {
        $user = $this->userContext('user_id', 'device_id', null, ['[Experiment] test-sticky-bucketing' => 'on']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-sticky-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
        $this->assertEquals('sticky-bucketing', $variant->metadata['segmentName']);
    }

    public function testStickyBucketingOff()
    {
        $user = $this->userContext('user_id', 'device_id', null, ['[Experiment] test-sticky-bucketing' => 'off']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-sticky-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('off', $variant->key);
        $this->assertEquals('All Other Users', $variant->metadata['segmentName']);
    }

    public function testStickyBucketingNonVariant()
    {
        $user = $this->userContext('user_id', 'device_id', null, ['[Experiment] test-sticky-bucketing' => 'not-a-variant']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-sticky-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('off', $variant->key);
        $this->assertEquals('All Other Users', $variant->metadata['segmentName']);
    }

    public function testExperiment()
    {
        $user = $this->userContext('user_id', 'device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-experiment'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
        $this->assertEquals('exp-1', $variant->metadata['experimentKey']);
    }

    public function testFlag()
    {
        $user = $this->userContext('user_id', 'device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-flag'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
        $this->assertArrayNotHasKey('experimentKey', $variant->metadata);
    }

    public function testMultipleConditionsAndValuesAllMatch()
    {
        $user = $this->userContext('user_id', 'device_id', null, [
            'key-1' => 'value-1',
            'key-2' => 'value-2',
            'key-3' => 'value-3',
        ]);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-multiple-conditions-and-values'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testMultipleConditionsAndValuesSomeMatch()
    {
        $user = $this->userContext('user_id', 'device_id', null, [
            'key-1' => 'value-1',
            'key-2' => 'value-2',
        ]);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-multiple-conditions-and-values'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('off', $variant->key);
    }

    public function testAmplitudePropertyTargeting()
    {
        $user = $this->userContext('user_id', 'device_id', null, ['key-1' => 'value-1']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-amplitude-property-targeting'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testCohortTargetingOn()
    {
        $user = $this->userContext(null, null, null, null, ['u0qtvwla', '12345678']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-cohort-targeting'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testCohortTargetingOff()
    {
        $user = $this->userContext(null, null, null, null, ['12345678', '87654321']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-cohort-targeting'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('off', $variant->key);
    }

    public function testGroupNameTargeting()
    {
        $user = $this->groupContext('org name', 'amplitude');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-group-name-targeting'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testGroupPropertyTargeting()
    {
        $user = $this->groupContext('org name', 'amplitude', ['org plan' => 'enterprise2']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-group-property-targeting'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testAmplitudeIdBucketing()
    {
        $user = $this->userContext(null, null, '1234567890');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-amplitude-id-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testUserIdBucketing()
    {
        $user = $this->userContext('user_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-user-id-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testDeviceIdBucketing()
    {
        $user = $this->userContext(null, 'device_id');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-device-id-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testCustomUserPropertyBucketing()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-custom-user-property-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testGroupNameBucketing()
    {
        $user = $this->groupContext('org name', 'amplitude');
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-group-name-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testGroupPropertyBucketing()
    {
        $user = $this->groupContext('org name', 'amplitude', ['org plan' => 'enterprise2']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-group-name-bucketing'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testOnePercentAllocation()
    {
        $on = 0;
        for ($i = 0; $i < 10000; $i++) {
            $user = $this->userContext(null, (string)($i + 1));
            $results = $this->engine->evaluate($user, $this->flags);
            $result = $results['test-1-percent-allocation'];
            $variant = Variant::convertEvaluationVariantToVariant($result);
            if ($variant->key === 'on') {
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
            $results = $this->engine->evaluate($user, $this->flags);
            $result = $results['test-50-percent-allocation'];
            $variant = Variant::convertEvaluationVariantToVariant($result);
            if ($variant->key === 'on') {
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
            $results = $this->engine->evaluate($user, $this->flags);
            $result = $results['test-99-percent-allocation'];
            $variant = Variant::convertEvaluationVariantToVariant($result);
            if ($variant->key === 'on') {
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
            $results = $this->engine->evaluate($user, $this->flags);
            $result = $results['test-1-percent-distribution'];
            $variant = Variant::convertEvaluationVariantToVariant($result);
            if ($variant->key === 'control') {
                $control++;
            } elseif ($variant->key === 'treatment') {
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
            $results = $this->engine->evaluate($user, $this->flags);
            $result = $results['test-50-percent-distribution'];
            $variant = Variant::convertEvaluationVariantToVariant($result);
            if ($variant->key === 'control') {
                $control++;
            } elseif ($variant->key === 'treatment') {
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
            $results = $this->engine->evaluate($user, $this->flags);
            $result = $results['test-99-percent-distribution'];
            $variant = Variant::convertEvaluationVariantToVariant($result);
            if ($variant->key === 'control') {
                $control++;
            } elseif ($variant->key === 'treatment') {
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
            $results = $this->engine->evaluate($user, $this->flags);
            $result = $results['test-multiple-distributions'];
            $variant = Variant::convertEvaluationVariantToVariant($result);
            if ($variant->key === 'a') {
                $a++;
            } elseif ($variant->key === 'b') {
                $b++;
            } elseif ($variant->key === 'c') {
                $c++;
            } elseif ($variant->key === 'd') {
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
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-is'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testIsNot()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-is-not'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testContains()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-contains'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testDoesNotContain()
    {
        $user = $this->userContext(null, null, null, ['key' => 'value']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-does-not-contain'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testLess()
    {
        $user = $this->userContext(null, null, null, ['key' => '-1']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-less'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testLessOrEqual()
    {
        $user = $this->userContext(null, null, null, ['key' => '0']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-less-or-equal'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testGreater()
    {
        $user = $this->userContext(null, null, null, ['key' => '1']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-greater'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testGreaterOrEqual()
    {
        $user = $this->userContext(null, null, null, ['key' => '0']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-greater-or-equal'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testVersionLess()
    {
        $user = $this->freeformUserContext(['version' => '1.9.0']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-version-less'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testVersionLessOrEqual()
    {
        $user = $this->freeformUserContext(['version' => '1.10.0']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-version-less-or-equal'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testVersionGreater()
    {
        $user = $this->freeformUserContext(['version' => '1.10.0']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-version-greater'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testVersionGreaterOrEqual()
    {
        $user = $this->freeformUserContext(['version' => '1.9.0']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-version-greater-or-equal'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testSetIs()
    {
        $user = $this->userContext(null, null, null, ['key' => ['1', '2', '3']]);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-set-is'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testSetIsNot()
    {
        $user = $this->userContext(null, null, null, ['key' => ['1', '2']]);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-set-is-not'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testSetContains()
    {
        $user = $this->userContext(null, null, null, ['key' => ['1', '2', '3', '4']]);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-set-contains'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testSetDoesNotContain()
    {
        $user = $this->userContext(null, null, null, ['key' => ['1', '2', '4']]);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-set-does-not-contain'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testSetContainsAny()
    {
        $user = $this->userContext(null, null, null, null, ['u0qtvwla', '12345678']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-set-contains-any'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testSetDoesNotContainAny()
    {
        $user = $this->userContext(null, null, null, null, ['12345678', '87654321']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-set-does-not-contain-any'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testGlobMatch()
    {
        $user = $this->userContext(null, null, null, ['key' => '/path/1/2/3/end']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-glob-match'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testGlobDoesNotMatch()
    {
        $user = $this->userContext(null, null, null, ['key' => '/path/1/2/3']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-glob-does-not-match'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
    }

    public function testIsWithBooleans()
    {
        $user = $this->userContext(null, null, null, ['true' => 'TRUE', 'false' => 'FALSE']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-is-with-booleans'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
        $user = $this->userContext(null, null, null, ['true' => 'True', 'false' => 'False']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-is-with-booleans'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
        $user = $this->userContext(null, null, null, ['true' => 'true', 'false' => 'false']);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-is-with-booleans'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
        $user = $this->userContext(null, null, null, ['true' => true, 'false' => false]);
        $results = $this->engine->evaluate($user, $this->flags);
        $result = $results['test-is-with-booleans'];
        $variant = Variant::convertEvaluationVariantToVariant($result);
        $this->assertEquals('on', $variant->key);
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

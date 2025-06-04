<?php

namespace AmplitudeExperiment\Test\EvaluationCore;

use AmplitudeExperiment\EvaluationCore\EvaluationEngine;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationFlag;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationSegment;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationVariant;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationCondition;
use PHPUnit\Framework\TestCase;

class EvaluationEngineTest extends TestCase
{
    private EvaluationEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new EvaluationEngine();
    }

    public function testBooleanMatching()
    {
        $variants = [
            'on' => new EvaluationVariant('on'),
            'off' => new EvaluationVariant('off')
        ];

        // Create test segments for different boolean conditions
        $trueSegment = new EvaluationSegment(
            null,
            [[new EvaluationCondition(
                ['context','user', 'user_properties', 'boolProp'],
                'is',
                ['true']
            )]],
            'on'
        );

        $falseSegment = new EvaluationSegment(
            null,
            [[new EvaluationCondition(
                ['context','user', 'user_properties', 'boolProp'],
                'is',
                ['false']
            )]],
            'off'
        );

        // Default segment for all other users
        $defaultSegment = new EvaluationSegment(
            null,
            null,
            'off'
        );

        $segments = [$trueSegment, $falseSegment, $defaultSegment];
        $flag = new EvaluationFlag('test-bool', $variants, $segments);
        $flags = ['test-bool' => $flag];

        // Test case 1: PHP boolean true
        $context = ['user' => ['user_properties' => ['boolProp' => true]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool']->key);

        // Test case 2: PHP boolean false
        $context = ['user' => ['user_properties' => ['boolProp' => false]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);

        // Test case 3: String 'true'
        $context = ['user' => ['user_properties' => ['boolProp' => 'true']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool']->key);

        // Test case 4: String 'false'
        $context = ['user' => ['user_properties' => ['boolProp' => 'false']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);

        // Test case 5: String 'True' (capitalized)
        $context = ['user' => ['user_properties' => ['boolProp' => 'True']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool']->key);

        // Test case 6: String 'False' (capitalized)
        $context = ['user' => ['user_properties' => ['boolProp' => 'False']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);

        // Test case 7: Non-boolean value - should fall to default segment
        $context = ['user' => ['user_properties' => ['boolProp' => 'not a boolean']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);

        // Test case 8: Missing property - should fall to default segment
        $context = ['user' => ['user_properties' => []]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);

        // Test case 9: Numeric 1 - should NOT match 'true' and fall to default
        $context = ['user' => ['user_properties' => ['boolProp' => 1]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);

        // Test case 10: Numeric 0 - should NOT match 'false' and fall to default
        $context = ['user' => ['user_properties' => ['boolProp' => 0]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);
    }

    public function testBooleanIsNotMatching()
    {
        $variants = [
            'on' => new EvaluationVariant('on'),
            'off' => new EvaluationVariant('off')
        ];

        // Segment for "is not true"
        $notTrueSegment = new EvaluationSegment(
            null,
            [[new EvaluationCondition(
                ['context','user', 'user_properties', 'boolProp'],
                'is not',
                ['true']
            )]],
            'on'
        );

        // Default segment for all other users
        $defaultSegment = new EvaluationSegment(
            null,
            null,
            'off'
        );

        $segments = [$notTrueSegment, $defaultSegment];
        $flag = new EvaluationFlag('test-bool-not', $variants, $segments);
        $flags = ['test-bool-not' => $flag];

        // Test with PHP boolean false - should match 'is not true'
        $context = ['user' => ['user_properties' => ['boolProp' => false]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool-not']->key);

        // Test with string 'false' - should match 'is not true'
        $context = ['user' => ['user_properties' => ['boolProp' => 'false']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool-not']->key);

        // Test with capitalized 'False' - should match 'is not true'
        $context = ['user' => ['user_properties' => ['boolProp' => 'False']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool-not']->key);

        // Test with PHP boolean true - should NOT match 'is not true' and fall to default
        $context = ['user' => ['user_properties' => ['boolProp' => true]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool-not']->key);

        // Test with string 'true' - should NOT match 'is not true' and fall to default
        $context = ['user' => ['user_properties' => ['boolProp' => 'true']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool-not']->key);

        // Test with capitalized 'True' - should NOT match 'is not true' and fall to default
        $context = ['user' => ['user_properties' => ['boolProp' => 'True']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool-not']->key);

        // Test with non-boolean value - should match 'is not true'
        $context = ['user' => ['user_properties' => ['boolProp' => 'not a boolean']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool-not']->key);

        // Test with missing property - should match 'is not true'
        $context = ['user' => ['user_properties' => []]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool-not']->key);
    }

    public function testCaseInsensitiveBooleanMatching()
    {
        $variants = [
            'on' => new EvaluationVariant('on'),
            'off' => new EvaluationVariant('off')
        ];

        // Create segments with mixed case boolean conditions
        $trueSegment = new EvaluationSegment(
            null,
            [[new EvaluationCondition(
                ['context','user', 'user_properties', 'boolProp'],
                'is',
                ['TRUE']
            )]],
            'on'
        );

        $falseSegment = new EvaluationSegment(
            null,
            [[new EvaluationCondition(
                ['context','user', 'user_properties', 'boolProp'],
                'is',
                ['FALSE']
            )]],
            'off'
        );

        // Default segment for all other users
        $defaultSegment = new EvaluationSegment(
            null,
            null,
            'off'
        );

        $segments = [$trueSegment, $falseSegment, $defaultSegment];
        $flag = new EvaluationFlag('test-case-bool', $variants, $segments);
        $flags = ['test-case-bool' => $flag];

        // Test with different case TRUE representations
        $trueCases = ['true', 'True', 'TRUE', 'tRuE'];
        foreach ($trueCases as $testCase) {
            $context = ['user' => ['user_properties' => ['boolProp' => $testCase]]];
            $results = $this->engine->evaluate($context, $flags);
            $this->assertEquals('on', $results['test-case-bool']->key,
                "Failed for TRUE value: " . var_export($testCase, true));
        }

        // Test with PHP boolean true
        $context = ['user' => ['user_properties' => ['boolProp' => true]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-case-bool']->key,
            "Failed for PHP boolean true");

        // Test with different case FALSE representations
        $falseCases = ['false', 'False', 'FALSE', 'fAlSe'];
        foreach ($falseCases as $testCase) {
            $context = ['user' => ['user_properties' => ['boolProp' => $testCase]]];
            $results = $this->engine->evaluate($context, $flags);
            $this->assertEquals('off', $results['test-case-bool']->key,
                "Failed for FALSE value: " . var_export($testCase, true));
        }

        // Test with PHP boolean false
        $context = ['user' => ['user_properties' => ['boolProp' => false]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-case-bool']->key,
            "Failed for PHP boolean false");

        // Test with non-boolean value - should fall to default segment
        $context = ['user' => ['user_properties' => ['boolProp' => 'not a boolean']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-case-bool']->key,
            "Non-boolean value should match default segment");
    }
}

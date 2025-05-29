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

        $segments = [$trueSegment, $falseSegment];
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

        // Test case 7: Numeric 1
        $context = ['user' => ['user_properties' => ['boolProp' => 1]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool']->key);

        // Test case 8: Numeric 0
        $context = ['user' => ['user_properties' => ['boolProp' => 0]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);

        // Test case 9: String '1'
        $context = ['user' => ['user_properties' => ['boolProp' => '1']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool']->key);

        // Test case 10: String '0'
        $context = ['user' => ['user_properties' => ['boolProp' => '0']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('off', $results['test-bool']->key);
    }

    public function testBooleanIsNotMatching()
    {
        $variants = [
            'on' => new EvaluationVariant('on'),
            'off' => new EvaluationVariant('off')
        ];

        $segment = new EvaluationSegment(
            null,
            [[new EvaluationCondition(
                ['context','user', 'user_properties', 'boolProp'],
                'is not',
                ['true']
            )]],
            'on'
        );

        $flag = new EvaluationFlag('test-bool-not', $variants, [$segment]);
        $flags = ['test-bool-not' => $flag];

        // Test negative cases
        $context = ['user' => ['user_properties' => ['boolProp' => false]]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool-not']->key);

        $context = ['user' => ['user_properties' => ['boolProp' => 'false']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool-not']->key);

        $context = ['user' => ['user_properties' => ['boolProp' => 'False']]];
        $results = $this->engine->evaluate($context, $flags);
        $this->assertEquals('on', $results['test-bool-not']->key);
    }
}

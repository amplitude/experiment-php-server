<?php

declare(strict_types=1);

namespace AmplitudeExperiment\Test\EvaluationCore;

use AmplitudeExperiment\EvaluationCore\EvaluationEngine;
use PHPUnit\Framework\TestCase;

final class EvaluationEngineTest extends TestCase
{
    private EvaluationEngine $evaluation;

    protected function setUp() : void
    {
        parent::setUp();

        $this->evaluation = new EvaluationEngine();
    }

    public function booleanValues() : iterable
    {
        yield ['true', ['true']];
        yield ['true', ['True']];

        yield ['True', ['true']];
        yield ['True', ['True']];

        yield [true, ['1']];

        yield ['1', ['1']];

        yield ['false', ['false']];
        yield ['false', ['False']];

        yield ['False', ['false']];
        yield ['False', ['False']];

        yield ['0', ['0']];
    }

    /**
     * @dataProvider booleanValues
     */
    public function testBooleans($propValue, array $filterValues) : void
    {
        self::assertSame(
            [
                'feature1' => [
                    'key' => 'on',
                    'value' => 'on',
                    'metadata' => [
                        'segmentName' => 'Employee only'
                    ],
                ],
            ],
            $this->evaluation->evaluate([
                'user' => [
                    'user_properties' => [
                        'isEmployee' => $propValue,
                    ],
                ],
            ], [
                [
                    'key' => 'feature1',
                    'metadata' => [],
                    'segments' => [
                        [
                            'conditions' => [
                                [
                                    [
                                        'op' => 'is',
                                        'selector' => ['context', 'user', 'user_properties', 'isEmployee'],
                                        'values' => $filterValues,
                                    ],
                                ],
                            ],
                            'metadata' => [
                                'segmentName' => 'Employee only',
                            ],
                            'variant' => 'on',
                        ],
                        [
                            'metadata' => [
                                'segmentName' => 'All Other Users',
                            ],
                            'variant' => 'off',
                        ],
                    ],
                    'variants' =>
                        [
                            'off' => [
                                'key' => 'off',
                                'metadata' => [
                                    'default' => true,
                                ],
                            ],
                            'on' => [
                                'key' => 'on',
                                'value' => 'on',
                            ],
                        ],
                ],
            ]),
        );
    }
}

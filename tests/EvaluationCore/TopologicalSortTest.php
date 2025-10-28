<?php

namespace AmplitudeExperiment\Test\EvaluationCore;

require_once __DIR__ . '/../../src/EvaluationCore/Util.php';

use AmplitudeExperiment\EvaluationCore\Types\EvaluationFlag;
use Exception;
use PHPUnit\Framework\TestCase;
use function AmplitudeExperiment\EvaluationCore\topologicalSort;

class TopologicalSortTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testEmpty()
    {
        // No flag keys
        $flags = [];
        $result = $this->topologicalSortInternal($flags);
        $this->assertEquals([], $result);

        // With flag keys
        $flags = [];
        $result = $this->topologicalSortInternal($flags, ['1']);
        $this->assertEquals([], $result);
    }

    /**
     * @throws Exception
     */
    public function testSingleFlagNoDependencies()
    {
        // No flag keys
        $flags = [$this->flag(1)];
        $result = $this->topologicalSortInternal($flags);
        $this->assertEquals([$this->flag(1)], $result);

        // With flag keys
        $flags = [$this->flag(1)];
        $result = $this->topologicalSortInternal($flags, ['1']);
        $this->assertEquals([$this->flag(1)], $result);

        // With flag keys, no match
        $flags = [$this->flag(1)];
        $result = $this->topologicalSortInternal($flags, ['999']);
        $this->assertEquals([], $result);
    }

    /**
     * @throws Exception
     */
    public function testSingleFlagWithDependencies()
    {
        // No flag keys
        $flags = [$this->flag(1, [2])];
        $result = $this->topologicalSortInternal($flags);
        $this->assertEquals([$this->flag(1, [2])], $result);

        // With flag keys
        $flags = [$this->flag(1, [2])];
        $result = $this->topologicalSortInternal($flags, ['1']);
        $this->assertEquals([$this->flag(1, [2])], $result);

        // With flag keys, no match
        $flags = [$this->flag(1, [2])];
        $result = $this->topologicalSortInternal($flags, ['999']);
        $this->assertEquals([], $result);
    }

    /**
     * @throws Exception
     */
    public function testMultipleFlagsNoDependencies()
    {
        // No flag keys
        $flags = [$this->flag(1), $this->flag(2)];
        $result = $this->topologicalSortInternal($flags);
        $this->assertEquals([$this->flag(1), $this->flag(2)], $result);

        // With flag keys
        $flags = [$this->flag(1), $this->flag(2)];
        $result = $this->topologicalSortInternal($flags, ['1', '2']);
        $this->assertEquals([$this->flag(1), $this->flag(2)], $result);

        // With flag keys, no match
        $flags = [$this->flag(1), $this->flag(2)];
        $result = $this->topologicalSortInternal($flags, ['99', '999']);
        $this->assertEquals([], $result);
    }

    /**
     * @throws Exception
     */
    public function testMultipleFlagsWithDependencies()
    {
        // No flag keys
        $flags = [$this->flag(1, [2]), $this->flag(2, [3]), $this->flag(3)];
        $result = $this->topologicalSortInternal($flags);
        $this->assertEquals([$this->flag(3), $this->flag(2, [3]), $this->flag(1, [2])], $result);

        // With flag keys
        $flags = [$this->flag(1, [2]), $this->flag(2, [3]), $this->flag(3)];
        $result = $this->topologicalSortInternal($flags, ['1', '2']);
        $this->assertEquals([$this->flag(3), $this->flag(2, [3]), $this->flag(1, [2])], $result);

        // With flag keys, no match
        $flags = [$this->flag(1, [2]), $this->flag(2, [3]), $this->flag(3)];
        $result = $this->topologicalSortInternal($flags, ['99', '999']);
        $this->assertEquals([], $result);
    }

    public function testSingleFlagCycle()
    {
        // No flag keys
        $flags = [$this->flag(1, [1])];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 1');
        $this->topologicalSortInternal($flags);

        // With flag keys
        $flags = [$this->flag(1, [1])];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 1');
        $this->topologicalSortInternal($flags, ['1']);

        // With flag keys, no match
        $flags = [$this->flag(1, [1])];
        $this->expectNotToPerformAssertions();
        $this->topologicalSortInternal($flags, ['999']);
    }

    public function testTwoFlagCycle()
    {
        // No flag keys
        $flags = [$this->flag(1, [2]), $this->flag(2, [1])];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 1,2');
        $this->topologicalSortInternal($flags);

        // With flag keys
        $flags = [$this->flag(1, [2]), $this->flag(2, [1])];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 2,1');
        $this->topologicalSortInternal($flags, ['2']);

        // With flag keys, no match
        $flags = [$this->flag(1, [2]), $this->flag(2, [1])];
        $this->expectNotToPerformAssertions();
        $this->topologicalSortInternal($flags, ['999']);
    }

    public function testMultipleFlagsComplexCycle()
    {
        $flags = [
            $this->flag(3, [1, 2]),
            $this->flag(1),
            $this->flag(4, [21, 3]),
            $this->flag(2),
            $this->flag(5, [3]),
            $this->flag(6),
            $this->flag(7),
            $this->flag(8, [9]),
            $this->flag(9),
            $this->flag(20, [4]),
            $this->flag(21, [20]),
        ];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 4,21,20');
        $this->topologicalSortInternal($flags);
    }

    /**
     * @throws Exception
     */
    function testComplexNoCycleStartingWithLeaf()
    {
        $flags = [
            $this->flag(1, [6, 3]),
            $this->flag(2, [8, 5, 3, 1]),
            $this->flag(3, [6, 5]),
            $this->flag(4, [8, 7]),
            $this->flag(5, [10, 7]),
            $this->flag(7, [8]),
            $this->flag(6, [7, 4]),
            $this->flag(8),
            $this->flag(9, [10, 7, 5]),
            $this->flag(10, [7]),
            $this->flag(20),
            $this->flag(21, [20]),
            $this->flag(30),
        ];
        $result = $this->topologicalSortInternal($flags);
        $expectedResult = [
            $this->flag(8),
            $this->flag(7, [8]),
            $this->flag(4, [8, 7]),
            $this->flag(6, [7, 4]),
            $this->flag(10, [7]),
            $this->flag(5, [10, 7]),
            $this->flag(3, [6, 5]),
            $this->flag(1, [6, 3]),
            $this->flag(2, [8, 5, 3, 1]),
            $this->flag(9, [10, 7, 5]),
            $this->flag(20),
            $this->flag(21, [20]),
            $this->flag(30),
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws Exception
     */
    function testComplexNoCycleStartingWithMiddle()
    {
        $flags = [
            $this->flag(6, [7, 4]),
            $this->flag(1, [6, 3]),
            $this->flag(2, [8, 5, 3, 1]),
            $this->flag(3, [6, 5]),
            $this->flag(4, [8, 7]),
            $this->flag(5, [10, 7]),
            $this->flag(7, [8]),
            $this->flag(8),
            $this->flag(9, [10, 7, 5]),
            $this->flag(10, [7]),
            $this->flag(20),
            $this->flag(21, [20]),
            $this->flag(30),
        ];
        $result = $this->topologicalSortInternal($flags);
        $expectedResult = [
            $this->flag(8),
            $this->flag(7, [8]),
            $this->flag(4, [8, 7]),
            $this->flag(6, [7, 4]),
            $this->flag(10, [7]),
            $this->flag(5, [10, 7]),
            $this->flag(3, [6, 5]),
            $this->flag(1, [6, 3]),
            $this->flag(2, [8, 5, 3, 1]),
            $this->flag(9, [10, 7, 5]),
            $this->flag(20),
            $this->flag(21, [20]),
            $this->flag(30),
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws Exception
     */
    function testComplexNoCycleStartingWithRoot()
    {
        $flags = [
            $this->flag(8),
            $this->flag(1, [6, 3]),
            $this->flag(2, [8, 5, 3, 1]),
            $this->flag(3, [6, 5]),
            $this->flag(4, [8, 7]),
            $this->flag(5, [10, 7]),
            $this->flag(7, [8]),
            $this->flag(6, [7, 4]),
            $this->flag(9, [10, 7, 5]),
            $this->flag(10, [7]),
            $this->flag(20),
            $this->flag(21, [20]),
            $this->flag(30),
        ];
        $result = $this->topologicalSortInternal($flags);
        $expectedResult = [
            $this->flag(8),
            $this->flag(7, [8]),
            $this->flag(4, [8, 7]),
            $this->flag(6, [7, 4]),
            $this->flag(10, [7]),
            $this->flag(5, [10, 7]),
            $this->flag(3, [6, 5]),
            $this->flag(1, [6, 3]),
            $this->flag(2, [8, 5, 3, 1]),
            $this->flag(9, [10, 7, 5]),
            $this->flag(20),
            $this->flag(21, [20]),
            $this->flag(30),
        ];
        $this->assertEquals($expectedResult, $result);
    }


    /**
     * @throws Exception
     */
    private function topologicalSortInternal($flags, $flagKeys = null): array
    {
        $flagsMap = array_reduce($flags, function ($map, $flag) {
            $map[$flag->key] = $flag;
            return $map;
        }, []);
        return topologicalSort($flagsMap, $flagKeys);
    }

    private function flag($key, $dependencies = null): EvaluationFlag
    {
        $flag = new EvaluationFlag($key, [], []);
        $flag->key = strval($key);
        $flag->variants = [];
        $flag->segments = [];
        $flag->dependencies = is_array($dependencies) ? array_map('strval', $dependencies) : null;
        return $flag;
    }
}

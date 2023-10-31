<?php

namespace AmplitudeExperiment\Test\EvaluationCore;

use Exception;
use PHPUnit\Framework\TestCase;
use AmplitudeExperiment\EvaluationCore\Util;
class TopologicalSortTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testEmpty()
    {
        // No flag keys
        $flags = [];
        $result = $this->topologicalSort($flags);
        $this->assertEquals([], $result);

        // With flag keys
        $flags = [];
        $result = $this->topologicalSort($flags, ['1']);
        $this->assertEquals([], $result);
    }

    /**
     * @throws Exception
     */
    public function testSingleFlagNoDependencies()
    {
        // No flag keys
        $flags = [$this->flag(1)];
        $result = $this->topologicalSort($flags);
        $this->assertEquals([$this->flag(1)], $result);

        // With flag keys
        $flags = [$this->flag(1)];
        $result = $this->topologicalSort($flags, ['1']);
        $this->assertEquals([$this->flag(1)], $result);

        // With flag keys, no match
        $flags = [$this->flag(1)];
        $result = $this->topologicalSort($flags, ['999']);
        $this->assertEquals([], $result);
    }

    /**
     * @throws Exception
     */
    public function testSingleFlagWithDependencies()
    {
        // No flag keys
        $flags = [$this->flag(1, [2])];
        $result = $this->topologicalSort($flags);
        $this->assertEquals([$this->flag(1, [2])], $result);

        // With flag keys
        $flags = [$this->flag(1, [2])];
        $result = $this->topologicalSort($flags, ['1']);
        $this->assertEquals([$this->flag(1, [2])], $result);

        // With flag keys, no match
        $flags = [$this->flag(1, [2])];
        $result = $this->topologicalSort($flags, ['999']);
        $this->assertEquals([], $result);
    }

    /**
     * @throws Exception
     */
    public function testMultipleFlagsNoDependencies()
    {
        // No flag keys
        $flags = [$this->flag(1), $this->flag(2)];
        $result = $this->topologicalSort($flags);
        $this->assertEquals([$this->flag(1), $this->flag(2)], $result);

        // With flag keys
        $flags = [$this->flag(1), $this->flag(2)];
        $result = $this->topologicalSort($flags, ['1', '2']);
        $this->assertEquals([$this->flag(1), $this->flag(2)], $result);

        // With flag keys, no match
        $flags = [$this->flag(1), $this->flag(2)];
        $result = $this->topologicalSort($flags, ['99', '999']);
        $this->assertEquals([], $result);
    }

    /**
     * @throws Exception
     */
    public function testMultipleFlagsWithDependencies()
    {
        // No flag keys
        $flags = [$this->flag(1, [2]), $this->flag(2, [3]), $this->flag(3)];
        $result = $this->topologicalSort($flags);
        $this->assertEquals([$this->flag(3), $this->flag(2, [3]), $this->flag(1, [2])], $result);

        // With flag keys
        $flags = [$this->flag(1, [2]), $this->flag(2, [3]), $this->flag(3)];
        $result = $this->topologicalSort($flags, ['1', '2']);
        $this->assertEquals([$this->flag(3), $this->flag(2, [3]), $this->flag(1, [2])], $result);

        // With flag keys, no match
        $flags = [$this->flag(1, [2]), $this->flag(2, [3]), $this->flag(3)];
        $result = $this->topologicalSort($flags, ['99', '999']);
        $this->assertEquals([], $result);
    }

    public function testSingleFlagCycle()
    {
        // No flag keys
        $flags = [$this->flag(1, [1])];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 1');
        $this->topologicalSort($flags);

        // With flag keys
        $flags = [$this->flag(1, [1])];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 1');
        $this->topologicalSort($flags, ['1']);

        // With flag keys, no match
        $flags = [$this->flag(1, [1])];
        $this->expectNotToPerformAssertions();
        $this->topologicalSort($flags, ['999']);
    }

    public function testTwoFlagCycle()
    {
        // No flag keys
        $flags = [$this->flag(1, [2]), $this->flag(2, [1])];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 1,2');
        $this->topologicalSort($flags);

        // With flag keys
        $flags = [$this->flag(1, [2]), $this->flag(2, [1])];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Detected a cycle between flags 2,1');
        $this->topologicalSort($flags, ['2']);

        // With flag keys, no match
        $flags = [$this->flag(1, [2]), $this->flag(2, [1])];
        $this->expectNotToPerformAssertions();
        $this->topologicalSort($flags, ['999']);
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
        $this->topologicalSort($flags);
    }

    /**
     * @throws Exception
     */
    function testComplexNoCycleStartingWithLeaf() {
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
        $result = $this->topologicalSort($flags);
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
    function testComplexNoCycleStartingWithMiddle() {
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
        $result = $this->topologicalSort($flags);
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
    function testComplexNoCycleStartingWithRoot() {
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
        $result = $this->topologicalSort($flags);
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
    private function topologicalSort($flags, $flagKeys = null): array
    {
        $flagsMap = array_reduce($flags, function ($map, $flag) {
            $map[$flag->key] = $flag;
            return $map;
        }, []);
        return Util::topologicalSort($flagsMap, $flagKeys);
    }

    private function flag($key, $dependencies = null): object
    {
        return (object) [
            'key' => strval($key),
            'variants' => (object) [],
            'segments' => [],
            'dependencies' => is_array($dependencies) ? array_map('strval', $dependencies) : null,
        ];
    }
}

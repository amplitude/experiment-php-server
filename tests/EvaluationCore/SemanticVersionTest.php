<?php

namespace AmplitudeExperiment\Test\EvaluationCore;

use PHPUnit\Framework\TestCase;
use AmplitudeExperiment\EvaluationCore\SemanticVersion;
use AmplitudeExperiment\EvaluationCore\EvaluationOperator;

class SemanticVersionTest extends TestCase {
    public function testInvalidVersions() {
        $this->assertInvalidVersion('10');
        $this->assertInvalidVersion('10.');
        $this->assertInvalidVersion('10..');
        $this->assertInvalidVersion('10.2.');
        $this->assertInvalidVersion('10.2.33.');
        $this->assertInvalidVersion('10..2.33');
        $this->assertInvalidVersion('102...33');
        $this->assertInvalidVersion('a.2.3');
        $this->assertInvalidVersion('23!');
        $this->assertInvalidVersion('23.#5');
        $this->assertInvalidVersion('');
        $this->assertInvalidVersion(null);
        $this->assertInvalidVersion('2.3.4.567');
        $this->assertInvalidVersion('2.3.4.5.6.7');
        $this->assertInvalidVersion('10.2.alpha');
        $this->assertInvalidVersion('10.alpha');
        $this->assertInvalidVersion('alpha-1.2.3');
        $this->assertInvalidVersion('10.2.3alpha');
        $this->assertInvalidVersion('10.2.3alpha-1.2.3');
        $this->assertInvalidVersion('-10.1');
        $this->assertInvalidVersion('10.-1');
    }

    public function testValidVersions() {
        $this->assertValidVersion('100.2');
        $this->assertValidVersion('0.102.39');
        $this->assertValidVersion('0.0.0');
        $this->assertValidVersion('01.02');
        $this->assertValidVersion('001.001100.000900');
        $this->assertValidVersion('10.20.30-alpha');
        $this->assertValidVersion('10.20.30-1.x.y');
        $this->assertValidVersion('10.20.30-aslkjd');
        $this->assertValidVersion('10.20.30-b894');
        $this->assertValidVersion('10.20.30-b8c9');
    }

    public function testVersionComparison() {
        $this->assertVersionComparison('66.12.23', EvaluationOperator::IS, '66.12.23');
        $this->assertVersionComparison('5.6', EvaluationOperator::IS, '5.6.0');
        $this->assertVersionComparison('06.007.0008', EvaluationOperator::IS, '6.7.8');
        $this->assertVersionComparison('1.23.4-b-1.x.y', EvaluationOperator::IS, '1.23.4-b-1.x.y');
        $this->assertVersionComparison('1.23.4-alpha-1.2', EvaluationOperator::IS_NOT, '1.23.4-alpha-1');
        $this->assertVersionComparison('1.2.300', EvaluationOperator::IS_NOT, '1.2.3');
        $this->assertVersionComparison('1.20.3', EvaluationOperator::IS_NOT, '1.2.3');
        $this->assertVersionComparison('50.2', EvaluationOperator::VERSION_LESS_THAN, '50.2.1');
        $this->assertVersionComparison('20.9', EvaluationOperator::VERSION_LESS_THAN, '20.20');
        $this->assertVersionComparison('20.9.4-alpha1', EvaluationOperator::VERSION_LESS_THAN, '20.9.4');
        $this->assertVersionComparison('20.9.4-a-1.2.3', EvaluationOperator::VERSION_LESS_THAN, '20.9.4-a-1.3');
        $this->assertVersionComparison('20.9.4-a1.23', EvaluationOperator::VERSION_LESS_THAN, '20.9.4-a1.5');
        $this->assertVersionComparison('12.30.2', EvaluationOperator::VERSION_GREATER_THAN, '12.4.1');
        $this->assertVersionComparison('7.100', EvaluationOperator::VERSION_GREATER_THAN, '7.1');
        $this->assertVersionComparison('7.10', EvaluationOperator::VERSION_GREATER_THAN, '7.9');
        $this->assertVersionComparison('07.010.0020', EvaluationOperator::VERSION_GREATER_THAN, '7.009.1');
        $this->assertVersionComparison('20.5.6-b1.2.x', EvaluationOperator::VERSION_GREATER_THAN, '20.5.5');
    }

    private function assertInvalidVersion($version) {
        $this->assertNull(SemanticVersion::parse($version));
    }

    private function assertValidVersion($version) {
        $this->assertNotNull(SemanticVersion::parse($version));
    }

    private function assertVersionComparison($v1, $op, $v2) {
        $sv1 = SemanticVersion::parse($v1);
        $sv2 = SemanticVersion::parse($v2);
        $this->assertNotNull($sv1);
        $this->assertNotNull($sv2);
        if (!$sv1 || !$sv2) return;
        if ($op === EvaluationOperator::IS) {
            $this->assertEquals(0, $sv1->compareTo($sv2));
        } elseif ($op === EvaluationOperator::IS_NOT) {
            $this->assertNotEquals(0, $sv1->compareTo($sv2));
        } elseif ($op === EvaluationOperator::VERSION_LESS_THAN) {
            $this->assertLessThan(0, $sv1->compareTo($sv2));
        } elseif ($op === EvaluationOperator::VERSION_GREATER_THAN) {
            $this->assertGreaterThan(0, $sv1->compareTo($sv2));
        }
    }
}


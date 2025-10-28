<?php

namespace AmplitudeExperiment\Test\EvaluationCore;

require_once __DIR__ . '/../../src/EvaluationCore/Util.php';

use PHPUnit\Framework\TestCase;
use function AmplitudeExperiment\EvaluationCore\select;

class SelectTest extends TestCase {
    public function testSelectorEvaluationContextTypes() {
        $primitiveObject = [
            'null' => null,
            'string' => 'value',
            'number' => 13,
            'boolean' => true,
        ];

        $nestedObject = $primitiveObject;
        $nestedObject['object'] = $primitiveObject;

        $context = $nestedObject;

        $this->assertNull(select($context, ['does', 'not', 'exist']));
        $this->assertNull(select($context, ['null']));
        $this->assertEquals('value', select($context, ['string']));
        $this->assertEquals(13, select($context, ['number']));
        $this->assertTrue(select($context, ['boolean']));
        $this->assertEquals($primitiveObject, select($context, ['object']));
        $this->assertNull(select($context, ['object', 'does', 'not', 'exist']));
        $this->assertNull(select($context, ['object', 'null']));
        $this->assertEquals('value', select($context, ['object', 'string']));
        $this->assertEquals(13, select($context, ['object', 'number']));
        $this->assertTrue(select($context, ['object', 'boolean']));
    }
}

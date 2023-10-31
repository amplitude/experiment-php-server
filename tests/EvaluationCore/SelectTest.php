<?php

namespace AmplitudeExperiment\Test\EvaluationCore;

use PHPUnit\Framework\TestCase;
use AmplitudeExperiment\EvaluationCore\Util;

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

        $this->assertNull(Util::select($context, ['does', 'not', 'exist']));
        $this->assertNull(Util::select($context, ['null']));
        $this->assertEquals('value', Util::select($context, ['string']));
        $this->assertEquals(13, Util::select($context, ['number']));
        $this->assertTrue(Util::select($context, ['boolean']));
        $this->assertEquals($primitiveObject, Util::select($context, ['object']));
        $this->assertNull(Util::select($context, ['object', 'does', 'not', 'exist']));
        $this->assertNull(Util::select($context, ['object', 'null']));
        $this->assertEquals('value', Util::select($context, ['object', 'string']));
        $this->assertEquals(13, Util::select($context, ['object', 'number']));
        $this->assertTrue(Util::select($context, ['object', 'boolean']));
    }
}

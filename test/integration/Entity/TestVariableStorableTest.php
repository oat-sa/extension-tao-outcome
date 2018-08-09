<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoResultServer\test\integration\Entity;

use oat\taoResultServer\models\Entity\TestVariableStorable;
use taoResultServer_models_classes_OutcomeVariable;

class TestVariableStorableTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeAsExpected()
    {
        $resultVariable = $this->getMockBuilder(taoResultServer_models_classes_OutcomeVariable::class)->disableOriginalConstructor()->getMock();

        $var = new TestVariableStorable('deliveryResultIdentifier', 'test', $resultVariable, 'callIdTest');

        $this->assertInstanceOf(\JsonSerializable::class, $var);
        $this->assertEquals([
            "deliveryResultIdentifier" => 'deliveryResultIdentifier',
            "test" => 'test',
            "item" => null,
            "variable" => serialize($resultVariable),
            "callIdItem" => null,
            "uri" => 'deliveryResultIdentifiercallIdTest',
            "callIdTest" => 'callIdTest',
            "class" => get_class($resultVariable)

        ], $var->jsonSerialize());
    }
}
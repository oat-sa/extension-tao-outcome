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

namespace oat\taoResultServer\test\integration\Collection;

use oat\taoResultServer\models\Collection\VariableStorableCollection;
use taoResultServer_models_classes_OutcomeVariable;
use taoResultServer_models_classes_Variable;

class VariableStorableCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateWithSuccess()
    {
        $testVariables = [
            $this->getMockBuilder(taoResultServer_models_classes_Variable::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(taoResultServer_models_classes_Variable::class)->disableOriginalConstructor()->getMock(),
        ];

        $testCollection = VariableStorableCollection::createTestVariableCollection('callIdTest', 'deliveryResultIdentifier', 'testIdentifier', $testVariables);
        $itemCollection = VariableStorableCollection::createItemVariableCollection('callIdItem', 'item', 'deliveryResultIdentifier', 'testIdentifier', $testVariables);

        $this->assertInstanceOf(VariableStorableCollection::class, $testCollection);
        $this->assertInstanceOf(\oat\taoResultServer\models\Collection\VariableStorableCollection::class, $itemCollection);
    }

    public function testTestsCollection()
    {
        $resultVariable1 = $this->getMockBuilder(taoResultServer_models_classes_OutcomeVariable::class)->disableOriginalConstructor()->getMock();
        $resultVariable1
            ->method('getIdentifier')
            ->willReturn('variable1');

        $resultVariable2 = $this->getMockBuilder(taoResultServer_models_classes_OutcomeVariable::class)->disableOriginalConstructor()->getMock();
        $resultVariable2
            ->method('getIdentifier')
            ->willReturn('variable2');

        $testVariables = [
            $resultVariable1,
            $resultVariable2
        ];

        $collection = VariableStorableCollection::createTestVariableCollection('callIdTest', 'deliveryResultIdentifier', 'testIdentifier', $testVariables);

        $this->assertInstanceOf(VariableStorableCollection::class, $collection);

        $array = $collection->toStorableArray();
        $this->assertArrayHasKey('variable1', $array);
        $this->assertArrayHasKey('variable2', $array);
        $this->assertInternalType('string', $array['variable1']);
        $this->assertInternalType('string', $array['variable2']);
        $this->assertSame('callIdTest', $collection->getIdentifier());
    }

    public function testItemsCollection()
    {
        $resultVariable1 = $this->getMockBuilder(taoResultServer_models_classes_Variable::class)->disableOriginalConstructor()->getMock();
        $resultVariable1
            ->method('getIdentifier')
            ->willReturn('variable1');

        $resultVariable2 = $this->getMockBuilder(taoResultServer_models_classes_Variable::class)->disableOriginalConstructor()->getMock();
        $resultVariable2
            ->method('getIdentifier')
            ->willReturn('variable2');

        $testVariables = [
            $resultVariable1,
            $resultVariable2
        ];

        $collection = VariableStorableCollection::createItemVariableCollection('callIdItem', 'item', 'deliveryResultIdentifier', 'testIdentifier', $testVariables);

        $this->assertInstanceOf(VariableStorableCollection::class, $collection);

        $array = $collection->toStorableArray();
        $this->assertArrayHasKey('variable1', $array);
        $this->assertArrayHasKey('variable2', $array);
        $this->assertInternalType('string', $array['variable1']);
        $this->assertInternalType('string', $array['variable2']);
        $this->assertSame('callIdItem', $collection->getIdentifier());
    }
}

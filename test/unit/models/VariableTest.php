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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

use oat\generis\test\TestCase;
use taoResultServer_models_classes_Variable as Variable;

class VariableTest extends TestCase
{
    /**
     * @var Variable
     */
    private $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = $this->getMockForAbstractClass(Variable::class);
    }

    /**
     * Test for setCardinality method with invalid value.
     *
     * @param $cardinality
     * @throws common_exception_InvalidArgumentType
     *
     * @dataProvider providerTestSetCardinalityInvalidValue
     */
    public function testSetCardinalityInvalidValue($cardinality)
    {
        $this->expectException(common_exception_InvalidArgumentType::class);

        $this->object->setCardinality($cardinality);
    }

    /**
     * Test set cardinality with correct value.
     *
     * @param $cardinality
     * @throws common_exception_InvalidArgumentType
     *
     * @dataProvider providerTestSetCardinality
     */
    public function testSetCardinality($cardinality)
    {
        $this->object->setCardinality($cardinality);

        $result = $this->object->getCardinality();

        $this->assertEquals($cardinality, $result, "Variable's cardinality must be as expected.");
    }

    /**
     * Test for isMultiple method
     *
     * @param string $cardinality
     * @param bool $expectedResult
     * @throws common_exception_InvalidArgumentType
     *
     * @dataProvider providerTestIsMultiple
     */
    public function testIsMultiple($cardinality, $expectedResult)
    {
        $this->object->setCardinality($cardinality);
        $result = $this->object->isMultiple();

        $this->assertEquals($expectedResult, $result, 'Result of checking if variable is of multiple cardinality must be as expected.');
    }

    /**
     * @return array
     */
    public function providerTestIsMultiple()
    {
        return [
            'Single' => [
                'cardinality' => Variable::CARDINALITY_SINGLE,
                'expectedResult' => false,
            ],
            'Multiple' => [
                'cardinality' => Variable::CARDINALITY_MULTIPLE,
                'expectedResult' => true,
            ],
            'Ordered' => [
                'cardinality' => Variable::CARDINALITY_ORDERED,
                'expectedResult' => true,
            ],
            'Record' => [
                'cardinality' => Variable::CARDINALITY_RECORD,
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @return array
     */
    public function providerTestSetCardinalityInvalidValue()
    {
        return [
            'Invalid value' => [
                'cardinality' => 'INVALID_CARDINALITY',
            ],
            'Empty string' => [
                'cardinality' => '',
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerTestSetCardinality()
    {
        return [
            'Single' => [
                'cardinality' => Variable::CARDINALITY_SINGLE,
            ],
            'Multiple' => [
                'cardinality' => Variable::CARDINALITY_MULTIPLE,
            ],
            'Ordered' => [
                'cardinality' => Variable::CARDINALITY_ORDERED,
            ],
            'Record' => [
                'cardinality' => Variable::CARDINALITY_RECORD,
            ],
        ];
    }
}

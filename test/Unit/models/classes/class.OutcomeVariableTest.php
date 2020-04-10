<?php

declare(strict_types=1);

namespace oat\taoResultServer\test\Unit\models\classes;

use PHPUnit\Framework\TestCase;

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies S.A.
 */

class taoResultServer_models_classes_OutcomeVariableTest extends TestCase
{
    public function testVariableCanBeJsonSerialized(): void
    {
        $subject = (new \taoResultServer_models_classes_OutcomeVariable())
            ->setIdentifier('testIdentifier')
            ->setCardinality('single')
            ->setBaseType('testBaseType')
            ->setEpoch('testEpoch')
            ->setNormalMinimum(1.00)
            ->setNormalMaximum(10.00)
            ->setValue('testValue');

        $this->assertSame(json_encode([
            'identifier' => 'testIdentifier',
            'cardinality' => 'single',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
            'type' => \taoResultServer_models_classes_OutcomeVariable::TYPE,
            'normalMinimum' => 1.00,
            'normalMaximum' => 10.00,
            'value' => base64_encode('testValue'),
        ]), json_encode($subject));
    }
}

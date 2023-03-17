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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoResultServer\test\Unit\models\Import\Input;

use oat\taoResultServer\models\Import\Input\ImportResultInput;
use PHPUnit\Framework\TestCase;

class ImportResultInputTest extends TestCase
{
    public function testGetters(): void
    {
        $sut = new ImportResultInput('id', true);
        $sut->addOutcome('item-1', 'SCORE', 0.5);
        $sut->addOutcome('item-2', 'SCORE', 0.6);
        $sut->addResponse('item-1', 'RESPONSE', ['correctResponse' => true]);
        $sut->addResponse('item-2', 'RESPONSE', ['correctResponse' => true]);

        $this->assertSame('id', $sut->getDeliveryExecutionId());
        $this->assertTrue($sut->isSendAgs());
        $this->assertSame(
            [
                'deliveryExecutionId' => 'id',
                'sendAgs' => true,
                'outcomes' => [
                    'item-1' => [
                        'SCORE' => 0.5,
                    ],
                    'item-2' => [
                        'SCORE' => 0.6,
                    ]
                ],
                'responses' => [
                    'item-1' => [
                        'RESPONSE' => ['correctResponse' => true],
                    ],
                    'item-2' => [
                        'RESPONSE' => ['correctResponse' => true],
                    ]
                ],
            ],
            $sut->jsonSerialize()
        );
    }

    public function testFromJson(): void
    {
        $json = [
            'deliveryExecutionId' => 'id',
            'sendAgs' => true,
            'outcomes' => [
                'item-1' => [
                    'SCORE' => 0.5,
                ],
                'item-2' => [
                    'SCORE' => 0.6,
                ]
            ],
            'responses' => [
                'item-1' => [
                    'RESPONSE' => ['correctResponse' => true],
                ],
                'item-2' => [
                    'RESPONSE' => ['correctResponse' => true],
                ]
            ],
        ];

        $this->assertSame($json, ImportResultInput::fromJson($json)->jsonSerialize());
    }
}

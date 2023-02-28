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

namespace oat\taoResultServer\test\Unit\models\Import\Factory;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Factory\QtiResultXmlFactory;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use taoResultServer_models_classes_OutcomeVariable;
use taoResultServer_models_classes_ResponseVariable;

class QtiResultXmlFactoryTest extends TestCase
{
    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ResultServerService|MockObject */
    private $resultServerService;

    /** @var ResultManagement|MockObject */
    private ResultManagement $resultStorage;
    private QtiResultXmlFactory $sut;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resultServerService = $this->createMock(ResultServerService::class);
        $this->resultStorage = $this->createMock(ResultManagement::class);
        $this->sut = new QtiResultXmlFactory($this->ontology, $this->resultServerService);

        $this->resultServerService
            ->expects($this->any())
            ->method('getResultStorage')
            ->willReturn($this->resultStorage);
    }

    public function testCreateByImportResult(): void
    {
        $input = new ImportResultInput('executionId', true);
        $input->addOutcome('item-1', 'SCORE', 1);
        $input->addOutcome('item-2', 'SCORE', 1);

        $test = $this->createMock(core_kernel_classes_Resource::class);
        $test->expects($this->any())
            ->method('getUri')
            ->willReturn('testId');

        $delivery = $this->createMock(core_kernel_classes_Property::class);
        $delivery->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn('testUri');

        $testOutcomeScoreTotalFirst = $this->createTestVariable(3, 'SCORE_TOTAL'); // Considered
        $testOutcomeScoreTotalLast = $this->createTestVariable(5, 'SCORE_TOTAL'); // Not considered
        $testOutcomeScoreTotalMaxFirst = $this->createTestVariable(10, 'SCORE_TOTAL_MAX'); // Considered
        $testOutcomeScoreTotalMaxLast = $this->createTestVariable(2, 'SCORE_TOTAL_MAX'); // Not considered

        $itemOutcome1First = $this->createOutcomeVariable(1); // Not considered
        $itemOutcome1Last = $this->createOutcomeVariable(0.5); // Considered

        $itemOutcome2First = $this->createOutcomeVariable(1); // Not considered
        $itemOutcome2Last = $this->createOutcomeVariable(0.5); // Considered

        $this->resultStorage
            ->expects($this->once())
            ->method('getDeliveryVariables')
            ->willReturn(
                [
                    [
                        (object)[
                            'variable' => $testOutcomeScoreTotalFirst,
                        ],
                        (object)[
                            'variable' => $testOutcomeScoreTotalLast,
                        ]
                    ],
                    [
                        (object)[
                            'variable' => $testOutcomeScoreTotalMaxFirst,
                        ],
                        (object)[
                            'variable' => $testOutcomeScoreTotalMaxLast,
                        ],
                    ]
                ]
            );

        $this->resultStorage
            ->method('getVariable')
            ->willReturnCallback(
                function ($callItemId) use (
                    $itemOutcome1First,
                    $itemOutcome1Last,
                    $itemOutcome2First,
                    $itemOutcome2Last
                ) {
                    if (strpos($callItemId, 'item-1') !== false) {
                        return [
                            [
                                'variable' => $itemOutcome1First,
                            ],
                            [
                                'variable' => $itemOutcome1Last,
                            ],
                        ];
                    }

                    if (strpos($callItemId, 'item-2') !== false) {
                        return [
                            [
                                'variable' => $itemOutcome2First,
                            ],
                            [
                                'variable' => $itemOutcome2Last,
                            ],
                        ];
                    }

                    return [];
                }
            );

        $this->resultStorage
            ->method('getDelivery')
            ->willReturn('deliveryId');

        $this->ontology
            ->method('getResource')
            ->willReturn($delivery);

        $this->ontology
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $xml = $this->sut->createByImportResult($input);

        $xmlDocument = simplexml_load_string($xml);

        $testAttributes = $xmlDocument->testResult->outcomeVariable->attributes();
        $itemAttributes = $xmlDocument->itemResult->outcomeVariable->attributes();

        $this->assertSame('testUri', (string)$xmlDocument->testResult->attributes()['identifier'][0]);
        $this->assertSame('SCORE_TOTAL', (string)$testAttributes['identifier'][0]);
        $this->assertSame('single', (string)$testAttributes['cardinality'][0]);
        $this->assertSame('float', (string)$testAttributes['baseType'][0]);
        $this->assertSame(4.0, (float)$xmlDocument->testResult->outcomeVariable->value);

        $this->assertSame('item-1', (string)$xmlDocument->itemResult->attributes()['identifier'][0]);
        $this->assertSame('SCORE', (string)$itemAttributes['identifier'][0]);
        $this->assertSame('single', (string)$itemAttributes['cardinality'][0]);
        $this->assertSame('float', (string)$itemAttributes['baseType'][0]);
        $this->assertSame(1.0, (float)$xmlDocument->itemResult->outcomeVariable->value);

        $this->assertIsString($xml);
    }

    private function createOutcomeVariable(float $value): taoResultServer_models_classes_OutcomeVariable
    {
        $variable = new taoResultServer_models_classes_OutcomeVariable();
        $variable->setValue($value);
        $variable->setIdentifier('SCORE');
        $variable->setCardinality('single');
        $variable->setBaseType('float');

        return $variable;
    }

    private function createTestVariable(
        float $value,
        string $identifier
    ): taoResultServer_models_classes_ResponseVariable {
        $variable = new taoResultServer_models_classes_ResponseVariable();
        $variable->setValue($value);
        $variable->setIdentifier($identifier);
        $variable->setCardinality('single');
        $variable->setBaseType('float');

        return $variable;
    }
}

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

namespace oat\taoResultServer\test\Unit\models\Import\Service;

use common_persistence_SqlPersistence;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\taoOutcomeRds\model\AbstractRdsResultStorage;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Exception\ImportResultException;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use oat\taoResultServer\models\Import\Service\ResultImporter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use taoResultServer_models_classes_OutcomeVariable;
use taoResultServer_models_classes_ResponseVariable;

class ResultImporterTest extends TestCase
{
    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ResultServerService|MockObject */
    private $resultServerService;

    /** @var AbstractRdsResultStorage|MockObject */
    private $resultStorage;
    private ImportResultInput $input;
    private ResultImporter $sut;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resultServerService = $this->createMock(ResultServerService::class);
        $this->resultStorage = $this->createMock(AbstractRdsResultStorage::class);
        $this->persistence = $this->createMock(common_persistence_SqlPersistence::class);
        $this->input = new ImportResultInput('executionId', true);

        $this->resultServerService
            ->expects($this->any())
            ->method('getResultStorage')
            ->willReturn($this->resultStorage);

        $this->resultStorage
            ->method('getPersistence')
            ->willReturn($this->persistence);

        $this->persistence
            ->method('transactional')
            ->willReturnCallback(
                static function (callable $call) {
                    $call();
                }
            );

        $test = $this->createMock(core_kernel_classes_Resource::class);
        $test->expects($this->any())
            ->method('getUri')
            ->willReturn('testId');

        $delivery = $this->createMock(core_kernel_classes_Property::class);
        $delivery->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn('testUri');

        $this->resultStorage
            ->method('getDelivery')
            ->willReturn('deliveryId');

        $this->ontology
            ->method('getResource')
            ->willReturn($delivery);

        $this->ontology
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $this->sut = new ResultImporter($this->ontology, $this->resultServerService);
    }

    public function testCreateByImportResult(): void
    {
        $this->input->addOutcome('item-1', 'SCORE', 1);
        $this->input->addOutcome('item-2', 'SCORE', 1);
        $this->input->addResponse('item-1', 'RESPONSE', ['correctResponse' => true]);
        $this->input->addResponse('item-2', 'RESPONSE', ['correctResponse' => true]);

        $this->resultStorage
            ->expects($this->once())
            ->method('getDeliveryVariables')
            ->willReturn(
                [
                    777 => [
                        (object)[
                            'variable' => $this->createTestVariable(3, 'SCORE_TOTAL'), // Considered
                        ],
                        (object)[
                            'variable' => $this->createTestVariable(5, 'SCORE_TOTAL'), // Not considered,
                        ]
                    ],
                    999 => [
                        (object)[
                            'variable' => $this->createTestVariable(10, 'SCORE_TOTAL_MAX'), // Considered,
                        ],
                        (object)[
                            'variable' => $this->createTestVariable(2, 'SCORE_TOTAL_MAX'), // Not considered,
                        ],
                    ]
                ]
            );

        $this->resultStorage
            ->expects($this->any())
            ->method('replaceItemVariables')
            ->withConsecutive(
                // Replace item responses
                [
                    'executionId',
                    'testUri',
                    'item1Uri',
                    'executionId.item-1.0',
                    [
                        12 => $this->createResponseVariable(true),
                    ]
                ],
                [
                    'executionId',
                    'testUri',
                    'item2Uri',
                    'executionId.item-2.0',
                    [
                        22 => $this->createResponseVariable(true),
                    ]
                ],
                // Replace item outcomes
                [
                    'executionId',
                    'testUri',
                    'item1Uri',
                    'executionId.item-1.0',
                    [
                        12 => $this->createOutcomeVariable(1),
                    ]
                ],
                [
                    'executionId',
                    'testUri',
                    'item2Uri',
                    'executionId.item-2.0',
                    [
                        22 => $this->createOutcomeVariable(1),
                    ]
                ]
            );

        $this->resultStorage
            ->expects($this->once())
            ->method('replaceTestVariables')
            ->with(
                'executionId',
                'testUri',
                'executionId',
                [
                    777 => $this->createTestVariable(4, 'SCORE_TOTAL'),
                ]
            );

        $this->resultStorage
            ->method('getVariable')
            ->willReturnCallback(
                function ($callItemId, $responseId) {
                    if (strpos($callItemId, 'item-1') !== false) {
                        if ($responseId === 'SCORE') {
                            return [
                                11 => [
                                    'item' => 'item1Uri',
                                    'variable' => $this->createOutcomeVariable(1), // Not considered,
                                ],
                                12 => [
                                    'item' => 'item1Uri',
                                    'variable' => $this->createOutcomeVariable(0.5), // Considered,
                                ],
                            ];
                        }

                        if ($responseId === 'RESPONSE') {
                            return [
                                11 => [
                                    'item' => 'item1Uri',
                                    'variable' => $this->createResponseVariable(false), // Not considered,
                                ],
                                12 => [
                                    'item' => 'item1Uri',
                                    'variable' => $this->createResponseVariable(false), // Considered,
                                ],
                            ];
                        }
                    }

                    if (strpos($callItemId, 'item-2') !== false) {
                        if ($responseId === 'SCORE') {
                            return [
                                21 => [
                                    'item' => 'item2Uri',
                                    'variable' => $this->createOutcomeVariable(1), // Not considered,
                                ],
                                22 => [
                                    'item' => 'item2Uri',
                                    'variable' => $this->createOutcomeVariable(0.5), // Considered,
                                ],
                            ];
                        }

                        if ($responseId === 'RESPONSE') {
                            return [
                                21 => [
                                    'item' => 'item2Uri',
                                    'variable' => $this->createResponseVariable(false), // Not considered,
                                ],
                                22 => [
                                    'item' => 'item2Uri',
                                    'variable' => $this->createResponseVariable(false), // Considered,
                                ],
                            ];
                        }
                    }

                    return [];
                }
            );

        $this->sut->importByResultInput($this->input);
    }

    public function testCreateByImportResultWithOverflowMaxScoreWillFail(): void
    {
        $this->input->addOutcome('item-1', 'SCORE', 1.1);

        $this->resultStorage
            ->expects($this->once())
            ->method('getDeliveryVariables')
            ->willReturn(
                [
                    777 => [
                        (object)[
                            'variable' => $this->createTestVariable(1, 'SCORE_TOTAL'),
                        ],
                    ],
                    999 => [
                        (object)[
                            'variable' => $this->createTestVariable(1, 'SCORE_TOTAL_MAX'),
                        ],
                    ]
                ]
            );

        $this->resultStorage
            ->method('getVariable')
            ->willReturnCallback(
                function ($callItemId, $responseId) {
                    if (strpos($callItemId, 'item-1') !== false) {
                        if ($responseId === 'SCORE') {
                            return [
                                11 => [
                                    'item' => 'item1Uri',
                                    'variable' => $this->createOutcomeVariable(1),
                                ],
                            ];
                        }
                    }

                    return [];
                }
            );

        $this->expectException(ImportResultException::class);
        $this->expectExceptionMessage('SCORE_TOTAL_MAX cannot be higher than 1, 1.1 provided');

        $this->sut->importByResultInput($this->input);
    }

    public function testCreateByImportResultWithNoItemVariableWillFail(): void
    {
        $this->input->addOutcome('item-1', 'SCORE', 1.1);

        $this->resultStorage
            ->expects($this->once())
            ->method('getDeliveryVariables')
            ->willReturn(
                [
                    777 => [
                        (object)[
                            'variable' => $this->createTestVariable(1, 'SCORE_TOTAL'),
                        ],
                    ],
                    999 => [
                        (object)[
                            'variable' => $this->createTestVariable(1, 'SCORE_TOTAL_MAX'),
                        ],
                    ]
                ]
            );

        $this->resultStorage
            ->method('getVariable')
            ->willReturnCallback(
                function ($callItemId, $responseId) {
                    if (strpos($callItemId, 'item-1') !== false) {
                        if ($responseId === 'SCORE') {
                            return [];
                        }
                    }

                    return [];
                }
            );

        $this->expectException(ImportResultException::class);
        $this->expectExceptionMessage('Variable SCORE not found for item item-1 on delivery execution executionId');

        $this->sut->importByResultInput($this->input);
    }

    public function testCreateByImportResultWithInvalidVariableWillFail(): void
    {
        $this->input->addOutcome('item-1', 'SCORE', 1.1);

        $this->resultStorage
            ->expects($this->once())
            ->method('getDeliveryVariables')
            ->willReturn(
                [
                    777 => [
                        (object)[
                            'variable' => $this->createTestVariable(1, 'SCORE_TOTAL'),
                        ],
                    ],
                    999 => [
                        (object)[
                            'variable' => $this->createTestVariable(1, 'SCORE_TOTAL_MAX'),
                        ],
                    ]
                ]
            );

        $this->resultStorage
            ->method('getVariable')
            ->willReturnCallback(
                function ($callItemId, $responseId) {
                    if (strpos($callItemId, 'item-1') !== false) {
                        if ($responseId === 'SCORE') {
                            return [
                                0 => [
                                    'variable' => new stdClass(),
                                ]
                            ];
                        }
                    }

                    return [];
                }
            );

        $this->expectException(ImportResultException::class);
        $this->expectExceptionMessage(
            'Variable SCORE is typeof stdClass, expected instance of taoResultServer_models_classes_Variable, ' .
            'for item item-1 and execution executionId'
        );

        $this->sut->importByResultInput($this->input);
    }

    private function createOutcomeVariable(float $value): taoResultServer_models_classes_OutcomeVariable
    {
        $variable = new taoResultServer_models_classes_OutcomeVariable();
        $variable->setValue($value);
        $variable->setIdentifier('SCORE');
        $variable->setCardinality('single');
        $variable->setBaseType('float');
        $variable->setExternallyGraded(true);

        return $variable;
    }

    private function createResponseVariable(bool $correctResponse): taoResultServer_models_classes_ResponseVariable
    {
        $variable = new taoResultServer_models_classes_ResponseVariable();
        $variable->setIdentifier('RESPONSE');
        $variable->setCorrectResponse($correctResponse);

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

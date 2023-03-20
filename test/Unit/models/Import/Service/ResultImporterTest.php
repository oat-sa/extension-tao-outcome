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
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use oat\taoResultServer\models\Import\Service\ResultImporter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
    private ResultImporter $sut;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resultServerService = $this->createMock(ResultServerService::class);
        $this->resultStorage = $this->createMock(AbstractRdsResultStorage::class);
        $this->persistence = $this->createMock(common_persistence_SqlPersistence::class);

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

        $this->sut = new ResultImporter(
            $this->ontology,
            $this->resultServerService
        );
    }

    public function testCreateByImportResult(): void
    {
        $input = new ImportResultInput('executionId', true);
        $input->addOutcome('item-1', 'SCORE', 1);
        $input->addOutcome('item-2', 'SCORE', 1);
        $input->addResponse('item-1', 'RESPONSE', ['correctResponse' => true]);
        $input->addResponse('item-2', 'RESPONSE', ['correctResponse' => true]);

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

        $itemResponse1First = $this->createResponseVariable(false); // Not considered
        $itemResponse1Last = $this->createResponseVariable(false); // Considered

        $itemResponse2First = $this->createResponseVariable(false); // Not considered
        $itemResponse2Last = $this->createResponseVariable(false); // Considered

        $this->resultStorage
            ->expects($this->once())
            ->method('getDeliveryVariables')
            ->willReturn(
                [
                    777 => [
                        (object)[
                            'variable' => $testOutcomeScoreTotalFirst,
                        ],
                        (object)[
                            'variable' => $testOutcomeScoreTotalLast,
                        ]
                    ],
                    999 => [
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
                function (
                    $callItemId,
                    $responseId
                ) use (
                    $itemOutcome1First,
                    $itemOutcome1Last,
                    $itemOutcome2First,
                    $itemOutcome2Last,
                    $itemResponse1First,
                    $itemResponse1Last,
                    $itemResponse2First,
                    $itemResponse2Last
                ) {
                    if (strpos($callItemId, 'item-1') !== false) {
                        if ($responseId === 'SCORE') {
                            return [
                                11 => [
                                    'item' => 'item1Uri',
                                    'variable' => $itemOutcome1First,
                                ],
                                12 => [
                                    'item' => 'item1Uri',
                                    'variable' => $itemOutcome1Last,
                                ],
                            ];
                        }

                        if ($responseId === 'RESPONSE') {
                            return [
                                11 => [
                                    'item' => 'item1Uri',
                                    'variable' => $itemResponse1First,
                                ],
                                12 => [
                                    'item' => 'item1Uri',
                                    'variable' => $itemResponse1Last,
                                ],
                            ];
                        }
                    }

                    if (strpos($callItemId, 'item-2') !== false) {
                        if ($responseId === 'SCORE') {
                            return [
                                21 => [
                                    'item' => 'item2Uri',
                                    'variable' => $itemOutcome2First,
                                ],
                                22 => [
                                    'item' => 'item2Uri',
                                    'variable' => $itemOutcome2Last,
                                ],
                            ];
                        }

                        if ($responseId === 'RESPONSE') {
                            return [
                                21 => [
                                    'item' => 'item2Uri',
                                    'variable' => $itemResponse2First,
                                ],
                                22 => [
                                    'item' => 'item2Uri',
                                    'variable' => $itemResponse2Last,
                                ],
                            ];
                        }
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

        $this->sut->importByResultInput($input);
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

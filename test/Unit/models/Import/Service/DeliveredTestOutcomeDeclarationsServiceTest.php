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

declare(strict_types=1);

namespace oat\taoResultServer\test\Unit\models\Import\Service;

use oat\taoDeliveryRdf\model\DeliveryContainerService;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoQtiTest\models\runner\QtiRunnerService;
use oat\taoResultServer\models\Import\Service\DeliveredTestOutcomeDeclarationsService;
use PHPUnit\Framework\TestCase;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\TestPart;
use oat\taoQtiTest\models\runner\QtiRunnerServiceContext;

class DeliveredTestOutcomeDeclarationsServiceTest extends TestCase
{
    private QtiRunnerService $qtiRunnerServiceMock;
    private DeliveryExecutionService $deliveryExecutionServiceMock;
    private DeliveryContainerService $deliveryContainerServiceMock;

    public function setUp(): void
    {
        $this->qtiRunnerServiceMock = $this->createMock(QtiRunnerService::class);
        $this->deliveryExecutionServiceMock = $this->createMock(DeliveryExecutionService::class);
        $this->deliveryContainerServiceMock = $this->createMock(DeliveryContainerService::class);
        $this->item = [
            'type' => 'qti',
            'data' => [
                'outcomes' =>
                    [
                        [
                            'identifier' => 'SCORE',
                            'attributes' =>
                                [
                                    'identifier' => 'SCORE',
                                ],
                        ],
                        [
                            'identifier' => 'MAXSCORE',
                            'attributes' =>
                                [
                                    'identifier' => 'MAXSCORE',
                                ],
                        ],
                        [
                            'identifier' => 'OUTCOME_1',
                            'attributes' =>
                                [
                                    'identifier' => 'OUTCOME_1',
                                    'externalScored' => 'externalMachine',
                                ],
                        ],
                    ],
            ]
        ];
    }

    public function testGetItemsByDeliveryExecutionIdReturnStructure()
    {
        $extendedAssessmentItemRef = $this->createMock(ExtendedAssessmentItemRef::class);
        $extendedAssessmentItemRef
            ->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('test-item-1');

        $extendedAssessmentSectionMock = $this->createMock(ExtendedAssessmentSection::class);
        $extendedAssessmentSectionMock
            ->expects($this->once())
            ->method('getSectionParts')
            ->willReturn([$extendedAssessmentItemRef]);

        $testPartMock = $this->createMock(TestPart::class);
        $testPartMock
            ->expects($this->once())
            ->method('getAssessmentSections')
            ->willReturn([$extendedAssessmentSectionMock]);

        $definitionMock = $this->createMock(AssessmentTest::class);
        $definitionMock->expects($this->once())->method('getTestParts')->willReturn([$testPartMock]);

        $this->qtiRunnerServiceMock->method('getItemData')->willReturn($this->item);
        $qtiTestItemsServicePartialMock = $this
            ->getMockBuilder(DeliveredTestOutcomeDeclarationsService::class)
            ->onlyMethods(['getDefinition','getServiceContext'])
            ->setConstructorArgs(
                [
                    $this->qtiRunnerServiceMock,
                    $this->deliveryExecutionServiceMock,
                    $this->deliveryContainerServiceMock
                ]
            )
            ->getMock();
        $qtiTestItemsServicePartialMock
            ->expects($this->once())
            ->method('getDefinition')
            ->willReturn($definitionMock);

        $contextMock = $this->createMock(QtiRunnerServiceContext::class);
        $qtiTestItemsServicePartialMock
            ->expects($this->once())
            ->method('getServiceContext')
            ->willReturn($contextMock);

        $testItems = $qtiTestItemsServicePartialMock
            ->getDeliveredTestOutcomeDeclarations('test_execution_id');
        $this->assertIsArray($testItems);
        $this->assertArrayNotHasKey('type', $testItems);
        $this->assertArrayNotHasKey('data', $testItems);

        $testItems = current($testItems);
        $this->assertCount(3, $testItems['outcomes']);

        foreach ($testItems['outcomes'] as $outcome) {
            $this->assertArrayHasKey('identifier', $outcome);
            $this->assertArrayHasKey('attributes', $outcome);
            $this->assertArrayHasKey('identifier', $outcome['attributes']);
            $this->assertSame($outcome['identifier'], $outcome['attributes']['identifier']);
        }

        $this->assertArrayHasKey(2, $testItems['outcomes']);
        $this->assertArrayHasKey('externalScored', $testItems['outcomes'][2]['attributes']);
        $this->assertSame('externalMachine', $testItems['outcomes'][2]['attributes']['externalScored']);
    }
}

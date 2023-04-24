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
use oat\taoProctoring\model\execution\DeliveryExecutionManagerService;
use oat\taoQtiTest\models\runner\QtiRunnerService;
use oat\taoResultServer\models\Import\Service\QtiTestItemsService;
use PHPUnit\Framework\TestCase;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\TestPart;
use oat\taoQtiTest\models\runner\QtiRunnerServiceContext;

class QtiTestItemsServiceTest extends TestCase
{
    private QtiRunnerService $qtiRunnerServiceMock;
    private DeliveryExecutionManagerService $deliveryExecutionServiceMock;
    private DeliveryContainerService $deliveryContainerServiceMock;

    public function setUp(): void
    {
        $this->qtiRunnerServiceMock = $this->createMock(QtiRunnerService::class);
        $this->deliveryExecutionServiceMock = $this->createMock(DeliveryExecutionManagerService::class);
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
        $extendedAssessmentItemRef->expects($this->once())->method('getIdentifier')->willReturn('test-item-1');
        $extendedAssessmentItemRef->expects($this->once())->method('getHref')->willReturn('test-href');

        $extendedAssessmentSectionMock = $this->createMock(ExtendedAssessmentSection::class);
        $extendedAssessmentSectionMock->expects($this->once())->method('getSectionParts')->willReturn([$extendedAssessmentItemRef]);
        $extendedAssessmentSectionMock->expects($this->once())->method('getIdentifier')->willReturn('test-section-1');

        $testPartMock = $this->createMock(TestPart::class);
        $testPartMock->expects($this->once())->method('getAssessmentSections')->willReturn([$extendedAssessmentSectionMock]);
        $testPartMock->expects($this->once())->method('getIdentifier')->willReturn('test-part-1');

        $definitionMock = $this->createMock(AssessmentTest::class);
        $definitionMock->expects($this->once())->method('getTestParts')->willReturn([$testPartMock]);

        $this->qtiRunnerServiceMock->method('getItemData')->willReturn($this->item);
        $qtiTestItemsServicePartialMock = $this
            ->getMockBuilder(QtiTestItemsService::class)
            ->setMethodsExcept(['getItemsByDeliveryExecutionId'])
            ->onlyMethods(['getDefinition', 'getServiceContext'])
            ->setConstructorArgs([$this->qtiRunnerServiceMock, $this->deliveryExecutionServiceMock, $this->deliveryContainerServiceMock])
            ->getMock();
        $qtiTestItemsServicePartialMock->expects($this->once())->method('getDefinition')->willReturn($definitionMock);

        $contextMock = $this->createMock(QtiRunnerServiceContext::class);
        $qtiTestItemsServicePartialMock->expects($this->once())->method('getServiceContext')->willReturn($contextMock);

        $testItems = $qtiTestItemsServicePartialMock->getItemsByDeliveryExecutionId('test_execution_id');
        $this->assertIsArray($testItems);
        $this->assertArrayNotHasKey('type', $testItems);
        $this->assertArrayNotHasKey('data', $testItems);

        $this->assertArrayHasKey('test-part-1', $testItems);
        $this->assertArrayHasKey('test-section-1', $testItems['test-part-1']);
        $this->assertArrayHasKey('test-item-1', $testItems['test-part-1']['test-section-1']);
        $this->assertArrayHasKey('outcomes', $testItems['test-part-1']['test-section-1']['test-item-1']);

        $this->assertCount(3, $testItems['test-part-1']['test-section-1']['test-item-1']['outcomes']);

        foreach ($testItems['test-part-1']['test-section-1']['test-item-1']['outcomes'] as $outcome) {
            $this->assertArrayHasKey('identifier', $outcome);
            $this->assertArrayHasKey('attributes', $outcome);
            $this->assertArrayHasKey('identifier', $outcome['attributes']);
            $this->assertSame($outcome['identifier'], $outcome['attributes']['identifier']);
        }

        $this->assertArrayHasKey(2, $testItems['test-part-1']['test-section-1']['test-item-1']['outcomes']);
        $this->assertArrayHasKey('externalScored', $testItems['test-part-1']['test-section-1']['test-item-1']['outcomes'][2]['attributes']);
        $this->assertSame('externalMachine', $testItems['test-part-1']['test-section-1']['test-item-1']['outcomes'][2]['attributes']['externalScored']);

        $this->assertTrue($testItems['test-part-1']['test-section-1']['test-item-1']['isExternallyScored']);
    }
}

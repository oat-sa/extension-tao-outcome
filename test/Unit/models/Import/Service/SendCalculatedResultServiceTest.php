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

use oat\oatbox\event\EventManager;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoOutcomeRds\model\RdsResultStorage;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Import\Service\DeliveredTestOutcomeDeclarationsService;
use oat\taoResultServer\models\Import\Service\SendCalculatedResultService;
use PHPUnit\Framework\TestCase;
use taoResultServer_models_classes_OutcomeVariable as OutcomeVariable;
use stdClass;

class SendCalculatedResultServiceTest extends TestCase
{
    private RdsResultStorage $rdsResultStorageMock;
    private ResultServerService $resultServerServiceMock;
    private EventManager $eventManagerMock;
    private DeliveryExecutionService $deliveryExecutionServiceMock;
    private DeliveryExecution $deliveryExecutionMock;
    private DeliveredTestOutcomeDeclarationsService $QtiTestItemsService;
    private SendCalculatedResultService $sut;

    public function setUp(): void
    {
        $this->rdsResultStorageMock = $this->createMock(RdsResultStorage::class);
        $this->resultServerServiceMock = $this->createMock(ResultServerService::class);
        $this->eventManagerMock = $this->createMock(EventManager::class);
        $this->deliveryExecutionServiceMock = $this->createMock(DeliveryExecutionService::class);
        $this->deliveryExecutionMock = $this->createMock(DeliveryExecution::class);
        $this->QtiTestItemsService = $this->createMock(DeliveredTestOutcomeDeclarationsService::class);

        $this->resultServerServiceMock
            ->expects($this->any())
            ->method('getResultStorage')
            ->willReturn($this->rdsResultStorageMock);

        $this->eventManagerMock
            ->expects($this->any())
            ->method('trigger');

        $this->deliveryExecutionServiceMock
            ->expects($this->any())
            ->method('getDeliveryExecution')
            ->willReturn($this->deliveryExecutionMock);

        $this->sut = new SendCalculatedResultService(
            $this->resultServerServiceMock,
            $this->eventManagerMock,
            $this->deliveryExecutionServiceMock,
            $this->QtiTestItemsService
        );
    }

    public function testDeclarationIsScoredVariableNotGraded()
    {
        $qtiTestItems = $this->createDeclarations(1, true);
        $outcomeVariables = $this->createVariables(1, false);

        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->QtiTestItemsService
            ->expects($this->any())
            ->method('getDeliveredTestOutcomeDeclarations')
            ->willReturn($qtiTestItems);

        $return = $this->sut->sendByDeliveryExecutionId('test_delivery_id');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('isFullyGraded', $return);
        $this->assertFalse($return['isFullyGraded']);
    }

    public function testDeclarationIsNotScoredVariableIsGraded()
    {
        $qtiTestItems = $this->createDeclarations(1, false);

        $outcomeVariables = $this->createVariables(1, true);

        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->QtiTestItemsService
            ->expects($this->any())
            ->method('getDeliveredTestOutcomeDeclarations')
            ->willReturn($qtiTestItems);

        $return = $this->sut->sendByDeliveryExecutionId('test_delivery_id');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('isFullyGraded', $return);
        $this->assertTrue($return['isFullyGraded']);
    }

    public function testDeclarationIsNotScoredVariableNotGraded()
    {
        $qtiTestItems = $this->createDeclarations(1, false);

        $outcomeVariables = $this->createVariables(1, false);

        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->QtiTestItemsService
            ->expects($this->any())
            ->method('getDeliveredTestOutcomeDeclarations')
            ->willReturn($qtiTestItems);

        $return = $this->sut->sendByDeliveryExecutionId('test_delivery_id');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('isFullyGraded', $return);
        $this->assertTrue($return['isFullyGraded']);
    }

    public function testDeclarationIsScoredVariableIsGraded()
    {
        $qtiTestItems = $this->createDeclarations(1, true);

        $outcomeVariables = $this->createVariables(1, true);

        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->QtiTestItemsService
            ->expects($this->any())
            ->method('getDeliveredTestOutcomeDeclarations')
            ->willReturn($qtiTestItems);

        $return = $this->sut->sendByDeliveryExecutionId('test_delivery_id');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('isFullyGraded', $return);
        $this->assertTrue($return['isFullyGraded']);
    }

    /**
     * One of items need to be externally scored and it has connected variable which was scored
     */
    public function testMultipleDeclarationsOneIsScoredVariableIsGraded()
    {
        $qtiTestItems1 = $this->createDeclarations(2, true);
        $qtiTestItems2 = $this->createDeclarations(1, false);

        $qtiTestItems = array_merge($qtiTestItems1, $qtiTestItems2);

        $outcomeVariables = $this->createVariables(1, true);

        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->QtiTestItemsService
            ->expects($this->any())
            ->method('getDeliveredTestOutcomeDeclarations')
            ->willReturn($qtiTestItems);

        $return = $this->sut->sendByDeliveryExecutionId('test_delivery_id');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('isFullyGraded', $return);
        $this->assertFalse($return['isFullyGraded']);
    }

    /**
     * One of items need to be externally scored and it has connected variable which was not scored
     */
    public function testMultipleMixedDeclarationsVariableIsNotScored()
    {
        $qtiTestItems1 = $this->createDeclarations(2, true);
        $qtiTestItems2 = $this->createDeclarations(1, false);

        $qtiTestItems = array_merge($qtiTestItems1, $qtiTestItems2);
        $outcomeVariables1 = $this->createVariables(1, true);
        $outcomeVariables2 = $this->createVariables(2, false);
        $outcomeVariables = array_merge($outcomeVariables1, $outcomeVariables2);

        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->QtiTestItemsService
            ->expects($this->any())
            ->method('getDeliveredTestOutcomeDeclarations')
            ->willReturn($qtiTestItems);

        $return = $this->sut->sendByDeliveryExecutionId('test_delivery_id');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('isFullyGraded', $return);
        $this->assertFalse($return['isFullyGraded']);
    }

    /**
     * Multiple items need to be graded and all of them have scored variables
     */
    public function testMultipleGradedDeclarationsMultipleVariableScored()
    {
        $qtiTestItems1 = $this->createDeclarations(2, true);
        $qtiTestItems2 = $this->createDeclarations(1, true);

        $qtiTestItems = array_merge($qtiTestItems1, $qtiTestItems2);
        $outcomeVariables1 = $this->createVariables(1, true);
        $outcomeVariables2 = $this->createVariables(2, true);
        $outcomeVariables = array_merge($outcomeVariables1, $outcomeVariables2);

        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->QtiTestItemsService
            ->expects($this->any())
            ->method('getDeliveredTestOutcomeDeclarations')
            ->willReturn($qtiTestItems);

        $return = $this->sut->sendByDeliveryExecutionId('test_delivery_id');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('isFullyGraded', $return);
        $this->assertTrue($return['isFullyGraded']);
    }

    /**
     * Multiple items need to be graded and all of them have scored variables
     */
    public function testMultipleGradedDeclarationsMultipleVariableNotScored()
    {
        $qtiTestItems1 = $this->createDeclarations(2, true);
        $qtiTestItems2 = $this->createDeclarations(1, true);

        $qtiTestItems = array_merge($qtiTestItems1, $qtiTestItems2);
        $outcomeVariables1 = $this->createVariables(1, false);
        $outcomeVariables2 = $this->createVariables(2, false);
        $outcomeVariables = array_merge($outcomeVariables1, $outcomeVariables2);

        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->QtiTestItemsService
            ->expects($this->any())
            ->method('getDeliveredTestOutcomeDeclarations')
            ->willReturn($qtiTestItems);

        $return = $this->sut->sendByDeliveryExecutionId('test_delivery_id');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('isFullyGraded', $return);
        $this->assertFalse($return['isFullyGraded']);
    }

    private function createVariables(int $howMany, bool $isExternallyGraded): array
    {
        $list = [];
        for ($i = 1; $i <= $howMany; $i++) {
            $variable = $this->createVariable($i, $isExternallyGraded);
            $container = new stdClass();
            $container->variable = $variable;
            $container->item = sprintf('item_%d', $i);
            $container->callIdItem = sprintf('item-%d', $i);
            $list[] = [$container];
        }

        return $list;
    }

    private function createVariable(int $number, bool $isExternallyGraded): OutcomeVariable
    {
        $data = [
            'type' => OutcomeVariable::TYPE,
            'normalMaximum' => null,
            'normalMinimum' => null,
            'value' => uniqid(),
            'identifier' => sprintf('OUTCOME_%d', $number),
            'cardinality' => 'single',
            'baseType' => 'float',
            'epoch' => '0.69485100 1690808122',
            'externallyGraded' => $isExternallyGraded,
        ];
        return OutcomeVariable::fromData($data);
    }

    private function createDeclarations(int $howMany, $isExternallyScored): array
    {
        $declaration = [];
        for ($i = 1; $i <= $howMany; $i++) {
            $declaration = array_merge($declaration, $this->createDeclarationItem($i, $isExternallyScored));
        }
        return $declaration;
    }

    private function createDeclarationItem(int $number, bool $isExternallyScored): array
    {
        $item = [
            sprintf('item-%d', $number) => [
                'identifier' => sprintf('item_%d', $number),
                'outcomes' => [
                    [
                        'identifier' => sprintf('OUTCOME_%d', $number),
                        'attributes' =>
                            [
                                'identifier' => sprintf('OUTCOME_%d', $number),
                            ],
                    ]
                ]
            ]
        ];

        if ($isExternallyScored) {
            $item[sprintf('item-%d', $number)]['outcomes'][0]['attributes']['externalScored']
                = 'externalMachine';
        }

        return $item;
    }
}

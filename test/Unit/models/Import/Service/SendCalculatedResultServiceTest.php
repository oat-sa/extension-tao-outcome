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

use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use oat\ltiTestReview\models\QtiRunnerInitDataBuilder;
use oat\ltiTestReview\models\QtiRunnerInitDataBuilderFactory;
use oat\oatbox\event\EventManager;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoOutcomeRds\model\RdsResultStorage;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Import\Service\SendCalculatedResultService;
use PHPUnit\Framework\TestCase;
use taoResultServer_models_classes_OutcomeVariable;
use stdClass;

class SendCalculatedResultServiceTest extends TestCase
{
    private RdsResultStorage $rdsResultStorageMock;
    private ResultServerService $resultServerServiceMock;
    private EventManager $eventManagerMock;
    private DeliveryExecutionService $deliveryExecutionServiceMock;
    private DeliveryExecution $deliveryExecutionMock;
    private QtiRunnerInitDataBuilder $qtiRunnerInitDataBuilderMock;
    private QtiRunnerInitDataBuilderFactory $qtiRunnerInitDataBuilderFactoryMock;
    public string $gradingStatus;

    public function setUp(): void
    {
        $this->rdsResultStorageMock = $this->createMock(RdsResultStorage::class);
        $this->resultServerServiceMock = $this->createMock(ResultServerService::class);
        $this->eventManagerMock = $this->createMock(EventManager::class);
        $this->deliveryExecutionServiceMock = $this->createMock(DeliveryExecutionService::class);
        $this->deliveryExecutionMock = $this->createMock(DeliveryExecution::class);
        $this->qtiRunnerInitDataBuilderMock = $this->createMock(QtiRunnerInitDataBuilder::class);
        $this->qtiRunnerInitDataBuilderFactoryMock = $this->createMock(QtiRunnerInitDataBuilderFactory::class);

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

        $this->qtiRunnerInitDataBuilderFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->qtiRunnerInitDataBuilderMock);
    }

    public function testDeclarationIsScoredVariableNotGraded()
    {
        $qtiTestItems = $this->createDeclarations(true);

        $outcomeVariables = $this->createVariables(false);

        $return = $this->doDeliveryExecution($outcomeVariables, $qtiTestItems);

        $this->assertIsArray($return);
        $this->assertArrayHasKey('gradingStatus', $return);
        $this->assertSame(ScoreInterface::GRADING_PROGRESS_STATUS_PENDING_MANUAL, $return['gradingStatus']);
    }

    public function testDeclarationIsNotScoredVariableIsGraded()
    {
        $qtiTestItems = $this->createDeclarations(false);

        $outcomeVariables = $this->createVariables(true);

        $return = $this->doDeliveryExecution($outcomeVariables, $qtiTestItems);

        $this->assertIsArray($return);
        $this->assertArrayHasKey('gradingStatus', $return);
        $this->assertSame(ScoreInterface::GRADING_PROGRESS_STATUS_FULLY_GRADED, $return['gradingStatus']);
    }

    public function testDeclarationIsNotScoredVariableNotGraded()
    {
        $qtiTestItems = $this->createDeclarations(false);

        $outcomeVariables = $this->createVariables(false);

        $return = $this->doDeliveryExecution($outcomeVariables, $qtiTestItems);

        $this->assertIsArray($return);
        $this->assertArrayHasKey('gradingStatus', $return);
        $this->assertSame(ScoreInterface::GRADING_PROGRESS_STATUS_FULLY_GRADED, $return['gradingStatus']);
    }

    public function testDeclarationIsScoredVariableIsGraded()
    {
        $qtiTestItems = $this->createDeclarations(true);

        $outcomeVariables = $this->createVariables(true);

        $return = $this->doDeliveryExecution($outcomeVariables, $qtiTestItems);

        $this->assertIsArray($return);
        $this->assertArrayHasKey('gradingStatus', $return);
        $this->assertSame(ScoreInterface::GRADING_PROGRESS_STATUS_FULLY_GRADED, $return['gradingStatus']);
    }

    private function createVariables(bool $isExternallyGraded): array
    {
        $data = [
            'type' => taoResultServer_models_classes_OutcomeVariable::TYPE,
            'normalMaximum' => null,
            'normalMinimum' => null,
            'value' => 'MA==',
            'identifier' => 'OUTCOME_1',
            'cardinality' => 'single',
            'baseType' => 'float',
            'epoch' => '0.80757100 1681904685',
            'externallyGraded' => $isExternallyGraded,
        ];
        $variable = taoResultServer_models_classes_OutcomeVariable::fromData($data);

        $container = new stdClass;
        $container->variable = $variable;

        return [
            [
                $container
            ]
        ];
    }

    private function createDeclarations($isExternallyScored): array
    {
        return [
            'testPart-1' => [
                'assessmentSection-1' => [
                    'item-1' => [
                        'outcomes' => [
                            [
                                'identifier' => 'OUTCOME_1',
                                'attributes' =>
                                    [
                                        'identifier' => 'OUTCOME_1',
                                        'externalScored' => 'externalMachine',
                                    ],
                            ]
                        ],
                        'isExternallyScored' => $isExternallyScored
                    ]
                ],
            ]
        ];
    }

    private function doDeliveryExecution(array $outcomeVariables, array $qtiTestItems): array
    {
        $this->rdsResultStorageMock
            ->expects($this->any())
            ->method('getDeliveryVariables')
            ->willReturn($outcomeVariables);

        $this->qtiRunnerInitDataBuilderMock
            ->expects($this->any())
            ->method('getQtiTestItems')
            ->willReturn($qtiTestItems);

        $scrs = new SendCalculatedResultService(
            $this->resultServerServiceMock,
            $this->eventManagerMock,
            $this->deliveryExecutionServiceMock,
            $this->qtiRunnerInitDataBuilderFactoryMock
        );
        return $scrs->sendByDeliveryExecutionId('test_delivery_id');
    }
}

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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\Import\Service;

use common_exception_Error;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Events\DeliveryExecutionResultsRecalculated;
use stdClass;
use taoResultServer_models_classes_ReadableResultStorage as ReadableResultStorage;
use taoResultServer_models_classes_Variable;

class SendCalculatedResultService
{
    private ResultServerService $resultServerService;
    private EventManager $eventManager;
    private DeliveryExecutionService $deliveryExecutionService;
    private QtiTestItemsService $qtiTestItemsService;

    public function __construct(
        ResultServerService $resultServerService,
        EventManager $eventManager,
        DeliveryExecutionService $deliveryExecutionService,
        QtiTestItemsService $qtiTestItemsService
    )
    {
        $this->resultServerService = $resultServerService;
        $this->eventManager = $eventManager;
        $this->deliveryExecutionService = $deliveryExecutionService;
        $this->qtiTestItemsService = $qtiTestItemsService;
    }

    public function sendByDeliveryExecutionId(string $deliveryExecutionId): array
    {
        $deliveryExecution = $this->deliveryExecutionService->getDeliveryExecution($deliveryExecutionId);
        $outcomeVariables = $this->getResultsStorage()->getDeliveryVariables($deliveryExecutionId);

        [$scoreTotal, $scoreTotalMax] = $this->getScores($outcomeVariables);

        $gradingStatus = $this->getGradingStatus($deliveryExecutionId, $outcomeVariables);

        $this->eventManager->trigger(
            new DeliveryExecutionResultsRecalculated(
                $deliveryExecution,
                $scoreTotal,
                $scoreTotalMax,
                $gradingStatus,
            )
        );

        return [
            'deliveryExecution' => $deliveryExecution,
            'scoreTotal' => $scoreTotal,
            'scoreTotalMax' => $scoreTotalMax,
            'gradingStatus' => $gradingStatus,
        ];
    }

    /**
     * @throws common_exception_Error
     * @throws InvalidServiceManagerException
     */
    private function getResultsStorage(): ReadableResultStorage
    {
        $storage = $this->resultServerService->getResultStorage();

        if (!$storage instanceof ReadableResultStorage) {
            throw new common_exception_Error('Configured result storage is not writable.');
        }

        return $storage;
    }

    private function getScores(array $outcomeVariables): array
    {
        $scoreTotal = null;
        $scoreTotalMax = null;

        foreach ($outcomeVariables as $outcomeVariable) {
            if (!is_array($outcomeVariable)) {
                continue;
            }

            /** @var stdClass $variable */
            $variable = current($outcomeVariable);

            if (!is_object($variable) || !property_exists($variable, 'variable')) {
                continue;
            }

            /** @var taoResultServer_models_classes_Variable $variable */
            $variable = $variable->variable;
            if (!$variable instanceof taoResultServer_models_classes_Variable) {
                continue;
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL') {
                $scoreTotal = (float)$variable->getValue();

                continue;
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL_MAX') {
                $scoreTotalMax = (float)$variable->getValue();
            }
        }
        return [$scoreTotal, $scoreTotalMax];
    }

    private function getGradingStatus(string $deliveryExecutionId, array $outcomeVariables): string
    {
        $qtiTestItems = $this->qtiTestItemsService->getItemsByDeliveryExecutionId($deliveryExecutionId);
        $gradingStatus = ScoreInterface::GRADING_PROGRESS_STATUS_FULLY_GRADED;

        foreach ($qtiTestItems as $parts) {
            foreach ($parts as $section) {
                foreach ($section as $item) {
                    if ($item['isExternallyScored'] === false) {
                        continue;
                    }
                    $gradingStatus = ScoreInterface::GRADING_PROGRESS_STATUS_PENDING_MANUAL;
                    foreach ($item['outcomes'] ?? [] as $outcomeDeclaration) {
                        $isOutcomeVariableFullyGraded = $this->isOutcomeVariableFullyGraded(
                            $outcomeVariables,
                            $outcomeDeclaration['identifier']
                        );
                        if ($isOutcomeVariableFullyGraded) {
                            $gradingStatus = ScoreInterface::GRADING_PROGRESS_STATUS_FULLY_GRADED;
                        }
                    }
                }
            }
        }
        return $gradingStatus;
    }

    private function isOutcomeVariableFullyGraded(array $outcomeVariables, string $outcomeDeclarationIdentifier): bool
    {
        foreach ($outcomeVariables as $outcomeVariableArray) {
            $outcomeVariable = current($outcomeVariableArray);
            if (!$outcomeVariable->variable instanceof taoResultServer_models_classes_Variable) {
                continue;
            }
            $variable = $outcomeVariable->variable;
            if ($outcomeDeclarationIdentifier !== $variable->getIdentifier()) {
                continue;
            }
            if ($variable->getExternallyGraded()) {
                return true;
            }
        }
        return false;
    }
}

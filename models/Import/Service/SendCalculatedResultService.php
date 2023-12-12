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
use oat\oatbox\event\EventManager;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Events\DeliveryExecutionResultsRecalculated;
use stdClass;
use taoResultServer_models_classes_ReadableResultStorage as ReadableResultStorage;
use taoResultServer_models_classes_Variable as ResultVariable;
use taoResultServer_models_classes_OutcomeVariable as OutcomeVariable;
use tao_helpers_Date as DateHelper;

class SendCalculatedResultService
{
    private ResultServerService $resultServerService;
    private EventManager $eventManager;
    private DeliveryExecutionService $deliveryExecutionService;
    private DeliveredTestOutcomeDeclarationsService $deliveredTestOutcomeDeclarationsService;

    public function __construct(
        ResultServerService $resultServerService,
        EventManager $eventManager,
        DeliveryExecutionService $deliveryExecutionService,
        DeliveredTestOutcomeDeclarationsService $qtiTestItemsService
    ) {
        $this->resultServerService = $resultServerService;
        $this->eventManager = $eventManager;
        $this->deliveryExecutionService = $deliveryExecutionService;
        $this->deliveredTestOutcomeDeclarationsService = $qtiTestItemsService;
    }

    /**
     * @throws common_exception_Error
     * @throws \common_exception_NotFound
     * @throws InvalidServiceManagerException
     */
    public function sendByDeliveryExecutionId(string $deliveryExecutionId): array
    {
        $deliveryExecution = $this->deliveryExecutionService->getDeliveryExecution($deliveryExecutionId);
        $outcomeVariables = $this->getResultsStorage()->getDeliveryVariables($deliveryExecutionId);

        [$scoreTotal, $scoreTotalMax] = $this->getScores($outcomeVariables);

        $isFullyGraded = $this->checkIsFullyGraded($deliveryExecutionId, $outcomeVariables);

        $timestamp = DateHelper::formatMicrotime($this->getScoreTotalTimestamp($outcomeVariables));

        $this->eventManager->trigger(
            new DeliveryExecutionResultsRecalculated(
                $deliveryExecution,
                $scoreTotal,
                $scoreTotalMax,
                $isFullyGraded,
                $timestamp
            )
        );

        return [
            'deliveryExecution' => $deliveryExecution,
            'scoreTotal' => $scoreTotal,
            'scoreTotalMax' => $scoreTotalMax,
            'isFullyGraded' => $isFullyGraded,
            'timestamp' => $timestamp
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

            /** @var ResultVariable $variable */
            $variable = $variable->variable;
            if (!$variable instanceof ResultVariable) {
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

    private function checkIsFullyGraded(string $deliveryExecutionId, array $outcomeVariables): bool
    {
        $testItemsData = $this->deliveredTestOutcomeDeclarationsService
            ->getDeliveredTestOutcomeDeclarations($deliveryExecutionId);

        $isFullyGraded = true;
        foreach ($testItemsData as $itemIdentifier => $itemData) {
            foreach ($itemData['outcomes'] ?? [] as $outcomeDeclaration) {
                if (!isset($outcomeDeclaration['attributes']['externalScored'])) {
                    continue;
                }
                $isFullyGraded = false;
                $isSubjectOutcomeVariableGraded = $this->isSubjectOutcomeVariableGraded(
                    $outcomeVariables,
                    $outcomeDeclaration['identifier'],
                    $itemIdentifier,
                );
                if ($isSubjectOutcomeVariableGraded) {
                    $isFullyGraded = true;
                }
            }
        }
        return $isFullyGraded;
    }

    private function isSubjectOutcomeVariableGraded(
        array $outcomeVariables,
        string $outcomeDeclarationIdentifier,
        string $itemIdentifier
    ): bool {
        foreach ($outcomeVariables as $outcomeVariableArray) {
            $outcomeVariable = current($outcomeVariableArray);
            $outcomeItemIdentifier = $outcomeVariable->callIdItem;
            if ($outcomeItemIdentifier !== null && strpos($outcomeItemIdentifier, $itemIdentifier) === false) {
                continue;
            }

            if (!$outcomeVariable->variable instanceof ResultVariable) {
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

    private function getScoreTotalTimestamp(array $outcomeVariables): ?string
    {
        foreach ($outcomeVariables as $outcomeVariable) {
            if ($outcomeVariable instanceof OutcomeVariable && $outcomeVariable->getIdentifier() === 'SCORE_TOTAL') {
                return $outcomeVariable->getEpoch();
            }
        }
        return null;
    }

    private function getLatestOutcomesTimestamp(array $outcomeVariables): ?string
    {
        $microtimeList = array_map(function ($outcome) {
            $outcome = end($outcome);
            if ($outcome->variable instanceof OutcomeVariable) {
                return $outcome->variable->getEpoch();
            }
            return 0;
        }, $outcomeVariables);
        $sortedMicrotime = $this->sortMicrotimeList(array_filter($microtimeList));
        return array_pop($sortedMicrotime);
    }

    private function sortMicrotimeList(array $microtimeList): array
    {
        usort($microtimeList, function ($a, $b) {
            // Extract the values from each string
            $microtimeA = explode(' ', $a);
            $microtimeB = explode(' ', $b);

            // Compare the timestamp values
            $compareTimestamp = (float)$microtimeA[1] - (float)$microtimeB[1];

            // If the timestamp values are equal, compare the microseconds
            if ($compareTimestamp == 0) {
                $d = (float)$microtimeA[0] - (float)$microtimeB[0];
                if ($d == 0) {
                    return 0;
                } elseif ($d > 0) {
                    return 1;
                } else {
                    return -1;
                }
            }

            return $compareTimestamp;
        });

        return $microtimeList;
    }
}

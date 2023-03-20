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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\Import\Service;

use common_exception_Error;
use core_kernel_persistence_Exception;
use oat\generis\model\data\Ontology;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoOutcomeRds\model\AbstractRdsResultStorage;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Exception\ImportResultException;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use taoResultServer_models_classes_ResponseVariable;
use taoResultServer_models_classes_Variable;
use Throwable;

class ResultImporter
{
    private Ontology $ontology;
    private ResultServerService $resultServerService;

    public function __construct(Ontology $ontology, ResultServerService $resultServerService)
    {
        $this->ontology = $ontology;
        $this->resultServerService = $resultServerService;
    }

    /**
     * @param ImportResultInput $input
     * @return void
     * @throws core_kernel_persistence_Exception|Throwable|common_exception_Error|ImportResultException
     */
    public function importByResultInput(ImportResultInput $input): void
    {
        $resultStorage = $this->getResultStorage();
        $deliveryExecutionUri = $input->getDeliveryExecutionId();
        $testUri = $this->getTestUri($resultStorage, $deliveryExecutionUri);
        $testScoreVariables = $this->getTestScoreVariables($resultStorage, $deliveryExecutionUri);

        $resultStorage->getPersistence()->transactional(
            function () use ($resultStorage, $input, $testUri, $deliveryExecutionUri, $testScoreVariables) {
                $this->updateItemResponseVariables($resultStorage, $input, $testUri);

                $updatedScoreTotal = $this->updateItemOutcomeVariables(
                    $resultStorage,
                    $input,
                    $testUri,
                    $testScoreVariables['scoreTotalMax'],
                    $testScoreVariables['updatedScoreTotal']
                );

                $this->updateTestVariables(
                    $resultStorage,
                    $testScoreVariables['scoreTotalVariable'],
                    $testScoreVariables['scoreTotalVariableId'],
                    $deliveryExecutionUri,
                    $testUri,
                    $updatedScoreTotal
                );
            }
        );
    }

    private function updateTestVariables(
        $resultStorage,
        taoResultServer_models_classes_Variable $scoreTotalVariable,
        int $scoreTotalVariableId,
        string $deliveryExecutionUri,
        string $testUri,
        float $updatedScoreTotal
    ): void {
        $scoreTotalVariable->setValue($updatedScoreTotal);

        $resultStorage->replaceTestVariables(
            $deliveryExecutionUri,
            $testUri,
            $deliveryExecutionUri,
            [
                $scoreTotalVariableId => $scoreTotalVariable
            ]
        );
    }

    private function updateItemResponseVariables(
        AbstractRdsResultStorage $resultStorage,
        ImportResultInput $input,
        string $testUri
    ): void {
        $deliveryExecutionUri = $input->getDeliveryExecutionId();

        foreach ($input->getResponses() as $itemId => $responses) {
            $callItemId = $this->createCallItemId($deliveryExecutionUri, $itemId);
            $itemVariables = [];

            foreach ($responses as $responseId => $responseValue) {
                if (!array_key_exists('correctResponse', $responseValue)) {
                    continue;
                }

                $responseVariable = $this->getItemVariable(
                    $resultStorage,
                    $deliveryExecutionUri,
                    $itemId,
                    $callItemId,
                    $responseId
                );

                /** @var taoResultServer_models_classes_ResponseVariable $variable */
                $variable = $responseVariable['variable'];
                $variableId = $responseVariable['variableId'];
                $itemUri = $responseVariable['itemUri'];

                $variable->setCorrectResponse(boolval($responseValue['correctResponse']));

                $itemVariables[$variableId] = $variable;
            }

            $resultStorage->replaceItemVariables(
                $deliveryExecutionUri,
                $testUri,
                $itemUri,
                $callItemId,
                $itemVariables
            );
        }
    }

    /**
     * @throws ImportResultException
     */
    private function updateItemOutcomeVariables(
        AbstractRdsResultStorage $resultStorage,
        ImportResultInput $input,
        string $testUri,
        float $scoreTotalMax,
        float $updatedScoreTotal
    ): float {
        $deliveryExecutionUri = $input->getDeliveryExecutionId();

        foreach ($input->getOutcomes() as $itemId => $outcomes) {
            $itemUri = null;
            $updateOutcomeVariables = [];
            $callItemId = $this->createCallItemId($deliveryExecutionUri, $itemId);

            foreach ($outcomes as $outcomeId => $outcomeValue) {
                $outcomeVariable = $this->getItemVariable(
                    $resultStorage,
                    $deliveryExecutionUri,
                    $itemId,
                    $callItemId,
                    $outcomeId
                );

                /** @var taoResultServer_models_classes_Variable $variable */
                $variable = $outcomeVariable['variable'];
                $itemUri = $outcomeVariable['itemUri'];
                $variableId = $outcomeVariable['variableId'];

                $updatedScoreTotal -= (float)$variable->getValue();
                $updatedScoreTotal += $outcomeValue;

                $variable->setValue($outcomeValue);

                $updateOutcomeVariables[$variableId] = $variable;

                if ($updatedScoreTotal > $scoreTotalMax) {
                    throw new ImportResultException(
                        sprintf(
                            'SCORE_TOTAL_MAX cannot be higher than %s, %s provided',
                            $scoreTotalMax,
                            $updatedScoreTotal
                        )
                    );
                }
            }

            $resultStorage->replaceItemVariables(
                $deliveryExecutionUri,
                $testUri,
                $itemUri,
                $callItemId,
                $updateOutcomeVariables
            );
        }

        return $updatedScoreTotal;
    }

    /**
     * @throws ImportResultException
     */
    private function getTestScoreVariables(AbstractRdsResultStorage $resultStorage, string $deliveryExecutionUri): array
    {
        /** @var taoResultServer_models_classes_ResponseVariable $scoreTotalVariable */
        $scoreTotalVariable = null;
        $scoreTotal = null;
        $scoreTotalMax = null;
        $updatedScoreTotal = null;
        $scoreTotalVariableId = null;
        $outcomeVariables = $resultStorage->getDeliveryVariables($deliveryExecutionUri);

        foreach ($outcomeVariables as $id => $outcomeVariable) {
            $variable = $this->getVariable($outcomeVariable);

            if ($variable === null) {
                continue;
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL') {
                $scoreTotalVariableId = $id;
                $scoreTotal = (float)$variable->getValue();

                $updatedScoreTotal = $scoreTotal;
                $scoreTotalVariable = $variable;

                continue;
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL_MAX') {
                $scoreTotalMax = (float)$variable->getValue();
            }
        }

        if ($scoreTotal === null) {
            throw new ImportResultException(
                sprintf(
                    'SCORE_TOTAL is null for delivery execution %s',
                    $deliveryExecutionUri
                )
            );
        }

        return [
            'scoreTotalVariableId' => $scoreTotalVariableId,
            'scoreTotalVariable' => $scoreTotalVariable,
            'updatedScoreTotal' => $updatedScoreTotal,
            'scoreTotalMax' => $scoreTotalMax,
        ];
    }

    /**
     * @throws ImportResultException
     */
    private function getItemVariable(
        AbstractRdsResultStorage $resultStorage,
        string $deliveryExecutionUri,
        string $itemId,
        string $callItemId,
        string $variableIdentifier
    ): array {
        $variableVersions = $resultStorage->getVariable($callItemId, $variableIdentifier);

        if (!is_array($variableVersions) || empty($variableVersions)) {
            throw new ImportResultException(
                sprintf(
                    'Variable %s not found for item %s on delivery execution %s',
                    $variableIdentifier,
                    $itemId,
                    $deliveryExecutionUri
                )
            );
        }

        $lastVariable = (array)end($variableVersions);

        if (empty($lastVariable)) {
            throw new ImportResultException(
                sprintf(
                    'There is no variable %s for %s',
                    $variableIdentifier,
                    $callItemId
                )
            );
        }

        /** @var taoResultServer_models_classes_Variable $variable */
        $variable = $lastVariable['variable'] ?? null;

        if (!$variable instanceof taoResultServer_models_classes_Variable) {
            throw new ImportResultException(
                sprintf(
                    'Variable %s is typeof %s, expected instance of %s, for item %s and execution %s',
                    $variableIdentifier,
                    get_class($variable),
                    taoResultServer_models_classes_Variable::class,
                    $itemId,
                    $deliveryExecutionUri
                )
            );
        }

        return [
            'itemUri' => $lastVariable['item'] ?? null,
            'variableId' => key($variableVersions),
            'variable' => $variable,
        ];
    }

    /**
     * @param array|mixed $outcomeVariable
     */
    private function getVariable($outcomeVariable): ?taoResultServer_models_classes_Variable
    {
        if (!is_array($outcomeVariable)) {
            return null;
        }

        $variable = current($outcomeVariable);

        if (!is_object($variable) || !property_exists($variable, 'variable')) {
            return null;
        }

        /** @var taoResultServer_models_classes_Variable $variable */
        $variable = $variable->variable;

        if ($variable instanceof taoResultServer_models_classes_Variable) {
            return $variable;
        }

        return null;
    }

    private function createCallItemId(string $deliveryExecutionUri, string $itemId): string
    {
        return sprintf('%s.%s.0', $deliveryExecutionUri, $itemId);
    }

    /**
     * @throws core_kernel_persistence_Exception
     */
    private function getTestUri(AbstractRdsResultStorage $resultStorage, string $deliveryExecutionUri): string
    {
        $deliveryId = $resultStorage->getDelivery($deliveryExecutionUri);

        return (string)$this->ontology->getResource($deliveryId)
            ->getOnePropertyValue($this->ontology->getProperty(DeliveryAssemblyService::PROPERTY_ORIGIN));
    }

    /**
     * @throws ImportResultException
     * @throws common_exception_Error
     */
    private function getResultStorage(): AbstractRdsResultStorage
    {
        $resultStorage = $this->resultServerService->getResultStorage();

        if ($resultStorage instanceof AbstractRdsResultStorage) {
            return $resultStorage;
        }

        throw new ImportResultException(
            sprintf(
                'ResultStorage must be an instance of %s. Instance of %s provided',
                AbstractRdsResultStorage::class,
                get_class($resultStorage)
            )
        );
    }
}

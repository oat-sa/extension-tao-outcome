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
use common_exception_NotFound;
use core_kernel_persistence_Exception;
use oat\generis\model\data\Ontology;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoOutcomeRds\model\AbstractRdsResultStorage;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Exception\ImportResultException;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use stdClass;
use taoResultServer_models_classes_ResponseVariable;
use taoResultServer_models_classes_Variable;

class ResultImporter
{
    private Ontology $ontology;
    private ResultServerService $resultServerService;
    private DeliveryExecutionService $deliveryExecutionService;

    public function __construct(
        Ontology $ontology,
        ResultServerService $resultServerService,
        DeliveryExecutionService $deliveryExecutionService
    ) {
        $this->ontology = $ontology;
        $this->resultServerService = $resultServerService;
        $this->deliveryExecutionService = $deliveryExecutionService;
    }

    /**
     * @param ImportResultInput $input
     * @return void
     * @throws ImportResultException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws core_kernel_persistence_Exception
     */
    public function importByResultInput(ImportResultInput $input): void
    {
        //@TODO Add unit tests
        //@TODO Refactor the code to be more maintainable

        $resultStorage = $this->getResultStorage();

        $deliveryExecutionId = $input->getDeliveryExecutionId();
        $deliveryExecution = $this->deliveryExecutionService->getDeliveryExecution($deliveryExecutionId);
        $delivery = $deliveryExecution->getDelivery();
        $test = $this->ontology->getResource($delivery->getUri())
            ->getOnePropertyValue($this->ontology->getProperty(DeliveryAssemblyService::PROPERTY_ORIGIN));
        $testUri = $test->getUri();

        $deliveryExecutionId = $input->getDeliveryExecutionId();
        $outcomeVariables = $resultStorage->getDeliveryVariables($deliveryExecutionId);

        $this->updateResponses($resultStorage, $input, $testUri);

        /** @var taoResultServer_models_classes_ResponseVariable $scoreTotalVariable */
        $scoreTotalVariable = null;
        $scoreTotal = null;
        $scoreTotalMax = null;
        $updatedScoreTotal = null;
        $scoreTotalVariableId = null;

        foreach ($outcomeVariables as $id => $outcomeVariable) {
            if (!is_array($outcomeVariable)) {
                continue;
            }

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
                    $deliveryExecutionId
                )
            );
        }

        foreach ($input->getOutcomes() as $itemId => $outcomes) {
            $callItemId = sprintf('%s.%s.0', $deliveryExecutionId, $itemId);
            $itemUri = null;
            $updateOutcomeVariables = [];

            foreach ($outcomes as $outcomeId => $outcomeValue) {
                $outcomeVariableVersions = $resultStorage->getVariable($callItemId, $outcomeId);

                if (!is_array($outcomeVariableVersions) || empty($outcomeVariableVersions)) {
                    throw new ImportResultException(
                        sprintf(
                            'Outcome variable %s not found for item %s on delivery execution %s',
                            $outcomeId,
                            $itemId,
                            $deliveryExecutionId
                        )
                    );
                }

                $lastOutcomeVariable = (array)end($outcomeVariableVersions);

                if (empty($lastOutcomeVariable)) {
                    throw new ImportResultException(
                        sprintf(
                            'There is no outcome variable %s for %s',
                            $outcomeId,
                            $callItemId
                        )
                    );
                }

                /** @var taoResultServer_models_classes_Variable $variable */
                $variable = $lastOutcomeVariable['variable'] ?? null;
                $itemUri = $lastOutcomeVariable['item'] ?? null;
                $variableId = key($outcomeVariableVersions);

                if (!$variable instanceof taoResultServer_models_classes_Variable) {
                    throw new ImportResultException(
                        sprintf(
                            'Outcome variable %s is typeof %s, expected instance of %s, for item %s and execution %s',
                            $outcomeId,
                            get_class($variable),
                            taoResultServer_models_classes_Variable::class,
                            $itemId,
                            $deliveryExecutionId
                        )
                    );
                }

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
                $deliveryExecutionId,
                $testUri,
                $itemUri,
                $callItemId,
                $updateOutcomeVariables
            );
        }

        $scoreTotalVariable->setValue($updatedScoreTotal);

        $resultStorage->replaceTestVariables(
            $deliveryExecutionId,
            $testUri,
            $deliveryExecutionId,
            [
                $scoreTotalVariableId => $scoreTotalVariable
            ]
        );
    }

    private function updateResponses(
        AbstractRdsResultStorage $resultStorage,
        ImportResultInput $input,
        string $testUri
    ): void {
        $deliveryExecutionId = $input->getDeliveryExecutionId();

        foreach ($input->getResponses() as $itemId => $responses) {
            $callItemId = sprintf('%s.%s.0', $deliveryExecutionId, $itemId);
            $itemVariables = [];

            foreach ($responses as $responseId => $responseValue) {
                if (!isset($responseValue['correctResponse'])) {
                    continue;
                }

                $correctResponse = boolval($responseValue['correctResponse']);

                $responseVariableVersions = $resultStorage->getVariable($callItemId, $responseId);

                if (!is_array($responseVariableVersions) || empty($responseVariableVersions)) {
                    throw new ImportResultException(
                        sprintf(
                            'Response variable %s not found for item %s on delivery execution %s',
                            $responseId,
                            $itemId,
                            $deliveryExecutionId
                        )
                    );
                }

                $lastResponseVariable = (array)end($responseVariableVersions);
                $variableId = key($responseVariableVersions);

                if (empty($lastResponseVariable)) {
                    throw new ImportResultException(
                        sprintf(
                            'There is no response variable %s for %s',
                            $responseId,
                            $callItemId
                        )
                    );
                }

                $itemUri = $lastResponseVariable['item'] ?? null;
                $variable = $lastResponseVariable['variable'] ?? null;

                if (!$variable instanceof taoResultServer_models_classes_ResponseVariable) {
                    throw new ImportResultException(
                        sprintf(
                            'Response variable %s is typeof %s, expected instance of %s, for item %s and execution %s',
                            $responseId,
                            $variable ? get_class($variable) : 'NULL',
                            taoResultServer_models_classes_ResponseVariable::class,
                            $itemId,
                            $deliveryExecutionId
                        )
                    );
                }

                $variable->setCorrectResponse($correctResponse);

                $itemVariables[$variableId] = $variable;
            }

            $resultStorage->replaceItemVariables(
                $deliveryExecutionId,
                $testUri,
                $itemUri,
                $callItemId,
                $itemVariables
            );
        }
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

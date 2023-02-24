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

namespace oat\taoResultServer\models\Import\Factory;

use common_exception_Error;
use core_kernel_persistence_Exception;
use oat\dtms\DateTime;
use oat\generis\model\data\Ontology;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Exception\ImportResultException;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use stdClass;
use taoResultServer_models_classes_ReadableResultStorage;
use taoResultServer_models_classes_ResponseVariable;
use taoResultServer_models_classes_Variable;

class QtiResultXmlFactory
{
    private Ontology $ontology;
    private ResultServerService $resultServerService;

    public function __construct(Ontology $ontology, ResultServerService $resultServerService)
    {
        $this->ontology = $ontology;
        $this->resultServerService = $resultServerService;
    }

    /**
     * @throws ImportResultException
     * @throws common_exception_Error
     * @throws core_kernel_persistence_Exception
     */
    public function createByImportResult(ImportResultInput $input): string
    {
        $itemResults = [];
        $timestamp = (new DateTime())->format(DATE_RFC3339_EXTENDED);
        $deliveryExecutionId = $input->getDeliveryExecutionId();

        $resultStorage = $this->resultServerService->getResultStorage();

        if (!$resultStorage instanceof ResultManagement) {
            throw new ImportResultException(
                sprintf(
                    'ResultsStorage must implement %s. Instance of %s provided',
                    ResultManagement::class,
                    get_class($resultStorage)
                )
            );
        }

        $outcomeVariables = $resultStorage->getDeliveryVariables($deliveryExecutionId);

        /** @var taoResultServer_models_classes_ResponseVariable $scoreTotalVariable */
        $scoreTotalVariable = null;
        $scoreTotal = null;
        $scoreTotalMax = null;
        $updatedScoreTotal = null;

        foreach ($outcomeVariables as $id => $outcomeVariable) {
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

                $updatedScoreTotal = $scoreTotal;
                $scoreTotalVariable = $variable;
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL_MAX') {
                $scoreTotalMax = (float)$variable->getValue();
            }
        }

        if (is_null($scoreTotal)) {
            throw new ImportResultException(
                sprintf(
                    'SCORE_TOTAL is null for delivery execution %s',
                    $deliveryExecutionId
                )
            );
        }

        foreach ($input->getOutcomes() as $itemId => $outcomes) {
            $callItemId = sprintf('%s.%s.0', $deliveryExecutionId, $itemId);

            foreach ($outcomes as $outcomeId => $outcomeValue) {
                $variable = $this->getCurrentOutcomeVariable(
                    $resultStorage,
                    $callItemId,
                    $outcomeId,
                    $itemId,
                    $deliveryExecutionId
                );

                $updatedScoreTotal = $updatedScoreTotal - (float)$variable->getValue();
                $updatedScoreTotal = $updatedScoreTotal + $outcomeValue;

                if ($updatedScoreTotal > $scoreTotalMax) {
                    throw new ImportResultException(
                        sprintf(
                            'SCORE_TOTAL_MAX cannot be higher than %s, %s provided',
                            $scoreTotalMax,
                            $updatedScoreTotal
                        )
                    );
                }

                $itemResults[] = sprintf(
                    '<itemResult identifier="%s" datestamp="%s" sessionStatus="final">
                    <outcomeVariable identifier="%s" cardinality="%s" baseType="%s" %s %s>
                        <value>%s</value>
                    </outcomeVariable>
                </itemResult>',
                    $itemId,
                    $timestamp,
                    $outcomeId,
                    $variable->getCardinality(),
                    $variable->getBaseType(),
                    $variable->getNormalMaximum() ? ('normalMaximum="' . $variable->getNormalMaximum() . '"') : '',
                    $variable->getNormalMinimum() ? ('normalMinimum="' . $variable->getNormalMinimum() . '"') : '',
                    $outcomeValue
                );
            }
        }

        $deliveryId = $resultStorage->getDelivery($deliveryExecutionId);
        $testUri = $this->ontology->getResource($deliveryId)
            ->getOnePropertyValue($this->ontology->getProperty(DeliveryAssemblyService::PROPERTY_ORIGIN));

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
                    <assessmentResult 
                        xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p1" 
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                        xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_result_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_result_v2p1.xsd">
                    <context/>
                    <testResult identifier="%s" datestamp="%s">
                        <outcomeVariable identifier="SCORE_TOTAL" cardinality="%s" baseType="%s">
                            <value>%s</value>
                        </outcomeVariable>
                    </testResult>
                    %s
                    </assessmentResult>',
            $testUri,
            $timestamp,
            $scoreTotalVariable->getCardinality(),
            $scoreTotalVariable->getBaseType(),
            $updatedScoreTotal,
            implode('', $itemResults)
        );
    }


    private function getCurrentOutcomeVariable(
        taoResultServer_models_classes_ReadableResultStorage $resultStorage,
        string $callItemId,
        string $outcomeId,
        string $itemId,
        string $deliveryExecutionId
    ): taoResultServer_models_classes_Variable {
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

        return $variable;
    }
}

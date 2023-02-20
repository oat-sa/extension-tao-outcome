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

use Exception;
use oat\dtms\DateTime;
use oat\generis\model\data\Ontology;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use taoResultServer_models_classes_OutcomeVariable;
use taoResultServer_models_classes_ResponseVariable;

class QtiResultXmlFactory
{
    private Ontology $ontology;
    private ResultServerService $resultServerService;

    public function __construct(Ontology $ontology, ResultServerService $resultServerService)
    {
        $this->ontology = $ontology;
        $this->resultServerService = $resultServerService;
    }

    public function createByImportResult(ImportResultInput $input): string
    {
        $itemResults = [];
        $timestamp = (new DateTime())->format(DATE_RFC3339_EXTENDED);
        $deliveryExecutionId = $input->getDeliveryExecutionId();

        $resultStorage = $this->resultServerService->getResultStorage();

        $outcomeVariables = $resultStorage->getDeliveryVariables($deliveryExecutionId);

        /** @var taoResultServer_models_classes_ResponseVariable $scoreTotalVariable */
        $scoreTotalVariable = null;
        $scoreTotal = null;
        $scoreTotalMax = null;
        $updatedScoreTotal = null;

        foreach ($outcomeVariables as $id => $outcomeVariable) {
            /** @var taoResultServer_models_classes_ResponseVariable $variable */
            $variable = current($outcomeVariable)->variable;

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
            throw new Exception(
                sprintf(
                    'SCORE_TOTAL is null for delivery execution %s',
                    $deliveryExecutionId
                )
            );
        }

        foreach ($input->getOutcomes() as $itemId => $outcomes) {
            $callItemId = sprintf('%s.%s.0', $deliveryExecutionId, $itemId);

            foreach ($outcomes as $outcomeId => $outcomeValue) {
                $outcomeVariableVersions = $resultStorage->getVariable($callItemId, $outcomeId);
                $lastOutcomeVariable = (array)end($outcomeVariableVersions);

                if (empty($lastOutcomeVariable)) {
                    throw new Exception(
                        sprintf(
                            'There is no outcome variable %s for %s',
                            $outcomeId,
                            $callItemId
                        )
                    );
                }

                /** @var taoResultServer_models_classes_OutcomeVariable $variable */
                $variable = $lastOutcomeVariable['variable'];

                $updatedScoreTotal = $updatedScoreTotal - (float)$variable->getValue();
                $updatedScoreTotal = $updatedScoreTotal + $outcomeValue;

                if ($updatedScoreTotal > $scoreTotalMax) {
                    throw new Exception(
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
}

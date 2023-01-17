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

namespace oat\taoResultServer\controller;

use http\Exception\BadQueryStringException;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoOutcomeRds\model\AbstractRdsResultStorage;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoResultServer\models\Events\DeliveryExecutionResultsRecalculated;
use tao_actions_RestController;
use taoResultServer_models_classes_OutcomeVariable;

class DeliveryExecutionResults extends tao_actions_RestController
{
    use EventManagerAwareTrait;

    private const Q_PARAM_DELIVERY_EXECUTION_ID = 'execution';
    private const Q_PARAM_TRIGGER_AGS_SEND = 'send_ags';

    public function patch(DeliveryExecutionService $deliveryExecutionService): void
    {
        $queryParams = $this->getPsrRequest()->getQueryParams();
        $this->validateRequestParams($queryParams);

        $deliveryExecution = $deliveryExecutionService->getDeliveryExecution(
            $queryParams[self::Q_PARAM_DELIVERY_EXECUTION_ID]
        );

        // Todo patch variables pending in scope of TR-4952

        if (isset($queryParams[self::Q_PARAM_TRIGGER_AGS_SEND])) {
            $this->triggerAgsResultSend($deliveryExecution);
        }
    }

    private function validateRequestParams(array $queryParams): void
    {
        if (!isset($queryParams[self::Q_PARAM_DELIVERY_EXECUTION_ID])) {
            throw new BadQueryStringException(
                sprintf('Missing %s query', self::Q_PARAM_DELIVERY_EXECUTION_ID)
            );
        };
    }

    private function triggerAgsResultSend(DeliveryExecutionInterface $deliveryExecution): void
    {
        $variables = $this->getResultsService()->getVariables(
            $deliveryExecution->getIdentifier(),
        );
        $testLevelVariables = $this->getResultsService()->extractTestVariables(
            $variables,
            [taoResultServer_models_classes_OutcomeVariable::class],
            ResultsService::VARIABLES_FILTER_LAST_SUBMITTED
        );

        $scoreTotal = null;
        $scoreTotalMax = null;

        foreach ($testLevelVariables as $variable) {
            if ($variable->getIdentifier() === 'SCORE_TOTAL') {
                $scoreTotal = $variable->getValue();
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL_MAX') {
                $scoreTotalMax = $variable->getValue();
            }

            if ($scoreTotal !== null && $scoreTotalMax !== null) {
                break;
            }
        }

        $this->getEventManager()->trigger(
            new DeliveryExecutionResultsRecalculated($deliveryExecution, $scoreTotal, $scoreTotalMax)
        );
    }

    private function getResultsService(): ResultsService
    {
        $implementation = $this->getServiceLocator()->get(AbstractRdsResultStorage::SERVICE_ID);
        $resultService = $this->getServiceLocator()->get(ResultServiceWrapper::SERVICE_ID)->getService();
        $resultService->setImplementation($implementation);

        return $resultService;
    }
}

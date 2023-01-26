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

namespace oat\taoResultServer\controller\rest;

use common_exception_Error;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Events\DeliveryExecutionResultsRecalculated;
use tao_actions_RestController;
use taoResultServer_models_classes_ReadableResultStorage as ReadableResultStorage;

class DeliveryExecutionResults extends tao_actions_RestController
{
    use EventManagerAwareTrait;
    use HttpJsonResponseTrait;

    private const Q_PARAM_DELIVERY_EXECUTION_ID = 'execution';
    private const Q_PARAM_TRIGGER_AGS_SEND = 'send_ags';

    public function patch(DeliveryExecutionService $deliveryExecutionService): void
    {
        $queryParams = $this->getPsrRequest()->getQueryParams();
        $agsNotificationTriggered = false;

        if (!isset($queryParams[self::Q_PARAM_DELIVERY_EXECUTION_ID])) {
            $this->setErrorJsonResponse(
                sprintf('Missing %s query param', self::Q_PARAM_DELIVERY_EXECUTION_ID)
            );
            return;
        };

        $deliveryExecution = $deliveryExecutionService->getDeliveryExecution(
            $queryParams[self::Q_PARAM_DELIVERY_EXECUTION_ID]
        );

        if (!$deliveryExecution->getFinishTime()) {
            $this->setErrorJsonResponse("Finished delivery execution not found", 0, [], 404);
            return;
        }

        // Todo patch variables pending in scope of the next phase of development

        if (
            isset($queryParams[self::Q_PARAM_TRIGGER_AGS_SEND]) &&
            $queryParams[self::Q_PARAM_TRIGGER_AGS_SEND] !== 'false'
        ) {
            $this->triggerAgsResultSend($deliveryExecution);
            $agsNotificationTriggered = true;
        }

        $this->setSuccessJsonResponse([
            'agsNotificationTriggered' => $agsNotificationTriggered
        ]);
    }

    private function triggerAgsResultSend(DeliveryExecutionInterface $deliveryExecution): void
    {
        $variables = $this->getResultsStorage()->getVariables(
            $deliveryExecution->getIdentifier(),
        );
        $variableObjects = array_map(static function (array $variableObject) {
            return current($variableObject)->variable;
        }, $variables);

        $scoreTotal = null;
        $scoreTotalMax = null;

        foreach ($variableObjects as $variable) {
            if ($variable->getIdentifier() === 'SCORE_TOTAL') {
                $scoreTotal = (float)$variable->getValue();
                continue;
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL_MAX') {
                $scoreTotalMax = (float)$variable->getValue();
                continue;
            }

            if ($scoreTotal !== null && $scoreTotalMax !== null) {
                break;
            }
        }

        $this->getEventManager()->trigger(
            new DeliveryExecutionResultsRecalculated($deliveryExecution, $scoreTotal, $scoreTotalMax)
        );
    }

    /**
     * @throws common_exception_Error
     * @throws InvalidServiceManagerException
     * @throws ServiceNotFoundException
     */
    private function getResultsStorage(): ReadableResultStorage
    {
        /* @var ResultServerService $resultService */
        $resultService = $this->getServiceManager()->get(ResultServerService::SERVICE_ID);

        $storage = $resultService->getResultStorage();

        if (!$storage instanceof ReadableResultStorage) {
            throw new common_exception_Error('Configured result storage is not writable.');
        }

        return $storage;
    }
}

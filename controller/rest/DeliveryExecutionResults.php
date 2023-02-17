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

use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\Import\ImportResultInput;
use oat\taoResultServer\models\Import\ResultImportScheduler;
use tao_actions_RestController;

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

        $task = $this->getResultImportScheduler()->schedule(ImportResultInput::fromRequest($this->getPsrRequest()));

        if (
            isset($queryParams[self::Q_PARAM_TRIGGER_AGS_SEND]) &&
            $queryParams[self::Q_PARAM_TRIGGER_AGS_SEND] !== 'false'
        ) {
            $agsNotificationTriggered = true;
        }

        $this->setSuccessJsonResponse(
            [
                'agsNotificationTriggered' => $agsNotificationTriggered,
                'taskId' => $task->getId()
            ]
        );
    }

    private function getResultImportScheduler(): ResultImportScheduler
    {
        return $this->getServiceManager()->get(ResultImportScheduler::class);
    }
}

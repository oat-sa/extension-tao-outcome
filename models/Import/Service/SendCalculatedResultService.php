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

namespace oat\taoResultServer\models\Import\Service;

use common_exception_Error;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Events\DeliveryExecutionResultsRecalculated;
use taoResultServer_models_classes_ReadableResultStorage as ReadableResultStorage;
use taoResultServer_models_classes_ResponseVariable;

class SendCalculatedResultService
{
    private ResultServerService $resultServerService;
    private EventManager $eventManager;
    private DeliveryExecutionService $deliveryExecutionService;

    public function __construct(
        ResultServerService $resultServerService,
        EventManager $eventManager,
        DeliveryExecutionService $deliveryExecutionService
    ) {
        $this->resultServerService = $resultServerService;
        $this->eventManager = $eventManager;
        $this->deliveryExecutionService = $deliveryExecutionService;
    }

    public function sendByDeliveryExecutionId(string $deliveryExecutionId): void
    {
        $deliveryExecution = $this->deliveryExecutionService
            ->getDeliveryExecution($deliveryExecutionId);

        $outcomeVariables = $this->getResultsStorage()->getDeliveryVariables($deliveryExecutionId);
        $scoreTotal = null;
        $scoreTotalMax = null;

        foreach ($outcomeVariables as $id => $outcomeVariable) {
            /** @var taoResultServer_models_classes_ResponseVariable $variable */
            $variable = current($outcomeVariable)->variable;

            if ($variable->getIdentifier() === 'SCORE_TOTAL') {
                $scoreTotal = (float)$variable->getValue();
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL_MAX') {
                $scoreTotalMax = (float)$variable->getValue();
            }
        }

        $this->eventManager->trigger(
            new DeliveryExecutionResultsRecalculated(
                $deliveryExecution,
                $scoreTotal,
                $scoreTotalMax
            )
        );
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
}

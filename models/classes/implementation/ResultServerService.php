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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes\implementation;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceNotFoundException;
use oat\taoDelivery\model\execution\Delete\DeliveryExecutionDeleteRequest;
use oat\taoResultServer\models\classes\ResultServerService as ResultServerServiceInterface;
use taoResultServer_models_classes_WritableResultStorage as WritableResultStorage;
use oat\oatbox\service\exception\InvalidServiceManagerException;

/**
 * Class ResultServerService
 *
 * Configuration example (taoResultServer/resultservice.conf.php):
 * ```php
 * use oat\taoResultServer\models\classes\implementation\ResultServerService;
 * return new ResultServerService([
 *     ResultServerService::OPTION_RESULT_STORAGE => 'taoOutcomeRds/RdsResultStorage'
 * ]);
 *
 * ```
 *
 * @package oat\taoResultServer\models\classes\implementation
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ResultServerService  extends ConfigurableService implements ResultServerServiceInterface
{

    const OPTION_RESULT_STORAGE = 'result_storage';

    /**
     * Starts or resume a taoResultServerStateFull session for results submission
     *
     * @param \core_kernel_classes_Resource $compiledDelivery
     * @param string $executionIdentifier
     * @param string $userUri
     * @throws
     */
    public function initResultServer($compiledDelivery, $executionIdentifier, $userUri)
    {
        $storage = $this->getResultStorage();
        //link test taker identifier with results
        $storage->storeRelatedTestTaker($executionIdentifier, $userUri);
        //link delivery identifier with results
        $storage->storeRelatedDelivery($executionIdentifier, $compiledDelivery->getUri());
    }

    /**
     * @param string $serviceId
     * @return WritableResultStorage
     * @throws \common_exception_Error
     * @throws InvalidServiceManagerException
     */
    public function instantiateResultStorage($serviceId)
    {
        try {
            $storage = $this->getServiceManager()->get($serviceId);
        } catch (ServiceNotFoundException $e) {
            throw new \common_exception_Error(sprintf('Cannot instantiate %s result storage', $serviceId));
        }

        if (!$storage instanceof WritableResultStorage) {
            throw new \common_exception_Error('Configured result storage is not writable.');
        }

        return $storage;
    }

    /**
     * @param DeliveryExecutionDeleteRequest $request
     * @return bool
     * @throws \common_exception_Error
     * @throws InvalidServiceManagerException
     */
    public function deleteDeliveryExecutionData(DeliveryExecutionDeleteRequest $request)
    {
        $storage = $this->getResultStorage();
        return $storage->deleteDeliveryExecutionData($request);
    }

    /**
     * Returns the storage engine of the result server
     *
     * @return \taoResultServer_models_classes_WritableResultStorage
     * @throws InvalidServiceManagerException
     * @throws \common_exception_Error
     */
    public function getResultStorage()
    {
        $resultServerId = $this->getOption(self::OPTION_RESULT_STORAGE);
        return $this->instantiateResultStorage($resultServerId);
    }
}

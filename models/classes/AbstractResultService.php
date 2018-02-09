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

namespace oat\taoResultServer\models\classes;

use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\Delete\DeliveryExecutionDeleteRequest;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use \taoResultServer_models_classes_WritableResultStorage as WritableResultStorage;

abstract class AbstractResultService extends ConfigurableService implements ResultServerService
{
    /** @var bool $configurable Whether this ResultServerService instance is configurable */
    protected $configurable = true;

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
        $storage = $this->getResultStorage($compiledDelivery);
        //$storage->spawnResult($executionIdentifier);

        //link test taker identifier with results
        $storage->storeRelatedTestTaker($executionIdentifier, $userUri);

        //link delivery identifier with results
        $storage->storeRelatedDelivery($executionIdentifier, $compiledDelivery->getUri());
    }

    /**
     * @param string $serviceId
     * @return WritableResultStorage
     * @throws \common_exception_Error
     */
    public function instantiateResultStorage($serviceId)
    {
        $storage = null;
        if (class_exists($serviceId)) { //some old serialized session can has class name instead of service id
            $storage = new $serviceId();
        } elseif($this->getServiceManager()->has($serviceId)) {
            $storage = $this->getServiceManager()->get($serviceId);
        }

        if ($storage instanceof ServiceLocatorAwareInterface) {
            $storage->setServiceLocator($this->getServiceLocator());
        }

        if ($storage === null || !$storage instanceof WritableResultStorage) {
            throw new \common_exception_Error('Configured result storage is not writable.');
        }

        return $storage;
    }


    abstract public function getResultStorage($deliveryId);

    public function isConfigurable()
    {
        return $this->configurable;
    }

    public function deleteDeliveryExecutionData(DeliveryExecutionDeleteRequest $request)
    {
        $storage = $this->getResultStorage($request->getDeliveryResource()->getUri());
        return $storage->deleteDeliveryExecutionData($request);
    }
}

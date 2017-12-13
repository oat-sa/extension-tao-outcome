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


use common_exception_NoImplementation;

trait WritableResultStorage
{
    public function spawnResult()
    {
        return $this->getWritableStorage()->spawnResult();
    }

    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier);
    }

    /**
     * Store Related Delivery
     *
     * Store a delivery related to a specific delivery execution
     *
     * @param string $deliveryResultIdentifier (mostly delivery execution uri)
     * @param string $deliveryIdentifier (uri recommended)
     * @throws common_exception_NoImplementation
     */
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier);
    }

    public function storeItemVariable($deliveryResultIdentifier, $test, $item, \taoResultServer_models_classes_Variable $itemVariable, $callIdItem)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeItemVariable($deliveryResultIdentifier, $test, $item, $itemVariable, $callIdItem);
    }

    public function storeItemVariables($deliveryResultIdentifier, $test, $item, array $itemVariables, $callIdItem)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeItemVariables($deliveryResultIdentifier, $test, $item, $itemVariables, $callIdItem);
    }

    public function storeTestVariable($deliveryResultIdentifier, $test, \taoResultServer_models_classes_Variable $testVariable, $callIdTest)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeTestVariable($deliveryResultIdentifier, $test, $testVariable, $callIdTest);
    }

    public function storeTestVariables($deliveryResultIdentifier, $test, array $testVariables, $callIdTest)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeTestVariables($deliveryResultIdentifier, $test, $testVariables, $callIdTest);
    }

    public function configure($callOptions = array())
    {
        return $this->getWritableStorage()->storeTestVariables($callOptions);
    }

    /**
     * @param $deliveryResultIdentifier
     * @return \taoResultServer_models_classes_WritableResultStorage
     * @throws common_exception_NoImplementation
     */
    private function getWritableStorage($deliveryResultIdentifier = null)
    {
        $storage = $this->getResultServer($deliveryResultIdentifier)->getStorageInterface();
        if (!$storage instanceof \taoResultServer_models_classes_WritableResultStorage) {
            throw new common_exception_NoImplementation('No writable support for current storage');
        }
        return $storage;
    }
}
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
    /** @see  \taoResultServer_models_classes_WritableResultStorage::spawnResult() */
    public function spawnResult($executionIdentifier = null)
    {
        return $this->getWritableStorage($executionIdentifier)->spawnResult();
    }
    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeRelatedTestTaker() */
    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeRelatedDelivery() */
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeItemVariable() */
    public function storeItemVariable($deliveryResultIdentifier, $test, $item, \taoResultServer_models_classes_Variable $itemVariable, $callIdItem)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeItemVariable($deliveryResultIdentifier, $test, $item, $itemVariable, $callIdItem);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeItemVariables() */
    public function storeItemVariables($deliveryResultIdentifier, $test, $item, array $itemVariables, $callIdItem)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeItemVariables($deliveryResultIdentifier, $test, $item, $itemVariables, $callIdItem);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeTestVariable() */
    public function storeTestVariable($deliveryResultIdentifier, $test, \taoResultServer_models_classes_Variable $testVariable, $callIdTest)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeTestVariable($deliveryResultIdentifier, $test, $testVariable, $callIdTest);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeTestVariables() */
    public function storeTestVariables($deliveryResultIdentifier, $test, array $testVariables, $callIdTest)
    {
        return $this->getWritableStorage($deliveryResultIdentifier)->storeTestVariables($deliveryResultIdentifier, $test, $testVariables, $callIdTest);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::configure() */
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
        $storage = $this->getStorageInterface($deliveryResultIdentifier);
        if (!$storage instanceof \taoResultServer_models_classes_WritableResultStorage) {
            throw new common_exception_NoImplementation('No writable support for current storage');
        }
        return $storage;
    }
}
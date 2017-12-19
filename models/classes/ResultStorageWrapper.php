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

class ResultStorageWrapper
{

    /**
     * @var string
     */
    private $deliveryExecutionIdentifier;

    /**
     * @var \taoResultServer_models_classes_WritableResultStorage
     */
    protected $resultServer;

    /**
     * ResultStorageWrapper constructor.
     * @param string $deliveryExecutionIdentifier
     * @param \taoResultServer_models_classes_WritableResultStorage $resultServer
     */
    public function __construct($deliveryExecutionIdentifier, \taoResultServer_models_classes_WritableResultStorage $resultServer)
    {
        $this->deliveryExecutionIdentifier = $deliveryExecutionIdentifier;
        $this->resultServer = $resultServer;
    }

    protected function getResultServer()
    {
        return $this->resultServer;
    }

    /**
     * @return string
     */
    protected function getDeliveryExecutionIdentifier()
    {
        return $this->deliveryExecutionIdentifier;
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeItemVariable() */
    public function storeItemVariable($test, $item, \taoResultServer_models_classes_Variable $itemVariable, $callIdItem)
    {
        return $this->getWritableStorage()->storeItemVariable($this->getDeliveryExecutionIdentifier(), $test, $item, $itemVariable, $callIdItem);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeItemVariables() */
    public function storeItemVariables($test, $item, array $itemVariables, $callIdItem)
    {
        return $this->getWritableStorage()->storeItemVariables($this->getDeliveryExecutionIdentifier(), $test, $item, $itemVariables, $callIdItem);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeTestVariable() */
    public function storeTestVariable($test, \taoResultServer_models_classes_Variable $testVariable, $callIdTest)
    {
        return $this->getWritableStorage()->storeTestVariable($this->getDeliveryExecutionIdentifier(), $test, $testVariable, $callIdTest);
    }

    /** @see  \taoResultServer_models_classes_WritableResultStorage::storeTestVariables() */
    public function storeTestVariables($test, array $testVariables, $callIdTest)
    {
        return $this->getWritableStorage()->storeTestVariables($this->getDeliveryExecutionIdentifier(), $test, $testVariables, $callIdTest);
    }

    /**
     * @return \taoResultServer_models_classes_WritableResultStorage
     */
    protected function getWritableStorage()
    {
        return $this->getResultServer();
    }

}
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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\taoResultServer\models\classes\implementation;

use oat\taoDelivery\model\execution\Delete\DeliveryExecutionDeleteRequest;
use taoResultServer_models_classes_Variable;
use oat\taoResultServer\models\classes\ResultManagement  as StorageManage;
use taoResultServer_models_classes_ReadableResultStorage as StorageRead;
use taoResultServer_models_classes_WritableResultStorage as StorageWrite;


class StorageAggregation implements
    StorageRead,
    StorageWrite,
    StorageManage
{
    private $implementations = array();    
    
    public function __construct($implementations)
    {
        $interfaces = class_implements($this);
        foreach ($interfaces as $interface) {
            $this->implementations[$interface] = array();
        }
        foreach ($implementations as $impl) {
            foreach (array_intersect($interfaces, class_implements($impl)) as $interface) {
                $this->implementations[$interface][] = $impl;
            }
        }
    }


    protected function getOneImplementation($interface)
    {
        return reset($this->implementations[$interface]);
    }
    
    protected function getAllImplementations($interface)
    {
        return $this->implementations[$interface];
    }
    
    // read interface
    
    public function getVariables($callId)
    {
        return $this->getOneImplementation(StorageRead::class)->getVariables($callId);
    }

    public function getVariable($callId, $variableIdentifier)
    {
        return $this->getOneImplementation(StorageRead::class)->getVariable($callId, $variableIdentifier);
    }
    
    public function getTestTaker($deliveryResultIdentifier)
    {
        return $this->getOneImplementation(StorageRead::class)->getTestTaker($deliveryResultIdentifier);
    }
    
    public function getDelivery($deliveryResultIdentifier)
    {
        return $this->getOneImplementation(StorageRead::class)->getDelivery($deliveryResultIdentifier);
    }
    
    public function getAllCallIds()
    {
        return $this->getOneImplementation(StorageRead::class)->getAllCallIds();
    }
    
    public function getAllTestTakerIds()
    {
        return $this->getOneImplementation(StorageRead::class)->getAllTestTakerIds();
    }
    
    public function getAllDeliveryIds()
    {
        return $this->getOneImplementation(StorageRead::class)->getAllDeliveryIds();
    }

    // write interface
    
    /**
     * Not sure how multiple engines are supposed to handle this
     */
    public function spawnResult()
    {
        $result = null;
        foreach ($this->getAllImplementations(StorageWrite::class) as $impl) {
            $result = $impl->spawnResult();
        }
        return $result;
    }
    
    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier)
    {
        foreach ($this->getAllImplementations(StorageWrite::class) as $impl) {
            $impl->storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier);
        }
    }
    
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier)
    {
        foreach ($this->getAllImplementations(StorageWrite::class) as $impl) {
            $impl->storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier);
        }
    }
    
    public function storeItemVariable($deliveryResultIdentifier, $test, $item, taoResultServer_models_classes_Variable $itemVariable, $callIdItem)
    {
        foreach ($this->getAllImplementations(StorageWrite::class) as $impl) {
            $impl->storeItemVariable($deliveryResultIdentifier, $test, $item, $itemVariable, $callIdItem);
        }
    }
    
    public function storeItemVariables($deliveryResultIdentifier, $test, $item, array $itemVariables, $callIdItem)
    {
        foreach ($this->getAllImplementations(StorageWrite::class) as $impl) {
            $impl->storeItemVariables($deliveryResultIdentifier, $test, $item, $itemVariables, $callIdItem);
        }
    }
    
    public function storeTestVariable($deliveryResultIdentifier, $test, taoResultServer_models_classes_Variable $testVariable, $callIdTest)
    {
        foreach ($this->getAllImplementations(StorageWrite::class) as $impl) {
            $impl->storeTestVariable($deliveryResultIdentifier, $test, $testVariable, $callIdTest);
        }
    }
    
    public function storeTestVariables($deliveryResultIdentifier, $test, array $testVariables, $callIdTest)
    {
        foreach ($this->getAllImplementations(StorageWrite::class) as $impl) {
            $impl->storeTestVariables($deliveryResultIdentifier, $test, $testVariables, $callIdTest);
        }
    }
    
    public function configure($callOptions = array())
    {
        foreach ($this->getAllImplementations(StorageWrite::class) as $impl) {
            $success = $impl->configure($callOptions);
        }
    }
    
    // manage interface

    public function getVariableProperty($variableId, $property)
    {
        return $this->getOneImplementation(StorageManage::class)->getVariableProperty($variableId, $property);
    }
    
    public function getRelatedItemCallIds($deliveryResultIdentifier)
    {
        return $this->getOneImplementation(StorageManage::class)->getRelatedItemCallIds($deliveryResultIdentifier);
    }
    
    public function getRelatedTestCallIds($deliveryResultIdentifier)
    {
        return $this->getOneImplementation(StorageManage::class)->getRelatedTestCallIds($deliveryResultIdentifier);
    }
    
    public function getResultByDelivery($delivery, $options = array())
    {
        return $this->getOneImplementation(StorageManage::class)->getResultByDelivery($delivery, $options = array());
    }
    
    public function countResultByDelivery($delivery)
    {
        return $this->getOneImplementation(StorageManage::class)->countResultByDelivery($delivery);
    }

    public function getDeliveryVariables($delivery)
    {
        return $this->getOneImplementation(StorageManage::class)->getDeliveryVariables($delivery);
    }

    public function deleteResult($deliveryResultIdentifier)
    {
        $success = null;
        foreach ($this->getAllImplementations(StorageManage::class) as $impl) {
            $success = $impl->deleteResult($deliveryResultIdentifier) && ($success === true || is_null($success));
        }
        return $success === true;
    }

    /**
     * @inheritdoc
     */
    public function deleteDeliveryExecutionData(DeliveryExecutionDeleteRequest $request)
    {
        $success = null;
        /** @var \oat\taoDelivery\model\execution\Delete\DeliveryExecutionDelete $impl */
        foreach ($this->getAllImplementations(StorageManage::class) as $impl) {
            $success = $impl->deleteDeliveryExecutionData($request) && ($success === true || is_null($success));
        }
        return $success === true;
    }
}

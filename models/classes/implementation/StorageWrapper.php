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

use oat\taoResultServer\models\classes\ResultManagement  as StorageManage;
use taoResultServer_models_classes_ReadableResultStorage as StorageRead;
use taoResultServer_models_classes_WritableResultStorage as StorageWrite;
use oat\taoResultServer\models\classes\VariableManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class StorageWrapper implements ServiceLocatorAwareInterface, StorageRead, StorageWrite, StorageManage
{
    use ServiceLocatorAwareTrait;
    
    private $implementation;
    
    public function __construct($implementation)
    {
        $this->setImplementation($implementation);
    }
    
    protected function getImplementation()
    {
        return $this->implementation;
    }
    
    protected function setImplementation($implementation)
    {
        $this->implementation = $implementation;
    }
    
    protected function getVariableManager()
    {
        return $this->getServiceLocator()->get(VariableManager::SERVICE_ID);
    }
    
    public function getVariables($callId)
    {
        if (($storage = $this->getImplementation()) instanceof StorageRead) {
            $variableManager = $this->getVariableManager();
            $variables = $storage->getVariables($callId);
            
            foreach ($variables as $variable) {
                foreach ($variable as $v) {
                    $variableManager->retrieve($v->variable);
                }
            }
            
            return $variables;
            
        } else {
            return [];
        }
    }
    
    public function getVariable($callId, $variableIdentifier)
    {
         if (($storage = $this->getImplementation()) instanceof StorageRead) {
            $variableManager = $this->getVariableManager();
            $variables = $storage->getVariable($callId, $variableIdentifier);
            
            foreach ($variables as $variable) {
                foreach ($variable as $v) {
                    $variableManager->retrieve($v->variable);
                }
            }
            
            return $variables;
            
        } else {
            return [];
        }
    }
    
    public function getTestTaker($deliveryResultIdentifier)
    {
        if (($storage = $this->getImplementation()) instanceof StorageRead) {
            return $storage->getTestTaker($deliveryResultIdentifier);
        } else {
            return false;
        }
    }
    
    public function getDelivery($deliveryResultIdentifier)
    {
        if (($storage = $this->getImplementation()) instanceof StorageRead) {
            return $storage->getDelivery($deliveryResultIdentifier);
        } else {
            return false;
        }
    }
    
    public function getAllCallIds()
    {
        if (($storage = $this->getImplementation()) instanceof StorageRead) {
            return $storage->getAllCallIds();
        } else {
            return [];
        }
    }
    
    public function getAllTestTakerIds()
    {
        if (($storage = $this->getImplementation()) instanceof StorageRead) {
            return $storage->getAllTestTakerIds();
        } else {
            return false;
        }
    }
    
    public function getAllDeliveryIds()
    {
        if (($storage = $this->getImplementation()) instanceof StorageRead) {
            return $storage->getAllDeliveryIds();
        } else {
            return [];
        }
    }
    
    public function spawnResult()
    {
        if (($storage = $this->getImplementation()) instanceof StorageWrite) {
            return $storage->spawnResult();
        } else {
            return false;
        }
    }
    
    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier)
    {
        if (($storage = $this->getImplementation()) instanceof StorageWrite) {
            $storage->storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier);
        }
    }
    
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier)
    {
        if (($storage = $this->getImplementation()) instanceof StorageWrite) {
            $storage->storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier);
        }
    }
    
    public function storeItemVariable($deliveryResultIdentifier, $test, $item, \taoResultServer_models_classes_Variable $itemVariable, $callIdItem)
    {
        if (($storage = $this->getImplementation()) instanceof StorageWrite) {
            $this->getVariableManager()->persist($itemVariable);
            $storage->storeItemVariable($deliveryResultIdentifier, $test, $item, $itemVariable, $callIdItem);
        }
    }
    
    public function storeTestVariable($deliveryResultIdentifier, $test, \taoResultServer_models_classes_Variable $testVariable, $callIdTest)
    {
        if (($storage = $this->getImplementation()) instanceof StorageWrite) {
            $this->getVariableManager()->persist($testVariable);
            $storage->storeTestVariable($deliveryResultIdentifier, $test, $testVariable, $callIdTest);
        }
    }
    
    public function configure(\core_kernel_classes_Resource $resultServer, $callOptions = array())
    {
        if (($storage = $this->getImplementation()) instanceof StorageWrite) {
            $storage->configure($resultServer, $callOptions);
        }
    }
    
    public function getVariableProperty($variableId, $property)
    {
        if (($storage = $this->getImplementation()) instanceof StorageManage) {
            // Retrieve info to be given to the VariableManager in force...
            $baseType = $storage->getVariableProperty($variableId, 'baseType');
            $propertyValue = $storage->getVariableProperty($variableId, $property);
            
            // Ask the VariableManager for a possible transformation...
            return $this->getVariableManager()->retrieveProperty($baseType, $property, $propertyValue);
        } else {
            return null;
        }
    }
    
    public function getRelatedItemCallIds($deliveryResultIdentifier)
    {
        if (($storage = $this->getImplementation()) instanceof StorageManage) {
            return $storage->getRelatedItemCallIds($deliveryResultIdentifier);
        } else {
            return [];
        }
    }
    
    public function getRelatedTestCallIds($deliveryResultIdentifier)
    {
        if (($storage = $this->getImplementation()) instanceof StorageManage) {
            return $storage->getRelatedTestCallIds($deliveryResultIdentifier);
        } else {
            return [];
        }
    }
    
    public function getResultByDelivery($delivery, $options = array())
    {
        if (($storage = $this->getImplementation()) instanceof StorageManage) {
            return $storage->getResultByDelivery($delivery, $options);
        } else {
            return [];
        }
    }
    
    public function countResultByDelivery($delivery)
    {
        if (($storage = $this->getImplementation()) instanceof StorageManage) {
            return $storage->countResultByDelivery($delivery);
        } else {
            return 0;
        }
    }
    
    public function deleteResult($deliveryResultIdentifier)
    {
        if (($storage = $this->getImplementation()) instanceof StorageManage) {
            $this->getVariableManager()->delete($deliveryResultIdentifier);
            return $storage->deleteResult($deliveryResultIdentifier);
        } else {
            return false;
        }
    }
    
    public function getDeliveryVariables($deliveryResultIdentifier)
    {
        if (($storage = $this->getImplementation()) instanceof StorageManage) {
            return $storage->getDeliveryVariables($deliveryResultIdentifier);
        } else {
            return [];
        }
    }
}

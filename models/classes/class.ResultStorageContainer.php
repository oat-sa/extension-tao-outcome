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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 * Implements the Services for the storage of item and test variables,
 * This implementations depends on results for the the physical storage
 * TODO : move the impl to results services
 * @author "Patrick Plichart, <patrick@taotesting.com>"
 */

use oat\oatbox\service\ServiceManager;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\ResultServiceTrait;

class taoResultServer_models_classes_ResultStorageContainer 
extends tao_models_classes_GenerisService 
implements taoResultServer_models_classes_WritableResultStorage
{

    use ResultServiceTrait;

    /** @var array */
    private $implementations = [];

    /** @var array */
    private $implementationsConfig = [];

    /** @var bool whether implementations are initialized */
    private $initialized = false;

    /**
     *
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param array $implementations
     */
    public function __construct($implementations = array())
    {
        parent::__construct();
        $this->implementationsConfig = $implementations;
    }

    /*
     * (non-PHPdoc) @see taoResultServer_models_classes_WritableResultStorage::storeRelatedTestTaker()
     */
    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier)
    {
        foreach ($this->getImplementations() as $implementation) {
            $implementation["object"]->storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier);
        }
    }

    /*
     * (non-PHPdoc) @see taoResultServer_models_classes_WritableResultStorage::storeRelatedDelivery()
     */
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier)
    {
        foreach ($this->getImplementations() as $implementation) {
            $implementation["object"]->storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier);
        }
    }

    /*
     * (non-PHPdoc) @see taoResultServer_models_classes_WritableResultStorage::storeItemVariable()
     */
    public function storeItemVariable(
        $deliveryResultIdentifier,
        $test,
        $item,
        taoResultServer_models_classes_Variable $itemVariable,
        $callIdItem
    ) {
        foreach ($this->getImplementations() as $implementation) {
            $implementation["object"]->storeItemVariable($deliveryResultIdentifier, $test, $item, $itemVariable,
                $callIdItem);
        }
    }

    /*
     * (non-PHPdoc) @see taoResultServer_models_classes_WritableResultStorage::storeTestVariable()
     */
    public function storeTestVariable(
        $deliveryResultIdentifier,
        $test,
        taoResultServer_models_classes_Variable $testVariable,
        $callIdTest
    ) {
        foreach ($this->getImplementations() as $implementation) {
            $implementation["object"]->storeTestVariable($deliveryResultIdentifier, $test, $testVariable, $callIdTest);
        }
    }

    /*
     * (non-PHPdoc) @see taoResultServer_models_classes_WritableResultStorage::configure()
     */
    public function configure(core_kernel_classes_Resource $resultServer, $callOptions = array())
    {
        foreach ($this->getImplementations() as $key => $implementation) {
            if (isset($this->implementationsConfig[$key]['params'])) {
                $implementation["object"]->configure($resultServer, $this->implementationsConfig[$key]['params']);
            }
        }
    }

    public function getVariables($callId)
    {

        $returnData = array();
        foreach ($this->getImplementations() as $implementation) {
            if ($implementation["object"] instanceof taoResultServer_models_classes_ReadableResultStorage) {
                $implData = $implementation["object"]->getVariables($callId);
                $returnData = array_merge($implData, $returnData);
            }
        }
        return $returnData;
    }

    public function getVariable($callId, $variableIdentifier)
    {
        $returnData = array();
        foreach ($this->getImplementations() as $implementation) {
            if ($implementation["object"] instanceof taoResultServer_models_classes_ReadableResultStorage) {
                $implData = $implementation["object"]->getVariable($callId, $variableIdentifier);
                $returnData = array_merge($implData, $returnData);
            }
            return $returnData;
        }
    }

    public function getTestTaker($deliveryResultIdentifier)
    {
        $returnData = array();
        foreach ($this->getImplementations() as $implementation) {
            if ($implementation["object"] instanceof taoResultServer_models_classes_ReadableResultStorage) {

                $implData = $implementation["object"]->getTestTaker($deliveryResultIdentifier);
                $returnData = array_merge($implData, $returnData);
            }
        }
        return $returnData;
    }

    public function getDelivery($deliveryResultIdentifier)
    {
        $returnData = array();
        foreach ($this->getImplementations() as $implementation) {
            if ($implementation["object"] instanceof taoResultServer_models_classes_ReadableResultStorage) {
                $implData = $implementation["object"]->getDelivery($deliveryResultIdentifier);
                $returnData = array_merge($implData, $returnData);
            }
        }
        return $returnData;
    }

    /*
     * (non-PHPdoc) @see taoResultServer_models_classes_WritableResultStorage::spawnResult()
     */
    public function spawnResult()
    {
        // should be improved by changing the interface,
        // currently the first found implementation will generate an Id
        // to be used as a result identifier across all implementations,
        foreach ($this->getImplementations() as $implementation) {
            $spawnedIdentifier = $implementation["object"]->spawnResult();
            if ((!is_null($spawnedIdentifier)) and $spawnedIdentifier != "") {
                return $spawnedIdentifier;
            }
        }
    }

    /**
     * Initialize and return storage implementations
     * @return array
     */
    protected function getImplementations()
    {
        if (!$this->initialized) {
            $initializedImplementations = [];
            foreach ($this->implementationsConfig as $key => $implementationParams) {
                $initializedImplementations[$key]["object"] = $this->instantiateResultStorage($implementationParams["serviceId"]);
            }
            $this->implementations = $initializedImplementations;
            $this->initialized = true;
        }
        return $this->implementations;
    }

    /**
     * @return ServiceManager
     */
    protected function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }
}

<?php

use oat\taoResultServer\models\classes\ResultServerService;

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
 * @author "Patrick Plichart, <patrick@taotesting.com>"
 * @package taoResultServer
 */
use oat\oatbox\service\ServiceManager;

class taoResultServer_models_classes_ResultServer
{

    /**
     * @var core_kernel_classes_Resource
     */
    private $resultServer; 
    
    private $implementations;

    /**
     *
     * @param mixed $resultServerId - result storage uri, resource or service id
     * @param array $additionalStorages
     *              $additionalStorages['implementation'] - service id or class name of result storage
     *              $additionalStorages['parameters'] - parameters to be used during configuration of result storage (@see \taoResultServer_models_classes_WritableResultStorage::configure)
     * @param string uri or resource
     * @throws \common_Exception if no models exist for given result server
     */
    public function __construct($resultServerId, $additionalStorages = [])
    {
        $this->implementations = [];

        $resultServer = null;

        if (common_Utils::isUri($resultServerId)) {
            $resultServer = new core_kernel_classes_Resource($resultServerId);
        }

        if ($resultServer instanceof core_kernel_classes_Resource) {
            $this->resultServer = $resultServer;
            // the static storages
            if ($this->resultServer->getUri() != TAO_VOID_RESULT_SERVER) {
                $resultServerModels = $this->resultServer->getPropertyValues(new core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP));
                if ((! isset($resultServerModels)) or (count($resultServerModels) == 0)) {
                    throw new common_Exception("The result server is not correctly configured (Resource definition)");
                }
                foreach ($resultServerModels as $resultServerModelUri) {
                    $resultServerModel = new core_kernel_classes_Resource($resultServerModelUri);
                    $this->addImplementation($resultServerModel->getUniquePropertyValue(new core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_IMPL_PROP))->literal);
                }
            }
        } else {
            $this->addImplementation($resultServerId);
        }

        if ($additionalStorages !== null) {
            // the dynamic storages
            foreach ($additionalStorages as $additionalStorage) {
                $this->addImplementation($additionalStorage["implementation"], $additionalStorage["parameters"]);
            }
        }

        common_Logger::i("Result Server Initialized using definition: " . $resultServerId);
    }

    /**
     * @access public
     * @author "Patrick Plichart, <patrick@taotesting.com>"
     * @param string $serviceId
     * @param array $options
     */
    public function addImplementation($serviceId, $options = array())
    {
        $this->implementations[] = array(
            "serviceId" => $serviceId,
            "params" => $options
        );
    }

    /**
     * @access public
     * @author "Patrick Plichart, <patrick@taotesting.com>"
     * @return taoResultServer_models_classes_ResultStorageContainer
     */
    public function getStorageInterface()
    {
        $storageContainer = new taoResultServer_models_classes_ResultStorageContainer($this->implementations);
        $storageContainer->setServiceManager(ServiceManager::getServiceManager());
        if ($this->resultServer !== null) {
            $storageContainer->configure($this->resultServer);
        }
        return $storageContainer;
    }
}
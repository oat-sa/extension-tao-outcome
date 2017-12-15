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


use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\log\LoggerAwareTrait;

trait ImplementationResultInitializer
{
    use OntologyAwareTrait;
    use LoggerAwareTrait;

    protected $implementations;
    private $resultStorageContainer;

    /**
     *
     * @param mixed $resultServerId - result storage uri, resource or service id
     * @param array $additionalStorages
     *              $additionalStorages['implementation'] - service id or class name of result storage
     *              $additionalStorages['parameters'] - parameters to be used during configuration of result storage (@see \taoResultServer_models_classes_WritableResultStorage::configure)
     * @param string uri or resource
     * @return array
     * @throws \common_Exception if no models exist for given result server
     */
    protected function initStorageInterfaces($resultServerId, array $additionalStorages = [])
    {
        $this->implementations = [];

        $resultServer = null;

        if (\common_Utils::isUri($resultServerId)) {
            $resultServer = $this->getResource($resultServerId);
        }

        if ($resultServer instanceof core_kernel_classes_Resource) {
            // the static storages
            if (ResultServerService::INSTANCE_VOID_RESULT_SERVER != $resultServer->getUri()) {
                $resultServerModels = $resultServer->getPropertyValues($this->getProperty(ResultServerService::PROPERTY_HAS_MODEL));
                if (!isset($resultServerModels) || 0 == count($resultServerModels)) {
                    throw new \common_Exception('The result server is not correctly configured (Resource definition)');
                }
                foreach ($resultServerModels as $resultServerModelUri) {
                    $resultServerModel = $this->getResource($resultServerModelUri);
                    $this->addImplementation($resultServerModel->getUniquePropertyValue($this->getProperty(ResultServerService::PROPERTY_MODEL_IMPL))->literal);
                }
            }
        } else {
            $this->addImplementation($resultServerId);
        }

        if ($additionalStorages !== null) {
            // the dynamic storages
            foreach ($additionalStorages as $additionalStorage) {
                $this->addImplementation($additionalStorage['implementation'], $additionalStorage['parameters']);
            }
        }

        $this->logInfo('Result Server Initialized using definition: ' . $resultServerId);

        return $this->implementations;
    }

    /**
     * @access public
     * @author "Patrick Plichart, <patrick@taotesting.com>"
     * @param string $serviceId
     * @param array $options
     */
    private function addImplementation($serviceId, $options = array())
    {
        $this->implementations[] = array(
            'serviceId' => $serviceId,
            'params' => $options
        );
    }

    /**
     * @access public
     * @param null $deliveryResultIdentifier
     * @return \taoResultServer_models_classes_ResultStorageContainer
     */
    public function getStorageInterface($deliveryResultIdentifier = null)
    {
        if (!$this->resultStorageContainer) {
//            $this->prepareImplementationStorageInterface(null, $deliveryResultIdentifier);
            $storageContainer = new \taoResultServer_models_classes_ResultStorageContainer($this->implementations);
            $storageContainer->setServiceManager($this->getServiceManager());
            $storageContainer->configure();
            $this->resultStorageContainer = $storageContainer;
        }
        return $this->resultStorageContainer;
    }
}
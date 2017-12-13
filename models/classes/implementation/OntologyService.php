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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\taoResultServer\models\classes\implementation;

use oat\taoDelivery\model\execution\ServiceProxy;
use taoResultServer_models_classes_ResultServer;
use oat\generis\model\OntologyAwareTrait;
use oat\taoResultServer\models\classes\ResultServiceTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoResultServer\models\classes\ResultServerService;

/**
 * Class OntologyService
 * @package oat\taoResultServer\models\classes\implementation
 * @deprecated ResultServerService should be used instead
 */
class OntologyService extends ConfigurableService implements ResultServerService
{

    use OntologyAwareTrait;
    use ResultServiceTrait;

    /**
     * @var taoResultServer_models_classes_ResultServer[]
     */
    private $localcache = [];

    const CACHE_PREFIX = 'RS_';

    const OPTION_DEFAULT_MODEL = 'default';
    /** @deprecated */
    const PROPERTY_RESULT_SERVER = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#DeliveryResultServer';

    /**
     * @param \core_kernel_classes_Resource $compiledDelivery
     * @param string $executionIdentifier
     * @param array $options
     * @return taoResultServer_models_classes_ResultServer
     * @throws \common_Exception
     * @throws \common_cache_NotFoundException
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function initResultServer($compiledDelivery, $executionIdentifier, $options = []){

        //retrieve the result server definition

        $rs = $this->getResultServer($executionIdentifier, $compiledDelivery, $options);

        //a unique identifier for data collected through this delivery execution
        //in the case of LTI, we should use the sourceId

        $rs->getStorageInterface()->spawnResult();

        \common_Logger::i('Spawning/resuming result identifier related to process execution ' . $executionIdentifier);
        //set up the related test taker
        //a unique identifier for the test taker
        $rs->getStorageInterface()->storeRelatedTestTaker($executionIdentifier, \common_session_SessionManager::getSession()->getUserUri());

        //a unique identifier for the delivery
        $rs->getStorageInterface()->storeRelatedDelivery($executionIdentifier, $compiledDelivery->getUri());
        return $rs;
    }

    /**
     * @param $executionIdentifier
     * @param \core_kernel_classes_Resource|null $compiledDelivery
     * @param array $options
     * @return taoResultServer_models_classes_ResultServer
     * @throws \common_cache_NotFoundException
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \common_Exception
     * @throws \common_exception_NotFound
     */
    public function getResultServer($executionIdentifier = null, $compiledDelivery = null, array $options = [])
    {
        if (!isset ($this->localcache[$executionIdentifier])) {
            if ($this->getCache()->has(self::CACHE_PREFIX . $executionIdentifier)) {
                $rs = $this->getCache()->get(self::CACHE_PREFIX . $executionIdentifier);
            } else {
                if (!$compiledDelivery) {
                    $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($executionIdentifier);
                    $compiledDelivery = $deliveryExecution->getDelivery();
                }

                try {
                    /** @var taoResultServer_models_classes_ResultServer $resultServer */
                    $resultServer = $compiledDelivery->getUniquePropertyValue($this->getProperty(self::PROPERTY_RESULT_SERVER));
                } catch (\core_kernel_classes_EmptyProperty $e) {
                    $resultServer = \taoResultServer_models_classes_ResultServerAuthoringService::singleton()->getDefaultResultServer();
                }

                $rs = new taoResultServer_models_classes_ResultServer($resultServer->getUri(), $options);
            }
            $this->localcache[$executionIdentifier] = $rs;
        }
        return $this->localcache[$executionIdentifier];
    }

    /**
     * Returns the storage engine of the result server
     *
     * @param string $deliveryId
     * @throws \common_exception_Error
     * @return \taoResultServer_models_classes_ReadableResultStorage|\taoResultServer_models_classes_WritableResultStorage|oat\taoResultServer\models\classes\ResultManagement
     */
    public function getResultStorage($deliveryId = null)
    {
        if ($deliveryId !== null) {
            $delivery = $this->getResource($deliveryId);
            $deliveryResultServer = $delivery->getOnePropertyValue($this->getProperty(self::PROPERTY_RESULT_SERVER));
        }
        if (!$deliveryResultServer) {
            $deliveryResultServer = \taoResultServer_models_classes_ResultServerAuthoringService::singleton()->getDefaultResultServer();
        }

        if(is_null($deliveryResultServer)){
            throw new \common_exception_Error(__('This delivery has no Result Server'));
        }
        $resultServerModel = $deliveryResultServer->getPropertyValues($this->getProperty(ResultServerService::PROPERTY_HAS_MODEL));

        if(is_null($resultServerModel)){
            throw new \common_exception_Error(__('This delivery has no readable Result Server'));
        }

        $implementations = array();
        foreach($resultServerModel as $model){
            $model = $this->getClass($model);

            /** @var $implementation \core_kernel_classes_Literal*/
            $implementation = $model->getOnePropertyValue($this->getProperty(ResultServerService::PROPERTY_MODEL_IMPL));

            if ($implementation !== null) {
                $implementations[] = $this->instantiateResultStorage($implementation->literal);
            }
        }

        if (empty($implementations)) {
            throw new \common_exception_Error(__('This delivery has no readable Result Server'));
        } elseif (count($implementations) == 1) {
            return reset($implementations);
        } else {
            return new StorageAggregation($implementations);
        }
    }

    /**
     * @return \common_cache_Cache
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    private function getCache()
    {
        /** @var \common_persistence_Manager $pm */
        return $this->getServiceLocator()->get(\common_cache_Cache::SERVICE_ID);
    }

}

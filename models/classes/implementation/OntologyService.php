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

use taoResultServer_models_classes_ResultServerStateFull;
use oat\generis\model\OntologyAwareTrait;
use oat\taoResultServer\models\classes\ResultServiceTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoResultServer\models\classes\ResultServerService;

class OntologyService extends ConfigurableService implements ResultServerService
{
    
    use OntologyAwareTrait;
    use ResultServiceTrait;

    const OPTION_DEFAULT_MODEL = 'default';
    
    const PROPERTY_RESULT_SERVER = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#DeliveryResultServer';

    public function initResultServer($compiledDelivery, $executionIdentifier){
    
        //starts or resume a taoResultServerStateFull session for results submission
    
        //retrieve the result server definition
        $resultServer = $compiledDelivery->getUniquePropertyValue($this->getProperty(self::PROPERTY_RESULT_SERVER));
        //callOptions are required in the case of a LTI basic storage
    
        taoResultServer_models_classes_ResultServerStateFull::singleton()->initResultServer($resultServer->getUri());
    
        //a unique identifier for data collected through this delivery execution
        //in the case of LTI, we should use the sourceId
    
    
        taoResultServer_models_classes_ResultServerStateFull::singleton()->spawnResult($executionIdentifier, $executionIdentifier);
        \common_Logger::i("Spawning/resuming result identifier related to process execution ".$executionIdentifier);
        //set up the related test taker
        //a unique identifier for the test taker
        taoResultServer_models_classes_ResultServerStateFull::singleton()->storeRelatedTestTaker(\common_session_SessionManager::getSession()->getUserUri());
    
        //a unique identifier for the delivery
        taoResultServer_models_classes_ResultServerStateFull::singleton()->storeRelatedDelivery($compiledDelivery->getUri());
    }
    
    /**
     * Returns the storage engine of the result server
     * 
     * @param string $deliveryId
     * @throws \common_exception_Error
     * @return \taoResultServer_models_classes_ReadableResultStorage|\taoResultServer_models_classes_WritableResultStorage|oat\taoResultServer\models\classes\ResultManagement
     */
    public function getResultStorage($deliveryId)
    {
        if(is_null($deliveryId)){
            throw new \common_exception_Error(__('This delivery doesn\'t exists'));
        }
        
        $delivery = $this->getResource($deliveryId);
        
        $deliveryResultServer = $delivery->getOnePropertyValue($this->getProperty(self::PROPERTY_RESULT_SERVER));
        
        if(is_null($deliveryResultServer)){
            throw new \common_exception_Error(__('This delivery has no Result Server'));
        }
        $resultServerModel = $deliveryResultServer->getPropertyValues($this->getProperty(TAO_RESULTSERVER_MODEL_PROP));

        if(is_null($resultServerModel)){
            throw new \common_exception_Error(__('This delivery has no readable Result Server'));
        }

        $implementations = array();
        foreach($resultServerModel as $model){
            $model = $this->getClass($model);

            /** @var $implementation \core_kernel_classes_Literal*/
            $implementation = $model->getOnePropertyValue($this->getProperty(TAO_RESULTSERVER_MODEL_IMPL_PROP));

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
}

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

use oat\generis\model\OntologyAwareTrait;
use oat\taoResultServer\models\classes\AbstractResultService;

/**
 * Class OntologyService
 * @package oat\taoResultServer\models\classes\implementation
 * @deprecated ResultServerService should be used instead
 */
class OntologyService extends AbstractResultService
{
    use OntologyAwareTrait;

    const OPTION_DEFAULT_MODEL = 'default';
    /** @deprecated */
    const PROPERTY_RESULT_SERVER = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#DeliveryResultServer';

    /** @var bool $configurable Whether this ResultServerService instance is configurable */
    protected $configurable = false;

    /**
     * Returns the storage engine of the result server
     *
     * @param string $deliveryId
     * @throws \common_exception_Error
     * @return \taoResultServer_models_classes_ReadableResultStorage
     */
    public function getResultStorage($deliveryId = null)
    {
        if ($deliveryId !== null) {
            $delivery = $this->getResource($deliveryId);
            $deliveryResultServer = $delivery->getOnePropertyValue($this->getProperty(self::PROPERTY_RESULT_SERVER));
        }
        if (!isset($deliveryResultServer) || !$deliveryResultServer) {
            $deliveryResultServer = \taoResultServer_models_classes_ResultServerAuthoringService::singleton()->getDefaultResultServer();
        }

        if(is_null($deliveryResultServer)){
            throw new \common_exception_Error(__('This delivery has no Result Server'));
        }
        $resultServerModel = $deliveryResultServer->getPropertyValues($this->getProperty(static::PROPERTY_HAS_MODEL));

        if(is_null($resultServerModel)){
            throw new \common_exception_Error(__('This delivery has no readable Result Server'));
        }

        $implementations = array();
        foreach($resultServerModel as $model){
            $model = $this->getClass($model);

            /** @var $implementation \core_kernel_classes_Literal*/
            $implementation = $model->getOnePropertyValue($this->getProperty(static::PROPERTY_MODEL_IMPL));

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

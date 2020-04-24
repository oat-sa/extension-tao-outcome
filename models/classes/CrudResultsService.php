<?php

/*
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

use common_exception_Error;
use core_kernel_classes_Class;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use taoResultServer_models_classes_ReadableResultStorage;
use taoResultServer_models_classes_ResponseVariable;

/**
 * .Crud services implements basic CRUD services, orginally intended for REST controllers/ HTTP exception handlers
 *  Consequently the signatures and behaviors is closer to REST and throwing HTTP like exceptions
 *
 *
 *
 */
class CrudResultsService extends \tao_models_classes_CrudService
{

    const GROUP_BY_DELIVERY = 0;
    const GROUP_BY_TEST = 1;
    const GROUP_BY_ITEM = 2;

    protected $resultClass = null;

    public function __construct()
    {
        parent::__construct();
        $this->resultClass = new core_kernel_classes_Class(ResultService::DELIVERY_RESULT_CLASS_URI);
    }

    public function getRootClass()
    {
        return $this->resultClass;
    }

    public function get($uri, $groupBy = self::GROUP_BY_DELIVERY)
    {
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $implementation = $resultService->getResultStorage();
        return $this->format($implementation, $uri);
    }

    /**
     * @param taoResultServer_models_classes_ReadableResultStorage $resultStorage
     * @param $resultIdentifier
     * @param int $groupBy
     * @param bool $fetchOnlyLastAttemptResult
     * @return array
     * @throws common_exception_Error
     */
    public function format(
        taoResultServer_models_classes_ReadableResultStorage $resultStorage,
        $resultIdentifier,
        $groupBy = self::GROUP_BY_DELIVERY,
        $fetchOnlyLastAttemptResult = false
    )
    {
        $groupedData = [];
        if ($groupBy === self::GROUP_BY_DELIVERY || $groupBy === self::GROUP_BY_ITEM) {
            $calls = $resultStorage->getRelatedItemCallIds($resultIdentifier);
        } else {
            $calls = $resultStorage->getRelatedTestCallIds($resultIdentifier);
            $fetchOnlyLastAttemptResult = false;
        }

        foreach ($calls as $callId) {
            $results = $resultStorage->getVariables($callId);
            $lastData = [];
            foreach ($results as $result) {
                $result = array_pop($result);
                if (isset($result->variable)) {
                    $resource = $this->getFormResource($result->variable);
                    if (!$fetchOnlyLastAttemptResult) {
                        $lastData[] = $resource;
                    } else {
                        $currentResTime = preg_replace('/0(\.\d*)\s(\d*)/', '$2$1', $resource['epoch']);
                        $previousResTime = preg_replace(
                            '/0(\.\d*)\s(\d*)/',
                            '$2$1',
                            $lastData[$resource['identifier']]['epoch'] ?? '0.0 0'
                        );
                        if ($currentResTime > $previousResTime) {
                            $lastData[$resource['identifier']] = $resource;
                        }
                    }
                }
            }
            $groupKey = $groupBy === self::GROUP_BY_DELIVERY ? $resultIdentifier : $callId;
            $groupedData[$groupKey][] = array_values($lastData);
        }
        foreach($groupedData as $groupKey => $items){
            $returnData[$groupKey] = array_merge(...$items);
        }
        return $returnData ?? [];
    }

    public function getAll()
    {
        $resources = [];
        $deliveryService = DeliveryAssemblyService::singleton();
        foreach ($deliveryService->getAllAssemblies() as $assembly) {
            // delivery uri
            $delivery = $assembly->getUri();

            $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
            $implementation = $resultService->getResultStorage();

            // get delivery executions

            //get all info
            foreach ($implementation->getResultByDelivery([$delivery]) as $result) {
                $result = array_merge($result, [RDFS_LABEL => $assembly->getLabel()]);
                $properties = [];
                foreach ($result as $key => $value) {
                    $property = [];
                    $type = 'resource';
                    switch ($key) {
                        case 'deliveryResultIdentifier':
                            $property['predicateUri'] = "http://www.tao.lu/Ontologies/TAOResult.rdf#Identifier";
                            break;
                        case 'testTakerIdentifier':
                            $property['predicateUri'] = "http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfSubject";
                            break;
                        case 'deliveryIdentifier':
                            $property['predicateUri'] = "http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfDelivery";
                            break;
                        default:
                            $property['predicateUri'] = $key;
                            $type = 'literal';
                            break;
                    }
                    $property['values'] = ['valueType' => $type, 'value' => $value];

                    $properties[] = $property;
                }
                $resources[] = [
                    'uri'        => $result['deliveryResultIdentifier'],
                    'properties' => $properties
                ];
            }
        }
        return $resources;
    }


    public function delete($resource)
    {
        throw new \common_exception_NoImplementation();
    }

    public function deleteAll()
    {
        throw new \common_exception_NoImplementation();
    }


    public function update($uri = null, $propertiesValues = [])
    {
        throw new \common_exception_NoImplementation();
    }

    public function isInScope($uri)
    {
        return true;
    }

    /**
     *
     * @author Patrick Plichart, patrick@taotesting.com
     * return tao_models_classes_ClassService
     */
    protected function getClassService()
    {
        // TODO: Implement getClassService() method.
    }

    /**
     * @param $variable
     * @return array
     * @throws common_exception_Error
     */
    protected function getFormResource($variable){
        $resource['value'] = $variable->getValue();
        $resource['identifier'] = $variable->getIdentifier();
        if ($variable instanceof taoResultServer_models_classes_ResponseVariable) {
            $resource['type'] = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable');
        } else {
            $resource['type']= new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable');
        }
        $resource['epoch'] = $variable->getEpoch();
        $resource['cardinality'] = $variable->getCardinality();
        $resource['basetype'] = $variable->getBaseType();
        return $resource;
    }
}

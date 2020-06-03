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
 * Copyright (c) 2017-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use core_kernel_classes_Class;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use tao_models_classes_CrudService;
use taoResultServer_models_classes_ReadableResultStorage;
use taoResultServer_models_classes_ResponseVariable;

/**
 * Crud services implements basic CRUD services, originally intended for REST controllers/ HTTP exception handlers
 * Consequently the signatures and behaviors is closer to REST and throwing HTTP like exceptions
 */
class CrudResultsService extends tao_models_classes_CrudService
{

    public const GROUP_BY_DELIVERY = 0;
    public const GROUP_BY_TEST = 1;
    public const GROUP_BY_ITEM = 2;

    /** @var core_kernel_classes_Class  */
    protected $resultClass;

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

    public function format(
        taoResultServer_models_classes_ReadableResultStorage $resultStorage,
        $resultIdentifier,
        int $groupBy = self::GROUP_BY_DELIVERY,
        bool $fetchOnlyLastAttemptResult = false,
        bool $splitByAttempt = false
    ): array {
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
            $groupedData[$groupKey][] = $splitByAttempt ? $this->splitByAttempt($lastData) : $lastData;
        }
        foreach ($groupedData as $groupKey => $items) {
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
     */
    protected function getFormResource($variable)
    {
        $resource['value'] = $variable->getValue();
        $resource['identifier'] = $variable->getIdentifier();
        if ($variable instanceof taoResultServer_models_classes_ResponseVariable) {
            $resource['type'] = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable');
        } else {
            $resource['type'] = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable');
        }
        $resource['epoch'] = $variable->getEpoch();
        $resource['cardinality'] = $variable->getCardinality();
        $resource['basetype'] = $variable->getBaseType();
        return $resource;
    }


    private function convertTime(string $epoch): float
    {
        [$usec, $sec] = explode(' ', $epoch);

        return ((float)$usec + (float)$sec);
    }

    private function splitByAttempt(array $itemVariables): array
    {
        $attempts = [];
        foreach ($itemVariables as $variable) {
            if ($variable['identifier'] == 'numAttempts') {
                $attempts[(string)$this->convertTime($variable['epoch'])] = [];
            }
        }
        foreach ($itemVariables as $variable) {
            $cand = null;
            $bestDist = null;
            foreach (array_keys($attempts) as $time) {
                $dist = abs($time - $this->convertTime($variable['epoch']));
                if (is_null($bestDist) || $dist < $bestDist) {
                    $bestDist = $dist;
                    $cand = $time;
                }
            }
            $attempts[$cand][] = $variable;
        }

        return $attempts;
    }
}

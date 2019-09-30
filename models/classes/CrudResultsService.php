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

use oat\oatbox\service\ServiceNotFoundException;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use taoResultServer_models_classes_ReadableResultStorage as ReadableResultStorageInterface;

/**
 * .Crud services implements basic CRUD services, orginally intended for REST controllers/ HTTP exception handlers
 *  Consequently the signatures and behaviors is closer to REST and throwing HTTP like exceptions
 */
class CrudResultsService extends \tao_models_classes_CrudService
{
    // How to group variables?
    const GROUP_BY_DELIVERY = 0;
    const GROUP_BY_TEST = 1;
    const GROUP_BY_ITEM = 2;

    // Which attempts to return?
    const ATTEMPTS_NONE = 0;
    const ATTEMPTS_ALL = 1;
    const ATTEMPTS_LATEST = 2;

    protected $resultClass = null;

    /**
     * CrudResultsService constructor.
     * @throws \common_exception_Error
     */
    public function __construct()
    {
        parent::__construct();
        $this->resultClass = new \core_kernel_classes_Class(ResultService::DELIVERY_RESULT_CLASS_URI);
    }

    public function getRootClass()
    {
        return $this->resultClass;
    }

    /**
     * @param $uri
     * @param int $groupBy
     * @return array|object
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function get($uri, $groupBy = self::GROUP_BY_DELIVERY)
    {
        $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($uri);
        $delivery = $deliveryExecution->getDelivery();

        $implementation = $this->getResultServerService()->getResultStorage($delivery->getUri());

        return $this->readQtiResult($implementation, $uri, self::GROUP_BY_DELIVERY, self::ATTEMPTS_LATEST);
    }

    /**
     * @param ReadableResultStorageInterface|ResultManagement $resultStorage
     * @param $resultIdentifier
     * @param int $groupBy
     * @param int $returnAttempts Which attempts do we want? One of self ATTEMPTS_*
     * @return array
     * @throws \common_exception_Error
     */
    public function readQtiResult(ReadableResultStorageInterface $resultStorage, $resultIdentifier, $groupBy, $returnAttempts = self::ATTEMPTS_NONE)
    {
        $returnData = [];

        // Finds calls to the ResultStorage we have to make.
        $calls = ($groupBy === self::GROUP_BY_DELIVERY || $groupBy === self::GROUP_BY_ITEM
            ? $resultStorage->getRelatedItemCallIds($resultIdentifier)
            : $resultStorage->getRelatedTestCallIds($resultIdentifier)
        );

        // Retrieves result variables.
        foreach ($calls as $callId) {
            $results = $resultStorage->getVariables($callId);
            foreach ($results as $attempts) {
                foreach ($attempts as $attempt) {
                    if (isset($attempt->variable)) {
                        $resource = $this->convertVariableToResource($attempt->variable);

                        $timestamp = ($returnAttempts !== self::ATTEMPTS_NONE
                            ? (int)explode(' ', $resource['epoch'])[1]
                            : ''
                        );

                        $returnData[$timestamp . $callId][] = $resource;
                    }
                }
            }
        }

        // Sorts attempts by timestamp or takes only the most recent one.
        if ($returnAttempts === self::ATTEMPTS_ALL) {
            ksort($returnData);
        } else {
            krsort($returnData);
            $returnData = array_slice($returnData, 0, 1);
        }

        return $returnData;
    }

    /**
     * @return array|\stdClass
     * @throws \common_exception_Error
     */
    public function getAll()
    {
        $resources = array();
        $deliveryService = DeliveryAssemblyService::singleton();
        foreach ($deliveryService->getAllAssemblies() as $assembly) {
            /** @var \core_kernel_classes_Resource $assembly */
            // delivery uri
            $delivery = $assembly->getUri();

            $implementation = $this->getResultServerService()->getResultStorage($delivery);

            // get delivery executions

            //get all info
            foreach ($implementation->getResultByDelivery(array($delivery)) as $result) {
                $result = array_merge($result, array(RDFS_LABEL => $assembly->getLabel()));
                $properties = array();
                foreach ($result as $key => $value) {
                    $property = array();
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
                    $property['values'] = array('valueType' => $type, 'value' => $value);

                    $properties[] = $property;

                }
                $resources[] = array(
                    'uri' => $result['deliveryResultIdentifier'],
                    'properties' => $properties
                );
            }
        }

        return $resources;
    }

    /**
     * @param string $resource
     * @throws \common_exception_NoImplementation
     */
    public function delete($resource)
    {
        throw new \common_exception_NoImplementation();
    }

    /**
     * @throws \common_exception_NoImplementation
     */
    public function deleteAll()
    {
        throw new \common_exception_NoImplementation();
    }

    /**
     * @param null $uri
     * @param array $propertiesValues
     * @return \core_kernel_classes_Resource|void
     * @throws \common_exception_NoImplementation
     */
    public function update($uri = null, $propertiesValues = array())
    {
        throw new \common_exception_NoImplementation();
    }

    public function isInScope($uri)
    {
        return true;
    }

    /**
     * Retrieves result storage for the given delivery.
     *
     * @return ResultServerService
     * @throws ServiceNotFoundException when the ResultServerService is not instanciated.
     */
    protected function getResultServerService()
    {
        $resultServerService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        if (!$resultServerService instanceof ResultServerService) {
            throw new ServiceNotFoundException('Unable to load ResultServer service.');
        }
        return $resultServerService;
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
     * @param \taoResultServer_models_classes_Variable $variable
     * @return array
     * @throws \common_exception_Error
     */
    protected function convertVariableToResource(\taoResultServer_models_classes_Variable $variable)
    {
        $type = $variable instanceof \taoResultServer_models_classes_ResponseVariable
            ? 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable'
            : 'http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable';

        return [
            'value' => $variable->getValue(),
            'identifier' => $variable->getIdentifier(),
            'type' => new \core_kernel_classes_Class($type),
            'epoch' => $variable->getEpoch(),
            'cardinality' => $variable->getCardinality(),
            'basetype' => $variable->getBaseType(),
        ];
    }
}

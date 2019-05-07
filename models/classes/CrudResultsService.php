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
 * Copyright (c) 2017-2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * .Crud services implements basic CRUD services, originally intended for REST controllers/ HTTP exception handlers
 *  Consequently the signatures and behaviors is closer to REST and throwing HTTP like exceptions
 */
class CrudResultsService extends \tao_models_classes_CrudService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const GROUP_BY_DELIVERY = 0;
    const GROUP_BY_TEST = 1;
    const GROUP_BY_ITEM = 2;

    public function __construct()
    {
        parent::__construct();
    }

    public function getRootClass()
    {
        return $this->getClass(ResultService::DELIVERY_RESULT_CLASS_URI);
    }

    public function get($uri, $groupBy = self::GROUP_BY_DELIVERY)
    {
        $deliveryExecution = $this->getDeliveryExecution($uri);
        $delivery = $deliveryExecution->getDelivery();

        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $implementation = $resultService->getResultStorage($delivery->getUri());
        echo $deliveryExecution->getIdentifier();
        return $this->format($implementation, $uri);
    }

    public function format(\taoResultServer_models_classes_ReadableResultStorage $resultStorage, $resultIdentifier, $groupBy = self::GROUP_BY_DELIVERY)
    {
        $returnData = array();

        if ($groupBy === self::GROUP_BY_DELIVERY || $groupBy === self::GROUP_BY_ITEM) {
            $calls = $resultStorage->getRelatedItemCallIds($resultIdentifier);
        } else {
            $calls = $resultStorage->getRelatedTestCallIds($resultIdentifier);
        }

        foreach ($calls as $callId) {
            $results = $resultStorage->getVariables($callId);
            $resource = array();
            foreach ($results as $result) {
                $result = array_pop($result);
                if (isset($result->variable)) {
                    $resource['value'] = $result->variable->getValue();
                    $resource['identifier'] = $result->variable->getIdentifier();
                    if ($result->variable instanceof \taoResultServer_models_classes_ResponseVariable) {
                        $type = "http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable";
                    } else {
                        $type = "http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable";
                    }
                    $resource['type'] = $this->getClass($type);
                    $resource['epoch'] = $result->variable->getEpoch();
                    $resource['cardinality'] = $result->variable->getCardinality();
                    $resource['basetype'] = $result->variable->getBaseType();
                }

                if ($groupBy === self::GROUP_BY_DELIVERY) {
                    $returnData[$resultIdentifier][] = $resource;
                } else {
                    $returnData[$callId][] = $resource;
                }
            }
        }
        return $returnData;
    }

    public function getAll()
    {
        $resources = array();
        $deliveryService = DeliveryAssemblyService::singleton();

        foreach ($deliveryService->getAllAssemblies() as $assembly) {
            // delivery uri
            $delivery = $assembly->getUri();
            // get delivery executions
            $deliveryExecutions = $this->getDeliveryExecutions($delivery);

            //get all info
            foreach ($deliveryExecutions as $result) {
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


    public function delete($resource)
    {
        throw new \common_exception_NoImplementation();
    }

    public function deleteAll()
    {
        throw new \common_exception_NoImplementation();
    }


    public function update($uri = null, $propertiesValues = array())
    {
        throw new \common_exception_NoImplementation();
    }

    public function isInScope($uri)
    {
        return true;
    }

    /**
     * Get a delivery execution based on given $identifier
     * Filters can be applied
     *
     * @param $uri
     * @return DeliveryExecutionInterface
     */
    protected function getDeliveryExecution($uri)
    {
        return $this->filter([
            $this->getServiceLocator()
                ->get(ServiceProxy::SERVICE_ID)
                ->getDeliveryExecution($uri)
        ])[0];
    }

    /**
     * Get list of delivery executions based on given $delivery
     * Filters can be applied
     *
     * @param $delivery
     * @return DeliveryExecutionInterface[]
     */
    protected function getDeliveryExecutions($delivery)
    {
        return $this->filter(
            $this->getServiceLocator()
                ->get(ResultServerService::SERVICE_ID)
                ->getResultStorage($delivery)
                ->getResultByDelivery(array($delivery))
        );
    }

    /**
     * List a list of delivery, by default no filter
     *
     * @param array $deliveryExecutions
     * @return array
     */
    protected function filter(array $deliveryExecutions)
    {
        return $deliveryExecutions;
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

}

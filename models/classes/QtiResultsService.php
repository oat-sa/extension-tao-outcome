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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\service\ServiceNotFoundException;
use oat\taoDelivery\model\execution\DeliveryExecution as DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;

class QtiResultsService extends ConfigurableService implements ResultService
{
    /**
     * @var ServiceProxy
     */
    protected $deliveryExecutionService;

    /**
     * @var ResultServerService
     */
    protected $resultServerService;

    /**
     * @deprecated
     */
    public static function singleton()
    {
        return ServiceManager::getServiceManager()->get(self::SERVICE_ID);
    }

    /**
     * Get last delivery execution from $delivery & $testtaker uri
     *
     * @param string $delivery uri
     * @param string $testtaker uri
     * @return \oat\taoDelivery\model\execution\DeliveryExecutionInterface
     * @throws
     */
    public function getDeliveryExecutionByTestTakerAndDelivery($delivery, $testtaker)
    {
        $delivery = new \core_kernel_classes_Resource($delivery);
        $deliveryExecutions = $this->getDeliveryExecutionService()->getUserExecutions($delivery, $testtaker);
        if (empty($deliveryExecutions)) {
            throw new \common_exception_NotFound('Provided parameters don\'t match with any delivery execution.');
        }
        return array_pop($deliveryExecutions);
    }

    /**
     * Get Delivery execution from resource
     *
     * @param $deliveryExecutionId
     * @return DeliveryExecutionInterface
     * @throws \common_exception_NotFound
     */
    public function getDeliveryExecutionById($deliveryExecutionId)
    {
        $deliveryExecution = $this->getDeliveryExecutionService()->getDeliveryExecution($deliveryExecutionId);
        try {
            $deliveryExecution->getDelivery();
        } catch (\common_exception_NotFound $e) {
            throw new \common_exception_NotFound('Provided parameters don\'t match with any delivery execution.');
        }
        return $deliveryExecution;
    }

    /**
     * Return delivery execution as xml of testtaker based on delivery
     *
     * @param DeliveryExecutionInterface $deliveryExecution
     *
     * @return string
     * @throws \common_exception_Error when the result storage can not be instanciated or is not readable.
     * @throws ServiceNotFoundException when the ResultServer service can not be instanciated.
     * @throws InvalidServiceManagerException when the service locator is not initialized
     * @throws \common_Exception when timestamp is not recognized
     */
    public function getDeliveryExecutionXml(DeliveryExecutionInterface $deliveryExecution)
    {
        return $this->getQtiResultXml($deliveryExecution->getDelivery()->getUri(), $deliveryExecution->getIdentifier());
    }

    /**
     * @param string $deliveryId
     * @param string $resultId
     *
     * @return string
     * @throws \common_exception_Error when the result storage can not be instanciated or is not readable.
     * @throws ServiceNotFoundException when the ResultServer service can not be instanciated.
     * @throws InvalidServiceManagerException when the service locator is not initialized
     * @throws \common_Exception when timestamp is not recognized
     */
    public function getQtiResultXml($deliveryId, $resultId)
    {
        // Retrieves result storage and delivery execution id.
        $resultServer = $this->getResultServerService()->getResultStorage($deliveryId);;
        $deliveryExecutionId = $this->getResultAliasService()->getDeliveryExecutionId($resultId) ?: $resultId;

        $crudService = new CrudResultsService();
        $testTaker = $resultServer->getTestTaker($deliveryExecutionId);
        $testResults = $crudService->readQtiResult($resultServer, $deliveryExecutionId, CrudResultsService::GROUP_BY_TEST);
        $itemResults = $crudService->readQtiResult($resultServer, $deliveryExecutionId, CrudResultsService::GROUP_BY_ITEM, CrudResultsService::ATTEMPTS_ALL);

        // Converts array result to xml.
        $qtiToXmlConverter = new QtiToXmlConverter();
        return $qtiToXmlConverter->convertToXml($testTaker, $testResults, $itemResults);
    }

    /**
     * Get the implementation of delivery execution service
     *
     * @return ServiceProxy
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    protected function getDeliveryExecutionService()
    {
        if (!$this->deliveryExecutionService) {
            $this->deliveryExecutionService = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
        }
        return $this->deliveryExecutionService;
    }

    /**
     * Retrieves result storage for the given delivery.
     *
     * @return ResultServerService
     * @throws ServiceNotFoundException when the ResultServerService is not instanciated.
     */
    protected function getResultServerService()
    {
        if (!$this->resultServerService) {
            $resultServerService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
            if (!$resultServerService instanceof ResultServerService) {
                throw new ServiceNotFoundException('Unable to load ResultServer service.');
            }
            $this->resultServerService = $resultServerService;
        }
        return $this->resultServerService;
    }

    /**
     * Retrieves the delivery execution id given the result id.
     * Can be the same as result id if no alias is set.
     *
     * @return ResultAliasServiceInterface
     * @throws InvalidServiceManagerException
     */
    protected function getResultAliasService()
    {
        $resultAliasService = $this->getServiceManager()->get(ResultAliasServiceInterface::SERVICE_ID);
        if (!$resultAliasService instanceof ResultAliasServiceInterface) {
            throw new ServiceNotFoundException('Unable to load ResultAlias service.');
        }
        return $resultAliasService;
    }
}

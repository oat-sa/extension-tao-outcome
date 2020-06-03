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
 * Copyright (c) 2016-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\classes;

use common_Exception;
use common_exception_InvalidArgumentType;
use common_exception_NotFound;
use common_exception_NotImplemented;
use core_kernel_classes_Resource;
use DOMDocument;
use DOMElement;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoDelivery\model\execution\DeliveryExecution as DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoResultServer\models\Exceptions\DuplicateVariableException;
use oat\taoResultServer\models\Mapper\ResultMapper;
use oat\taoResultServer\models\Parser\QtiResultParser;
use qtism\common\enums\Cardinality;
use qtism\data\storage\xml\XmlStorageException;
use tao_helpers_Date;
use taoResultServer_models_classes_WritableResultStorage as WritableResultStorage;

class QtiResultsService extends ConfigurableService implements ResultService
{
    protected $deliveryExecutionService;

    private const QTI_NS = 'http://www.imsglobal.org/xsd/imsqti_result_v2p1';

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
     * Get last delivery execution from $delivery & $testtaker uri
     *
     * @param string $delivery uri
     * @param string $testtaker uri
     * @return \oat\taoDelivery\model\execution\DeliveryExecutionInterface
     * @throws
     */
    public function getDeliveryExecutionByTestTakerAndDelivery($delivery, $testtaker)
    {
        $delivery = new core_kernel_classes_Resource($delivery);
        $deliveryExecutions = $this->getDeliveryExecutionService()->getUserExecutions($delivery, $testtaker);
        if (empty($deliveryExecutions)) {
            throw new common_exception_NotFound('Provided parameters don\'t match with any delivery execution.');
        }
        return array_pop($deliveryExecutions);
    }

    /**
     * Get Delivery execution from resource
     *
     * @param $deliveryExecutionId
     * @return DeliveryExecutionInterface
     * @throws common_exception_NotFound
     */
    public function getDeliveryExecutionById($deliveryExecutionId)
    {
        $deliveryExecution = $this->getDeliveryExecutionService()->getDeliveryExecution($deliveryExecutionId);
        try {
            $deliveryExecution->getDelivery();
        } catch (common_exception_NotFound $e) {
            throw new common_exception_NotFound('Provided parameters don\'t match with any delivery execution.');
        }
        return $deliveryExecution;
    }

    /**
     * Return delivery execution as xml of testtaker based on delivery
     *
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return string
     */
    public function getDeliveryExecutionXml(DeliveryExecutionInterface $deliveryExecution)
    {
        return $this->getQtiResultXml($deliveryExecution->getDelivery()->getUri(), $deliveryExecution->getIdentifier());
    }

    /**
     * @param $deliveryId
     * @param $resultId
     * @param bool $fetchOnlyLastAttemptResult
     * @return string
     * @throws common_Exception
     * @throws InvalidServiceManagerException
     */
    public function getQtiResultXml($deliveryId, $resultId, $fetchOnlyLastAttemptResult = false)
    {
        $deId = $this->getServiceManager()->get(ResultAliasServiceInterface::SERVICE_ID)->getDeliveryExecutionId($resultId);
        if ($deId === null) {
            $deId = $resultId;
        }

        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $resultServer = $resultService->getResultStorage();

        $crudService = new CrudResultsService();

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $itemResultsByAttempt = $crudService->format($resultServer, $deId, CrudResultsService::GROUP_BY_ITEM, $fetchOnlyLastAttemptResult, true);
        $testResults = $crudService->format($resultServer, $deId, CrudResultsService::GROUP_BY_TEST);

        $assessmentResultElt = $dom->createElementNS(self::QTI_NS, 'assessmentResult');
        $dom->appendChild($assessmentResultElt);

        /** Context */
        $contextElt = $dom->createElementNS(self::QTI_NS, 'context');
        $contextElt->setAttribute('sourcedId', \tao_helpers_Uri::getUniqueId($resultServer->getTestTaker($deId)));
        $assessmentResultElt->appendChild($contextElt);

        /** Test Result */
        foreach ($testResults as $testResultIdentifier => $testResult) {
            $identifierParts = explode('.', $testResultIdentifier);
            $testIdentifier = array_pop($identifierParts);

            $testResultElement = $dom->createElementNS(self::QTI_NS, 'testResult');
            $testResultElement->setAttribute('identifier', $testIdentifier);
            $testResultElement->setAttribute('datestamp', $this->getDisplayDate($testResult[0]['epoch']));

            /** Item Variable */
            foreach ($testResult as $itemVariable) {
                $isResponseVariable = $itemVariable['type']->getUri() === 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable';
                $testVariableElement = $dom->createElementNS(self::QTI_NS, ($isResponseVariable) ? 'responseVariable' : 'outcomeVariable');
                $testVariableElement->setAttribute('identifier', $itemVariable['identifier']);
                $testVariableElement->setAttribute('cardinality', $itemVariable['cardinality']);
                $testVariableElement->setAttribute('baseType', $itemVariable['basetype']);

                $valueElement = $this->createCDATANode($dom, 'value', trim($itemVariable['value']));

                if ($isResponseVariable) {
                    $candidateResponseElement = $dom->createElementNS(self::QTI_NS, 'candidateResponse');
                    $candidateResponseElement->appendChild($valueElement);
                    $testVariableElement->appendChild($candidateResponseElement);
                } else {
                    $testVariableElement->appendChild($valueElement);
                }

                $testResultElement->appendChild($testVariableElement);
            }

            $assessmentResultElt->appendChild($testResultElement);
        }

        /** Item Result */
        foreach ($itemResultsByAttempt as $itemResultIdentifier => $itemResults) {
            /** Iterates variables  */
            foreach ($itemResults as $itemResult) {
                $itemElement = $this->createItemResultNode($dom, $itemResultIdentifier, $itemResult);
                /** Item variables */
                foreach ($itemResult as $key => $itemVariable) {
                    $isResponseVariable = $itemVariable['type']->getUri() === 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable';

                    if ($itemVariable['identifier'] == 'comment') {
                        /** Comment */
                        $itemVariableElement = $dom->createElementNS(self::QTI_NS,'candidateComment', $itemVariable['value']);
                    } else {
                        $itemVariableElement = $this->createItemVariableNode($dom, $isResponseVariable, $itemVariable);
                    }
                    $itemElement->appendChild($itemVariableElement);
                }
                $assessmentResultElt->appendChild($itemElement);
            }
        }

        return $dom->saveXML();
    }

    /**
     * Parse the xml to save including variables into given deliveryExecution
     *
     * @param string $deliveryExecutionId
     * @param string $xml
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_NotFound
     * @throws common_exception_NotImplemented
     * @throws XmlStorageException
     * @throws DuplicateVariableException
     */
    public function injectXmlResultToDeliveryExecution($deliveryExecutionId, $xml)
    {
        $deliveryExecution = $this->getDeliveryExecutionById($deliveryExecutionId);

        /** @var QtiResultParser $parser */
        $parser = $this->getServiceLocator()->get(QtiResultParser::class);
        /** @var ResultMapper $map */
        $map = $parser->parse($xml);

        /** @var WritableResultStorage $resultStorage */
        $resultStorage = $this->getServiceLocator()
            ->get(ResultServerService::SERVICE_ID)
            ->getResultStorage();


        $this->storeTestVariables($resultStorage, $deliveryExecutionId, $map->getTestVariables());
        $this->storeItemVariables($resultStorage, $deliveryExecutionId, $map->getItemVariables());
    }

    /**
     * Store test variables associated to a delivery execution
     *
     * @param WritableResultStorage $resultStorage
     * @param string $deliveryExecutionId
     * @param array $itemVariablesByTestResult
     * @throws DuplicateVariableException
     */
    protected function storeTestVariables(WritableResultStorage $resultStorage, $deliveryExecutionId, array $itemVariablesByTestResult)
    {
        $test = ' ';
        foreach ($itemVariablesByTestResult as $test => $testVariables) {
            $resultStorage->storeTestVariables($deliveryExecutionId, $test, $testVariables, $test);
        }
    }

    /**
     * Store item variables associated to a delivery execution
     *
     * @param WritableResultStorage $resultStorage
     * @param string $deliveryExecutionId
     * @param array $itemVariablesByItemResult
     * @throws DuplicateVariableException
     */
    protected function storeItemVariables(WritableResultStorage $resultStorage, $deliveryExecutionId, array $itemVariablesByItemResult)
    {
        $test = null;
        foreach ($itemVariablesByItemResult as $itemResultIdentifier => $itemVariables) {
            $callIdItem = $deliveryExecutionId . '.' . $itemResultIdentifier;
            foreach ($itemVariables as $variable) {
                if ($variable->getIdentifier() == 'numAttempts') {
                    $callIdItem .= '.' . (int)$variable->getValue();
                }
            }
            $resultStorage->storeItemVariables($deliveryExecutionId, $test, $itemResultIdentifier, $itemVariables, $callIdItem);
        }
    }

    /**
     * @param DOMDocument $dom
     * @param string $tag Xml tag to create
     * @param string $data Data to escape
     * @return DOMElement
     */
    protected function createCDATANode($dom, $tag, $data)
    {
        $node = $dom->createCDATASection($data);
        $returnValue = $dom->createElementNS(self::QTI_NS, $tag);
        $returnValue->appendChild($node);
        return $returnValue;
    }

    private function createItemResultNode(DOMDocument $dom, string $itemResultIdentifier, array $itemResult): DOMElement
    {
        $identifierParts = explode('.', $itemResultIdentifier);
        $occurrenceNumber = array_pop($identifierParts);
        $refIdentifier = array_pop($identifierParts);

        $itemElement = $dom->createElementNS(self::QTI_NS, 'itemResult');
        $itemElement->setAttribute('identifier', $refIdentifier);
        $itemElement->setAttribute('datestamp', $this->getDisplayDate($itemResult[0]['epoch']));
        $itemElement->setAttribute('sessionStatus', 'final');

        return $itemElement;
    }

    private function createItemVariableNode(DOMDocument $dom, bool $isResponseVariable, $itemVariable): DOMElement
    {
        /** Item variable */
        $itemVariableElement = $dom->createElementNS(
            self::QTI_NS,
            ($isResponseVariable) ? 'responseVariable' : 'outcomeVariable'
        );
        $itemVariableElement->setAttribute('identifier', $itemVariable['identifier']);
        $itemVariableElement->setAttribute('cardinality', $itemVariable['cardinality']);
        $itemVariableElement->setAttribute('baseType', $itemVariable['basetype']);

        /** Split multiple response */
        $itemVariable['value'] = trim($itemVariable['value'], '[]');
        if ($itemVariable['cardinality'] !== Cardinality::getNameByConstant(Cardinality::SINGLE)) {
            $values = explode(';', $itemVariable['value']);
            $returnValue = [];
            foreach ($values as $value) {
                $returnValue[] = $this->createCDATANode($dom, 'value', $value);
            }
        } else {
            $returnValue = $this->createCDATANode($dom, 'value', $itemVariable['value']);
        }

        /** Get response parent element */
        if ($isResponseVariable) {
            /** Response variable */
            $responseElement = $dom->createElementNS(self::QTI_NS, 'candidateResponse');
        } else {
            /** Outcome variable */
            $responseElement = $itemVariableElement;
        }

        /** Write a response node foreach answer  */
        if (is_array($returnValue)) {
            foreach ($returnValue as $valueElement) {
                $responseElement->appendChild($valueElement);
            }
        } else {
            $responseElement->appendChild($returnValue);
        }

        if ($isResponseVariable) {
            $itemVariableElement->appendChild($responseElement);
        }

        return $itemVariableElement;
    }

    /**
     * @throws common_Exception
     */
    private function getDisplayDate(string $epoch): string
    {
        return tao_helpers_Date::displayeDate($epoch, tao_helpers_Date::FORMAT_ISO8601);
    }
}

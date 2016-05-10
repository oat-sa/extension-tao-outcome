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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use oat\taoDelivery\model\execution\DeliveryExecution as DeliveryExecutionInterface;
use qtism\common\enums\Cardinality;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class QtiResultsService extends \tao_models_classes_CrudService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $deliveryExecutionService;

    const QTI_NS = 'http://www.imsglobal.org/xsd/imsqti_result_v2p1';

    /**
     * Get the implementation of delivery execution service
     *
     * @return \taoDelivery_models_classes_execution_DeliveryExecution
     */
    protected function getDeliveryExecutionService()
    {
        if (!$this->deliveryExecutionService) {
            $this->deliveryExecutionService = $this->getServiceLocator()
                ->get('taoDelivery/' . \taoDelivery_models_classes_execution_ServiceProxy::CONFIG_KEY);
        }
        return $this->deliveryExecutionService;
    }

    /**
     * Get last delivery execution from $delivery & $testtaker uri
     *
     * @param \core_kernel_classes_Resource $delivery
     * @param \core_kernel_classes_Resource $testtaker
     * @return mixed
     */
    public function getDeliveryExecutionByTestTakerAndDelivery(
        \core_kernel_classes_Resource $delivery,
        \core_kernel_classes_Resource $testtaker
    ) {
        $deliveryExecutions = $this->getDeliveryExecutionService()->getUserExecutions($delivery, $testtaker->getUri());
        return array_pop($deliveryExecutions);
    }

    /**
     * Get Delivery execution from resource
     *
     * @param \core_kernel_classes_Resource $deliveryExecution
     * @return \taoDelivery_models_classes_execution_DeliveryExecution
     */
    public function getDeliveryExecutionByResource(\core_kernel_classes_Resource $deliveryExecution)
    {
        return \taoDelivery_models_classes_execution_ServiceProxy::singleton()->getDeliveryExecution($deliveryExecution->getUri());
    }

    /**
     * Return delivery execution as xml of testtaker based on delivery
     *
     * @return string
     */
    public function getDeliveryExecution(DeliveryExecutionInterface $deliveryExecution)
    {
        $resultService = new CrudResultsService();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $itemResults = $resultService->get($deliveryExecution->getUri(), CrudResultsService::GROUP_BY_ITEM);
        $testResults = $resultService->get($deliveryExecution->getUri(), CrudResultsService::GROUP_BY_TEST);

        $assessmentResultElt = $dom->createElementNS(self::QTI_NS, 'assessmentResult');
        $dom->appendChild($assessmentResultElt);

        /** Context */
        $contextElt = $dom->createElementNS(self::QTI_NS, 'context');
        $contextElt->setAttribute('sourcedId', \tao_helpers_Uri::getUniqueId($deliveryExecution->getUserIdentifier()));
        $assessmentResultElt->appendChild($contextElt);

        /** Test Result */
        foreach ($testResults as $testResultIdentifier => $testResult) {
            $identifierParts = explode('.', $testResultIdentifier);
            $testIdentifier = array_pop($identifierParts);

            $testResultElement = $dom->createElementNS(self::QTI_NS, 'testResult');
            $testResultElement->setAttribute('identifier', $testIdentifier);
            $testResultElement->setAttribute('datestamp', \tao_helpers_Date::displayeDate(
                $testResult[0]['epoch'],
                \tao_helpers_Date::FORMAT_ISO8601
            ));

            /** Item Variable */
            foreach ($testResult as $itemVariable) {

                $isResponseVariable = $itemVariable['type']->getUri() === 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable';
                $testVariableElement = $dom->createElementNS(self::QTI_NS, ($isResponseVariable) ? 'responseVariable' : 'outcomeVariable');
                $testVariableElement->setAttribute('identifier', $itemVariable['identifier']);
                $testVariableElement->setAttribute('cardinality', $itemVariable['cardinality']);
                $testVariableElement->setAttribute('baseType', $itemVariable['basetype']);

                $valueElement = $dom->createElementNS(self::QTI_NS, 'value', trim($itemVariable['value']));

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
        foreach ($itemResults as $itemResultIdentifier => $itemResult) {

            // Retrieve identifier.
            $identifierParts = explode('.', $itemResultIdentifier);
            $occurenceNumber = array_pop($identifierParts);
            $refIdentifier = array_pop($identifierParts);

            $itemElement = $dom->createElementNS(self::QTI_NS, 'itemResult');
            $itemElement->setAttribute('identifier', $refIdentifier);
            $itemElement->setAttribute('datestamp', \tao_helpers_Date::displayeDate(
                $itemResult[0]['epoch'],
                \tao_helpers_Date::FORMAT_ISO8601
            ));
            $itemElement->setAttribute('sessionStatus', 'final');

            /** Item variables */
            foreach ($itemResult as $key => $itemVariable) {
                $isResponseVariable = $itemVariable['type']->getUri() === 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable';

                if ($itemVariable['identifier']=='comment') {
                    /** Comment */
                    $itemVariableElement = $dom->createElementNS(self::QTI_NS, 'candidateComment', $itemVariable['value']);
                } else {
                    /** Item variable */
                    $itemVariableElement = $dom->createElementNS(self::QTI_NS, ($isResponseVariable) ? 'responseVariable' : 'outcomeVariable');
                    $itemVariableElement->setAttribute('identifier', $itemVariable['identifier']);
                    $itemVariableElement->setAttribute('cardinality', $itemVariable['cardinality']);
                    $itemVariableElement->setAttribute('baseType', $itemVariable['basetype']);

                    /** Split multiple response */
                    $itemVariable['value'] = trim($itemVariable['value'], '[]');
                    if ($itemVariable['cardinality']!==Cardinality::getNameByConstant(Cardinality::SINGLE)) {
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
                }

                $itemElement->appendChild($itemVariableElement);
            }

            $assessmentResultElt->appendChild($itemElement);
        }

        return $dom->saveXML();
    }

    /**
     * @param $dom \DOMDocument
     * @param $tag Xml tag to create
     * @param $data Data to escape
     * @return \DOMElement
     */
    protected function createCDATANode($dom, $tag, $data)
    {
        $node =  $dom->createCDATASection($data);
        $returnValue = $dom->createElementNS(self::QTI_NS, $tag);
        $returnValue->appendChild($node);
        return $returnValue;
    }

    public function delete($uri)
    {
        throw new \common_exception_NoImplementation();
    }

    public function deleteAll()
    {
        throw new \common_exception_NoImplementation();
    }

    public function create($label = "", $type = null, $propertiesValues = array())
    {
        throw new \common_exception_NoImplementation();
    }

    public function update($uri, $propertiesValues = array())
    {
        throw new \common_exception_NoImplementation();
    }

    protected function getClassService()
    {
        // Unused
    }
}
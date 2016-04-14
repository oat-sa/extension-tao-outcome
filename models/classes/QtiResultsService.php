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

use oat\oatbox\service\ServiceManager;

class QtiResultsService extends \tao_models_classes_CrudService
{
    const QTI_NS = 'http://www.imsglobal.org/xsd/imsqti_result_v2p1';

    protected $testtaker;
    protected $delivery;

    public function __construct()
    {
        parent::__construct();
    }

    public function setParameters($parameters)
    {
        $testtakerUri = $parameters[PROPERTY_DELVIERYEXECUTION_SUBJECT];
        $deliveryUri = $parameters[PROPERTY_DELVIERYEXECUTION_DELIVERY];
        if (!\common_Utils::isUri($testtakerUri)) {
            throw new \common_exception_InvalidArgumentType('QtiRestResults', 'setParameters', '', 'uri', $testtakerUri);
        }
        if (!\common_Utils::isUri($deliveryUri)) {
            throw new \common_exception_InvalidArgumentType('QtiRestResults', 'setParameters', '', 'uri', $deliveryUri);
        }
        $this->testtaker = new \core_kernel_classes_Resource($testtakerUri);
        $this->delivery = new \core_kernel_classes_Resource($deliveryUri);

        return $this;
    }

    protected function getRootClass()
    {
        // Unused
    }

    protected function getClassService()
    {
        // Unused
    }

    public function getDeliveryExecution()
    {
        $serviceManager = ServiceManager::getServiceManager();

        /*
         * 1. Retrieve the delivery executions related to this
         *    $deliveryUri <-> $testTakerUri combination.
         */
        $deliveryService = $serviceManager->get('taoDelivery/' . \taoDelivery_models_classes_execution_ServiceProxy::CONFIG_KEY);
        $deliveryExecutions = $deliveryService->getUserExecutions($this->delivery, $this->testtaker->getUri());

        /*
         * 2. Get Test/Item Results for $deliveryExecutions.
         */
        $resultService = new CrudResultsService();

        foreach ($deliveryExecutions as $deliveryExecution) {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;

            $itemResults = $resultService->get($deliveryExecution->getUri(), CrudResultsService::GROUP_BY_ITEM);
            $testResults = $resultService->get($deliveryExecution->getUri(), CrudResultsService::GROUP_BY_TEST);

            $assessmentResultElt = $dom->createElementNS(self::QTI_NS, 'assessmentResult');
            $dom->appendChild($assessmentResultElt);

            $contextElt = $dom->createElementNS(self::QTI_NS, 'context');
            $contextElt->setAttribute('sourceID', $this->testtaker->getUri());
            $assessmentResultElt->appendChild($contextElt);

            foreach ($itemResults as $itemResultIdentifier => $itemResult) {

                // Retrieve identifier.
                $identifierParts = explode('.', $itemResultIdentifier);
                $occurenceNumber = array_pop($identifierParts);
                $refIdentifier = array_pop($identifierParts);

                $itemResultElt = $dom->createElementNS(self::QTI_NS, 'itemResult');
                $itemResultElt->setAttribute('identifier', $refIdentifier);
                $itemResultElt->setAttribute('dateStamp', $itemResult[0]['epoch']);
                $itemResultElt->setAttribute('sessionStatus', 'final');

                foreach ($itemResult as $itemVariable) {


                    $isResponseVariable = $itemVariable['type']->getUri() === 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable';
                    $itemVariableElt = $dom->createElementNS(self::QTI_NS, ($isResponseVariable) ? 'responseVariable' : 'outcomeVariable');
                    $itemVariableElt->setAttribute('identifier', $itemVariable['identifier']);
                    $itemVariableElt->setAttribute('cardinality', $itemVariable['cardinality']);
                    $itemVariableElt->setAttribute('baseType', $itemVariable['basetype']);

                    $valueElt = $dom->createElementNS(self::QTI_NS, 'value');
                    $valueElt->textContent = $itemVariable['value'];

                    if ($isResponseVariable) {
                        $candidateResponseElt = $dom->createElementNS(self::QTI_NS, 'candidateResponse');
                        $candidateResponseElt->appendChild($valueElt);
                        $itemVariableElt->appendChild($candidateResponseElt);
                    } else {
                        $itemVariableElt->appendChild($valueElt);
                    }

                    $itemResultElt->appendChild($itemVariableElt);
                }

                $assessmentResultElt->appendChild($itemResultElt);
            }

            \common_Logger::d($dom->saveXML());

            // Do whatever you want with the DOMDocument representing the results
            // for this delivery execution e.g. import the root node in your XML response...

            return $dom->saveXML();
        }

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


}
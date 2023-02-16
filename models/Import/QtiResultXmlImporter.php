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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\Import;

use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\OntologyDeliveryExecution;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Parser\QtiResultParser;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentTest;
use qtism\data\QtiComponentCollection;
use taoQtiTest_models_classes_QtiTestService;
use taoResultServer_models_classes_WritableResultStorage;

class QtiResultXmlImporter extends ConfigurableService
{
    use OntologyAwareTrait;

    public function importByResultInput(ImportResultInput $input)
    {
        $xml = $this->createXml($input);

        $this->importQtiResultXml($input->getDeliveryExecutionId(), $xml);
    }

    public function importQtiResultXml(
        string $deliveryExecutionId,
        string $xmlContent
    ): void {
        $parser = $this->getQtiResultParser();
        $resultServerService = $this->getResultServerService();
        $resultMapper = $parser->parse($xmlContent);
        $deliveryExecution = $this->getClass($deliveryExecutionId);
        $delivery = $deliveryExecution->getProperty(OntologyDeliveryExecution::PROPERTY_SUBJECT);
        $resultStorage = $resultServerService->getResultStorage($delivery);
        $deliveryId = $resultStorage->getDelivery($deliveryExecutionId);

        $test = $this->getResource($deliveryId)->getOnePropertyValue($this->getProperty(DeliveryAssemblyService::PROPERTY_ORIGIN));
        $items = $this->getItems($test);

        $this->storeTestVariables($resultStorage, $test->getUri(), $deliveryExecutionId, $resultMapper->getTestVariables());
        $this->storeItemVariables($resultStorage, $test->getUri(), $items, $deliveryExecutionId, $resultMapper->getItemVariables());
    }

    private function createXml(ImportResultInput $input): string
    {
        $itemResults = [];
        $timeStamp = '2023-02-26T14:13:54.548';

        foreach ($input->getOutcomes() as $itemId => $outcomes) {
            foreach ($outcomes as $outcomeId => $outcomeValue) {
                $itemResults[] = sprintf(
                    '<itemResult identifier="%s" datestamp="%s" sessionStatus="final">
                    <outcomeVariable identifier="%s" cardinality="single" baseType="float">
                        <value>%s</value>
                    </outcomeVariable>
                </itemResult>',
                    $itemId,
                    $timeStamp,
                    $outcomeId,
                    $outcomeValue
                );
            }
        }

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
                    <assessmentResult 
                        xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p1" 
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                        xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_result_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_result_v2p1.xsd">
                    <context/>
                    %s
                    </assessmentResult>',
            implode('', $itemResults)
        );
    }

    private function storeItemVariables(
        taoResultServer_models_classes_WritableResultStorage $resultStorage,
        string $testUri,
        QtiComponentCollection $items,
        string $deliveryExecutionId,
        array $itemVariablesByItemResult
    ): void {
        foreach ($itemVariablesByItemResult as $itemResultIdentifier => $itemVariables) {
            $item = $this->getItem($itemResultIdentifier, $items);

            if (null === $item) {
                continue;
            }

            $resultStorage->storeItemVariables(
                $deliveryExecutionId,
                $testUri,
                $item->getHref(),
                $itemVariables,
                sprintf('%s.%s.0', $deliveryExecutionId, $itemResultIdentifier)
            );
        }
    }

    private function storeTestVariables(
        taoResultServer_models_classes_WritableResultStorage $resultStorage,
        string $testId,
        string $deliveryExecutionId,
        array $itemVariablesByTestResult
    ): void {
        foreach ($itemVariablesByTestResult as $test => $testVariables) {
            $resultStorage->storeTestVariables($deliveryExecutionId, $testId, $testVariables, $deliveryExecutionId);
        }
    }

    private function getItem(string $identifier, QtiComponentCollection $items): ?AssessmentItemRef
    {
        /** @var AssessmentItemRef $item */
        foreach ($items as $item) {
            if ($item->getIdentifier() === $identifier) {
                return $item;
            }
        }

        return null;
    }

    private function getItems(core_kernel_classes_Resource $test): QtiComponentCollection
    {
        $testDoc = $this->getQtiTestService()->getDoc($test);
        /** @var AssessmentTest $test */
        $assessmentTest = $testDoc->getDocumentComponent();

        return $assessmentTest->getComponentsByClassName('assessmentItemRef');
    }

    private function getQtiResultParser(): QtiResultParser
    {
        return $this->getServiceManager()->get(QtiResultParser::class);
    }

    private function getResultServerService(): ResultServerService
    {
        return $this->getServiceManager()->get(ResultServerService::SERVICE_ID);
    }

    private function getQtiTestService(): taoQtiTest_models_classes_QtiTestService
    {
        return $this->getServiceManager()->get(taoQtiTest_models_classes_QtiTestService::class);
    }
}
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

namespace oat\taoResultServer\models\Import\Service;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\taoDelivery\model\execution\OntologyDeliveryExecution;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Exception\ImportResultException;
use oat\taoResultServer\models\Import\Factory\QtiResultXmlFactory;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use oat\taoResultServer\models\Parser\QtiResultParser;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentTest;
use qtism\data\QtiComponentCollection;
use taoQtiTest_models_classes_QtiTestService;
use taoResultServer_models_classes_WritableResultStorage;

class QtiResultXmlImporter
{
    private Ontology $ontology;
    private ResultServerService $resultServerService;
    private QtiResultXmlFactory $qtiResultXmlFactory;
    private QtiResultParser $qtiResultParser;
    private taoQtiTest_models_classes_QtiTestService $qtiTestService;

    public function __construct(
        Ontology $ontology,
        ResultServerService $resultServerService,
        QtiResultXmlFactory $qtiResultXmlFactory,
        QtiResultParser $qtiResultParser,
        taoQtiTest_models_classes_QtiTestService $qtiTestService
    ) {
        $this->ontology = $ontology;
        $this->resultServerService = $resultServerService;
        $this->qtiResultXmlFactory = $qtiResultXmlFactory;
        $this->qtiResultParser = $qtiResultParser;
        $this->qtiTestService = $qtiTestService;
    }

    /**
     * @param ImportResultInput $input
     * @return void
     * @throws ImportResultException
     */
    public function importByResultInput(ImportResultInput $input)
    {
        $this->importQtiResultXml(
            $input->getDeliveryExecutionId(),
            $this->qtiResultXmlFactory->createByImportResult($input)
        );
    }

    public function importQtiResultXml(
        string $deliveryExecutionId,
        string $xmlContent
    ): void {
        $resultMapper = $this->qtiResultParser->parse($xmlContent);
        $deliveryExecution = $this->ontology->getClass($deliveryExecutionId);
        $delivery = $deliveryExecution->getProperty(OntologyDeliveryExecution::PROPERTY_SUBJECT);
        $resultStorage = $this->resultServerService->getResultStorage($delivery);
        $deliveryId = $resultStorage->getDelivery($deliveryExecutionId);

        $test = $this->ontology->getResource($deliveryId)
            ->getOnePropertyValue($this->ontology->getProperty(DeliveryAssemblyService::PROPERTY_ORIGIN));

        $this->storeTestVariables(
            $resultStorage,
            $test->getUri(),
            $deliveryExecutionId,
            $resultMapper->getTestVariables()
        );
        $this->storeItemVariables(
            $resultStorage,
            $test->getUri(),
            $this->getItems($test),
            $deliveryExecutionId,
            $resultMapper->getItemVariables()
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
        $testDoc = $this->qtiTestService->getDoc($test);
        /** @var AssessmentTest $test */
        $assessmentTest = $testDoc->getDocumentComponent();

        return $assessmentTest->getComponentsByClassName('assessmentItemRef');
    }
}
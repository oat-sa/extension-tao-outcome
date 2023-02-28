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

namespace oat\taoResultServer\test\Unit\models\Import\Service;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Factory\QtiResultXmlFactory;
use oat\taoResultServer\models\Import\Service\QtiResultXmlImporter;
use oat\taoResultServer\models\Mapper\ResultMapper;
use oat\taoResultServer\models\Parser\QtiResultParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\QtiComponentCollection;
use qtism\data\storage\xml\XmlDocument;
use taoQtiTest_models_classes_QtiTestService;
use taoResultServer_models_classes_OutcomeVariable;
use taoResultServer_models_classes_WritableResultStorage;

class QtiResultXmlImporterTest extends TestCase
{
    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ResultServerService|MockObject */
    private $resultServerService;

    /** @var QtiResultXmlFactory|MockObject */
    private $qtiResultXmlFactory;

    /** @var QtiResultParser|MockObject */
    private $qtiResultParser;

    /** @var taoQtiTest_models_classes_QtiTestService|MockObject */
    private $qtiTestService;
    /** @var DeliveryExecutionService|MockObject */
    private $deliveryExecutionService;
    private QtiResultXmlImporter $sut;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resultServerService = $this->createMock(ResultServerService::class);
        $this->qtiResultXmlFactory = $this->createMock(QtiResultXmlFactory::class);
        $this->qtiResultParser = $this->createMock(QtiResultParser::class);
        $this->qtiTestService = $this->createMock(taoQtiTest_models_classes_QtiTestService::class);
        $this->deliveryExecutionService = $this->createMock(DeliveryExecutionService::class);

        $this->sut = new QtiResultXmlImporter(
            $this->ontology,
            $this->resultServerService,
            $this->qtiResultXmlFactory,
            $this->qtiResultParser,
            $this->qtiTestService,
            $this->deliveryExecutionService
        );
    }

    public function testImportQtiResultXml(): void
    {
        $xmlContent = $this->getXmlContent();

        $testOutcomeVariable = new taoResultServer_models_classes_OutcomeVariable();
        $testOutcomeVariable->setValue('MA==');
        $testOutcomeVariable->setIdentifier('LtiOutcome');
        $testOutcomeVariable->setCardinality('single');
        $testOutcomeVariable->setBaseType('float');
        $testOutcomeVariable->setEpoch('0.00000100 1581515827');

        $itemOutcomeVariable = new taoResultServer_models_classes_OutcomeVariable();
        $itemOutcomeVariable->setValue('MA==');
        $itemOutcomeVariable->setIdentifier('SCORE');
        $itemOutcomeVariable->setCardinality('single');
        $itemOutcomeVariable->setBaseType('float');
        $itemOutcomeVariable->setEpoch('0.00000100 1581515827');

        $resultMapper = $this->getResultMapperMock($testOutcomeVariable, $itemOutcomeVariable);
        $deliveryExecution = $this->createMock(DeliveryExecution::class);
        $delivery = $this->createMock(core_kernel_classes_Property::class);
        $resultStorage = $this->createMock(taoResultServer_models_classes_WritableResultStorage::class);
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $test = $this->createMock(core_kernel_classes_Resource::class);
        $property = $this->createMock(core_kernel_classes_Property::class);
        $item = $this->createMock(ExtendedAssessmentItemRef::class);
        $qtiComponentCollection = new QtiComponentCollection([$item]);
        $assessmentTest = $this->createMock(AssessmentTest::class);
        $xmlDocument = $this->createMock(XmlDocument::class);

        $this->deliveryExecutionService
            ->method('getDeliveryExecution')
            ->with('deliveryExecutionId')
            ->willReturn($deliveryExecution);

        $this->resultServerService
            ->expects($this->once())
            ->method('getResultStorage')
            ->willReturn($resultStorage);

        $this->qtiResultParser
            ->expects($this->once())
            ->method('parse')
            ->with($xmlContent)
            ->willReturn($resultMapper);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('deliveryId')
            ->willReturn($resource);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with(DeliveryAssemblyService::PROPERTY_ORIGIN)
            ->willReturn($property);

        $this->qtiTestService
            ->expects($this->once())
            ->method('getDoc')
            ->with($test)
            ->willReturn($xmlDocument);

        $delivery->expects($this->once())
            ->method('getUri')
            ->willReturn('deliveryId');

        $deliveryExecution
            ->expects($this->once())
            ->method('getDelivery')
            ->willReturn($delivery);

        $test->expects($this->any())
            ->method('getUri')
            ->willReturn('testId');

        $resource->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($test);

        $item->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('itemIdentifier');

        $item->expects($this->once())
            ->method('getHref')
            ->willReturn('itemHref');

        $assessmentTest->expects($this->once())
            ->method('getComponentsByClassName')
            ->with('assessmentItemRef')
            ->willReturn($qtiComponentCollection);

        $xmlDocument->expects($this->once())
            ->method('getDocumentComponent')
            ->willReturn($assessmentTest);

        $resultStorage
            ->expects($this->once())
            ->method('storeTestVariables')
            ->with(
                'deliveryExecutionId',
                'testId',
                [$testOutcomeVariable],
                'deliveryExecutionId'
            );

        $resultStorage
            ->expects($this->once())
            ->method('storeItemVariables')
            ->with(
                'deliveryExecutionId',
                'testId',
                'itemHref',
                [$itemOutcomeVariable],
                'deliveryExecutionId.itemIdentifier.0'
            );

        $this->sut->importQtiResultXml('deliveryExecutionId', $this->getXmlContent());
    }

    private function getResultMapperMock($testOutcomeVariable, $itemOutcomeVariable): ResultMapper
    {
        $resultMapperMock = $this->createMock(ResultMapper::class);

        $resultMapperMock
            ->expects($this->once())
            ->method('getTestVariables')
            ->willReturn(
                [
                    [$testOutcomeVariable],
                ]
            );

        $resultMapperMock
            ->expects($this->once())
            ->method('getItemVariables')
            ->willReturn(
                [
                    'itemIdentifier' => [$itemOutcomeVariable],
                ]
            );

        return $resultMapperMock;
    }

    private function getXmlContent(): string
    {
        // phpcs:disable
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<assessmentResult 
    xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p1" 
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_result_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_result_v2p1.xsd"
></assessmentResult>
XML;
        // phpcs:enable
    }
}

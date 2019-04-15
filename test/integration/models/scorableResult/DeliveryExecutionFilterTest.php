<?php

namespace oat\taoResultServer\test\integration\models\scorableResult;

use oat\generis\test\TestCase;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\File;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\scorableResult\DeliveryExecutionFilter;
use Prophecy\Argument;

class DeliveryExecutionFilterTest extends TestCase
{
    public function testScorableOptionNotSet()
    {
        $deliveryExecutions = [
            [
                'deliveryIdentifier' => 'deliveryIdentifier-fixture1',
                'deliveryResultIdentifier' => 'deliveryExecutionIdentifier-fixture1',
            ],[
                'deliveryIdentifier' => 'deliveryIdentifier-fixture2',
                'deliveryResultIdentifier' => 'deliveryExecutionIdentifier-fixture2',
            ]
        ];
        $options = ['onlyScorable' => false];
        $results = $this->getDeliveryExecutionFilter()->filter($deliveryExecutions, $options);
        $this->assertCount(2, $results);
    }

    public function testWithNoScorable()
    {
        $deliveryExecutions = [
            [
                'deliveryIdentifier' => 'deliveryIdentifier-fixture1',
                'deliveryResultIdentifier' => 'deliveryExecutionIdentifier-fixture1',
            ],[
                'deliveryIdentifier' => 'deliveryIdentifier-fixture2',
                'deliveryResultIdentifier' => 'deliveryExecutionIdentifier-fixture2',
            ]
        ];
        $options = ['onlyScorable' => true];
        $results = $this->getDeliveryExecutionFilterWithNoScorable()->filter($deliveryExecutions, $options);
        $this->assertEmpty($results);
    }


    public function testItemDoesNotExist()
    {
        $deliveryExecutions = [
            [
                'deliveryIdentifier' => 'deliveryIdentifier-fixture',
                'deliveryResultIdentifier' => 'deliveryExecutionIdentifier-fixture',
            ]
        ];
        $options = ['onlyScorable' => true];
        $results = $this->getDeliveryExecutionFilter(false)->filter($deliveryExecutions, $options);
        $this->assertEmpty($results);
    }

    protected function getInMemoryCache()
    {
        $cache = new \common_cache_KeyValueCache();
        $driver = (new \common_persistence_InMemoryKvDriver())->connect('id', []);
        $property = new \ReflectionProperty(\common_cache_KeyValueCache::class, 'persistence');
        $property->setAccessible(true);
        $property->setValue($cache, $driver);
        return $cache;
    }

    protected function getDeliveryExecutionFilter($itemExists = true)
    {
        $resultVariable1 = new \stdClass();
        $resultVariable1->item = 'item';

        $resultVariable2 = clone $resultVariable1;
        $resultVariable3 = clone $resultVariable1;

        $resultVariables = [
            $resultVariable1, $resultVariable2, $resultVariable3
        ];

        $resultStorage = $this->prophesize(ResultManagement::class);
        $resultStorage->getRelatedItemCallIds(Argument::any())->willReturn(['callId']);
        $resultStorage->getVariables(Argument::any())->willReturn([$resultVariables]);

        $resultServer = $this->prophesize(ResultServerService::class);
        $resultServer->getResultStorage(Argument::any())->willReturn($resultStorage->reveal());

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ResultServerService::SERVICE_ID => $resultServer,
            \common_cache_Cache::SERVICE_ID => $this->getInMemoryCache()
        ]);

        $resourceProphecy = $this->prophesize(\core_kernel_classes_Resource::class);
        $resourceProphecy->exists()->willReturn($itemExists);

        $model = $this->prophesize(\core_kernel_persistence_smoothsql_SmoothModel::class);
        $model->getResource(Argument::any())->willReturn($resourceProphecy->reveal());

        $file = $this->prophesize(File::class);
        $file->read()->willReturn($this->getItemContentWithExtendedText());

        $directory = $this->prophesize(Directory::class);
        $directory->getFile(Argument::any())->willReturn($file->reveal());

        $itemService = $this->prophesize(\taoItems_models_classes_ItemsService::class);
        $itemService->getItemDirectory(Argument::any())->willReturn($directory->reveal());

        $service = new DeliveryExecutionFilterMock();
        $service
            ->setMock($itemService->reveal())
            ->setServiceLocator($serviceLocatorMock)
            ->setModel($model->reveal())
        ;

        return $service;
    }

    protected function getDeliveryExecutionFilterWithNoScorable()
    {
        $resultVariable1 = new \stdClass();
        $resultVariable1->item = 'item';

        $resultVariable2 = clone $resultVariable1;
        $resultVariable3 = clone $resultVariable1;

        $resultVariables = [
            $resultVariable1, $resultVariable2, $resultVariable3
        ];

        $resultStorage = $this->prophesize(ResultManagement::class);
        $resultStorage->getRelatedItemCallIds(Argument::any())->willReturn(['callId']);
        $resultStorage->getVariables(Argument::any())->willReturn([$resultVariables]);

        $resultServer = $this->prophesize(ResultServerService::class);
        $resultServer->getResultStorage(Argument::any())->willReturn($resultStorage->reveal());

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ResultServerService::SERVICE_ID => $resultServer,
            \common_cache_Cache::SERVICE_ID => $this->getInMemoryCache()
        ]);

        $resourceProphecy = $this->prophesize(\core_kernel_classes_Resource::class);
        $resourceProphecy->exists()->willReturn(true);

        $model = $this->prophesize(\core_kernel_persistence_smoothsql_SmoothModel::class);
        $model->getResource(Argument::any())->willReturn($resourceProphecy->reveal());

        $file = $this->prophesize(File::class);
        $file->read()->willReturn($this->getItemContentWithoutExtendedText());

        $directory = $this->prophesize(Directory::class);
        $directory->getFile(Argument::any())->willReturn($file->reveal());

        $itemService = $this->prophesize(\taoItems_models_classes_ItemsService::class);
        $itemService->getItemDirectory(Argument::any())->willReturn($directory->reveal());

        $service = new DeliveryExecutionFilterMock();
        $service
            ->setMock($itemService->reveal())
            ->setServiceLocator($serviceLocatorMock)
            ->setModel($model->reveal())
        ;

        return $service;
    }

    protected function getItemContentWithExtendedText()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
                <assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p2" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p2 http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_v2p2.xsd" identifier="i1553863910296214" title="Item 2" label="Item 2" xml:lang="en-US" adaptive="false" timeDependent="false" toolName="TAO" toolVersion="3.3.0-sprint99">
                  <responseDeclaration identifier="RESPONSE" cardinality="single" baseType="string"/>
                  <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float" normalMaximum="0"/>
                  <outcomeDeclaration identifier="MAXSCORE" cardinality="single" baseType="float">
                    <defaultValue>
                      <value>5</value>
                    </defaultValue>
                  </outcomeDeclaration>
                  <outcomeDeclaration identifier="GRAMMAR" cardinality="single" baseType="float" interpretation="Evaluate grammar" normalMaximum="5" normalMinimum="0"/>
                  <stylesheet href="style/custom/tao-user-styles.css" type="text/css" media="all" title=""/>
                  <itemBody>
                    <div class="grid-row">
                      <div class="col-12">
                        <extendedTextInteraction responseIdentifier="RESPONSE" base="10" minStrings="0" format="plain">
                          <prompt>prompt</prompt>
                        </extendedTextInteraction>
                      </div>
                    </div>
                  </itemBody>
                  <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
                </assessmentItem>
        ';
    }

    protected function getItemContentWithoutExtendedText()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
                <assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p2" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p2 http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_v2p2.xsd" identifier="i155385736540572" title="Item 1" label="Item 1" xml:lang="en-US" adaptive="false" timeDependent="false" toolName="TAO" toolVersion="3.3.0-sprint99">
                  <responseDeclaration identifier="RESPONSE" cardinality="multiple" baseType="directedPair"/>
                  <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float" normalMaximum="0"/>
                  <outcomeDeclaration identifier="MAXSCORE" cardinality="single" baseType="float">
                    <defaultValue>
                      <value>0</value>
                    </defaultValue>
                  </outcomeDeclaration>
                  <stylesheet href="style/custom/tao-user-styles.css" type="text/css" media="all" title=""/>
                  <itemBody>
                    <div class="grid-row">
                      <div class="col-12">
                        <gapMatchInteraction responseIdentifier="RESPONSE" shuffle="false">
                          <gapText identifier="choice_1" fixed="false" matchMax="1" matchMin="0">choice #1</gapText>
                          <p>Lorem ipsum dolor sit amet, consectetur adipisicing ...</p>
                        </gapMatchInteraction>
                      </div>
                    </div>
                  </itemBody>
                  <responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
                </assessmentItem>
        ';
    }

}

class DeliveryExecutionFilterMock extends DeliveryExecutionFilter
{
    private $mock;

    public function setMock($mock)
    {
        $this->mock = $mock;
        return $this;
    }

    protected function getItemService()
    {
        return $this->mock;
    }

}
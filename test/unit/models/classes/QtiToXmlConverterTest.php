<?php

namespace oat\taoResultServer\models\classes;

use oat\generis\test\TestCase;

class QtiToXmlConverterTest extends TestCase
{
    /** @var QtiToXmlConverter */
    private $sut;

    public function setUp()
    {
        $this->sut = new QtiToXmlConverter();
    }

    /**
     * @dataProvider expected
     */
    public function testConvertToXml($testTaker, $testResults, $itemResults, $expected)
    {
        $this->assertEquals($expected, $this->sut->convertToXml($testTaker, $testResults, $itemResults));
    }

    public function expected()
    {
        $testTaker = 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549443724964951';

        $outcomeVariable = new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable');
        $responseVariable = new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable');

        $testResults = [
            'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i15495620683550142' => [
                [
                    'value' => '0',
                    'identifier' => 'LtiOutcome',
                    'type' => $outcomeVariable,
                    'epoch' => '0.46565800 1549562103',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
            ],
        ];

        $itemResults = [
            'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i15495620683550142.item-1.0' => [
                [
                    'value' => '1',
                    'identifier' => 'numAttempts',
                    'type' => $responseVariable,
                    'epoch' => '0.54604200 1549562079',
                    'cardinality' => 'single',
                    'basetype' => 'integer',
                ],
                [
                    'value' => 'PT5.419702S',
                    'identifier' => 'duration',
                    'type' => $responseVariable,
                    'epoch' => '0.54604900 1549562079',
                    'cardinality' => 'single',
                    'basetype' => 'duration',
                ],
                [
                    'value' => 'completed',
                    'identifier' => 'completionStatus',
                    'type' => $outcomeVariable,
                    'epoch' => '0.54605100 1549562079',
                    'cardinality' => 'single',
                    'basetype' => 'identifier',
                ],
                [
                    'value' => '1',
                    'identifier' => 'SCORE',
                    'type' => $outcomeVariable,
                    'epoch' => '0.54605300 1549562079',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
                [
                    'value' => '1',
                    'identifier' => 'MAXSCORE',
                    'type' => $outcomeVariable,
                    'epoch' => '0.54605500 1549562079',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
                [
                    'value' => '[\'choice_1\']',
                    'identifier' => 'RESPONSE',
                    'type' => $responseVariable,
                    'epoch' => '0.54605600 1549562079',
                    'cardinality' => 'multiple',
                    'basetype' => 'identifier',
                ],
                [
                    'value' => '2',
                    'identifier' => 'numAttempts',
                    'type' => $responseVariable,
                    'epoch' => '0.31206000 1549562096',
                    'cardinality' => 'single',
                    'basetype' => 'integer',
                ],
                [
                    'value' => 'PT11.151032S',
                    'identifier' => 'duration',
                    'type' => $responseVariable,
                    'epoch' => '0.31207200 1549562096',
                    'cardinality' => 'single',
                    'basetype' => 'duration',
                ],
                [
                    'value' => 'completed',
                    'identifier' => 'completionStatus',
                    'type' => $outcomeVariable,
                    'epoch' => '0.31207500 1549562096',
                    'cardinality' => 'single',
                    'basetype' => 'identifier',
                ],
                [
                    'value' => '0',
                    'identifier' => 'SCORE',
                    'type' => $outcomeVariable,
                    'epoch' => '0.31207800 1549562096',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
                [
                    'value' => '1',
                    'identifier' => 'MAXSCORE',
                    'type' => $outcomeVariable,
                    'epoch' => '0.31208000 1549562096',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
                [
                    'value' => '[\'choice_2\']',
                    'identifier' => 'RESPONSE',
                    'type' => $responseVariable,
                    'epoch' => '0.31208200 1549562096',
                    'cardinality' => 'multiple',
                    'basetype' => 'identifier',
                ],
            ],
            'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i15495620683550142.item-2.0' => [
                [
                    'value' => '1',
                    'identifier' => 'numAttempts',
                    'type' => $responseVariable,
                    'epoch' => '0.96892200 1549562086',
                    'cardinality' => 'single',
                    'basetype' => 'integer',
                ],
                [
                    'value' => 'PT7.363893S',
                    'identifier' => 'duration',
                    'type' => $responseVariable,
                    'epoch' => '0.96895000 1549562086',
                    'cardinality' => 'single',
                    'basetype' => 'duration',
                ],
                [
                    'value' => 'completed',
                    'identifier' => 'completionStatus',
                    'type' => $outcomeVariable,
                    'epoch' => '0.96895500 1549562086',
                    'cardinality' => 'single',
                    'basetype' => 'identifier',
                ],
                [
                    'value' => '1',
                    'identifier' => 'SCORE',
                    'type' => $outcomeVariable,
                    'epoch' => '0.96895800 1549562086',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
                [
                    'value' => '1',
                    'identifier' => 'MAXSCORE',
                    'type' => $outcomeVariable,
                    'epoch' => '0.96896100 1549562086',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
                [
                    'value' => 'feedbackModal_1',
                    'identifier' => 'FEEDBACK_1',
                    'type' => $outcomeVariable,
                    'epoch' => '0.96896400 1549562086',
                    'cardinality' => 'single',
                    'basetype' => 'identifier',
                ],
                [
                    'value' => 'choice_1',
                    'identifier' => 'RESPONSE',
                    'type' => $responseVariable,
                    'epoch' => '0.96896700 1549562086',
                    'cardinality' => 'single',
                    'basetype' => 'identifier',
                ],
                [
                    'value' => '2',
                    'identifier' => 'numAttempts',
                    'type' => $responseVariable,
                    'epoch' => '0.35095700 1549562100',
                    'cardinality' => 'single',
                    'basetype' => 'integer',
                ],
                [
                    'value' => 'PT11.362917S',
                    'identifier' => 'duration',
                    'type' => $responseVariable,
                    'epoch' => '0.35098600 1549562100',
                    'cardinality' => 'single',
                    'basetype' => 'duration',
                ],
                [
                    'value' => 'completed',
                    'identifier' => 'completionStatus',
                    'type' => $outcomeVariable,
                    'epoch' => '0.35099100 1549562100',
                    'cardinality' => 'single',
                    'basetype' => 'identifier',
                ],
                [
                    'value' => '0',
                    'identifier' => 'SCORE',
                    'type' => $outcomeVariable,
                    'epoch' => '0.35099400 1549562100',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
                [
                    'value' => '1',
                    'identifier' => 'MAXSCORE',
                    'type' => $outcomeVariable,
                    'epoch' => '0.35099800 1549562100',
                    'cardinality' => 'single',
                    'basetype' => 'float',
                ],
                [
                    'value' => 'feedbackModal_2',
                    'identifier' => 'FEEDBACK_1',
                    'type' => $outcomeVariable,
                    'epoch' => '0.35100000 1549562100',
                    'cardinality' => 'single',
                    'basetype' => 'identifier',
                ],
                [
                    'value' => 'choice_3',
                    'identifier' => 'RESPONSE',
                    'type' => $responseVariable,
                    'epoch' => '0.35100300 1549562100',
                    'cardinality' => 'single',
                    'basetype' => 'identifier',
                ],
            ],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<assessmentResult xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p1">
  <context sourcedId="i1549443724964951"/>
  <testResult identifier="rdf#i15495620683550142" datestamp="2019-02-07T17:55:03.466">
    <outcomeVariable identifier="LtiOutcome" cardinality="single" baseType="float">
      <value><![CDATA[0]]></value>
    </outcomeVariable>
  </testResult>
  <itemResult identifier="item-1" datestamp="2019-02-07T17:54:39.546" sessionStatus="final">
    <responseVariable identifier="numAttempts" cardinality="single" baseType="integer">
      <candidateResponse>
        <value><![CDATA[1]]></value>
      </candidateResponse>
    </responseVariable>
    <responseVariable identifier="duration" cardinality="single" baseType="duration">
      <candidateResponse>
        <value><![CDATA[PT5.419702S]]></value>
      </candidateResponse>
    </responseVariable>
    <outcomeVariable identifier="completionStatus" cardinality="single" baseType="identifier">
      <value><![CDATA[completed]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="SCORE" cardinality="single" baseType="float">
      <value><![CDATA[1]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="MAXSCORE" cardinality="single" baseType="float">
      <value><![CDATA[1]]></value>
    </outcomeVariable>
    <responseVariable identifier="RESPONSE" cardinality="multiple" baseType="identifier">
      <candidateResponse>
        <value><![CDATA[\'choice_1\']]></value>
      </candidateResponse>
    </responseVariable>
    <responseVariable identifier="numAttempts" cardinality="single" baseType="integer">
      <candidateResponse>
        <value><![CDATA[2]]></value>
      </candidateResponse>
    </responseVariable>
    <responseVariable identifier="duration" cardinality="single" baseType="duration">
      <candidateResponse>
        <value><![CDATA[PT11.151032S]]></value>
      </candidateResponse>
    </responseVariable>
    <outcomeVariable identifier="completionStatus" cardinality="single" baseType="identifier">
      <value><![CDATA[completed]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="SCORE" cardinality="single" baseType="float">
      <value><![CDATA[0]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="MAXSCORE" cardinality="single" baseType="float">
      <value><![CDATA[1]]></value>
    </outcomeVariable>
    <responseVariable identifier="RESPONSE" cardinality="multiple" baseType="identifier">
      <candidateResponse>
        <value><![CDATA[\'choice_2\']]></value>
      </candidateResponse>
    </responseVariable>
  </itemResult>
  <itemResult identifier="item-2" datestamp="2019-02-07T17:54:46.969" sessionStatus="final">
    <responseVariable identifier="numAttempts" cardinality="single" baseType="integer">
      <candidateResponse>
        <value><![CDATA[1]]></value>
      </candidateResponse>
    </responseVariable>
    <responseVariable identifier="duration" cardinality="single" baseType="duration">
      <candidateResponse>
        <value><![CDATA[PT7.363893S]]></value>
      </candidateResponse>
    </responseVariable>
    <outcomeVariable identifier="completionStatus" cardinality="single" baseType="identifier">
      <value><![CDATA[completed]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="SCORE" cardinality="single" baseType="float">
      <value><![CDATA[1]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="MAXSCORE" cardinality="single" baseType="float">
      <value><![CDATA[1]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="FEEDBACK_1" cardinality="single" baseType="identifier">
      <value><![CDATA[feedbackModal_1]]></value>
    </outcomeVariable>
    <responseVariable identifier="RESPONSE" cardinality="single" baseType="identifier">
      <candidateResponse>
        <value><![CDATA[choice_1]]></value>
      </candidateResponse>
    </responseVariable>
    <responseVariable identifier="numAttempts" cardinality="single" baseType="integer">
      <candidateResponse>
        <value><![CDATA[2]]></value>
      </candidateResponse>
    </responseVariable>
    <responseVariable identifier="duration" cardinality="single" baseType="duration">
      <candidateResponse>
        <value><![CDATA[PT11.362917S]]></value>
      </candidateResponse>
    </responseVariable>
    <outcomeVariable identifier="completionStatus" cardinality="single" baseType="identifier">
      <value><![CDATA[completed]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="SCORE" cardinality="single" baseType="float">
      <value><![CDATA[0]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="MAXSCORE" cardinality="single" baseType="float">
      <value><![CDATA[1]]></value>
    </outcomeVariable>
    <outcomeVariable identifier="FEEDBACK_1" cardinality="single" baseType="identifier">
      <value><![CDATA[feedbackModal_2]]></value>
    </outcomeVariable>
    <responseVariable identifier="RESPONSE" cardinality="single" baseType="identifier">
      <candidateResponse>
        <value><![CDATA[choice_3]]></value>
      </candidateResponse>
    </responseVariable>
  </itemResult>
</assessmentResult>
';

        return [[$testTaker, $testResults, $itemResults, $xml]];
    }
}

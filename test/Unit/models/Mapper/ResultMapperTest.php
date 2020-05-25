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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoResultServer\test\Unit\models\nit\models\Mapper;

use oat\dtms\DateTime;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\taoResultServer\models\Mapper\ResultMapper;
use Psr\Log\NullLogger;
use qtism\data\results\AssessmentResult;
use qtism\data\storage\xml\XmlResultDocument;
use taoResultServer_models_classes_ResponseVariable;

class ResultMapperTest extends TestCase
{
    public function load($file = null)
    {
        if (null === $file) {
            $file = __DIR__ . '/../../../resources/result/simple-assessment-result.xml';
        }
        $doc = new XmlResultDocument();
        $doc->loadFromString(file_get_contents($file));
        $resultMapper = new ResultMapper();
        $resultMapper->setServiceLocator($this->getServiceLocatorMock([LoggerService::SERVICE_ID => new NullLogger()]));
        return $resultMapper->loadSource($doc->getDocumentComponent());
    }

    public function testLoad()
    {
        $mapper = $this->load();
        $reflectionProperty = new \ReflectionProperty(ResultMapper::class, 'assessmentResult');
        $reflectionProperty->setAccessible(true);
        $this->assertEquals(AssessmentResult::class, get_class($reflectionProperty->getValue($mapper)));
    }

    public function testGetContext()
    {
        $expected = [
            'sourcedId' => 'fixture-sourcedId',
            'sessionIdentifiers' => [
                'sessionIdentifier1-id' => 'http://sessionIdentifier1-sourceID',
                'sessionIdentifier2-id' => 'http://sessionIdentifier2-sourceID',
            ]
        ];

        $this->assertSame($expected, $this->load()->getContext());
    }

    public function testGetEmptyContext()
    {
        $resultWithEmptyContext = __DIR__ . '/../../../resources/result/qti-result-de.xml';
        $expected = [
            'sourcedId' => '',
            'sessionIdentifiers' => []
        ];

        $this->assertSame($expected, $this->load($resultWithEmptyContext)->getContext());
    }

    public function testGetContextWithoutLoad()
    {
        $this->expectException(\LogicException::class);
        (new ResultMapper())->getContext();
    }

    public function testGetTestVariables()
    {
        $variablesByTestResult = $this->load()->getTestVariables();

        $this->assertCount(1, $variablesByTestResult);
        $this->assertEquals('fixture-test-identifier', key($variablesByTestResult));

        $variables = $variablesByTestResult['fixture-test-identifier'];
        /** @var taoResultServer_models_classes_ResponseVariable $variable */
        $variable = reset($variables);
        $this->assertInstanceOf(taoResultServer_models_classes_ResponseVariable::class, $variable);
        $this->assertEquals('response-identifier', $variable->getIdentifier());
        $this->assertEquals('fixture-test-value3;fixture-test-value4;fixture-test-value5', $variable->getCandidateResponse());
        $this->assertEquals('fixture-test-value3;fixture-test-value4;fixture-test-value5', $variable->getValue());
        $this->assertEquals('fixture-test-value1;fixture-test-value2', $variable->getCorrectResponse());
        $this->assertEquals('single', $variable->getCardinality());
        $this->assertNull($variable->getBaseType());
        $epochDateTime = (new DateTime())->setTimestamp(explode(' ', $variable->getEpoch())[1]);
        $this->assertSame('2018-06-27T09:41:45', $epochDateTime->format('Y-m-d\TH:i:s'));
    }

    public function testGetTestWithEmptyTestResult()
    {
        $resultWithEmptyTestResult = __DIR__ . '/../../../resources/result/qti-result-de.xml';
        $this->assertEmpty($this->load($resultWithEmptyTestResult)->getTestVariables());
    }

    public function testGetTestVariablesWithTemplateVariables()
    {
        $resultWithTemplateVariables = __DIR__ . '/../../../resources/result/result-with-template-variable.xml';
        $this->expectException(\common_exception_NotImplemented::class);
        $this->load($resultWithTemplateVariables)->getTestVariables();
    }

    public function testGetItemVariables()
    {
        $variablesByItemResult = $this->load()->getItemVariables();
        $this->assertCount(3, $variablesByItemResult);

        $this->assertArrayHasKey('fixture-identifier-itemResult1', $variablesByItemResult);
        $this->assertArrayHasKey('fixture-identifier-itemResult2', $variablesByItemResult);

        /** @var taoResultServer_models_classes_ResponseVariable $variable0 */
        $variable0 = $variablesByItemResult['fixture-identifier-itemResult1'][0];
        $this->assertInstanceOf(taoResultServer_models_classes_ResponseVariable::class, $variable0);
        $this->assertEquals('fixture-identifier1', $variable0->getIdentifier());
        $this->assertEquals('fixture-value8;fixture-value9;fixture-value10', $variable0->getValue());
        $this->assertEquals('fixture-value8;fixture-value9;fixture-value10', $variable0->getCandidateResponse());
        $this->assertEquals('fixture-value6;fixture-value7', $variable0->getCorrectResponse());
        $this->assertEquals('single', $variable0->getCardinality());
        $this->assertEquals('string', $variable0->getBaseType());
        $epochDateTime = (new DateTime())->setTimestamp(explode(' ', $variable0->getEpoch())[1]);
        $this->assertSame('2018-06-27T09:41:45', $epochDateTime->format('Y-m-d\TH:i:s'));

        /** @var \taoResultServer_models_classes_OutcomeVariable $variable1 */
        $variable1 = $variablesByItemResult['fixture-identifier-itemResult1'][1];
        $this->assertInstanceOf(\taoResultServer_models_classes_OutcomeVariable::class, $variable1);
        $this->assertEquals('fixture-identifier2', $variable1->getIdentifier());
        $this->assertEquals('fixture-value11', $variable1->getValue());
        $this->assertEquals('2', $variable1->getNormalMinimum());
        $this->assertEquals('3', $variable1->getNormalMaximum());
        $this->assertEquals('single', $variable1->getCardinality());
        $this->assertEquals('string', $variable1->getBaseType());
        $epochDateTime = (new DateTime())->setTimestamp(explode(' ', $variable1->getEpoch())[1]);
        $this->assertSame('2018-06-27T09:41:45', $epochDateTime->format('Y-m-d\TH:i:s'));

        /** @var taoResultServer_models_classes_ResponseVariable $variable2 */
        $variable2 = $variablesByItemResult['fixture-identifier-itemResult2'][0];
        $this->assertInstanceOf(taoResultServer_models_classes_ResponseVariable::class, $variable2);
        $this->assertEquals('fixture-identifier3', $variable2->getIdentifier());
        $this->assertEquals('fixture-value16', $variable2->getValue());
        $this->assertEquals('fixture-value16', $variable2->getCandidateResponse());
        $this->assertEquals('fixture-value14', $variable2->getCorrectResponse());
        $this->assertEquals('single', $variable2->getCardinality());
        $this->assertEquals('string', $variable2->getBaseType());
        $epochDateTime = (new DateTime())->setTimestamp(explode(' ', $variable2->getEpoch())[1]);
        $this->assertSame('2018-06-27T09:41:45', $epochDateTime->format('Y-m-d\TH:i:s'));

        /** @var \taoResultServer_models_classes_OutcomeVariable $variable3 */
        $variable3 = $variablesByItemResult['fixture-identifier-itemResult2'][1];
        $this->assertInstanceOf(\taoResultServer_models_classes_OutcomeVariable::class, $variable3);
        $this->assertEquals('fixture-identifier4', $variable3->getIdentifier());
        $this->assertEquals('fixture-value19', $variable3->getValue());
        $this->assertEquals('4', $variable3->getNormalMinimum());
        $this->assertEquals('6', $variable3->getNormalMaximum());
        $this->assertEquals('single', $variable3->getCardinality());
        $this->assertEquals('string', $variable3->getBaseType());
        $epochDateTime = (new DateTime())->setTimestamp(explode(' ', $variable3->getEpoch())[1]);
        $this->assertSame('2018-06-27T09:41:45', $epochDateTime->format('Y-m-d\TH:i:s'));

        /** @var taoResultServer_models_classes_ResponseVariable $variable4 */
        $variable4 = $variablesByItemResult['fixture-identifier-itemResult3'][0];
        $this->assertInstanceOf(\taoResultServer_models_classes_ResponseVariable::class, $variable4);
        $this->assertEquals('fixture-identifier5', $variable4->getIdentifier());
        $this->assertEmpty($variable4->getValue());
        $this->assertEmpty($variable4->getCandidateResponse());
        $this->assertEquals('fixture-value20', $variable4->getCorrectResponse());
        $this->assertEquals('single', $variable4->getCardinality());
        $this->assertEquals('identifier', $variable4->getBaseType());
    }

    public function testGetItemVariablesWithTemplateVariables()
    {
        $resultWithTemplateVariables = __DIR__ . '/../../../resources/result/result-with-template-variable.xml';
        $this->expectException(\common_exception_NotImplemented::class);
        $this->load($resultWithTemplateVariables)->getItemVariables();
    }

    public function testGetTestVariablesWithoutLoad()
    {
        $this->expectException(\LogicException::class);
        (new ResultMapper())->getTestVariables();
    }
}

<?php

namespace oat\taoResultServer\models\classes;

use oat\generis\test\TestCase;

class CrudResultsServiceTest extends TestCase
{
    /**
     * @var CrudResultsService
     */
    private $sut;

    public function setUp()
    {
        $this->sut = new CrudResultsService();
    }

    /**
     * @dataProvider variablesToTest
     */
    public function testReadQtiResult_ByItem($resultIdentifier, $variables, $groupBy, $returnAttempts, $qtiResult)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResultManagement $resultStorage */
        $resultStorage = $this->getMockForAbstractClass(ResultManagement::class);

        $resultStorage->method('getRelatedItemCallIds')->willReturn([$resultIdentifier]);
        $resultStorage->method('getRelatedTestCallIds')->willReturn([$resultIdentifier]);
        $resultStorage->method('getVariables')->willReturn($variables);

        $this->assertEquals($qtiResult[$groupBy], $this->sut->readQtiResult($resultStorage, $resultIdentifier, $groupBy, $returnAttempts));
    }

    public function variablesToTest()
    {
        $deliveryResultIdentifier = 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i15495620683550142';
        $testValue = '0';
        $testId = 'LtiOutcome';
        $testType = 'float';
        $timestamp1 = '1549562079';
        $timestamp2 = '1549562096';
        $milliseconds1 = '0.54604200';
        $milliseconds2 = '0.54604900';
        $attemptId = 'numAttempts';
        $attemptType = 'integer';
        $attempt1 = 1;
        $attempt2 = 2;
        $durationId = 'duration';
        $durationType = 'duration';
        $duration1 = 'PT5.419702S';
        $duration2 = 'PT11.151032S';
        $cardinality = 'single';

        $variables = [
            'rds' => [
                198 => [
                    $this->buildVariable([
                        'uri' => 198,
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'candidateResponse' => $attempt1,
                        'identifier' => $attemptId,
                        'cardinality' => $cardinality,
                        'baseType' => $attemptType,
                        'epoch' => $milliseconds1 . ' ' . $timestamp1,
                    ]),
                ],
                199 => [
                    $this->buildVariable([
                        'uri' => 199,
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'candidateResponse' => $duration1,
                        'identifier' => $durationId,
                        'cardinality' => $cardinality,
                        'baseType' => $durationType,
                        'epoch' => $milliseconds1 . ' ' . $timestamp1,
                    ]),
                ],

                211 => [
                    $this->buildVariable([
                        'uri' => 211,
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'candidateResponse' => $attempt2,
                        'identifier' => $attemptId,
                        'cardinality' => $cardinality,
                        'baseType' => $attemptType,
                        'epoch' => $milliseconds2 . ' ' . $timestamp2,
                    ]),
                ],
                212 => [
                    $this->buildVariable([
                        'uri' => 212,
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'candidateResponse' => $duration2,
                        'identifier' => $durationId,
                        'cardinality' => $cardinality,
                        'baseType' => $durationType,
                        'epoch' => $milliseconds2 . ' ' . $timestamp2,
                    ]),
                ],
            ],
            'rds-test' => [
                224 => [
                    $this->buildVariable([
                        'uri' => 224,
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'value' => '0',
                        'identifier' => 'LtiOutcome',
                        'cardinality' => $cardinality,
                        'baseType' => 'float',
                        'epoch' => $milliseconds1 . ' ' . $timestamp1,
                    ], true),
                ],
            ],
            'kv' => [
                'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197.item-1.0numAttempts' => [
                    $this->buildVariable([
                        'uri' => 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197.item-1.0_prop_numAttempts',
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'candidateResponse' => $attempt1,
                        'identifier' => $attemptId,
                        'cardinality' => $cardinality,
                        'baseType' => $attemptType,
                        'epoch' => $milliseconds1 . ' ' . $timestamp1,
                    ]),
                    $this->buildVariable([
                        'uri' => 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197.item-1.0_prop_numAttempts',
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'candidateResponse' => $attempt2,
                        'identifier' => $attemptId,
                        'cardinality' => $cardinality,
                        'baseType' => $attemptType,
                        'epoch' => $milliseconds2 . ' ' . $timestamp2,
                    ]),
                ],
                'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197.item-1.0duration' => [
                    $this->buildVariable([
                        'uri' => 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197.item-1.0_prop_duration',
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'candidateResponse' => $duration1,
                        'identifier' => $durationId,
                        'cardinality' => $cardinality,
                        'baseType' => $durationType,
                        'epoch' => $milliseconds1 . ' ' . $timestamp1,
                    ]),
                    $this->buildVariable([
                        'uri' => 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197.item-1.0_prop_duration',
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'candidateResponse' => $duration2,
                        'identifier' => $durationId,
                        'cardinality' => $cardinality,
                        'baseType' => $durationType,
                        'epoch' => $milliseconds2 . ' ' . $timestamp2,
                    ]),
                ],
            ],
            'kv-test' => [
                'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197LtiOutcome' => [
                    $this->buildVariable([
                        'uri' => 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549623919594197_prop_LtiOutcome',
                        'deliveryResultIdentifier' => $deliveryResultIdentifier,
                        'value' => $testValue,
                        'identifier' => $testId,
                        'cardinality' => $cardinality,
                        'baseType' => $testType,
                        'epoch' => $milliseconds1 . ' ' . $timestamp1,
                    ], true),
                ],
            ],
        ];

        $results = [
            'attempt1' => [
                'value' => $attempt1,
                'identifier' => $attemptId,
                'type' => new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable'),
                'epoch' => $milliseconds1 . ' ' . $timestamp1,
                'cardinality' => $cardinality,
                'basetype' => $attemptType,
            ],
            'duration1' => [
                'value' => $duration1,
                'identifier' => $durationId,
                'type' => new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable'),
                'epoch' => $milliseconds1 . ' ' . $timestamp1,
                'cardinality' => $cardinality,
                'basetype' => $durationType,

            ],
            'attempt2' => [
                'value' => $attempt2,
                'identifier' => $attemptId,
                'type' => new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable'),
                'epoch' => $milliseconds2 . ' ' . $timestamp2,
                'cardinality' => $cardinality,
                'basetype' => $attemptType,

            ],
            'duration2' => [
                'value' => $duration2,
                'identifier' => $durationId,
                'type' => new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable'),
                'epoch' => $milliseconds2 . ' ' . $timestamp2,
                'cardinality' => $cardinality,
                'basetype' => $durationType,
            ],
            'test' => [
                'value' => $testValue,
                'identifier' => $testId,
                'type' => new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable'),
                'epoch' => $milliseconds1 . ' ' . $timestamp1,
                'cardinality' => $cardinality,
                'basetype' => $testType,
            ],
        ];

        $qtiResult = [
            CrudResultsService::GROUP_BY_ITEM => [
                $timestamp1 . $deliveryResultIdentifier => [
                    $results['attempt1'],
                    $results['duration1'],
                ],
                $timestamp2 . $deliveryResultIdentifier => [
                    $results['attempt2'],
                    $results['duration2'],
                ],
            ],
            CrudResultsService::GROUP_BY_DELIVERY => [
                $timestamp2 . $deliveryResultIdentifier => [
                    $results['attempt2'],
                    $results['duration2'],
                ],
            ],
            CrudResultsService::GROUP_BY_TEST => [
                $deliveryResultIdentifier => [
                    $results['test'],
                ],
            ],
        ];

        return [
            'rds-item' => [
                $deliveryResultIdentifier,
                $variables['rds'],
                CrudResultsService::GROUP_BY_ITEM,
                CrudResultsService::ATTEMPTS_ALL,
                $qtiResult,
            ],

            'kv-item' => [
                $deliveryResultIdentifier,
                $variables['kv'],
                CrudResultsService::GROUP_BY_ITEM,
                CrudResultsService::ATTEMPTS_ALL,
                $qtiResult,
            ],

            'rds-delivery' => [
                $deliveryResultIdentifier,
                $variables['rds'],
                CrudResultsService::GROUP_BY_DELIVERY,
                CrudResultsService::ATTEMPTS_LATEST,
                $qtiResult,
            ],

            'kv-delivery' => [
                $deliveryResultIdentifier,
                $variables['kv'],
                CrudResultsService::GROUP_BY_DELIVERY,
                CrudResultsService::ATTEMPTS_LATEST,
                $qtiResult,
            ],

            'rds-test' => [
                $deliveryResultIdentifier,
                $variables['rds-test'],
                CrudResultsService::GROUP_BY_TEST,
                CrudResultsService::ATTEMPTS_NONE,
                $qtiResult,
            ],

            'kv-test' => [
                $deliveryResultIdentifier,
                $variables['kv-test'],
                CrudResultsService::GROUP_BY_TEST,
                CrudResultsService::ATTEMPTS_NONE,
                $qtiResult,
            ],
        ];
    }

    protected function buildVariable(array $properties, $test = false)
    {
        $class = $test
            ? 'taoResultServer_models_classes_OutcomeVariable'
            : 'taoResultServer_models_classes_ResponseVariable';
        $callIdItem = $test ? null : 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549558434108966';
        $callIdTest = $test ? 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i15495620683550142' : null;
        $testUri = 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i15495616427232134-';
        $itemUri = $test ? null : 'http://127.0.0.1/oat/package-tao/tao-gui-installed.rdf#i1549558434108966';
        $correctResponse = null;

        $namespacedClass = '\\' . $class;
        $variable = new $namespacedClass();
        $variable->correctResponse = $correctResponse;
        if ($test) {
            $variable->value = base64_encode($properties['value']);
        } else {
            $variable->candidateResponse = base64_encode($properties['candidateResponse']);
        }
        $variable->identifier = $properties['identifier'];
        $variable->cardinality = $properties['cardinality'];
        $variable->baseType = $properties['baseType'];
        $variable->epoch = $properties['epoch'];

        $container = new \stdClass();
        $container->uri = $properties['uri'];
        $container->class = $class;
        $container->deliveryResultIdentifier = $properties['deliveryResultIdentifier'];
        $container->callIdItem = $callIdItem;
        $container->callIdTest = $callIdTest;
        $container->test = $testUri;
        $container->item = $itemUri;
        $container->variable = $variable;

        return $container;
    }
}
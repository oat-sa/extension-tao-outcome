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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

namespace oat\taoResultServer\test\Unit\models\Formatter;

use oat\generis\test\TestCase;
use oat\taoResultServer\models\Formatter\ItemResponseVariableSplitter;
use taoResultServer_models_classes_ResponseVariable;

class ItemResponseVariableSplitterTest extends TestCase
{
    public function testSplitByAttempt()
    {
        $subject = new ItemResponseVariableSplitter();
        $input = [
            [
                'value' => 'PT1.352193S',
                'identifier' => 'duration',
                'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                'epoch' => '0.00556600 1591278835',
                'cardinality' => 'single',
                'basetype' => 'duration',
            ],
            [
                'value' => 'PT8.635329S',
                'identifier' => 'duration',
                'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                'epoch' => '0.43220600 1591278842',
                'cardinality' => 'single',
                'basetype' => 'duration',
            ],
            [
                'value' => '1',
                'identifier' => 'numAttempts',
                'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                'epoch' => '0.99930200 1591278834',
                'cardinality' => 'single',
                'basetype' => 'integer',
            ],
            [
                'value' => '2',
                'identifier' => 'numAttempts',
                'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                'epoch' => '0.42553300 1591278842',
                'cardinality' => 'single',
                'basetype' => 'integer',
            ],
            [
                'value' => '',
                'identifier' => 'RESPONSE',
                'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                'epoch' => '0.00774200 1591278835',
                'cardinality' => 'single',
                'basetype' => 'file',
            ],
            [
                'value' => '',
                'identifier' => 'RESPONSE',
                'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                'epoch' => '0.43488600 1591278842',
                'cardinality' => 'single',
                'basetype' => 'file',
            ],
            [
                'value' => 'completed',
                'identifier' => 'completionStatus',
                'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable',
                'epoch' => '0.00672000 1591278835',
                'cardinality' => 'single',
                'basetype' => 'identifier',
            ],
            [
                'value' => 'completed',
                'identifier' => 'completionStatus',
                'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable',
                'epoch' => '0.43354300 1591278842',
                'cardinality' => 'single',
                'basetype' => 'identifier',
            ],
        ];
        $expected = [
            '1591278834.9993' =>
                [
                    [
                        'value' => 'PT1.352193S',
                        'identifier' => 'duration',
                        'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                        'epoch' => '0.00556600 1591278835',
                        'cardinality' => 'single',
                        'basetype' => 'duration',
                    ],
                    [
                        'value' => '1',
                        'identifier' => 'numAttempts',
                        'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                        'epoch' => '0.99930200 1591278834',
                        'cardinality' => 'single',
                        'basetype' => 'integer',
                    ],
                    [
                        'value' => '',
                        'identifier' => 'RESPONSE',
                        'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                        'epoch' => '0.00774200 1591278835',
                        'cardinality' => 'single',
                        'basetype' => 'file',
                    ],
                    [
                        'value' => 'completed',
                        'identifier' => 'completionStatus',
                        'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable',
                        'epoch' => '0.00672000 1591278835',
                        'cardinality' => 'single',
                        'basetype' => 'identifier',
                    ],
                ],
            '1591278842.4255' =>
                [
                    [
                        'value' => 'PT8.635329S',
                        'identifier' => 'duration',
                        'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                        'epoch' => '0.43220600 1591278842',
                        'cardinality' => 'single',
                        'basetype' => 'duration',
                    ],
                    [
                        'value' => '2',
                        'identifier' => 'numAttempts',
                        'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                        'epoch' => '0.42553300 1591278842',
                        'cardinality' => 'single',
                        'basetype' => 'integer',
                    ],
                    [
                        'value' => '',
                        'identifier' => 'RESPONSE',
                        'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
                        'epoch' => '0.43488600 1591278842',
                        'cardinality' => 'single',
                        'basetype' => 'file',
                    ],
                    [
                        'value' => 'completed',
                        'identifier' => 'completionStatus',
                        'type' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable',
                        'epoch' => '0.43354300 1591278842',
                        'cardinality' => 'single',
                        'basetype' => 'identifier',
                    ],
                ],
        ];
        $this->assertSame($expected, $subject->splitByAttempt($input));
    }

    public function testSplitObjByAttempt()
    {
        $subject = new ItemResponseVariableSplitter();
        $var1 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => null,
                    'candidateResponse' => 'TVE9PQ==',
                    'identifier' => 'numAttempts',
                    'cardinality' => 'single',
                    'baseType' => 'integer',
                    'epoch' => '0.87213700 1591278832',
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_numAttempts',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
        $var2 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => null,
                    'candidateResponse' => 'VUZRMUxqRXhOVFUyT0ZNPQ==',
                    'identifier' => 'duration',
                    'cardinality' => 'single',
                    'baseType' => 'duration',
                    'epoch' => '0.87837800 1591278832',
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_duration',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
        $var3 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => null,
                    'candidateResponse' => '',
                    'identifier' => 'completionStatus',
                    'cardinality' => 'single',
                    'baseType' => 'identifier',
                    'epoch' => '0.87976600 1591278832'
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_completionStatus',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_OutcomeVariable',
        ];
        $var4 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => null,
                    'candidateResponse' => '',
                    'identifier' => 'SCORE',
                    'cardinality' => 'single',
                    'baseType' => 'float',
                    'epoch' => '0.88077600 1591278832'
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_SCORE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_OutcomeVariable',
        ];
        $var5 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => false,
                    'candidateResponse' => 'VzBkaElFNWxYUT09',
                    'identifier' => 'RESPONSE',
                    'cardinality' => 'multiple',
                    'baseType' => 'pair',
                    'epoch' => '0.88178100 1591278832'
                ]
            )
            ,
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_RESPONSE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
        $var6 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => null,
                    'candidateResponse' => 'TWc9PQ==',
                    'identifier' => 'numAttempts',
                    'cardinality' => 'single',
                    'baseType' => 'integer',
                    'epoch' => '0.16855600 1591278840'
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_numAttempts',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable'
        ];
        $var7 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => null,
                    'candidateResponse' => 'VUZReE1pNHlOak14T1RGVA==',
                    'identifier' => 'duration',
                    'cardinality' => 'single',
                    'baseType' => 'duration',
                    'epoch' => '0.17513900 1591278840'
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_duration',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
        $var8 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => null,
                    'candidateResponse' => '',
                    'identifier' => 'completionStatus',
                    'cardinality' => 'single',
                    'baseType' => 'identifier',
                    'epoch' => '0.17644300 1591278840',
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_completionStatus',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_OutcomeVariable',
        ];
        $var9 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => null,
                    'candidateResponse' => '',
                    'identifier' => 'SCORE',
                    'cardinality' => 'single',
                    'baseType' => 'float',
                    'epoch' => '0.17767400 1591278840',
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_SCORE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_OutcomeVariable',
        ];
        $var10 = (object)[
            'deliveryResultIdentifier' => '_deliveryResultIdentifier_',
            'test' => '_testid_',
            'item' => '_itemid_',
            'variable' => $this->getVariable(
                [
                    'correctResponse' => false,
                    'candidateResponse' => 'VzBkaElFNWxPeUJOYnlCRllWMD0=',
                    'identifier' => 'RESPONSE',
                    'cardinality' => 'multiple',
                    'baseType' => 'pair',
                    'epoch' => '0.17890700 1591278840',
                ]
            ),
            'callIdItem' => '_deliveryResultIdentifier_.item-1.0',
            'uri' => '_deliveryResultIdentifier__deliveryResultIdentifier_.item-1.0_prop_RESPONSE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];

        $expected = [
            '1591278832.8721' => [
                $var1,
                $var2,
                $var3,
                $var4,
                $var5,
            ],
            '1591278840.1686' => [
                $var6,
                $var7,
                $var8,
                $var9,
                $var10,
            ]
        ];
        $input = [
            $var1,
            $var2,
            $var3,
            $var4,
            $var5,
            $var6,
            $var7,
            $var8,
            $var9,
            $var10,
        ];
        $this->assertSame($expected, $subject->splitObjByAttempt($input));
    }

    private function getVariable($data): taoResultServer_models_classes_ResponseVariable
    {
        return (new taoResultServer_models_classes_ResponseVariable())
            ->setCorrectResponse($data['correctResponse'])
            ->setIdentifier($data['identifier'])
            ->setCardinality($data['cardinality'])
            ->setBaseType($data['baseType'])
            ->setEpoch($data['epoch']);
    }
}

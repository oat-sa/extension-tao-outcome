<?php

declare(strict_types=1);

namespace oat\taoResultServer\test\Unit\models\classes;

use PHPUnit\Framework\TestCase;

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies S.A.
 */
class taoResultServer_models_classes_VariableTest extends TestCase
{
    public function testVariableCanBeJsonSerialized(): void
    {
        $subject = new class extends \taoResultServer_models_classes_Variable {
            public function getValue()
            {
            }

            public function setValue($value)
            {
            }

            protected function getType(): string
            {
                return 'testType';
            }
        };

        $subject
            ->setIdentifier('testIdentifier')
            ->setCardinality('single')
            ->setBaseType('testBaseType')
            ->setEpoch('testEpoch');

        $this->assertSame(json_encode([
            'identifier' => 'testIdentifier',
            'cardinality' => 'single',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
            'type' => 'testType',
        ]), json_encode($subject));
    }

    /**
     * @dataProvider provideInvalidVariableArrayRepresentation
     */
    public function testItThrowsExceptionIfVariableCannotBeReconstructedDueToMissingKeys(
        string $expectedExceptionMessage,
        array $variableAsArray
    ): void {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        \taoResultServer_models_classes_Variable::fromData($variableAsArray);
    }

    public function testIfOutComeVariableCanBeReconstructedFromArray(): void
    {
        $variableAsArray = [
            'identifier' => 'testIdentifier',
            'cardinality' => 'multiple',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
            'type' => \taoResultServer_models_classes_OutcomeVariable::TYPE,
            'normalMinimum' => 0.0,
            'normalMaximum' => 10.0,
            'value' => 'testValue',
        ];

        $result = \taoResultServer_models_classes_Variable::fromData($variableAsArray);
        $this->assertInstanceOf(\taoResultServer_models_classes_OutcomeVariable::class, $result);

        $expectedVariableData = [
            'identifier' => 'testIdentifier',
            'cardinality' => 'multiple',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
            'type' => \taoResultServer_models_classes_OutcomeVariable::TYPE,
            'normalMinimum' => 0.0,
            'normalMaximum' => 10.0,
            'value' => 'testValue',
        ];

        $this->assertSame($expectedVariableData, $result->jsonSerialize());
    }

    public function testIfResponseVariableCanBeConstructedFromArray(): void
    {
        $variableAsArray = [
            'identifier' => 'testIdentifier',
            'cardinality' => 'multiple',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
            'type' => \taoResultServer_models_classes_ResponseVariable::TYPE,
            'correctResponse' => 'testCorrectResponse',
            'candidateResponse' => 'testCandidateResponse',
        ];

        $result = \taoResultServer_models_classes_Variable::fromData($variableAsArray);
        $this->assertInstanceOf(\taoResultServer_models_classes_ResponseVariable::class, $result);

        $expectedVariableData = [
            'identifier' => 'testIdentifier',
            'cardinality' => 'multiple',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
            'type' => \taoResultServer_models_classes_ResponseVariable::TYPE,
            'correctResponse' => 'testCorrectResponse',
            'candidateResponse' => 'testCandidateResponse',
        ];

        $this->assertSame($expectedVariableData, $result->jsonSerialize());
    }

    public function testIfTraceVariableCanBeConstructedFromArray(): void
    {
        $variableAsArray = [
            'identifier' => 'testIdentifier',
            'cardinality' => 'multiple',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
            'type' => \taoResultServer_models_classes_TraceVariable::TYPE,
            'trace' => 'test',
        ];

        $result = \taoResultServer_models_classes_Variable::fromData($variableAsArray);
        $this->assertInstanceOf(\taoResultServer_models_classes_TraceVariable::class, $result);

        $expectedVariableData = [
            'identifier' => 'testIdentifier',
            'cardinality' => 'multiple',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
            'type' => \taoResultServer_models_classes_TraceVariable::TYPE,
            'trace' => 'test',
        ];

        $this->assertSame($expectedVariableData, $result->jsonSerialize());
    }

    public function testItThrowsExceptionIfInvalidVariableTypeIsProvided(): void
    {
        $variableAsArray = [
            'type' => 'invalid',
            'identifier' => 'testIdentifier',
            'cardinality' => 'multiple',
            'baseType' => 'testBaseType',
            'epoch' => 'testEpoch',
        ];

        $this->expectExceptionMessage('Unsupported variable type: invalid');

        \taoResultServer_models_classes_Variable::fromData($variableAsArray);
    }

    public function provideInvalidVariableArrayRepresentation(): array
    {
        return [
            'anyVariableWithMissingIdentifierKey' => [
                'expectedExceptionMessage' => 'Key "identifier" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => 'test',
                ],
            ],
            'anyVariableWithMissingCardinalityKey' => [
                'expectedExceptionMessage' => 'Key "cardinality" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => 'test',
                    'identifier' => 'test',
                ],
            ],
            'anyVariableWithMissingbaseTypeKey' => [
                'expectedExceptionMessage' => 'Key "baseType" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => 'test',
                    'identifier' => 'test',
                    'cardinality' => 'single',
                ],
            ],
            'anyVariableWithMissingEpochKey' => [
                'expectedExceptionMessage' => 'Key "epoch" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => 'test',
                    'identifier' => 'test',
                    'cardinality' => 'single',
                    'baseType' => 'test',
                ],
            ],
            'missingVariableType' => [
                'expectedExceptionMessage' => 'Key "type" is not defined in variable data.',
                'variableAsArray' => [
                    'identifier' => 'test',
                    'cardinality' => 'single',
                    'baseType' => 'test',
                    'epoch' => 'test',
                ],
            ],
            'outcomeVariableWithMissingNormalMinimumKey' => [
                'expectedExceptionMessage' => 'Key "normalMinimum" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => \taoResultServer_models_classes_OutcomeVariable::TYPE,
                    'identifier' => 'test',
                    'cardinality' => 'single',
                    'baseType' => 'test',
                    'epoch' => 'test',
                ],
            ],
            'outcomeVariableWithMissingNormalMaximumKey' => [
                'expectedExceptionMessage' => 'Key "normalMaximum" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => \taoResultServer_models_classes_OutcomeVariable::TYPE,
                    'identifier' => 'test',
                    'cardinality' => 'single',
                    'baseType' => 'test',
                    'epoch' => 'test',
                    'normalMinimum' => 1.00,
                ],
            ],
            'outcomeVariableWithMissingValueKey' => [
                'expectedExceptionMessage' => 'Key "value" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => \taoResultServer_models_classes_OutcomeVariable::TYPE,
                    'identifier' => 'test',
                    'cardinality' => 'single',
                    'baseType' => 'test',
                    'epoch' => 'test',
                    'normalMinimum' => 1.00,
                    'normalMaximum' => 2.00,
                ],
            ],
            'responseVariableWithMissingCorrectResponseKey' => [
                'expectedExceptionMessage' => 'Key "correctResponse" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => \taoResultServer_models_classes_ResponseVariable::TYPE,
                    'identifier' => 'test',
                    'cardinality' => 'single',
                    'baseType' => 'test',
                    'epoch' => 'test',
                ],
            ],
            'responseVariableWithMissingCandidateResponseKey' => [
                'expectedExceptionMessage' => 'Key "candidateResponse" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => \taoResultServer_models_classes_ResponseVariable::TYPE,
                    'identifier' => 'test',
                    'cardinality' => 'single',
                    'baseType' => 'test',
                    'epoch' => 'test',
                    'correctResponse' => 'test',
                ],
            ],
            'traceVariableWithMissingTraceKey' => [
                'expectedExceptionMessage' => 'Key "trace" is not defined in variable data.',
                'variableAsArray' => [
                    'type' => \taoResultServer_models_classes_TraceVariable::TYPE,
                    'identifier' => 'test',
                    'cardinality' => 'single',
                    'baseType' => 'test',
                    'epoch' => 'test',
                ],
            ],
        ];
    }
}

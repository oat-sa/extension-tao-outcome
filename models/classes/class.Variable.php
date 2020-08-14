<?php

declare(strict_types=1);

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
 * Copyright (c) 2013 (original work) Open Assessment Technologies S.A.
 *
 * @author "Patrick Plichart, <patrick@taotesting.com>"
 *
 * An Assessment Result is used to report the results of a candidate's interaction
 * with a test and/or one or more items attempted. Information about the test is optional,
 * in some systems it may be possible to interact with items that are not organized into a
 * test at all. For example, items that are organized with learning resources and presented
 * individually in a formative context.
 */
abstract class taoResultServer_models_classes_Variable implements JsonSerializable
{
    public const CARDINALITY_SINGLE = 'single';
    public const CARDINALITY_MULTIPLE = 'multiple';
    public const CARDINALITY_ORDERED = 'ordered';
    public const CARDINALITY_RECORD = 'record';

    public const TYPE_VARIABLE_INTEGER = 'integer';
    public const TYPE_VARIABLE_BOOLEAN = 'boolean';
    public const TYPE_VARIABLE_IDENTIFIER = 'identifier';
    public const TYPE_VARIABLE_DURATION = 'duration';
    public const TYPE_VARIABLE_FLOAT = 'float';

    /**
     * The purpose of an itemVariable is to report the value of the item variable with the given identifier.
     *
     * @var string|null
     */
    protected $identifier;

    /**
     * The cardinality of the variable, taken from the corresponding declaration or definition.
     *
     * @var string|null {single, multiple, ordered, record}
     */
    protected $cardinality;

    /**
     * The base type of the variable, taken from the corresponding declaration of definition.
     * This value is omitted only for variables with record cardinality.
     *
     * @var string|null should move to an enumeration
     */
    protected $baseType;

    /**
     * The epoch when the variable has been last modified
     *
     * @var string|null
     */
    protected $epoch;

    abstract protected function getType(): string;

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @throws common_exception_InvalidArgumentType
     */
    public function setCardinality($cardinality = self::CARDINALITY_SINGLE): self
    {
        if (!in_array($cardinality, [
            self::CARDINALITY_SINGLE,
            self::CARDINALITY_MULTIPLE,
            self::CARDINALITY_ORDERED,
            self::CARDINALITY_RECORD,
        ], true)
        ) {
            throw new common_exception_InvalidArgumentType('cardinality');
        }

        $this->cardinality = $cardinality;

        return $this;
    }

    public function getCardinality(): ?string
    {
        return $this->cardinality;
    }

    public function setBaseType(string $baseType): self
    {
        $this->baseType = $baseType;

        return $this;
    }

    public function getBaseType(): ?string
    {
        return $this->baseType;
    }

    public function setEpoch(string $epoch): self
    {
        $this->epoch = $epoch;

        return $this;
    }

    public function getEpoch(): ?string
    {
        return $this->epoch;
    }

    public function getCreationTime(): ?float
    {
        if (!isset($this->epoch)) {
            return null;
        }
        [$usec, $sec] = explode(' ', $this->epoch);

        return ((float)$usec + (float)$sec);
    }

    public function isSetEpoch(): bool
    {
        return (isset($this->epoch));
    }

    public function isMultiple(): bool
    {
        return in_array($this->cardinality, [self::CARDINALITY_MULTIPLE, self::CARDINALITY_ORDERED], true);
    }

    /**
     * get the value of the variable
     *
     * @return mixed
     */
    abstract public function getValue();

    /**
     * Set the value of the variable
     *
     * @param $value mixed
     */
    abstract public function setValue($value);

    /**
     * @deprecated Use jsonSerialize method instead
     */
    public function toJson(): string
    {
        return json_encode($this);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'identifier' => $this->identifier,
            'cardinality' => $this->cardinality,
            'baseType' => $this->baseType,
            'epoch' => $this->epoch,
            'type' => $this->getType(),
        ];
    }

    /**
     * @return taoResultServer_models_classes_OutcomeVariable|taoResultServer_models_classes_ResponseVariable|taoResultServer_models_classes_TraceVariable
     * @throws common_exception_InvalidArgumentType
     * @throws LogicException
     */
    public static function fromData(array $rawVariable)
    {
        self::validateKeys(['identifier', 'cardinality', 'baseType', 'epoch', 'type'], $rawVariable);

        switch ($rawVariable['type']) {
            case taoResultServer_models_classes_OutcomeVariable::TYPE:
                $variable = self::fromOutcomeVariableData($rawVariable);
                break;
            case taoResultServer_models_classes_ResponseVariable::TYPE:
                $variable = self::fromResponseVariableData($rawVariable);
                break;
            case taoResultServer_models_classes_TraceVariable::TYPE:
                $variable = self::fromTraceVariableData($rawVariable);
                break;
            default:
                throw new \LogicException(sprintf('Unsupported variable type: %s', $rawVariable['type']));
        }

        return $variable
            ->setIdentifier($rawVariable['identifier'])
            ->setCardinality($rawVariable['cardinality'])
            ->setBaseType($rawVariable['baseType'])
            ->setEpoch($rawVariable['epoch']);
    }

    /**
     * @throws LogicException
     */
    protected static function validateKeys(array $keys, array $data): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new LogicException(sprintf('Key "%s" is not defined in variable data.', $key));
            }
        }
    }

    private static function fromOutcomeVariableData(
        array $rawOutcomeVariable
    ): taoResultServer_models_classes_OutcomeVariable {
        self::validateKeys(['normalMinimum', 'normalMaximum', 'value'], $rawOutcomeVariable);

        return (new taoResultServer_models_classes_OutcomeVariable())
            ->setNormalMinimum($rawOutcomeVariable['normalMinimum'])
            ->setNormalMaximum($rawOutcomeVariable['normalMaximum'])
            ->setEncodedValue($rawOutcomeVariable['value']);
    }

    private static function fromResponseVariableData(
        array $rawResponseVariable
    ): taoResultServer_models_classes_ResponseVariable {
        self::validateKeys(['correctResponse', 'candidateResponse'], $rawResponseVariable);

        return (new taoResultServer_models_classes_ResponseVariable())
            ->setCorrectResponse($rawResponseVariable['correctResponse'])
            ->setEncodedCandidateResponse($rawResponseVariable['candidateResponse']);
    }

    private static function fromTraceVariableData(array $rawTraceVariable): taoResultServer_models_classes_TraceVariable
    {
        self::validateKeys(['trace'], $rawTraceVariable);

        return (new taoResultServer_models_classes_TraceVariable())->setTrace($rawTraceVariable['trace']);
    }
}

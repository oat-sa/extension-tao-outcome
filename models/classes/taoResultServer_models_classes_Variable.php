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
    public function setCardinality(string $cardinality = 'single'): self
    {
        if (!(in_array($cardinality, [
            'single',
            'multiple',
            'ordered',
            'record',
        ]))) {
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

    public function isSetEpoch(): bool
    {
        return (isset($this->epoch));
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
        return json_encode($this->jsonSerialize());
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
        ];
    }

    /**
     * @return taoResultServer_models_classes_OutcomeVariable|taoResultServer_models_classes_ResponseVariable|taoResultServer_models_classes_TraceVariable
     * @throws common_exception_InvalidArgumentType
     * @throws LogicException
     */
    public static function fromData(array $data)
    {
        if (!isset($data['type'])) {
            throw new \LogicException('Variable type declaration is missing.');
        }

        self::validateKeys(['identifier', 'cardinality', 'baseType', 'epoch'], $data);

        switch ($data['type']) {
            case taoResultServer_models_classes_OutcomeVariable::class:
                self::validateKeys(['normalMinimum', 'normalMaximum', 'value'], $data);
                $variable = (new taoResultServer_models_classes_OutcomeVariable())
                    ->setNormalMinimum($data['normalMinimum'])
                    ->setNormalMaximum($data['normalMaximum'])
                    ->setValue($data['value']);
                break;
            case taoResultServer_models_classes_ResponseVariable::class:
                self::validateKeys(['correctResponse', 'candidateResponse'], $data);
                $variable = (new taoResultServer_models_classes_ResponseVariable())
                    ->setCorrectResponse($data['correctResponse'])
                    ->setCandidateResponse($data['candidateResponse']);
                break;
            case taoResultServer_models_classes_TraceVariable::class:
                self::validateKeys(['trace'], $data);
                $variable = (new taoResultServer_models_classes_TraceVariable())->setTrace($data['trace']);
                break;
            default:
                throw new \LogicException(sprintf('Unsupported variable type: %s', $data['type']));
        }


        return $variable
            ->setIdentifier($data['identifier'])
            ->setCardinality($data['cardinality'])
            ->setBaseType($data['baseType'])
            ->setEpoch($data['epoch']);
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
}

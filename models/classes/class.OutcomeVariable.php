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
 * in some systems it may be possible to interact with items that are not organized into
 * a test at all. For example, items that are organized with learning resources and
 * presented individually in a formative context.
 */
class taoResultServer_models_classes_OutcomeVariable extends taoResultServer_models_classes_Variable
{
    public const TYPE = 'outcomeVariable';

    /**
     * taken from the corresponding outcomeDeclaration.
     *
     * @var float|null
     */
    protected $normalMaximum;

    /** @var float|null */
    protected $normalMinimum;

    /**
     * The value(s) of the outcome variable.
     * The order of the values is significant only if the outcome was declared with ordered cardinality.
     *
     * @var string|null base64 encoded
     */
    protected $value;

    public function setNormalMaximum(?float $normalMaximum): self
    {
        $this->normalMaximum = $normalMaximum;

        return $this;
    }

    public function getNormalMaximum(): ?float
    {
        return $this->normalMaximum;
    }

    public function setNormalMinimum(?float $normalMinimum): self
    {
        $this->normalMinimum = $normalMinimum;

        return $this;
    }

    public function getNormalMinimum(): ?float
    {
        return $this->normalMinimum;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return base64_decode((string)$this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value): self
    {
        return $this->setEncodedValue(base64_encode((string)$value));
    }

    public function setEncodedValue($encodedValue): self
    {
        $this->value = $encodedValue;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return parent::jsonSerialize() +
            [
                'normalMinimum' => $this->normalMinimum,
                'normalMaximum' => $this->normalMaximum,
                'value' => $this->value,
            ];
    }

    protected function getType(): string
    {
        return self::TYPE;
    }
}

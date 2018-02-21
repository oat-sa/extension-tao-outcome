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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoResultServer\models\Entity;

abstract class VariableStorable implements \JsonSerializable
{
    /** @var  string */
    protected $deliveryResultIdentifier;

    /** @var  string */
    protected $testIdentifier;

    /** @var \taoResultServer_models_classes_Variable */
    protected $variable;

    /**
     * @param string $deliveryResultIdentifier
     * @param string $testIdentifier
     * @param \taoResultServer_models_classes_Variable $variable
     */
    public function __construct(
        $deliveryResultIdentifier,
        $testIdentifier,
        \taoResultServer_models_classes_Variable $variable
    ) {
        $this->deliveryResultIdentifier = $deliveryResultIdentifier;
        $this->testIdentifier = $testIdentifier;
        $this->variable = $variable;
    }

    /**
     * @return string
     */
    public function getDeliveryResultIdentifier()
    {
        return $this->deliveryResultIdentifier;
    }

    /**
     * @return string
     */
    public function getTestIdentifier()
    {
        return $this->testIdentifier;
    }

    /**
     * @return \taoResultServer_models_classes_Variable
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->variable->getIdentifier();
    }
}
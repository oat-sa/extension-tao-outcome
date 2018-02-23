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

class ItemVariableStorable extends VariableStorable
{
    /** @var  string */
    private $callItemId;

    /** @var  string */
    private $item;

    /**
     * @param string $deliveryResultIdentifier
     * @param string $testIdentifier
     * @param \taoResultServer_models_classes_Variable $variable
     * @param $item
     * @param $callItemId
     */
    public function __construct(
        $deliveryResultIdentifier,
        $testIdentifier,
        \taoResultServer_models_classes_Variable $variable,
        $item,
        $callItemId
    ) {
        parent::__construct($deliveryResultIdentifier, $testIdentifier, $variable);
        $this->item = $item;
        $this->callItemId = $callItemId;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function createFromArray(array $data)
    {
        return new static($data['deliveryResultIdentifier'], $data['test'], unserialize($data['variable']), $data['item'], $data['callIdItem']);
    }

    /**
     * @return string
     */
    public function getCallItemId()
    {
        return $this->callItemId;
    }

    /**
     * @return string
     */
    public function getItemIdentifier()
    {
        return $this->item;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            "deliveryResultIdentifier" => $this->deliveryResultIdentifier,
            "test" => $this->testIdentifier,
            "item" => $this->item,
            "variable" => serialize($this->variable),
            "callIdItem" => $this->callItemId,
            "uri" => $this->deliveryResultIdentifier . $this->callItemId,
            "callIdTest" => null,
            "class" => get_class($this->variable)
        ];
    }

}
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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use oat\oatbox\service\ConfigurableService;
use taoResultServer_models_classes_Variable;
use taoResultServer_models_classes_WritableResultStorage;

class NoResultStorage extends ConfigurableService implements taoResultServer_models_classes_WritableResultStorage
{
    public function spawnResult()
    {
    }

    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier)
    {
    }

    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier)
    {
    }

    public function storeItemVariable($deliveryResultIdentifier, $test, $item, taoResultServer_models_classes_Variable $itemVariable, $callIdItem)
    {
    }

    public function storeItemVariables($deliveryResultIdentifier, $test, $item, array $itemVariables, $callIdItem)
    {
    }

    public function storeTestVariable($deliveryResultIdentifier, $test, taoResultServer_models_classes_Variable $testVariable, $callIdTest)
    {
    }

    public function storeTestVariables($deliveryResultIdentifier, $test, array $testVariables, $callIdTest)
    {
    }

    public function configure($callOptions = array())
    {
    }
}

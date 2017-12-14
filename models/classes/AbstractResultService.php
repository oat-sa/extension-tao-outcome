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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use oat\oatbox\service\ConfigurableService;
use oat\taoResultServer\models\classes\implementation\ReadableResultStorage;
use oat\taoResultServer\models\classes\implementation\WritableResultStorage;

abstract class AbstractResultService extends ConfigurableService implements ResultServerService, \taoResultServer_models_classes_ReadableResultStorage, \taoResultServer_models_classes_WritableResultStorage
{

    use ResultServiceTrait;
    use ReadableResultStorage;
    use WritableResultStorage;
    use ImplementationResultInitializer;

    /**
     * Starts or resume a taoResultServerStateFull session for results submission
     *
     * @param $compiledDelivery
     * @param $executionIdentifier
     * @param array $options additional result server options
     * @throws \common_Exception
     * @throws
     */
    public function initResultServer($compiledDelivery, $executionIdentifier, $options = [])
    {
        $this->prepareImplementationStorageInterface($compiledDelivery, $executionIdentifier, $options);

        $this->spawnResult($executionIdentifier);
        \common_Logger::i('Spawning/resuming result identifier related to process execution ' . $executionIdentifier);

        //link test taker identifier with results
        $this->storeRelatedTestTaker($executionIdentifier, \common_session_SessionManager::getSession()->getUserUri());


        //link delivery identifier with results
        $this->storeRelatedDelivery($executionIdentifier, $compiledDelivery->getUri());
    }

    abstract public function getResultStorage($deliveryId);

    abstract protected function prepareImplementationStorageInterface($compiledDelivery = null, $executionIdentifier = null, $options = []);

}

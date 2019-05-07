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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\ServiceProxy;
use qtism\data\storage\xml\XmlDocument;

/**
 * Class DeliveryExecutionFilter
 *
 * Service to filter delivery execution
 *
 * @package oat\taoResultServer\models\classes\scorableResult
 */
class DeliveryExecutionFilter extends ConfigurableService
{
    const SERVICE_ID = 'taoResultServer/deliveryExecutionFilter';

    const OPTION_CACHE = 'cache';

    const ONLY_SCORABLE = 'onlyScorable';
    const OPTION_SCORABLE_COMPONENTS = 'scorable-components';

    use OntologyAwareTrait;

    /**
     * Filter a list a delivery execution  based on $options
     * - if $options contains "onlyScorable", delivery executions are filtered as human scorable
     *
     * @param array $deliveryExecutions
     * @param array $options
     * @return array
     * @throws \common_Exception
     * @throws \common_cache_NotFoundException
     * @throws \common_exception_Error
     * @throws \core_kernel_persistence_Exception
     * @throws \qtism\data\storage\xml\XmlStorageException
     */
    public function filter(array $deliveryExecutions, $options = [])
    {
        $onlyScorable = false;
        if (array_key_exists(self::ONLY_SCORABLE, $options) && (
                $options[self::ONLY_SCORABLE] === true || $options[self::ONLY_SCORABLE] === 'true'
            )) {
            $onlyScorable = true;
        }

        $scorableDeliveryExecutions = [];
        foreach ($deliveryExecutions as $deliveryExecution) {
            $deliveryIdentifier = $deliveryExecution['deliveryIdentifier'];
            $deliveryExecutionIdentifier = $deliveryExecution['deliveryResultIdentifier'];

            if ($onlyScorable === true) {
                $cacheSerial = $this->getScorableSerial($deliveryExecutionIdentifier);

                if ($this->getCache()->has($cacheSerial)) {
                    $isScorable = $this->getCache()->get($cacheSerial);
                } else {
                    $isScorable = $this->isScorableDeliveryExecution($deliveryExecutionIdentifier, $deliveryIdentifier);

                    /** @var ServiceProxy $serviceProxy */
                    $serviceProxy = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
                    $closedStatuses = [DeliveryExecution::STATE_FINISHED, DeliveryExecution::STATE_TERMINATED];
                    if (in_array($serviceProxy->getDeliveryExecution($deliveryExecutionIdentifier)->getState(), $closedStatuses)) {
                        $this->getCache()->put($isScorable, $cacheSerial);
                    }
                }

                if ($isScorable) {
                    $scorableDeliveryExecutions[] = $deliveryExecution;
                }

            } else {
                $scorableDeliveryExecutions[] = $deliveryExecution;
            }

        }
        return $scorableDeliveryExecutions;
    }

    /**
     * Check if a delivery execution is human scorable by checking for
     * all delivery execution item results if at least one is scorable
     * - Evaluated delivery execution are cached to avoid to consume performance both
     *
     * @param $deliveryExecutionIdentifier
     * @param $deliveryIdentifier
     * @return bool
     * @throws \common_Exception
     * @throws \common_cache_NotFoundException
     * @throws \common_exception_Error
     * @throws \core_kernel_persistence_Exception
     * @throws \qtism\data\storage\xml\XmlStorageException
     */
    protected function isScorableDeliveryExecution($deliveryExecutionIdentifier, $deliveryIdentifier)
    {
        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);

        $resultStorage = $resultService->getResultStorage($deliveryIdentifier);
        $calls = $resultStorage->getRelatedItemCallIds($deliveryExecutionIdentifier);

        foreach ($calls as $callId) {
            $results = $resultStorage->getVariables($callId);

            foreach ($results as $itemResult) {
                $variable = $itemResult[count($itemResult) - 1];

                $cacheSerial = $this->getScorableSerial($variable->item);
                if (!$this->getCache()->has($cacheSerial)) {
                    $this->getCache()->put(
                        $this->isScorableItemResult($variable->item),
                        $cacheSerial
                    );
                }

                if ($this->getCache()->get($cacheSerial) === true) {
                    return true;
                }

            }
        }

        return false;
    }

    /**
     * Check if an item result is scorable by human by checking if item content file contains
     * token expected for human scoring (e.q. extendedTextInteraction)
     *
     * @param $itemRef
     * @return bool
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws \core_kernel_persistence_Exception
     * @throws \qtism\data\storage\xml\XmlStorageException
     */
    protected function isScorableItemResult($itemRef)
    {
        $item = $this->getResource($itemRef);
        if (!$item->exists()) {
            return false;
        }

        $file = $this->getItemService()->getItemDirectory($item)->getFile('qti.xml');

        // Check version
        $doc = new XmlDocument('2.1');
        $doc->loadFromString($file->read());

        $scorableInteractionCount = 0;
        foreach ($this->getOption(self::OPTION_SCORABLE_COMPONENTS) as $component) {
            $scorableInteractionCount += $doc->getDocumentComponent()->getComponentsByClassName($component, true)->count();
        }

        if ($scorableInteractionCount == 0) {
            return false;
        }

        return true;
    }

    /**
     * Get the cache used to keep evaluated delivery execution
     *
     * @return \common_cache_Cache
     */
    protected function getCache()
    {
        return $this->getServiceLocator()->get($this->getOption(self::OPTION_CACHE));
    }

    /**
     * Get a serial based on $identifier.
     *
     * Used to cache if delivery execution is scorable
     *
     * @param $identifier
     * @return string
     */
    protected function getScorableSerial($identifier)
    {
        return 'ResultFilter_' . $identifier . '_' . self::ONLY_SCORABLE;
    }

    /**
     * Get item service
     *
     * @return \taoItems_models_classes_ItemsService
     */
    protected function getItemService()
    {
        return \taoItems_models_classes_ItemsService::singleton();
    }
}
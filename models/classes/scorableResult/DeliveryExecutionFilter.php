<?php

namespace oat\taoResultServer\models\classes\scorableResult;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoResultServer\models\classes\ResultServerService;
use qtism\data\storage\xml\XmlDocument;

class DeliveryExecutionFilter extends  ConfigurableService
{
    const SERVICE_ID = 'taoResultServer/scorableResultFilter';

    const OPTION_CACHE = 'cache';
    const ONLY_SCORABLE = 'onlyScorable';
    const OPTION_SCORABLE_COMPONENTS = 'scorable-components';

    use OntologyAwareTrait;

    public function __construct(array $options = array())
    {
        $options = [
            'cache' => 'generis/cache',
            self::OPTION_SCORABLE_COMPONENTS => [
                'extendedTextInteraction'
            ]
        ];
        parent::__construct($options);
    }

    public function filter(array $deliveryExecutions, $options = [])
    {
        $this->getCache()->purge();
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
                if (!$this->getCache()->has($cacheSerial)) {
                    $this->getCache()->put(
                        $this->isScorableDeliveryExecution($deliveryExecutionIdentifier, $deliveryIdentifier),
                        $cacheSerial
                    );
                }
                if ($this->getCache()->get($cacheSerial) === true) {
                    $scorableDeliveryExecutions[] = $deliveryExecution;
                }
            } else {
                $scorableDeliveryExecutions[] = $deliveryExecution;
            }

        }
        return $scorableDeliveryExecutions;
    }

    /**
     * @return \common_cache_Cache
     */
    protected function getCache()
    {
        return $this->getServiceLocator()->get($this->getOption(self::OPTION_CACHE));
    }

    /**
     * @param $deliveryExecutionIdentifier
     * @param $deliveryIdentifier
     * @return bool
     * @throws \common_Exception
     * @throws \common_cache_NotFoundException
     * @throws \common_exception_Error
     * @throws \core_kernel_persistence_Exception
     * @throws \qtism\data\storage\xml\XmlStorageException
     */
    protected function  isScorableDeliveryExecution($deliveryExecutionIdentifier, $deliveryIdentifier)
    {

        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);

        $resultStorage = $resultService->getResultStorage($deliveryIdentifier);
        $calls = $resultStorage->getRelatedItemCallIds($deliveryExecutionIdentifier);

        foreach ($calls as $callId) {
            $results = $resultStorage->getVariables($callId);


            foreach ($results as $itemResult) {
                $variable = $itemResult[count($itemResult)-1];

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

    protected function getScorableSerial($identifier)
    {
        return 'ResultFilter_' . $identifier . '_' . self::ONLY_SCORABLE;
    }

    protected function getItemService()
    {
        return \taoItems_models_classes_ItemsService::singleton();
    }
}
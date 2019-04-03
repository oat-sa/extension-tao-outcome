<?php

namespace oat\taoResultServer\models\classes;

use qtism\data\state\OutcomeDeclaration;
use qtism\data\storage\xml\XmlDocument;

class CrudScorableResultsService extends CrudResultsService
{
    protected $cache = [];

    protected function getDeliveryExecutions($delivery)
    {
        $deliveryExecutions = parent::getDeliveryExecutions($delivery);
        return $this->filterScorableResults($deliveryExecutions);
    }

    protected function getDeliveryExecution($uri)
    {
        $deliveryExecution = parent::getDeliveryExecution($uri);
        return $this->filterScorableResults([$deliveryExecution]);
    }

    protected function filterScorableResults($deliveryExecutions)
    {
        $scorableDeliveryExecutions = [];
        foreach ($deliveryExecutions as $deliveryExecution) {

            if (!array_key_exists($deliveryExecution['deliveryResultIdentifier'], $this->cache)) {
                $this->cache[$deliveryExecution['deliveryResultIdentifier']] =
                    $this->isScorableDeliveryExecution($deliveryExecution['deliveryResultIdentifier'], $deliveryExecution['deliveryIdentifier']);
            }

            if ($this->cache[$deliveryExecution['deliveryResultIdentifier']] === true) {
                $scorableDeliveryExecutions[] = $deliveryExecution;
            }
        }
        return $scorableDeliveryExecutions;
    }

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

//                if (!array_key_exists($variable->item, $this->cache)) {
//                    $this->cache[$variable->item] = $this->isScorableItemResult($variable->item);
//                }
//
//                if ($this->cache[$variable->item] === true) {
//                    return true;
//                }
                if ($this->isScorableItemResult($variable->item) === true) {
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
        $item = new \core_kernel_classes_Resource($itemRef);
        if (!$item->exists()) {
            return false;
        }

        $service = \taoItems_models_classes_ItemsService::singleton();
        $file = $service->getItemDirectory($item)->getFile('qti.xml');

        // Check version
        $doc = new XmlDocument('2.1');
        $doc->loadFromString($file->read());

        // Create a service to define rules for scorable interaction
        $scorableInteractionCollection = $doc->getDocumentComponent()->getComponentsByClassName('extendedTextInteraction', true);
        if ($scorableInteractionCollection->count() == 0) {
            return false;
        }

        // Create a service to define rules for scorable outcomeDeclaration
        $outcomeDeclarationCollection = $doc->getDocumentComponent()->getComponentsByClassName('outcomeDeclaration', true);
        /** @var OutcomeDeclaration $outcomeDeclaration */
        foreach ($outcomeDeclarationCollection as $outcomeDeclaration) {
            $identifier = $outcomeDeclaration->getIdentifier();
            if (in_array(strtolower($identifier), ['score', 'maxscore'])) {
                continue;
            }

            \common_Logger::w(print_r(
                $file->getPrefix() . ' => ' . $scorableInteractionCollection->current()->getPrompt()->getContent()->current()->getContent() . ' (' . $identifier . ')'
            , true));

        }

        return true;
    }

}
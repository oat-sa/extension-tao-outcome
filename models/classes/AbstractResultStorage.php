<?php

namespace oat\taoResultServer\models\classes;

use oat\oatbox\service\ConfigurableService;

abstract class AbstractResultStorage extends ConfigurableService implements ResultServerService
{
    /**
     * Starts or resume a taoResultServerStateFull session for results submission
     *
     * @param $compiledDelivery
     * @param $executionIdentifier
     * @param array $options additional result server options @see \taoResultServer_models_classes_ResultServer::__construct()
     * @throws \common_Exception
     * @throws
     */
    public function initResultServer($compiledDelivery, $executionIdentifier, $options = [])
    {
        $rs = $this->getResultServer($executionIdentifier, null, $options);

        $rs->getStorageInterface()->spawnResult($executionIdentifier);
        \common_Logger::i('Spawning/resuming result identifier related to process execution ' . $executionIdentifier);

        //link test taker identifier with results
        $rs->getStorageInterface()->storeRelatedTestTaker($executionIdentifier, \common_session_SessionManager::getSession()->getUserUri());


        //link delivery identifier with results
        $rs->getStorageInterface()->storeRelatedDelivery($executionIdentifier, $compiledDelivery->getUri());
    }

    abstract public function getResultStorage($deliveryId);

    abstract protected function getResultServer($executionIdentifier = null, $compiledDelivery = null, array $options = []);

}

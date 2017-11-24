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
namespace oat\taoResultServer\models\classes\implementation;

use taoResultServer_models_classes_ResultServerStateFull;
use oat\generis\model\OntologyAwareTrait;
use oat\taoResultServer\models\classes\ResultServiceTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoResultServer\models\classes\ResultServerService as ResultServerServiceInterface;

/**
 * Class ResultServerService
 *
 * Configuration example (taoResultServer/resultservice.conf.php):
 * ```php
 *
 * use oat\taoResultServer\models\classes\implementation\ResultServerService;
 * return new ResultServerService([
 *     ResultServerService::OPTION_RESULT_STORAGE => 'taoOutcomeRds/RdsResultStorage'
 * ]);
 *
 * ```
 *
 * @package oat\taoResultServer\models\classes\implementation
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ResultServerService extends ConfigurableService implements ResultServerServiceInterface
{
    use OntologyAwareTrait;
    use ResultServiceTrait;

    const OPTION_RESULT_STORAGE = 'result_storage';

    /**
     * Starts or resume a taoResultServerStateFull session for results submission
     *
     * @param $compiledDelivery
     * @param $executionIdentifier
     * @param array $options additional result server options @see \taoResultServer_models_classes_ResultServer::__construct()
     * @throws
     */
    public function initResultServer($compiledDelivery, $executionIdentifier, $options = [])
    {
        taoResultServer_models_classes_ResultServerStateFull::singleton()->initResultServer($this->getOption(self::OPTION_RESULT_STORAGE), $options);
        taoResultServer_models_classes_ResultServerStateFull::singleton()->spawnResult($executionIdentifier, $executionIdentifier);
        \common_Logger::i("Spawning/resuming result identifier related to process execution ".$executionIdentifier);

        //link test taker identifier with results
        taoResultServer_models_classes_ResultServerStateFull::singleton()->storeRelatedTestTaker(\common_session_SessionManager::getSession()->getUserUri());
    
        //link delivery identifier with results
        taoResultServer_models_classes_ResultServerStateFull::singleton()->storeRelatedDelivery($compiledDelivery->getUri());
    }
    
    /**
     * Returns the storage engine of the result server
     *
     * @param string $deliveryId
     * @throws \common_exception_Error
     * @return \taoResultServer_models_classes_WritableResultStorage
     */
    public function getResultStorage($deliveryId)
    {
        $resultServerId = $this->getOption(self::OPTION_RESULT_STORAGE);
        return $this->instantiateResultStorage($resultServerId);
    }
}
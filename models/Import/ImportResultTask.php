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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoResultServer\models\Import;

use common_exception_Error;
use JsonSerializable;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\event\EventManager;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Events\DeliveryExecutionResultsRecalculated;
use Throwable;
use taoResultServer_models_classes_ReadableResultStorage as ReadableResultStorage;

class ImportResultTask extends AbstractAction implements TaskAwareInterface, JsonSerializable
{
    use ServiceManagerAwareTrait;
    use TaskAwareTrait;
    use OntologyAwareTrait;

    public const PARAM_IMPORT_JSON = 'importJson';

    public function __invoke($params = [])
    {
        $importResult = ImportResultInput::fromJson($params[self::PARAM_IMPORT_JSON]);
        $logger = $this->getLogger();

        try {
            $this->getQtiResultXmlImporter()->importByResultInput($importResult);

            if ($importResult->isSendAgs()) {
                $this->triggerAgsResultSend($importResult);
            }
        } catch (Throwable $exception) {
            $message = sprintf(
                'Error importing result for delivery execution %s, outcomes: %s, error: %s',
                $importResult->getDeliveryExecutionId(),
                var_export($importResult->getOutcomes(), true),
                $exception->getMessage()
            );

            $logger->error($message);

            return Report::createError($message);
        }

        $message = sprintf(
            'Results imported for delivery execution %s, outcomes: %s',
            $importResult->getDeliveryExecutionId(),
            var_export($importResult->getOutcomes(), true)
        );

        $logger->info($message);

        return Report::createSuccess($message);
    }

    public function jsonSerialize()
    {
        return __CLASS__;
    }

    private function triggerAgsResultSend(ImportResultInput $importResult): void
    {
        $deliveryExecution = $this->getDeliveryExecutionService()
            ->getDeliveryExecution($importResult->getDeliveryExecutionId());

        $variables = $this->getResultsStorage()->getVariables($deliveryExecution->getIdentifier());
        $variableObjects = array_map(
            static function (array $variableObject) {
                return current($variableObject)->variable;
            },
            $variables
        );

        $scoreTotal = null;
        $scoreTotalMax = null;

        foreach ($variableObjects as $variable) {
            if ($variable->getIdentifier() === 'SCORE_TOTAL') {
                $scoreTotal = (float)$variable->getValue();
                continue;
            }

            if ($variable->getIdentifier() === 'SCORE_TOTAL_MAX') {
                $scoreTotalMax = (float)$variable->getValue();
                continue;
            }

            if ($scoreTotal !== null && $scoreTotalMax !== null) {
                break;
            }
        }

        //@TODO It is not getting real score total
        var_dump($scoreTotal, $scoreTotalMax);//FIXME
        exit('SCORE TOTAL');//FIXME

        $this->getEventManager()->trigger(
            new DeliveryExecutionResultsRecalculated($deliveryExecution, $scoreTotal, $scoreTotalMax)
        );
    }

    /**
     * @throws common_exception_Error
     * @throws InvalidServiceManagerException
     */
    private function getResultsStorage(): ReadableResultStorage
    {
        $storage = $this->getResultServerService()->getResultStorage();

        if (!$storage instanceof ReadableResultStorage) {
            throw new common_exception_Error('Configured result storage is not writable.');
        }

        return $storage;
    }

    private function getQtiResultXmlImporter(): QtiResultXmlImporter
    {
        return $this->getServiceManager()->get(QtiResultXmlImporter::class);
    }

    private function getResultServerService(): ResultServerService
    {
        return $this->getServiceManager()->get(ResultServerService::SERVICE_ID);
    }

    private function getEventManager(): EventManager
    {
        return $this->getServiceManager()->get(EventManager::SERVICE_ID);
    }

    private function getDeliveryExecutionService(): DeliveryExecutionService
    {
        return $this->getServiceManager()->get(DeliveryExecutionService::SERVICE_ID);
    }
}

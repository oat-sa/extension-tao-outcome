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

namespace oat\taoResultServer\models\Import\Task;

use JsonSerializable;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use oat\taoResultServer\models\Import\Service\QtiResultXmlImporter;
use oat\taoResultServer\models\Import\Service\SendCalculatedResultService;
use Throwable;

class ImportResultTask extends AbstractAction implements TaskAwareInterface, JsonSerializable
{
    use ServiceManagerAwareTrait;
    use TaskAwareTrait;

    public const PARAM_IMPORT_JSON = 'importJson';

    public function __invoke($params = [])
    {
        $logger = $this->getLogger();

        try {
            $importResult = ImportResultInput::fromJson($params[self::PARAM_IMPORT_JSON]);

            $this->getQtiResultXmlImporter()->importByResultInput($importResult);

            if ($importResult->isSendAgs()) {
                $this->getSendCalculatedResultService()->sendByDeliveryExecutionId(
                    $importResult->getDeliveryExecutionId()
                );
            }

            $message = sprintf(
                'Results imported for delivery execution %s, outcomes: %s',
                $importResult->getDeliveryExecutionId(),
                var_export($importResult->getOutcomes(), true)
            );

            $logger->info($message);

            return Report::createSuccess($message);
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
    }

    public function jsonSerialize()
    {
        return __CLASS__;
    }

    private function getQtiResultXmlImporter(): QtiResultXmlImporter
    {
        return $this->getServiceManager()->getContainer()->get(QtiResultXmlImporter::class);
    }

    private function getSendCalculatedResultService(): SendCalculatedResultService
    {
        return $this->getServiceManager()->getContainer()->get(SendCalculatedResultService::class);
    }
}

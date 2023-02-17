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

use JsonSerializable;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Throwable;

class ImportResultTask extends AbstractAction implements TaskAwareInterface, JsonSerializable
{
    use ServiceManagerAwareTrait;
    use TaskAwareTrait;
    use OntologyAwareTrait;

    public const PARAM_IMPORT_JSON = 'importJson';

    public function __invoke($params = [])
    {
        $params = [];

        $importResult = ImportResultInput::fromJson($params[self::PARAM_IMPORT_JSON]);
        $logger = $this->getLogger();

        try {
            $this->getQtiResultXmlImporter()->createByImportResult($importResult);

            //@TODO Send Ags...
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

    private function getQtiResultXmlImporter(): QtiResultXmlFactory
    {
        return $this->getServiceManager()->get(QtiResultXmlImporter::class);
    }
}

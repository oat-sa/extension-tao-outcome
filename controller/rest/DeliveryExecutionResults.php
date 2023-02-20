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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoResultServer\controller\rest;

use common_exception_MissingParameter;
use common_exception_ResourceNotFound;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\taoResultServer\models\Import\Task\ResultImportScheduler;
use tao_actions_RestController;
use Throwable;

class DeliveryExecutionResults extends tao_actions_RestController
{
    use EventManagerAwareTrait;
    use HttpJsonResponseTrait;

    private const Q_PARAM_TRIGGER_AGS_SEND = 'send_ags';

    public function patch(): void
    {
        $queryParams = $this->getPsrRequest()->getQueryParams();

        try {
            $this->logInfo(
                sprintf(
                    '[DeliveryExecutionResults] requested with params: %s',
                    var_export($queryParams, true)
                )
            );

            $task = $this->getResultImportScheduler()->scheduleByRequest($this->getPsrRequest());

            $this->setSuccessJsonResponse(
                [
                    'agsNotificationTriggered' => isset($queryParams[self::Q_PARAM_TRIGGER_AGS_SEND]) &&
                        $queryParams[self::Q_PARAM_TRIGGER_AGS_SEND] !== 'false',
                    'taskId' => $task->getId()
                ]
            );

            $this->logInfo(
                sprintf(
                    '[DeliveryExecutionResults] successfully scheduled with params [%s]',
                    var_export($queryParams, true)
                )
            );
        } catch (common_exception_MissingParameter $e) {
            $this->setErrorJsonResponse($e->getMessage());
        } catch (common_exception_ResourceNotFound $e) {
            $this->setErrorJsonResponse($e->getMessage(), 0, [], 404);
        } catch (Throwable $e) {
            $this->setErrorJsonResponse(sprintf('Internal error: %s', $e->getMessage()), 0, [], 500);
        } finally {
            if (isset($e)) {
                $this->logError(
                    sprintf(
                        '[DeliveryExecutionResults] Error "%s" requesting with params [%s]',
                        $e->__toString(),
                        var_export($queryParams, true)
                    )
                );
            }
        }
    }

    private function getResultImportScheduler(): ResultImportScheduler
    {
        return $this->getServiceManager()->getContainer()->get(ResultImportScheduler::class);
    }
}

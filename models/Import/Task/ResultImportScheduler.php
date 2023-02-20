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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\Import\Task;

use common_exception_MissingParameter;
use common_exception_NotFound;
use common_exception_ResourceNotFound;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\taoResultServer\models\Import\Factory\ImportResultInputFactory;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use Psr\Http\Message\ServerRequestInterface;

class ResultImportScheduler
{
    private QueueDispatcher $dispatcher;
    private ImportResultInputFactory $importResultInputFactory;

    public function __construct(QueueDispatcher $dispatcher, ImportResultInputFactory $importResultInputFactory)
    {
        $this->dispatcher = $dispatcher;
        $this->importResultInputFactory = $importResultInputFactory;
    }

    /**
     * @throws common_exception_MissingParameter
     * @throws common_exception_NotFound
     * @throws common_exception_ResourceNotFound
     */
    public function scheduleByRequest(ServerRequestInterface $request): TaskInterface
    {
        return $this->schedule($this->importResultInputFactory->createFromRequest($request));
    }

    public function schedule(ImportResultInput $input): TaskInterface
    {
        return $this->dispatcher->createTask(
            new ImportResultTask(),
            [
                ImportResultTask::PARAM_IMPORT_JSON => $input->jsonSerialize()
            ],
            'Import Delivery Execution results'
        );
    }
}

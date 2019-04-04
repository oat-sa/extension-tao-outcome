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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoResultServer\scripts\install;


use common_Exception;
use common_report_Report as Report;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\classes\OutcomeFileSystemStorage\OutcomeFilesystemRepository;

class InstallFileStorage extends InstallAction
{
    /**
     * @param $params
     *
     * @return Report
     * @throws common_Exception
     * @throws InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $resultServerService = $this->getServiceManager()->get(ResultServerService::SERVICE_ID);

        $resultStorage = $resultServerService->getOption(ResultServerService::OPTION_RESULT_STORAGE);

        $fileSystemName = 'taoResultServer';

        $outcomeFileSystemRepository = new OutcomeFilesystemRepository(
            [
                OutcomeFilesystemRepository::OPTION_STORAGE    => $resultStorage,
                OutcomeFilesystemRepository::OPTION_FILESYSTEM => $fileSystemName
            ]
        );

        $resultServerService->setOption(
            ResultServerService::OPTION_RESULT_STORAGE,
            OutcomeFilesystemRepository::SERVICE_ID
        );

        $this->getServiceManager()->register(OutcomeFilesystemRepository::SERVICE_ID, $outcomeFileSystemRepository);
        $this->getServiceManager()->register(ResultServerService::SERVICE_ID, $resultServerService);

        /** @var FileSystemService $fileSystemService */
        $fileSystemService = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);

        $fileSystemService->createFileSystem($fileSystemName);
        $this->getServiceManager()->register(FileSystemService::SERVICE_ID, $fileSystemService);

        return new Report(Report::TYPE_SUCCESS, 'File system storage enabled.');
    }
}

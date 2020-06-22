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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoResultServer\scripts\update;

use common_ext_ExtensionUpdater;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\classes\QtiResultsService;
use oat\taoResultServer\models\classes\ResultAliasService;
use oat\taoResultServer\models\classes\ResultService;

/**
 *
 * @author Joel Bout <joel@taotesting.com>
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @deprecated use migrations instead. See https://github.com/oat-sa/generis/wiki/Tao-Update-Process
 */
class Updater extends common_ext_ExtensionUpdater
{

    /**
     * @inheritDoc
     */
    public function update($initialVersion)
    {

        $this->skip('2.6', '2.11.2');

        if ($this->isVersion('2.11.2')) {
            $this->getServiceManager()->register(QtiResultsService::SERVICE_ID, new QtiResultsService());
            $this->setVersion('2.12.0');
        }

        $this->skip('2.12.0', '3.2.0');
        if ($this->isVersion('3.2.0')) {
            $service = $this->getServiceManager()->get(QtiResultsService::SERVICE_ID);
            if (!$service instanceof ResultService) {
                $this->getServiceManager()->register(QtiResultsService::SERVICE_ID, new QtiResultsService());
            }
            $this->setVersion('3.2.1');
        }

        $this->skip('3.2.1', '3.3.1');

        if ($this->isVersion('3.3.1')) {
            $this->getServiceManager()->register(ResultAliasService::SERVICE_ID, new ResultAliasService());
            $this->setVersion('3.4.0');
        }

        $this->skip('3.4.0', '5.0.2');

        if ($this->isVersion('5.0.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('5.1.0');
        }

        $this->skip('5.1.0', '11.0.1');

        if ($this->isVersion('11.0.1')) {
            $resultServerService = $this->safeLoadService(ResultServerService::SERVICE_ID);
            if (get_class($resultServerService) === 'oat\taoResultServer\models\classes\implementation\OntologyService') {
                $resultService = new ResultServerService([
                    ResultServerService::OPTION_RESULT_STORAGE => 'taoOutcomeRds/RdsResultStorage'
                ]);
                $this->getServiceManager()->register(ResultServerService::SERVICE_ID, $resultService);
            }
            OntologyUpdater::syncModels();
            $this->setVersion('12.0.0');
        }

        $this->skip('12.0.0', '12.1.1');
        
        //Updater files are deprecated. Please use migrations.
        //See: https://github.com/oat-sa/generis/wiki/Tao-Update-Process

        $this->setVersion($this->getExtension()->getManifest()->getVersion());
    }
}

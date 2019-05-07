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
 * Copyright (c) 2014-2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoResultServer\scripts\update;

use oat\tao\scripts\update\OntologyUpdater;
use oat\taoResultServer\models\classes\DeliveryExecutionFilter;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\implementation\OntologyService;
use oat\taoResultServer\models\classes\QtiResultsService;
use oat\taoResultServer\models\classes\ResultService;
use oat\taoResultServer\models\classes\ResultAliasService;

/**
 *
 * @author Joel Bout <joel@taotesting.com>
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater
{
    /**
     * Perform update for taoResultServer extension
     *
     * @param string $initialVersion
     * @return string|void
     * @throws \common_Exception
     */
    public function update($initialVersion)
    {

        $this->skip('2.6', '2.10.2');

        if ($this->isVersion('2.10.2')) {
            $this->getServiceManager()->register(ResultServerService::SERVICE_ID, new OntologyService());
            $this->setVersion('2.11.0');
        }

        $this->skip('2.11.0', '2.11.2');

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

        $this->skip('5.1.0', '9.2.0');

        if ($this->isVersion('9.2.0')) {
            $this->getServiceManager()->register(DeliveryExecutionFilter::SERVICE_ID, new DeliveryExecutionFilter([
                DeliveryExecutionFilter::OPTION_CACHE => 'generis/cache',
                DeliveryExecutionFilter::OPTION_SCORABLE_COMPONENTS => [
                    'extendedTextInteraction'
                ]
            ]));
            $this->setVersion('9.3.0');
        }

    }
}

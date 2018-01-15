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
 * Copyright (c) 2014-2016 (original work) Open Assessment Technologies SA;
 *
 *
 */

use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\implementation\OntologyService;
use oat\taoResultServer\models\classes\QtiResultsService;
use oat\taoResultServer\models\classes\ResultService;
use oat\taoResultServer\models\classes\ResultAliasService;
use oat\taoResultServer\models\classes\search\ResultsDataProvider;
use oat\taoResultServer\models\classes\search\ResultsWatcher;
use oat\oatbox\event\EventManager;
use oat\tao\model\search\dataProviders\DataProvider;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\tao\model\search\dataProviders\SearchDataProvider;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;

/**
 * 
 * @author Joel Bout <joel@taotesting.com>
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 */
class taoResultServer_scripts_update_Updater extends \common_ext_ExtensionUpdater {

    /**
     *
     * @param string $initialVersion
     * @return string|void
     */
    public function update($initialVersion) {

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

        $this->skip('3.4.0', '5.0.1');

        if ($this->isVersion('5.0.1')) {
            $options = [
                DataProvider::INDEXES_MAP_OPTION => [
                    ResultService::DELIVERY_RESULT_CLASS_URI => [
                        DataProvider::SEARCH_CLASS_OPTION => DeliveryAssemblyService::CLASS_URI,
                        DataProvider::LABEL_CLASS_OPTION => 'results',
                        DataProvider::FIELDS_OPTION  => [
                            'label',
                            'resource_link_id'
                        ]
                    ]
                ]
            ];
            $resultsDataProvider = new ResultsDataProvider($options);
            $this->getServiceManager()->register(ResultsDataProvider::SERVICE_ID, $resultsDataProvider);
            $this->getServiceManager()->register(ResultsWatcher::SERVICE_ID, new ResultsWatcher());

            /** @var SearchDataProvider $searchDataProvider */
            $searchDataProvider = $this->getServiceLocator()->get(SearchDataProvider::SERVICE_ID);
            $options = $searchDataProvider->getOption(SearchDataProvider::PROVIDERS_OPTION);
            $options[] = ResultsDataProvider::SERVICE_ID;
            $searchDataProvider->setOption(SearchDataProvider::PROVIDERS_OPTION, $options);
            $this->getServiceManager()->register(SearchDataProvider::SERVICE_ID, $searchDataProvider);

            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
            $eventManager->attach(DeliveryExecutionCreated::class, [ResultsWatcher::SERVICE_ID, 'catchCreatedDeliveryExecutionEvent']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
            $this->setVersion('5.1.0');
        }
    }
}

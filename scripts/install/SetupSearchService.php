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
 * Copyright (c) 2018 Open Assessment Technologies SA
 *
 */

namespace oat\taoResultServer\scripts\install;

use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\search\dataProviders\SearchDataProvider;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoResultServer\models\classes\ResultService;
use oat\taoResultServer\models\classes\search\ResultsDataProvider;
use oat\taoResultServer\models\classes\search\ResultsWatcher;

class SetupSearchService extends InstallAction
{
    public function __invoke($params)
    {
        $options = [
            'indexesMap' => [
                ResultService::DELIVERY_RESULT_CLASS_URI => [
                    'fields' => [
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
    }

}
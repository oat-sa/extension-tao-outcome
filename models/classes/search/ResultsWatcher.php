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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoResultServer\models\classes\search;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\Search;
use oat\tao\model\search\SearchService;
use oat\tao\model\search\tasks\AddSearchIndex;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoTaskQueue\model\QueueDispatcher;


class ResultsWatcher extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'taoResultServer/ResultsWatcher';

    /**
     * @param DeliveryExecutionCreated $event
     * @return \common_report_Report
     * @throws \common_exception_NotFound
     */
    public function catchCreatedDeliveryExecutionEvent(DeliveryExecutionCreated $event)
    {
        /** @var DeliveryExecutionInterface $resource */
        $deliveryExecution = $event->getDeliveryExecution();

        /** @var Search $searchService */
        $searchService = SearchService::getSearchImplementation();
        $report = \common_report_Report::createSuccess();
        if ($searchService->supportCustomIndex()) {
            /** @var QueueDispatcher $queueDispatcher */
            $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcher::SERVICE_ID);

            $customData = $this->getCustomData($deliveryExecution);
            $taskReport = $queueDispatcher->createTask(new AddSearchIndex(), [$deliveryExecution->getIdentifier(), ResultsDataProvider::SERVICE_ID, $customData], __('Adding/Updating search index for result %s', $deliveryExecution->getLabel()));
            $report->add($taskReport);
        }

        return $report;

    }

    protected function getCustomData(DeliveryExecutionInterface $deliveryExecution)
    {
        return [
            'label' => $deliveryExecution->getLabel(),
            'delivery' => $deliveryExecution->getDelivery()->getUri(),
            'test_taker' => $deliveryExecution->getUserIdentifier()
        ];
    }

}

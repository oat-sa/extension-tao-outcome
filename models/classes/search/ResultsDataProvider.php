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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoResultServer\models\classes\search;

use oat\oatbox\service\ConfigurableService;
use oat\search\ResultSet;
use oat\tao\model\search\dataProviders\DataProvider;
use oat\tao\model\search\document\IndexDocument;
use oat\tao\model\search\SearchService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\ResultService;

/**
 * Class ResultsDataProvider
 * @package oat\tao\model\search\dataProviders
 */
class ResultsDataProvider extends ConfigurableService implements DataProvider
{
    const SERVICE_ID = 'taoResultServer/SearchResults';

    const SEARCH_DATA_PROVIDER_NAME = 'results';
    const SEARCH_DATA_OPTION_LABEL = 'label';
    const SEARCH_DATA_OPTION_DELIVERY = 'delivery';
    const SEARCH_DATA_OPTION_TEST_TAKER = 'test_taker';


    protected $tokenGenerator;
    protected $map;

    /**
     * @return string
     */
    public function getIndexPrefix()
    {
        return 'documents';
    }

    /**
     * @param      $queryString
     * @param null $rootClass
     * @param int  $start
     * @param int  $count
     * @return mixed|ResultSet
     */
    public function query($queryString, $rootClass = null, $start = 0, $count = 10)
    {
        $search = SearchService::getSearchImplementation();

        /** @var ResultSet $results */
        $results = $search->query($queryString, $rootClass, $start = 0, $count = 10);

        return $results;
    }


    /**
     * @param       $id
     * @param array $customBody
     * @return mixed|void
     */
    public function addIndex($id, $customBody = [])
    {
        $index = $this->getIndexPrefix().'-'.self::SEARCH_DATA_PROVIDER_NAME;
        $document = new IndexDocument(
            $id,
            $customBody['delivery'],
            $index,
            self::SERVICE_ID,
            ResultService::DELIVERY_RESULT_CLASS_URI,
            'document',
            $customBody);

        SearchService::getSearchImplementation()->index($document);
    }

    public function getResults($result)
    {
        return $result;
    }

    public function prepareDataForIndex($resourceTraversable = null)
    {
        $data = [];

        $deliveryService = DeliveryAssemblyService::singleton();
        $deliveryClass = $deliveryService->getRootClass();
        $deliveries = $deliveryClass->getInstances(true);

        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $index = $this->getIndexPrefix().'-'.self::SEARCH_DATA_PROVIDER_NAME;

        /** @var \core_kernel_classes_Resource $delivery */
        foreach ($deliveries as $delivery) {
            $implementation = $resultService->getResultStorage($delivery->getUri());
            foreach($implementation->getResultByDelivery(array($delivery->getUri())) as $result){
                $id = isset($result['deliveryResultIdentifier']) ? $result['deliveryResultIdentifier'] : null;
                if ($id) {
                    $deliveryResource = new \core_kernel_classes_Resource($result['deliveryIdentifier']);
                    $label = '';
                    if ($deliveryResource) {
                        $label = $deliveryResource->getLabel();
                    }
                    $body = [
                        self::SEARCH_DATA_OPTION_DELIVERY => $result['deliveryIdentifier'],
                        self::SEARCH_DATA_OPTION_TEST_TAKER => $result['testTakerIdentifier'],
                        self::SEARCH_DATA_OPTION_LABEL => $label
                    ];

                    $document = new IndexDocument(
                        $id,
                        $delivery->getUri(),
                        $index,
                        self::SERVICE_ID,
                        ResultService::DELIVERY_RESULT_CLASS_URI,
                        'document',
                        $body);
                    $data[] = $document;
                }
            }
        }

        return $data;
    }

    public function needIndex(\core_kernel_classes_Resource $resource)
    {
        return true;
    }
}

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

namespace oat\taoResultServer\test\integration\experiment;

use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\taoResultServer\models\classes\QtiResultsService;

class ResultInjection extends TestCase
{
    /**
     * Experiment to test the injection in a delivery execution
     * $deliveryExecutionId variable should be an existing DE
     */
    public function testIt()
    {
        $deliveryExecutionId = 'http://www.taotesting.com/ontologies/community.rdf#i5da7282de056c2548249cfaf808c9cb75e';

        $qtiResultService = new QtiResultsService();
        $qtiResultService->setServiceLocator(ServiceManager::getServiceManager());
        $xml = file_get_contents(__DIR__ . '/../../resources/result/qti-result-de.xml');
        $qtiResultService->injectXmlResultToDeliveryExecution($deliveryExecutionId, $xml);
    }
}
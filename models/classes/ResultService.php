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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use oat\oatbox\PhpSerializable;
use oat\taoDelivery\model\execution\DeliveryExecution as DeliveryExecutionInterface;

interface ResultService extends PhpSerializable
{
    const SERVICE_ID = 'taoResultServer/qtiResultsService';

    const CONFIG_ID = 'qtiResultsService';

    const DELIVERY_RESULT_CLASS_URI ='http://www.tao.lu/Ontologies/TAOResult.rdf#DeliveryResult';

    const SUBJECT_CLASS_URI ='http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfSubject';

    const DELIVERY_CLASS_URI ='http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfDelivery';

    /**
     * Get last delivery execution from $delivery & $testtaker uri
     *
     * @param $delivery
     * @param $testtaker
     * @return mixed
     * @throws
     */
    public function getDeliveryExecutionByTestTakerAndDelivery($delivery, $testtaker);

    /**
     * Get Delivery execution from resource
     *
     * @param $deliveryExecutionId
     * @return mixed
     * @throws \common_exception_NotFound
     */
    public function getDeliveryExecutionById($deliveryExecutionId);

    /**
     * Return delivery execution as xml of testtaker based on delivery
     *
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return string
     */
    public function getDeliveryExecutionXml(DeliveryExecutionInterface $deliveryExecution);

    /**
     * Get Qti Result depending on deliveryId & resultId
     *
     * @param $deliveryId
     * @param $resultId
     * @return mixed
     */
    public function getQtiResultXml($deliveryId, $resultId);
}
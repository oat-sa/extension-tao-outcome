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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\taoResultServer\models\classes;

interface ResultManagement extends \taoResultServer_models_classes_ReadableResultStorage {

    /**
     * Get only one property from a variable
     * @param $variableId
     * @param $property
     * @return mixed
     */
    public function getVariableProperty($variableId, $property);

    /**
     * @param $deliveryResultIdentifier
     * @return array the list of item executions ids (across all results)
     */
    public function getRelatedItemCallIds($deliveryResultIdentifier);

    /**
     * @param $columns list of columns on which to search array('http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfSubject','http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfDelivery')
     * @param $filter list of value to search array('http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfSubject' => array('test','myValue'))
     * @param $options params to restrict results such as order, order direction, offset and limit
     * @return mixed test taker, delivery and delivery result that match the filter array(array('deliveryResultIdentifier' => '123', 'testTakerIdentifier' => '456', 'deliveryIdentifier' => '789'))
     */
    public function getResultByColumn($columns, $filter, $options = array());

    /**
     * @param $columns list of columns on which to search array('http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfSubject','http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfDelivery')
     * @param $filter list of value to search array('http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfSubject' => array('test','myValue'))
     * @return int the number of results that match filter
     */
    public function countResultByFilter($columns, $filter);

    /**
     * Get the item from itemResult
     * @param $itemResult
     * @return core_kernel_classes_Resource
     */
    public function getItemFromItemResult($itemResult);

    /**
     * Remove the result and all the related variables
     * @param $deliveryResultIdentifier
     * @return bool
     */
    public function deleteResult($deliveryResultIdentifier);

    /**
     * prepare a data set as an associative array
     * @param $deliveryResultIdentifier
     * @param $filter
     * @return array
     */
    public function getDeliveryItemVariables($deliveryResultIdentifier, $filter);

    /**
     * return all variables linked to the delviery result
     * @param $deliveryResultIdentifier
     * @param $filter
     * @return array
     */
    public function getDeliveryResultVariables($deliveryResultIdentifier);

}
?>
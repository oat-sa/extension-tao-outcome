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

interface taoResultServer_models_classes_ResultManagement extends \taoResultServer_models_classes_ReadableResultStorage {

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
     * @param $filter list of valueto search array('http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfSubject' => array('test','myValue'))
     * @return mixed test taker, delivery and delivery result that match the filter array(array('deliveryResultIdentifier' => '123', 'testTakerIdentifier' => '456', 'deliveryIdentifier' => '789'))
     */
    public function getResultByColumn($columns, $filter);

    /**
     * Remove the result and all the related variables
     * @param $deliveryResultIdentifier
     * @return bool
     */
    public function deleteResult($deliveryResultIdentifier);

   
    
}
?>
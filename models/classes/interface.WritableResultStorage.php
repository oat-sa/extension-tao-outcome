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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

interface taoResultServer_models_classes_WritableResultStorage {

    /**
     * Optionnally spawn a new result and returns
     * an identifier for it, use of the other services with an unknow identifier
     * will trigger the spawning of a new result
     * you may also provide your own identifier to the other services like a lis_result_sourcedid:GUID
     * @return string deliveryResultIdentifier
     */
    public function spawnResult();

    /**
     * Store a test taker related to a specific delivery execution
     * @param string $deliveryResultIdentifier (mostly delivery execution uri)
     * @param string $testTakerIdentifier (uri recommended)
     *
     */
    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier);

    /**
     * Store a delivery related to a specific delivery execution
     * @param string $deliveryResultIdentifier (mostly delivery execution uri)
     * @param string $deliveryIdentifier (uri recommended)
     */
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier);

    /**
     * Submit a specific Item Variable, (ResponseVariable and OutcomeVariable shall be used respectively for collected data and score/interpretation computation)
     * and store it with all the dependencies
     * @param string $deliveryResultIdentifier
     * @param string $test (uri recommended)
     * @param string $item (uri recommended)
     * @param taoResultServer_models_classes_Variable $itemVariable the variable to store
     * @param string $callIdItem contextual call id for the variable, ex. :  to distinguish the same variable output by the same item and that is presented several times in the same test
     * 
     */
    public function storeItemVariable($deliveryResultIdentifier, $test, $item, taoResultServer_models_classes_Variable $itemVariable, $callIdItem );

    /**
     * Submit a specific test Variable and store it
     * @param string $deliveryResultIdentifier
     * @param string $test
     * @param taoResultServer_models_classes_Variable $testVariable
     * @param $callIdTest
     */
    public function storeTestVariable($deliveryResultIdentifier, $test, taoResultServer_models_classes_Variable $testVariable, $callIdTest);


    /**
     * The storage may configure itself based on the resultServer definition
     * @param core_kernel_classes_Resource $resultServer
     * @param array $callOptions
     */
    public function configure(core_kernel_classes_Resource $resultServer, $callOptions = array());
    
}
?>
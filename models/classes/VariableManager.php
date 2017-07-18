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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
 
namespace oat\taoResultServer\models\classes;

/**
 * Variable Manager Interface
 * 
 * Services implementing this interface claim that they are able to extend the behaviour
 * of TAO in terms of Result Variable Storage. By implementing the methods of this interface
 * it is possible to perform additional processing on Result Variables and their content at
 * persistence, retrieval and deletion time.
 */
interface VariableManager
{
    const SERVICE_ID = 'taoResultServer/variableManager';
    
    /**
     * Persist Variable Behaviour
     * 
     * Additional persistence behaviour to be performed on a Result Variable at storage time.
     * 
     * @param \taoResultServer_models_classes_Variable $variable
     * @param string $deliveryResultIdentifier
     * @throws oat\taoResultServer\models\classes\VariableManagementException In case an error occurs.
     */
    public function persist(\taoResultServer_models_classes_Variable $variable, $deliveryResultIdentifier);
    
    /**
     * Retrieve Variable Behaviour
     * 
     * Additional persistence behaviour to be performed on a Result Variable at retrieval time.
     * 
     * @param \taoResultServer_models_classes_Variable $variable
     * @param string $deliveryResultIdentifier
     * @throws oat\taoResultServer\models\classes\VariableManagementException In case an error occurs.
     */
    public function retrieve(\taoResultServer_models_classes_Variable $variable, $deliveryResultIdentifier);
    
    /**
     * Retrieve Variable Property Value Behaviour.
     * 
     * Additional persistence behaviour to be performed on a Result Variable Property Value at retrieval time.
     * 
     * @param \taoResultServer_models_classes_Variable $variable
     * @throws oat\taoResultServer\models\classes\VariableManagementException In case an error occurs.
     */
    public function retrieveProperty($variableBaseType, $propertyName, $propertyValue);
    
    /**
     * Delete Result Behaviour
     * 
     * Additional persistence behaviour to be performed on a whole Result at deletion time.
     * 
     * @param \taoResultServer_models_classes_Variable $variable
     * @throws oat\taoResultServer\models\classes\VariableManagementException In case an error occurs.
     */
    public function delete($deliveryResultIdentifier);
}

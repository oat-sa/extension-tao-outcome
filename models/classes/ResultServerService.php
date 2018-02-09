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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\taoResultServer\models\classes;

use oat\taoDelivery\model\execution\Delete\DeliveryExecutionDelete;

interface ResultServerService extends DeliveryExecutionDelete {

    const SERVICE_ID = 'taoResultServer/resultservice';

    const CLASS_URI	= 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServer';

    const PROPERTY_MODEL = 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerModel';

    const PROPERTY_HAS_MODEL ='http://www.tao.lu/Ontologies/TAOResultServer.rdf#hasResultServerModel';

    const PROPERTY_MODEL_IMPL = 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#implementation';

    const INSTANCE_VOID_RESULT_SERVER = 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#void';

    /**
     * For legacy non stateless storage
     *
     * @param \core_kernel_classes_Resource $compiledDelivery
     * @param string $executionIdentifier
     * @param string $userUri
     */
    public function initResultServer($compiledDelivery, $executionIdentifier, $userUri);

    /**
     * Returns the storage engine of the result server
     *
     * @param string $deliveryId @deprecated. Should be removed after \oat\taoResultServer\models\classes\implementation\OntologyService will be removed
     * @throws \common_exception_Error
     * @return \taoResultServer_models_classes_ReadableResultStorage|\taoResultServer_models_classes_WritableResultStorage|\oat\taoResultServer\models\classes\ResultManagement
     */
    public function getResultStorage($deliveryId);

    public function isConfigurable();

}
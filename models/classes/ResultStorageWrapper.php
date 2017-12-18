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


use oat\taoResultServer\models\classes\implementation\ReadableResultStorage;
use oat\taoResultServer\models\classes\implementation\WritableResultStorage;

class ResultStorageWrapper implements \taoResultServer_models_classes_WritableResultStorage, \taoResultServer_models_classes_ReadableResultStorage
{

    use implementation\WritableResultStorage {
        WritableResultStorage::getWritableStorage as parentGetWritableStorage;
    }

    use implementation\ReadableResultStorage {
        ReadableResultStorage::getReadableStorage as parentGetReadableStorage;

    }

    private $deliveryExecutionIdentifier;
    protected $resultServer;

    /**
     * ResultStorageWrapper constructor.
     * @param $deliveryExecutionIdentifier
     */
    public function __construct($deliveryExecutionIdentifier, $resultServer)
    {
        $this->deliveryExecutionIdentifier = $deliveryExecutionIdentifier;
        $this->resultServer = $resultServer;
    }

    protected function getWritableStorage()
    {
        return $this->parentGetWritableStorage($this->deliveryExecutionIdentifier);
    }


    protected function getReadableStorage()
    {
        return $this->parentGetReadableStorage($this->deliveryExecutionIdentifier);
    }

    protected function getResultServer(){
        return $this->resultServer;
    }

    protected function getDeliveryIdentifier(){
        return $this->deliveryExecutionIdentifier;
    }



}
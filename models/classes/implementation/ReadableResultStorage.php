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

namespace oat\taoResultServer\models\classes\implementation;


use common_exception_NoImplementation;

trait ReadableResultStorage
{

    /** @see \taoResultServer_models_classes_ReadableResultStorage::getVariables() */
    public function getVariables($callId)
    {
        return $this->getReadableStorage()->getVariables($callId);
    }


    /** @see \taoResultServer_models_classes_ReadableResultStorage::getVariable() */
    public function getVariable($callId, $variableIdentifier)
    {
        return $this->getReadableStorage()->getVariable($callId, $variableIdentifier);
    }

    /** @see \taoResultServer_models_classes_ReadableResultStorage::getTestTaker() */
    public function getTestTaker($deliveryResultIdentifier)
    {
        return $this->getReadableStorage($deliveryResultIdentifier)->getTestTaker($deliveryResultIdentifier);
    }

    /** @see \taoResultServer_models_classes_ReadableResultStorage::getDelivery() */
    public function getDelivery($deliveryResultIdentifier)
    {
        return $this->getReadableStorage($deliveryResultIdentifier)->getDelivery($deliveryResultIdentifier);
    }

    /** @see \taoResultServer_models_classes_ReadableResultStorage::getAllCallIds() */
    public function getAllCallIds()
    {
        return $this->getReadableStorage()->getAllCallIds();
    }

    /** @see \taoResultServer_models_classes_ReadableResultStorage::getAllTestTakerIds() */
    public function getAllTestTakerIds()
    {
        return $this->getReadableStorage()->getAllTestTakerIds();
    }

    /** @see \taoResultServer_models_classes_ReadableResultStorage::getAllDeliveryIds() */
    public function getAllDeliveryIds()
    {
        return $this->getReadableStorage()->getAllDeliveryIds();
    }

    /**
     * @param $deliveryResultIdentifier
     * @return \taoResultServer_models_classes_ReadableResultStorage
     * @throws common_exception_NoImplementation
     */
    private function getReadableStorage($deliveryResultIdentifier = null)
    {
        $storage = $this->getStorageInterface($deliveryResultIdentifier);
        if (!$storage instanceof \taoResultServer_models_classes_ReadableResultStorage) {
            throw new common_exception_NoImplementation('No readable support for current storage');
        }
        return $storage;
    }
}
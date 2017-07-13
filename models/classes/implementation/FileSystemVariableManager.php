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

use oat\taoResultServer\models\classes\VariableManager;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\filesystem\FileSystemService;

class FileSystemVariableManager extends ConfigurableService implements VariableManager
{
    public function persist(\taoResultServer_models_classes_Variable $variable)
    {
        if ($variable->baseType === 'file') {
            $fsId = uniqid();
            $fileSystem = $this->getServiceManager()->get(FileSystemService::SERVICE_ID)->getFileSystem('taoResultServer');
            
            if ($variable instanceof \taoResultServer_models_classes_ResponseVariable) {
                $data = $variable->getCandidateResponse();
                $variable->setCandidateResponse($fsId);
                $fileSystem->write($fsId, $data);
            } elseif ($variable instanceof \taoResultServer_models_classes_OutcomeVariable) {
                $data = $variable->getValue();
                $variable->value->setValue($fsId);
                $fileSystem->write($fsId, $data);
            }
        }
    }
    
    public function retrieve(\taoResultServer_models_classes_Variable $variable)
    {
        if ($variable->baseType === 'file') {
            $fileSystem = $this->getServiceManager()->get(FileSystemService::SERVICE_ID)->getFileSystem('taoResultServer');
            
            if ($variable instanceof \taoResultServer_models_classes_ResponseVariable) {
                $fsId = $variable->getCandidateResponse();
                $variable->setCandidateResponse($fileSystem->read($fsId));
            } elseif ($variable instanceof \taoResultServer_models_classes_OutcomeVariable) {
                $fsId = $variable->getValue();
                $variable->setValue($fileSystem->read($fsId));
            }
        }
    }
    
    public function retrieveProperty($variableBaseType, $propertyName, $propertyValue)
    {
        if ($variableBaseType === 'file' && ($propertyName === 'candidateResponse' || $propertyName === 'value')) {
            $fileSystem = $this->getServiceManager()->get(FileSystemService::SERVICE_ID)->getFileSystem('taoResultServer');
            return $fileSystem->read($propertyValue);
        } else {
            return $propertyValue;
        }
    }
     
    public function delete($deliveryResultIdentifier)
    {
        // @todo TO BE IMPLEMENTED LATER ON AFTER FIRST REVIEW.
        return;
    }
}

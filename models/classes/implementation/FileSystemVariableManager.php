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

/**
 * File System Variable Manager
 * 
 * This class aims at managing Results File Values on abstract file systems. Files are stored
 * in the taoResultServer file system.
 */
class FileSystemVariableManager extends ConfigurableService implements VariableManager
{
    public function persist(\taoResultServer_models_classes_Variable $variable, $deliveryResultIdentifier)
    {
        if ($variable->baseType === 'file') {
            $path = self::buildPath(
                $deliveryResultIdentifier,
                self::buildIdentifier()
            );
            
            \common_Logger::i('GENERATED PATH : ' . $path);
            
            $fileSystem = $this->getServiceManager()->get(FileSystemService::SERVICE_ID)->getFileSystem('taoResultServer');
            
            if ($variable instanceof \taoResultServer_models_classes_ResponseVariable) {
                $data = $variable->getCandidateResponse();
                $variable->setCandidateResponse($path);
                $fileSystem->write($path, $data);
            } elseif ($variable instanceof \taoResultServer_models_classes_OutcomeVariable) {
                $data = $variable->getValue();
                $variable->setValue($path);
                $fileSystem->write($path, $data);
            }
        }
    }
    
    public function retrieve(\taoResultServer_models_classes_Variable $variable, $deliveryResultIdentifier)
    {
        if ($variable->baseType === 'file') {
            $fileSystem = $this->getServiceManager()->get(FileSystemService::SERVICE_ID)->getFileSystem('taoResultServer');
            
            if ($variable instanceof \taoResultServer_models_classes_ResponseVariable) {
                $variable->setCandidateResponse($fileSystem->read($variable->getCandidateResponse()));
            } elseif ($variable instanceof \taoResultServer_models_classes_OutcomeVariable) {
                $variable->setValue($fileSystem->read($variable->getValue()));
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
        $path = self::buildPath($deliveryResultIdentifier);
        $fileSystem = $this->getServiceManager()->get(FileSystemService::SERVICE_ID)->getFileSystem('taoResultServer');
        $fileSystem->deleteDir($path);
    }
    
    private static function buildPath($deliveryResultIdentifier, $identifier = '')
    {
        $path = md5($deliveryResultIdentifier);
        
        if (empty($identifier) === false) {
            $path .= "/${identifier}";
        }
        
        return $path;
    }
    
    private static function buildIdentifier()
    {
        return md5(uniqid(true) . rand(0, 10000));
    }
}

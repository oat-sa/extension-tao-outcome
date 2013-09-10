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

/**
 * @access public
 * @package tao
 * @subpackage helpers
 */
class taoResultServer_helpers_ResultServerJsApi
{
    
    public static function getServiceApi(core_kernel_classes_Resource $resultServer) {
        return 'new ResultServerApi('.tao_helpers_Javascript::buildObject(self::getEndpoint($resultServer)).')';
    }
    
    private static function getEndpoint(core_kernel_classes_Resource $resultServer) {
        $endpoint = $resultServer->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_RESULTSERVER_ENDPOINT));
        if (empty($endpoint)) {
            $endpoint = _url('', 'ResultServerStateFull','taoResultServer');
        } 
        return $endpoint;
    }
    
}
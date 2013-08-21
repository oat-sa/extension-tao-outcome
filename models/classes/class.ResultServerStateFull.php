<?php
/*  
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
 * Copyright (c) 2013 Open Assessment Technologies
  * 
 */

/**
 *
 * Statefull api for results submission
 * 
 * 
 * @package taoResultServer
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
 
class taoResultServer_models_classes_ResultServerStateFull extends tao_models_classes_GenerisService {
	/**
	 * constructor: initialize the service and the default data
	 * @return Delivery
	 */
	public function __construct(){
    	parent::__construct();
	}
   
	

    
    public function initResultServer($resultServerUri, $callOptions) {
        if (common_Utils::isUri($resultServerUri)) {
        PHPSession::singleton()->setAttribute("resultServerUri", $resultServerUri);
        PHPSession::singleton()->setAttribute("resultServerCallOptions", $callOptions);
        } else {
            throw new common_exception_MissingParameter("resultServerUri");
        }
    }
    private function restoreResultServer() {
        if (PHPSession::singleton()->hasAttribute("resultServerUri")) {
        $resultServerUri = PHPSession::singleton()->getAttribute("resultServerUri");
        $callOptions = array();
        if (PHPSession::singleton()->hasAttribute("resultServerCallOptions")) {
            $callOptions = PHPSession::singleton()->getAttribute("resultServerCallOptions");
        }
        return new taoResultServer_models_classes_ResultServer($resultServerUri, $callOptions);
        } else {
           throw new common_exception_PreConditionFailure("The result server hasn't been initalized");
        }
    }
    /**
     * @example http://tao-dev/taoResultServer/ResultServerStateFull/spawnResult
     * @return type
     */
     public function spawnResult($deliveryResultIdentifier = null){
        
        if ($deliveryResultIdentifier == null) {
         $resultServer = $this->restoreResultServer();
        $resultServer_deliveryResultIdentifier = $resultServer->getStorageInterface()->spawnResult();
        } else {
            $resultServer_deliveryResultIdentifier = $deliveryResultIdentifier;
        }
        PHPSession::singleton()->setAttribute("resultServer_deliveryResultIdentifier",  $resultServer_deliveryResultIdentifier);
        return $resultServer_deliveryResultIdentifier;
        
     }
     /**
      * http://tao-dev/taoResultServer/ResultServerStateFull/storeRelatedTestTaker?testTakerIdentifier=15
      * @param string $testTakerIdentifier may be different from a uri
      * @return type
      */
    public function storeRelatedTestTaker($testTakerIdentifier){
        
        if ($testTakerIdentifier!="") {
        $resultServer = $this->restoreResultServer();
        $resultServer->getStorageInterface()->storeRelatedTestTaker(PHPSession::singleton()->getAttribute("resultServer_deliveryResultIdentifier"), $testTakerIdentifier);
         return PHPSession::singleton()->getAttribute("resultServer_deliveryResultIdentifier");
        } else {
            throw new common_exception_MissingParameter("testTakerIdentifier");
        }
    }
    /**
     * @example http://tao-dev/taoResultServer/ResultServerStateFull/storeRelatedDelivery?deliveryIdentifier=12
     * @param type $deliveryResultIdentifier
     * @param type $deliveryIdentifier
     * @return type
     */
    public function storeRelatedDelivery($deliveryIdentifier) {
        $resultServer = $this->restoreResultServer();
        if ($deliveryIdentifier != "") {
            $resultServer->getStorageInterface()->storeRelatedDelivery(PHPSession::singleton()->getAttribute("resultServer_deliveryResultIdentifier"), $deliveryIdentifier);
            return PHPSession::singleton()->getAttribute("resultServer_deliveryResultIdentifier");
        } else {
            throw new common_exception_MissingParameter("deliveryIdentifier");
        }
    }

    
    public function storeItemVariable($test, $item, taoResultServer_models_classes_Variable $itemVariable, $callIdItem ) {
        $resultServer = $this->restoreResultServer();
        $resultServer->getStorageInterface()->storeItemVariable(PHPSession::singleton()->getAttribute("resultServer_deliveryResultIdentifier"), $test, $item, $itemVariable, $callIdItem );
        return PHPSession::singleton()->getAttribute("resultServer_deliveryResultIdentifier");
    }
    
    
    public function storeTestVariable($deliveryResultIdentifier, $test, taoResultServer_models_classes_Variable $testVariable, $callIdTest){
        $resultServer = $this->restoreResultServer();
        $resultServer->getStorageInterface()->storeTestVariable(PHPSession::singleton()->getAttribute("resultServer_deliveryResultIdentifier"), $test, $testVariable,  $callIdTest );
        return PHPSession::singleton()->getAttribute("resultServer_deliveryResultIdentifier");
    }
    

	public function index(){
		
	}

}
?>
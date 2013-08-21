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
 * Statefull api for results submission from the client
 * 
 * 
 * @package taoResultServer
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
 
class taoResultServer_actions_ResultServerStateFull extends tao_actions_SaSModule {

    protected $service;
    /**
	 * constructor: initialize the service and the default data
	 * @return Delivery
	 */
	public function __construct(){
    	parent::__construct();
        $this->service = taoResultServer_models_classes_ResultServerStateFull::singleton();
	}
    public function getClassService() {
        return taoResultServer_models_classes_ResultServerStateFull::singleton();
    }
	/**
	 * @see TaoModule::getRootClass
	 * @return core_kernel_classes_Classes
	 */
	protected function getRootClass(){
	}
    protected function returnFailure(Exception $exception) {
    	$data = array();
	    $data['success']	=  false;
	    $data['errorCode']	=  $exception->getCode();
	    $data['errorMsg']	=  ($exception instanceof common_exception_UserReadableException) ? $exception->getUserMessage() : $exception->getMessage();
	    $data['version']	= TAO_VERSION;
	    echo json_encode($data);
	    exit(0);
	}
	protected function returnSuccess($rawData = "") {
	     $data = array();
	    $data['success']	= true;
	    $data['data']	= $rawData;
	    $data['version']	= TAO_VERSION;
	   
	    echo json_encode($data);
	    exit(0);
	}
    /**
     * 
     * @example http://tao-dev/taoResultServer/ResultServerStateFull/initResultServer?resultServerUri=http%3A%2F%2Fwww.tao.lu%2FOntologies%2FTAOResultServer.rdf%23taoResultServer
     * @param string result server definition uri
     */
    public function initResultServer() {
        if ($this->hasRequestParameter("resultServerUri")) {
        $this->service->initResultServer($this->getRequestParameter("resultServerUri"));
        $this->returnSuccess();
        } else {
            $this->returnFailure(new common_exception_MissingParameter("resultServerUri"));
        }
    }
    
    /**
     * @example http://tao-dev/taoResultServer/ResultServerStateFull/spawnResult
     * @return type
     */
     public function spawnResult(){

         try {
             $this->returnSuccess($this->service->spawnResult());
         } catch (exception $e) {
         $this->returnFailure($e);
         }

     }
     /**
      * http://tao-dev/taoResultServer/ResultServerStateFull/storeRelatedTestTaker?testTakerIdentifier=15
      * @param type $testTakerIdentifier
      * @return type
      */
    public function storeRelatedTestTaker(){
        if ($this->hasRequestParameter("testTakerIdentifier")) {
            try {
                $data = $this->service->storeRelatedTestTaker($this->getRequestParameter("testTakerIdentifier"));
            } catch (exception $e) {
                $this->returnFailure($e);
            }
             return $this->returnSuccess($data);
        } else {
            $this->returnFailure(new common_exception_MissingParameter("testTakerIdentifier"));
        }
    }
    /**
     * @example http://tao-dev/taoResultServer/ResultServerStateFull/storeRelatedDelivery?deliveryIdentifier=12
     * @param type $deliveryResultIdentifier
     * @param type $deliveryIdentifier
     * @return type
     */
    public function storeRelatedDelivery() {
        if ($this->hasRequestParameter("deliveryIdentifier")) {
            try {
            $data = $this->service->storeRelatedDelivery($this->getRequestParameter("deliveryIdentifier"));
            } catch (exception $e) {
                $this->returnFailure($e);
            }
            return $this->returnSuccess($data);
        } else {
            $this->returnFailure(new common_exception_MissingParameter("deliveryIdentifier"));
        }
    }

    
    public function storeItemData(){
        if ($this->hasRequestParameter("outcomeVariables")) {
            try {
                $outcomeVariables = $this->getRequestParameter("outcomeVariables");
                foreach ($outcomeVariables as $variableName => $outcomeValue) {
                $test = "hardcoded";
                $item = "use the item reported";
                $callIdItem = $this->getRequestParameter("serviceCallId");
                
               

                $outComeVariable = new taoResultServer_models_classes_OutcomeVariable();
                //$outComeVariable->setBaseType("int");
                //$outComeVariable->setCardinality("single");
                $outComeVariable->setIdentifier($variableName);
                $outComeVariable->setValue($outcomeValue);

              
                $data = $this->service->storeItemVariable($test, $item, $outComeVariable, $callIdItem );
                }
            } catch (exception $e) {
                $this->returnFailure($e);
            }

            return $this->returnSuccess($data);
        } 
    }
   
    
    /**
     *   $responseVariable = new taoResultServer_models_classes_ResponseVariable();
                    $responseVariable->setBaseType("int");
                    $responseVariable->setCardinality("single");
                    $responseVariable->setIdentifier("historyResponse");
                    $responseVariable->setCandidateResponse("choice_".rand(0,5));
                    $responseVariable->setCorrectResponse(true);
     */

    /*
    public function storeTestVariable($deliveryResultIdentifier, $test, taoResultServer_models_classes_Variable $testVariable, $callIdTest);
    */

	public function index(){
		
	}

}
?>
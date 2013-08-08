<?php

/**
 * Implements the Services for the storage of item and test variables,
 * This implementations depends on results for the the physical storage
 * TODO : move the impl to results services
 * @author plichart
 */
class taoResultServer_models_classes_LtiResultServer
    extends tao_models_classes_GenerisService
    implements taoResultServer_models_classes_ResultServer {
//https://testlauncher-a.secure.cito.nl/tools/consumer.aspx
//Basic LTI http://www.imsglobal.org/LTI/v1p1p1/ltiIMGv1p1p1.html#_Toc330273033
    
    private $url;

    private $deliveryResult;
    /**
    * @param string deliveryResultIdentifier if no such deliveryResult with this identifier exists a new one gets created
    */

    public function __construct($deliveryResultIdentifier){
        // section 10-13-1-39-5129ca57:1276133a327:-8000:00000000000020A9 begin
		parent::__construct();
		$this->deliveryResult = $this->getDeliveryResult($deliveryResultIdentifier);
        // section 10-13-1-39-5129ca57:1276133a327:-8000:00000000000020A9 end
    }
    
    /**
    * @param string testTakerIdentifier (uri recommended)
    */
    public function setTestTaker($testTakerIdentifier) {
       
    }

    /**
    * @param string deliveryIdentifier (uri recommended)
    */
    public function setDelivery($deliveryIdentifier) {
        

    }

    
    /**
    * Submit a specific Item Variable, (ResponseVariable and OutcomeVariable shall be used respectively for collected data and score/interpretation computation)
    * @param string test (uri recommended)
    * @param string item (uri recommended)
    * @param taoResultServer_models_classes_ItemVariable itemVariable
    * @param string callId an id for the item instanciation
    */
    public function setItemVariable($test, $item, taoResultServer_models_classes_ItemVariable $itemVariable, $callId){

    }

    /** Submit a complete Item result
    *
    * @param taoResultServer_models_classes_ItemResult itemResult
    * @param string callId an id for the item instanciation
    */
//    public function setItemResult($item, taoResultServer_models_classes_ItemResult $itemResult, $callId ) {}
//    public function setTestResult($test, taoResultServer_models_classes_TestResult $testResult, $callId){}

    public function setTestVariable($test, taoResultServer_models_classes_ItemVariable $testVariable, $callId){
    }

}
?>
<?php

/**
 * Implements the Services for the storage of item and test variables,
 * This implementations depends on results for the the physical storage
 * TODO : move the impl to results services
 * @author plichart
 */
class taoResultServer_models_classes_DbResultServer
    extends tao_models_classes_GenerisService
    implements taoResultServer_models_classes_ResultServer {

    private $taoResultsStorage;

    private $deliveryResult;
    /**
    * @param string deliveryResultIdentifier if no such deliveryResult with this identifier exists a new one gets created
    */

    public function __construct($deliveryResultIdentifier){
		parent::__construct();
		$this->taoResultsStorage = new taoResults_models_classes_ResultsService();
        //spawns a new delivery result or retrieve an existing one with this identifier
        $this->deliveryResult = $this->taoResultsStorage->storeDeliveryResult($deliveryResultIdentifier);
    }
    
    /**
    * @param string testTakerIdentifier (uri recommended)
    */
    public function storeTestTaker($testTakerIdentifier) {
        $this->taoResultsStorage->storeTestTaker($this->deliveryResult, $testTakerIdentifier);
    }
    /**
    * @param string deliveryIdentifier (uri recommended)
    */
    public function storeDelivery($deliveryIdentifier) {
        $this->taoResultsStorage->storeDelivery($this->deliveryResult, $deliveryIdentifier);
    }
    /**
    * Submit a specific Item Variable, (ResponseVariable and OutcomeVariable shall be used respectively for collected data and score/interpretation computation)
    * @param string test (uri recommended)
    * @param string item (uri recommended)
    * @param taoResultServer_models_classes_ItemVariable itemVariable
    * @param string callId an id for the item instanciation
    */
    public function storeItemVariable($test, $item, taoResultServer_models_classes_Variable $itemVariable, $callIdItem){
        $this->taoResultsStorage->storeItemVariable($this->deliveryResult, $test, $item, $itemVariable, $callIdItem);
        
    }
    /** Submit a complete Item result
    *
    * @param taoResultServer_models_classes_ItemResult itemResult
    * @param string callId an id for the item instanciation
    */
//    public function setItemResult($item, taoResultServer_models_classes_ItemResult $itemResult, $callId ) {}
//    public function setTestResult($test, taoResultServer_models_classes_TestResult $testResult, $callId){}

    public function storeTestVariable($test, taoResultServer_models_classes_Variable $testVariable, $callIdTest){
         $this->taoResultsStorage->storeTestVariable($this->deliveryResult, $test, $item, $testVariable, $callIdItem);

    }

}
?>
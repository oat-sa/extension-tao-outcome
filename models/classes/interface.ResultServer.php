<?php

interface taoResultServer_models_classes_ResultServer {

    /**
     * @param string callId, if no such deliveryResult with this identifier exists a new one gets created
     */
    public function __construct($callId);

    //public function __construct($callId, $test);
    /**
    * @param string testTakerIdentifier (uri recommended)
    */
    public function storeTestTaker($testTakerIdentifier);

    /**
    * @param string deliveryIdentifier (uri recommended)
    */
    public function storeDelivery($deliveryIdentifier);

    /**
    * Submit a specific Item Variable, (ResponseVariable and OutcomeVariable shall be used respectively for collected data and score/interpretation computation)
    * @param string test (uri recommended)
    * @param string item (uri recommended)
    * @param taoResultServer_models_classes_ItemVariable itemVariable
    * @param string callId an id for the item instanciation
    */
    public function storeItemVariable($test, $item, taoResultServer_models_classes_Variable $itemVariable, $callIdItem );

    

    public function storeTestVariable($test, taoResultServer_models_classes_Variable $testVariable, $callIdTest);

     /** Submit a complete Item result
    *
    * @param taoResultServer_models_classes_ItemResult itemResult
    * @param string callId an id for the item instanciation
    */
    //public function setItemResult($item, taoResultServer_models_classes_ItemResult $itemResult, $callId);
    
    //public function setTestResult($test, taoResultServer_models_classes_TestResult $testResult, $callId);
    
}
?>
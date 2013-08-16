<?php

interface taoResultServer_models_classes_ResultStorage {

    /**
     * Optionnally spawn a new result and returns
     * an identifier for it, use of the other services with an unknow identifier
     *  will trigger the spawning of a new result
     * @return string deliveryResultIdentifier
     */
    public function spawnResult();

    //public function __construct($callId, $test);
    /**
    * @param string testTakerIdentifier (uri recommended)
    *
    */
    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier);

    /**
    * @param string deliveryIdentifier (uri recommended)
    */
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier);

    /**
    * Submit a specific Item Variable, (ResponseVariable and OutcomeVariable shall be used respectively for collected data and score/interpretation computation)
    * @param string test (uri recommended)
    * @param string item (uri recommended)
    * @param taoResultServer_models_classes_ItemVariable itemVariable
    * @param string callId contextual call id for the variable, ex. :  to distinguish the same variable output by the same item but taht is presented several times in the same test
    */
    public function storeItemVariable($deliveryResultIdentifier, $test, $item, taoResultServer_models_classes_Variable $itemVariable, $callIdItem );

    

    public function storeTestVariable($deliveryResultIdentifier, $test, taoResultServer_models_classes_Variable $testVariable, $callIdTest);

     /** Submit a complete Item result
    *
    * @param taoResultServer_models_classes_ItemResult itemResult
    * @param string callId an id for the item instanciation
    */
    //public function setItemResult($item, taoResultServer_models_classes_ItemResult $itemResult, $callId);
    
    //public function setTestResult($test, taoResultServer_models_classes_TestResult $testResult, $callId);
    
}
?>
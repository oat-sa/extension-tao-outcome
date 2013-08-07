<?php

interface taoResultServer_actions_ResultServer {
    /**
     * @param string deliveryResultIdentifier if no such deliveryResult with this identifier exists a new one gets created
     */
    public function init($deliveryResultIdentifier);
    /**
    * @param string testTakerIdentifier (uri recommended)
    */
    public function setTestTaker($testTakerIdentifier);

    /**
    * @param string deliveryIdentifier (uri recommended)
    */
    public function setDelivery($deliveryIdentifier);

    /** Submit a complete Item result
    *
    * @param taoResultServer_models_classes_ItemResult itemResult
    */
    public function setItemResult(taoResultServer_models_classes_ItemResult $itemResult);
    /**
    * Submit a specific Item Variable, (ResponseVariable and OutcomeVariable shall be used respectively for collected data and score/interpretation computation)
    * @param string test (uri recommended)
    * @param string item (uri recommended)
    * @param taoResultServer_models_classes_ItemVariable itemVariable
    */
    public function setItemVariable($test, $item, taoResultServer_models_classes_ItemVariable $itemVariable);

    public function setTestResult($test, taoResultServer_models_classes_TestResult $testResult);

    public function setTestVariable($test, taoResultServer_models_classes_ItemVariable $itemVariable);
}
?>
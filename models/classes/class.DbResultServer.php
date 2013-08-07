<?php

/*
 * Implements the exposed Services for the storage of item and test variables,
 * The physical storage is delegated to the results extension
 
 * @author plichart
 */
public class taoResultServer_models_classes_DbResultServer
    extends tao_models_classes_GenerisService
    implements taoResultServer_model_classes_ResultServer {

    private taoResultsStorage;

    public function __construct()
    {
        // section 10-13-1-39-5129ca57:1276133a327:-8000:00000000000020A9 begin
		parent::__construct();
		$this->taoResultsStorage = new taoResults_models_classes_ResultsService();
        // section 10-13-1-39-5129ca57:1276133a327:-8000:00000000000020A9 end
    }
    /**
     * retrieve the deliveryResult or create as new deliveryResult
     * @return core_kernel_classes_Resource
     */
    private function getDeliveryResult($deliveryResultIdentifier) {
        
    }

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
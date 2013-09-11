<?php

/**
 * todo move it in the taoResults
 * Implements the Services for the storage of item and test variables,
 * This implementations depends on results for the the physical storage
 * TODO : move the impl to results services
 * @author plichart
 */
class taoResultServer_models_classes_LoggerStorage
    extends tao_models_classes_GenerisService
    implements taoResultServer_models_classes_ResultStorage {

    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier) {
         common_logger::i("LoggerStorage - Test taker storage :".$testTakerIdentifier." into ".$deliveryResultIdentifier);
    }
   
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier) {
         common_logger::i("LoggerStorage - Delivery storage:".$deliveryResultIdentifier." into ".$deliveryResultIdentifier);
    }

    public function storeItemVariable($deliveryResultIdentifier, $test, $item, taoResultServer_models_classes_Variable $itemVariable, $callIdItem){
        common_logger::i("LoggerStorage - StoreItemVariable :".$test." item:".$item." callid:".$callIdItem."variable:".serialize($itemVariable)." into ".$deliveryResultIdentifier);
    }
 
    public function storeTestVariable($deliveryResultIdentifier, $test, taoResultServer_models_classes_Variable $testVariable, $callIdTest){
        common_logger::i("LoggerStorage - StoreTestVariable :".$test." callid:".$callIdTest."variable:".serialize($testVariable)." into ".$deliveryResultIdentifier);
    }

    public function configure(core_kernel_classes_Resource $resultServer, $callOptions = array()) {
        common_logger::i("LoggerStorage - configuration:".$resultServer." configuration:".serialize($callOptions));
    }

    public function spawnResult(){
        common_logger::i("LoggerStorage - Spawn request made");
    }

}
?>
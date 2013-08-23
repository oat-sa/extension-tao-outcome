<?php

require_once dirname(__FILE__) . '/../../tao/test/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';
/**
 * TODO Not a real unit test, script used for the dev. time , will be removed
 */
class exampleLtiBasicOutcomeTestCase extends UnitTestCase {


	public function setUp(){		
		TaoTestRunner::initTest();
	}

    public function testLtiBasicOutcome() {
        $resultServerCallOptions = array(
                "type" =>"LTI_Basic_1.1.1",
                "result_identifier" => "lis_result_sourcedid",
                "consumer_key" => "Consumerkey",
                "service_url" => "http://tao-dev/log.php",
                "user_identifier" => "lis_person_sourcedid" //optional
                );

        $aResultServerUsingLtiBasicEngine = 'http://www.tao.lu/Ontologies/taoLtiBasicOutcome.rdf#ltiBasicOutcomeExample';
        $resultServer = new taoResultServer_models_classes_ResultServer(
            $aResultServerUsingLtiBasicEngine,
            $resultServerCallOptions
            );
        $api = $resultServer->getStorageInterface();
        $outComeVariable = new taoResultServer_models_classes_OutcomeVariable();
        //optional
        $outComeVariable->setBaseType("int");
        //optional
        $outComeVariable->setCardinality("single");
        $outComeVariable->setIdentifier("Rotation in Space");
        $outComeVariable->setValue(0.34);
     /*
     *  CreateResultValue(sourcedId,ResultValueRecord)
     *  CreateLineItem(sourcedId,lineItemRecord:LineItemRecord)
     */
        $myResultIdentifier = "My_lis_result_sourcedid";
        $api->storeTestVariable( $myResultIdentifier, "testidentifier_uri_isalways_preferred", $outComeVariable, "callid_useful to distinguish cases where the test was incldued severaltiems in the_same_delivery");
    }
    
}
?>
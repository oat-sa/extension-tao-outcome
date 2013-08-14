<?php

require_once dirname(__FILE__) . '/../../tao/test/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';
/**
 * TODO Not a real unit test, script used for the dev. time , will be removed
 */
class exampleResultServerTestCase extends UnitTestCase {
	 /**
	  *
	  * @var taoResultServer_models_classes_ResultsServer
	  */
	private $resultServer;

	public function setUp(){		
		TaoTestRunner::initTest();
		

	}



    public function testResultServer() {

        $resultServer = new taoResultServer_models_classes_ResultServer('http://www.tao.lu/Ontologies/TAODelivery.rdf#LocalResultServer');
        //the storage impl varies according to the resultserver
        $api = $resultServer->getStorageInterface();
        //optionnaly you may ask the server and its storage to generate for you an identifier
        $myResultIdentifier = $api->spawnResult();
        $testTaker = "19"; //or ideally a uri of a tao test taker
        $api->storeTestTaker($myResultIdentifier, "19" );

        $test = "22"; //or ideally a uri of a tao test
        $item = "Mammoth"; //or ideally a uri of ta tao item

        $outComeVariable = new taoResultServer_models_classes_OutcomeVariable();
        $outComeVariable->setBaseType("int");
        $outComeVariable->setCardinality("single");
        $outComeVariable->setIdentifier("Rotation in Space");
        $outComeVariable->setValue("".rand(0,50));
        //callID will help to distinguish the same variable output from the same item used twice in the same test for example
        $api->storeItemVariable( $myResultIdentifier, $test, $item, $outComeVariable, "An identifier of the variable instanciation context");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("historyResponse");
        $responseVariable->setCandidateResponse("choice_".rand(0,5));
        $responseVariable->setCorrectResponse(true);
        $api->storeItemVariable($resultIdentifier, $test, $item, $responseVariable, "yyy");
    }
}
?>
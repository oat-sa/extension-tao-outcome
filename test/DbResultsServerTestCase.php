<?php

require_once dirname(__FILE__) . '/../../tao/test/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

class DbResultsServerTestCase extends UnitTestCase {
	 /**
	  *
	  * @var taoResultServer_models_classes_ResultsServer
	  */
	private $resultServer;

	public function setUp(){		
		TaoTestRunner::initTest();
		$this->resultServer = new taoResultServer_models_classes_DbResultServer("12345");
	}
	public function testResultServer(){
		$this->assertIsA($this->resultServer, 'taoResultServer_models_classes_DbResultServer');
	}
    public function testSetTestTaker() {
        $this->resultServer->storeTestTaker("http://tao-dev/taodev.rdf#i1375965461523031");
    }
     public function testSetDelivery() {
        $this->resultServer->storeDelivery("http://tao-dev/taodev.rdf#i1375952861441416");
    }
    public function testSetItemVariable(){
        $outComeVariable = new taoResultServer_models_classes_OutcomeVariable();
        $outComeVariable->setBaseType("int");
        $outComeVariable->setCardinality("single");
        $outComeVariable->setIdentifier("a Score");
        $outComeVariable->setValue(15);
        $this->resultServer->storeItemVariable("myTest", "myItem", $outComeVariable, "xxx");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("Response");
        $responseVariable->setCandidateResponse("choice_1");
        $responseVariable->setCorrectResponse(true);
        $this->resultServer->storeItemVariable("http://tao-dev/taodev.rdf#i1375965000718922", "http://tao-dev/taodev.rdf#i137595147788425", $responseVariable, "yyy");


    }

}
?>
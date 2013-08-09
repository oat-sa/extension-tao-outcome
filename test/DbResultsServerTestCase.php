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
		$this->resultServer = new taoResultServer_models_classes_DbResultServer("13");
	}
	public function testResultServer(){
		$this->assertIsA($this->resultServer, 'taoResultServer_models_classes_DbResultServer');
	}
    public function testSetTestTaker() {
        $this->resultServer->storeTestTaker('http://tao-dev/taodev.rdf#i1376035802900929');
    }
     public function testSetDelivery() {
        $this->resultServer->storeDelivery('http://tao-dev/taodev.rdf#i1376035790164827');
    }
    public function testSetItemVariable(){
        $outComeVariable = new taoResultServer_models_classes_OutcomeVariable();
        $outComeVariable->setBaseType("int");
        $outComeVariable->setCardinality("single");
        $outComeVariable->setIdentifier("a Score");
        $outComeVariable->setValue("".rand(0,30));
        $this->resultServer->storeItemVariable("http://tao-dev/taodev.rdf#i1376035966325031", "http://tao-dev/taodev.rdf#i1376035671745", $outComeVariable, "xxx");

        $outComeVariable = new taoResultServer_models_classes_OutcomeVariable();
        $outComeVariable->setBaseType("int");
        $outComeVariable->setCardinality("single");
        $outComeVariable->setIdentifier("CognitiveVar");
        $outComeVariable->setValue("".rand(0,50));
        $this->resultServer->storeItemVariable("http://tao-dev/taodev.rdf#i1376035966325031", "http://tao-dev/taodev.rdf#i1376035671745", $outComeVariable, "yyy");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("Response");
        $responseVariable->setCandidateResponse("choice_".rand(0,5));
        $responseVariable->setCorrectResponse(true);
        $this->resultServer->storeItemVariable("http://tao-dev/taodev.rdf#i1376035966325031", "http://tao-dev/taodev.rdf#i1376035671745", $responseVariable, "yyy");


    }

}
?>
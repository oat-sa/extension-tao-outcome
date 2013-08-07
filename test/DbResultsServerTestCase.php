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
		$this->resultServer = new taoResultServer_models_classes_ResultServer();
	}
	public function testResultServer(){
		$this->assertIsA($this->resultServer, 'taoResultServer_models_classes_ResultServer');
	}
    public function testSetTestTaker() {
        $this->resultServer
    }

}
?>
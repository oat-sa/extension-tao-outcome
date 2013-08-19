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

    public function testResultServerWorstCase() {
        //the uri of a resultserver in the KB and probably associated with the delivery
        $resultServer = new taoResultServer_models_classes_ResultServer(TAO_DEFAULT_RESULT_SERVER);
        //the storage impl varies according to the resultserver
        $api = $resultServer->getStorageInterface();
        //optionnaly you may ask the server and its storage to generate for you an identifier
        //you may also provide your own identifier like a (example : lis_result_sourcedid)
        $myResultIdentifier = $api->spawnResult();
        $testTaker = "19"; //or ideally a uri of a tao test taker

        //if the resultidentifier is not know, a new result is spawned with the submitted identifier , could be a process execution etc.
        $api->storeRelatedTestTaker($myResultIdentifier, "19" );

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
        $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        //an unscored response
         $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("Dissertation");
        $responseVariable->setCandidateResponse("La raison du plus fort est toujours la meilleure :
Nous l'allons montrer tout à l'heure.
Un Agneau se désaltérait
Dans le courant d'une onde pure.
Un Loup survient à jeun qui cherchait aventure,
Et que la faim en ces lieux attirait.
Qui te rend si hardi de troubler mon breuvage ?
Dit cet animal plein de rage :
Tu seras châtié de ta témérité.");
        $responseVariable->setCorrectResponse(true);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");


        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("populationResponse");
        $responseVariable->setCandidateResponse("choice_".rand(0,5));
        $responseVariable->setCorrectResponse(false);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        //4 different observations are submitted for the sam variableIdentifier
         $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("planets");
        $responseVariable->setCandidateResponse("choice_1");
        $responseVariable->setCorrectResponse(false);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("planets");
        $responseVariable->setCandidateResponse("choice_3");
        $responseVariable->setCorrectResponse(false);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("planets");
        $responseVariable->setCandidateResponse("choice_5");
        $responseVariable->setCorrectResponse(true);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("planets");
        $responseVariable->setCandidateResponse("choice_4");
        $responseVariable->setCorrectResponse(false);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");
    }
    public function testResultServerNormalCase() {

        //get some test data created
        $tempData = $this->spawnDependantData();
        $testTaker = $tempData[0];
        $delivery = $tempData[3];
        $test = $tempData[1];
        $item = $tempData[2];

        //the uri of a resultserver in the KB and probably associated with the delivery
        $resultServer = new taoResultServer_models_classes_ResultServer(TAO_DEFAULT_RESULT_SERVER);
        //the storage impl varies according to the resultserver
        $api = $resultServer->getStorageInterface();
        //optionnaly you may ask the server and its storage to generate for you an identifier
        $myResultIdentifier = $api->spawnResult();
        

        //if the resultidentifier is not know, a new result is spawned with the submitted identifier , could be a process execution etc.
        $api->storeRelatedTestTaker($myResultIdentifier, $testTaker );

       

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
        $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        //an unscored response
         $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("Dissertation");
        $responseVariable->setCandidateResponse("La raison du plus fort est toujours la meilleure :
Nous l'allons montrer tout à l'heure.
Un Agneau se désaltérait
Dans le courant d'une onde pure.
Un Loup survient à jeun qui cherchait aventure,
Et que la faim en ces lieux attirait.
Qui te rend si hardi de troubler mon breuvage ?
Dit cet animal plein de rage :
Tu seras châtié de ta témérité.");
        $responseVariable->setCorrectResponse(true);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");


        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("populationResponse");
        $responseVariable->setCandidateResponse("choice_".rand(0,5));
        $responseVariable->setCorrectResponse(false);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        //4 different observations are submitted for the sam variableIdentifier
         $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("planets");
        $responseVariable->setCandidateResponse("choice_1");
        $responseVariable->setCorrectResponse(false);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("planets");
        $responseVariable->setCandidateResponse("choice_3");
        $responseVariable->setCorrectResponse(false);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("planets");
        $responseVariable->setCandidateResponse("choice_5");
        $responseVariable->setCorrectResponse(true);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");

        $responseVariable = new taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setBaseType("int");
        $responseVariable->setCardinality("single");
        $responseVariable->setIdentifier("planets");
        $responseVariable->setCandidateResponse("choice_4");
        $responseVariable->setCorrectResponse(false);
         $api->storeItemVariable($myResultIdentifier, $test, $item, $responseVariable, "yyy");
    }

     private function spawnDependantData() {
        $subjectClass= new core_kernel_classes_Class(TAO_SUBJECT_CLASS);
        $testTaker = $subjectClass->createInstanceWithProperties(array(
					RDFS_LABEL					=> "tempTTforResultsTest".rand(0,65535),
                    PROPERTY_USER_LOGIN	=> time().rand(0,65535),
                    PROPERTY_USER_FIRSTNAME	=> "randTest".rand(0,65535),
                    PROPERTY_USER_LASTNAME => "randTest".rand(0,65535),
                    PROPERTY_USER_MAIL => "foo@foo.bar",
				));
        $testClass= new core_kernel_classes_Class(TAO_TEST_CLASS);
        $test = $testClass->createInstanceWithProperties(array(
					RDFS_LABEL					=> "tempTestforResultsTest".rand(0,65535),
				));
        $itemClass= new core_kernel_classes_Class(TAO_ITEM_CLASS);
        $item = $itemClass->createInstanceWithProperties(array(
					RDFS_LABEL					=> "tempItemforResultsTest".rand(0,65535),
                    TAO_ITEM_MODEL_PROPERTY => TAO_ITEM_MODEL_XHTML
				));
        $deliveryClass= new core_kernel_classes_Class(TAO_DELIVERY_CLASS);
        $delivery = $deliveryClass->createInstanceWithProperties(array(
					RDFS_LABEL					=> "tempDeliveryforResultsTest".rand(0,65535),
				));
        return array($testTaker->getUri(),$test->getUri(),$item->getUri(), $delivery->getUri());
    }
}
?>
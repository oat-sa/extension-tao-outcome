<?php

abstract class taoResultServer_models_classes_ResultsService
    extends tao_models_classes_Service
{
    /**
     * @var taoResultServer_models_classes_assessmentResult
     */
    private $result;
    
    public function __construct()
    {
	$this->result = new taoResultServer_models_classes_AssessmentResult();
	parent::__construct();
    }
    //for testing puirpose
    public function getAssessmentResult(){
	return $this->result;
    }

} 

?>
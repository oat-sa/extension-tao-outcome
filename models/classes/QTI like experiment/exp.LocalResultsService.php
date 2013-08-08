<?php

class taoResultServer_models_classes_LocalResultsService 
    extends taoResultServer_models_classes_ResultsService{
    /**
     *
     * @var taoResultServer_models_classes_resultsPersistence
     */
    public $persistence;

    public function __construct($persistenceClass)
    {
	parent::__construct();

	if (class_exists($persistenceClass) && in_array('taoResultServer_models_classes_persistence_ResultsPersistence', class_implements($persistenceClass))) {
        $this->persistence = new $persistenceClass();
    }
    }

	
	  


} 

?>
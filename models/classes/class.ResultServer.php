<?php
class taoResultServer_models_classes_ResultServer {
    private $resultServer; //the KB Resource
    private $storage; //the storage implementation according to the selected resultServer
    //deprecated
    private $resultServerImplementation; // the KB resource representing the implementation
    //implementations
    private $implementations;
    /**
     * @param array callOptions an array of parameters sent to the results storage configuration 
     * @param mixed $resultServer string uri or resource
     */
    public function __construct($resultServer, $callOptions =array()){
    
        if (is_object($resultServer) and (get_class($resultServer)=='core_kernel_classes_Resource')) {
        $this->resultServer = $resultServer;
        } else {
            if (common_Utils::isUri($resultServer)) {
                $this->resultServer = new core_kernel_classes_Resource($resultServer);
            }
        }
        $resultServerModels = $this->resultServer->getPropertyValues(new core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP));
        if ( (!isset($resultServerModels)) or (count($resultServerModels)==0)) {
            throw new common_Exception("The result server is not correctly configured (Resource definition)");
        }
        //use constantly the default n ary implementation
        $this->resultServerImplementationContainer = "taoResultServer_models_classes_MultipleResultStorage";
        $this->implementations = array();
       
        foreach ($resultServerModels as $resultServerModelUri){
            $resultServerModel = new core_kernel_classes_Resource($resultServerModelUri);
            $this->implementations[] = $resultServerModel->getUniquePropertyValue(new core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_IMPL_PROP))->literal;
        }
        
        $this->callOptions = $callOptions;
        common_Logger::i("Result Server Initialized using defintion:".$this->resultServer->getUri());
        //sets the details required depending on the type of storage 

    }
    public function getResultServerInfo(){
        
    }
    /**
     * instanciate the result storage related to this resultServer 
     */
   private function setResultStorageInterface(taoResultServer_models_classes_ResultStorage $storageInterface) {
       $this->storage = $storageInterface;
   }
   
   public function getStorageInterface(){


        if (class_exists($this->resultServerImplementationContainer) && in_array('taoResultServer_models_classes_ResultStorage', class_implements($this->resultServerImplementationContainer))) {
            $resultStoragePolicy = $this->resultServerImplementationContainer;//constanly set to the multiple implementation container
            $this->setResultStorageInterface(new $resultStoragePolicy($this->implementations));
            //configure it , the storage may rely on specific extra parameters added to the result server like the lti consumer in the case of lti outcome
            $this->storage->configure($this->resultServer, $this->callOptions);
            common_Logger::i("Result Server Storage Policy selected:".$this->resultServerImplementation);
        } else {
            throw new common_Exception("The result server is not correctly configured (Implementation not found)".$this->resultServerImplementation);
        }
       return $this->storage;
   }

   
}
?>
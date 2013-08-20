<?php
class taoResultServer_models_classes_ResultServer {
    private $resultServer; //the KB Resource
    private $storage; //the storage implementation according to the selected resultServer
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
        
        //restricted to one imple for the moment
        $resultServerModel = new core_kernel_classes_Resource(current($resultServerModels));
       
        $resultServerImplementation = $resultServerModel->getUniquePropertyValue(new core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_IMPL_PROP))->literal;
        
        if (class_exists($resultServerImplementation) && in_array('taoResultServer_models_classes_ResultStorage', class_implements($resultServerImplementation))) {
        
            $resultStoragePolicy = $resultServerImplementation;
            $this->setResultStorageInterface(new $resultStoragePolicy());
            //configure it , the storage may rely on specific extra parameters added to the result server like the lti consumer in the case of lti outcome
            $this->storage->configure($this->resultServer, $callOptions);
            
        } else {
            throw new common_Exception("The result server is not correctly configured (Implementation not found)".$resultServerImplementation);
        }
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
    /*should have an impl of the interface  that propagates to n impl of the itnerface*/
   public function getStorageInterface(){
       return $this->storage;
   }

   
}
?>
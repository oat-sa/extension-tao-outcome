<?php

class taoResultServer_models_classes_ResultServer {

    private $resultServer; //the KB Resource
    private $storage; //the storage implementation according to the selected resultServer

    /**
     *
     * @param mixed $resultServer string uri or resource
     */
    public function __construct( $resultServer){
    
        if (is_object($resultServer) and (get_class($resultServer)=='core_kernel_classes_Resource')) {
        $this->resultServer = $resultServer;
        } else {
            if (common_Utils::isUri($resultServer)) {
                $this->resultServer = new core_kernel_classes_Resource($resultServer);
            }
        }

        //read storage method from Db and set it
        //$this->resultServer->getPropertiesValues($properties);
        //hardcoded
            $resultStoragePolicy = "taoResultServer_models_classes_DbResult";
            $this->setResultStorageInterface(new $resultStoragePolicy());

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
       return $this->storage;
   }

   
}
?>
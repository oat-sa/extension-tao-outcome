<?php
class taoResultServer_models_classes_ResultServer {
    private $resultServer; //the KB Resource
    private $storageContainer; //A result storage (taoResultServer_models_classes_ResultStorage)
    private $implementations;


    /**
     * @param array callOptions an array of parameters sent to the results storage configuration 
     * @param mixed $resultServer string uri or resource
     */
    public function __construct($resultServer, $additionalStorages =array()){

        $this->implementations = array();
        
        if (is_object($resultServer) and (get_class($resultServer)=='core_kernel_classes_Resource')) {
        $this->resultServer = $resultServer;
        } else {
            if (common_Utils::isUri($resultServer)) {
                $this->resultServer = new core_kernel_classes_Resource($resultServer);
            }
        }
        //the static storages
        if ($this->resultServer->getUri() != TAO_VOID_RESULT_SERVER) {
            $resultServerModels = $this->resultServer->getPropertyValues(new core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP));
            if ( (!isset($resultServerModels)) or (count($resultServerModels)==0)) {
                throw new common_Exception("The result server is not correctly configured (Resource definition)");
            }
            foreach ($resultServerModels as $resultServerModelUri){
                $resultServerModel = new core_kernel_classes_Resource($resultServerModelUri);
                $this->addImplementation($resultServerModel->getUniquePropertyValue(new core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_IMPL_PROP))->literal);
            }
        }
        //the dynamic storages
        foreach ($additionalStorages as $additionalStorage) {
            $this->addImplementation($additionalStorage["implementation"], $additionalStorage["parameters"]);
        }
        
        common_Logger::i("Result Server Initialized using defintion:".$this->resultServer->getUri());
        //sets the details required depending on the type of storage 
    }
   public function addImplementation($className, $options){
       $this->implementations[] = array("className" =>$className, "params" => $options);
   }
   
   public function getStorageInterface(){
            $storageContainer = new taoResultServer_models_classes_ResultStorageContainer($this->implementations);
            $storageContainer->configure($this->resultServer);
       return $storageContainer;
   }

   
}
?>
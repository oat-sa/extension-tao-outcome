<?php
namespace oat\taoResultServer\test;

use oat\tao\test\RestTestCase;
use oat\taoResultServer\models\classes\ResultServerService;

class RestResultServerTest extends RestTestCase
{
    public function serviceProvider(){
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoResultServer');
        return array(
            array('taoResultServer/RestResultServer',ResultServerService::SERVER_CLASS)
        );
    }
    

}
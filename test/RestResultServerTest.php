<?php
namespace oat\taoResultServer\test;

use oat\tao\test\RestTestCase;

class RestResultServerTest extends RestTestCase
{
    public function serviceProvider(){
        return array(
            array('taoResultServer/RestResultServer')
        );
    }
}
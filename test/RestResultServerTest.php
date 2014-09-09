<?php
require_once dirname(__FILE__) . '/../../tao/test/RestTestCase.php';

class RestResultServerTest extends RestTestCase
{
    public function serviceProvider(){
        return array(
            array('taoResultServer/RestResultServer')
        );
    }
}
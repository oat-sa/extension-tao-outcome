<?php

/*
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */
$todefine = array(
    'TAO_DELIVERY_RESULTSERVER_CLASS'	=> 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServer',
	'TAO_DELIVERY_RESULTSERVER_RESULT_URL_PROP' => 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#saveResultUrl',
	'TAO_DELIVERY_RESULTSERVER_EVENT_URL_PROP' => 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#saveEventUrl',

//todo problem here with a two way dependency
    'TAO_DELIVERY_RESULTSERVER_PROP'	=> 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ResultServer',

	"TAO_RESULTSERVER_STORAGE_PROP" => ""
);
?>
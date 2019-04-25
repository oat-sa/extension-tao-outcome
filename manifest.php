<?php

/*
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */

use oat\tao\model\accessControl\func\AccessRule;
use oat\taoResultServer\controller\ResultServerStateFull;
use oat\taoResultServer\scripts\update\Updater;

$extpath = dirname(__FILE__).DIRECTORY_SEPARATOR;
$taopath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'tao'.DIRECTORY_SEPARATOR;

return array(
    'name' => 'taoResultServer',
    'label' => 'Result core extension',
    'description' => 'Results Server management and exposed interfaces for results data submission',
    'license' => 'GPL-2.0',
    'version' => '9.3.0',
    'author' => 'Open Assessment Technologies',
    //taoResults may be needed for the taoResults taoResultServerModel that uses taoResults db storage
	'requires' => array(
        'tao' => '>=27.2.0'
	),
	'models' => array(
		'http://www.tao.lu/Ontologies/TAOResultServer.rdf#'
	),
	'install' => array(
        'rdf' => array(
			dirname(__FILE__). '/models/ontology/taoResultServer.rdf'
		),
        'php' => array(
        )
    ),
    'update' => Updater::class,
    'managementRole' => 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerRole',
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerRole', array('ext'=>'taoResultServer')),
        array(AccessRule::GRANT, 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole', ResultServerStateFull::class),
    ),
    'routes' => array(
        '/taoResultServer' => 'oat\\taoResultServer\\controller'
    ),
 	'constants' => array(
	 	# actions directory
		"DIR_ACTIONS"			=> $extpath . 'controller' . DIRECTORY_SEPARATOR,

		# views directory
		"DIR_VIEWS"				=> $extpath."views".DIRECTORY_SEPARATOR,

		# default module name
		'DEFAULT_MODULE_NAME'	=> 'Result',

		#default action name
		'DEFAULT_ACTION_NAME'	=> 'index',

		#BASE PATH: the root path in the file system (usually the document root)
		'BASE_PATH'				=> $extpath,

		#BASE URL (usually the domain root)
		'BASE_URL'				=> ROOT_URL . '/taoResultServer',
	)
);

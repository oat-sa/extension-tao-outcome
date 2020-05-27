<?php

/*
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */

use oat\taoResultServer\scripts\update\Updater;

$extpath = __DIR__ . DIRECTORY_SEPARATOR;
$taopath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tao' . DIRECTORY_SEPARATOR;

return [
    'name' => 'taoResultServer',
    'label' => 'Result core extension',
    'description' => 'Results Server management and exposed interfaces for results data submission',
    'license' => 'GPL-2.0',
    'version' => '12.0.2',
    'author' => 'Open Assessment Technologies',
    //taoResults may be needed for the taoResults taoResultServerModel that uses taoResults db storage
    'requires' => [
        'generis' => '>=12.15.0',
        'tao' => '>=27.2.0'
    ],
    'models' => [
        'http://www.tao.lu/Ontologies/TAOResultServer.rdf#'
    ],
    'install' => [
        'rdf' => [
            __DIR__ . '/models/ontology/taoResultServer.rdf'
        ],
        'php' => [
        ]
    ],
    'update' => Updater::class,

    'managementRole' => 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerRole',
    'acl' => [
        ['grant', 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerRole', ['ext' => 'taoResultServer']],
        ['grant', 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole', ['ext' => 'taoResultServer', 'mod' => 'ResultServerStateFull']],
    ],
    'constants' => [
        # actions directory
        "DIR_ACTIONS"           => $extpath . "actions" . DIRECTORY_SEPARATOR,

        # views directory
        "DIR_VIEWS"             => $extpath . "views" . DIRECTORY_SEPARATOR,

        # default module name
        'DEFAULT_MODULE_NAME'   => 'Result',

        #default action name
        'DEFAULT_ACTION_NAME'   => 'index',

        #BASE PATH: the root path in the file system (usually the document root)
        'BASE_PATH'             => $extpath,

        #BASE URL (usually the domain root)
        'BASE_URL'              => ROOT_URL . '/taoResultServer',
    ]
];

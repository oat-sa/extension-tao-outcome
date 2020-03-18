<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2014 (update and modification) Open Assessment Technologies SA
 */

use oat\taoResultServer\models\classes\ResultServerService;
use oat\tao\model\OntologyClassService;

/**
 * The Service class is an abstraction of each service instance.
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 *
 */
class taoResultServer_models_classes_ResultServerAuthoringService extends OntologyClassService
{

    const DEFAULT_RESULTSERVER_KEY = 'default_resultserver';

    /** (non-PHPdoc)
     * @see tao_models_classes_ClassService::getRootClass()
     */
    public function getRootClass()
    {
        return $this->getClass(ResultServerService::CLASS_URI);
    }

    /**
     * Return the default result server to use
     *
     * @return core_kernel_classes_Resource
     */
    public function getDefaultResultServer()
    {
        $ext = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoResultServer');
        if ($ext->hasConfig(self::DEFAULT_RESULTSERVER_KEY)) {
            $uri = $ext->getConfig(self::DEFAULT_RESULTSERVER_KEY);
        } else {
            $uri = ResultServerService::INSTANCE_VOID_RESULT_SERVER;
        }
        
        return new core_kernel_classes_Resource($uri);
    }
    
    /**
     * Sets the default result server to use
     *
     * @param core_kernel_classes_Resource $resultServer
     */
    public function setDefaultResultServer(core_kernel_classes_Resource $resultServer)
    {
        $ext = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoResultServer');
        $ext->setConfig(self::DEFAULT_RESULTSERVER_KEY, $resultServer->getUri());
    }
}

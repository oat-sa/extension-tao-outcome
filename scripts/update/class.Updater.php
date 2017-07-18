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
 * Copyright (c) 2014-2017 (original work) Open Assessment Technologies SA;
 *
 */

use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\implementation\OntologyService;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\user\TaoRoles;
use oat\taoResultServer\models\classes\QtiResultsService;
use oat\taoResultServer\models\classes\ResultService;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoResultServer\models\classes\implementation\DefaultVariableManager;

/**
 * 
 * @author Joel Bout <joel@taotesting.com>
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 */
class taoResultServer_scripts_update_Updater extends \common_ext_ExtensionUpdater {

	/**
     * 
     * @param string $currentVersion
     * @return string $versionUpdatedTo
     */
    public function update($initialVersion) {

        $this->skip('2.6', '2.10.2');

        if ($this->isVersion('2.10.2')) {
            $this->getServiceManager()->register(ResultServerService::SERVICE_ID, new OntologyService());
            $this->setVersion('2.11.0');
        }

        $this->skip('2.11.0', '2.11.2');

        if ($this->isVersion('2.11.2')) {
            $this->getServiceManager()->register(QtiResultsService::SERVICE_ID, new QtiResultsService());
            $this->setVersion('2.12.0');
        }

        $this->skip('2.12.0', '3.2.0');
        if ($this->isVersion('3.2.0')) {
            $service = $this->getServiceManager()->get(QtiResultsService::SERVICE_ID);
            if (!$service instanceof ResultService) {
                $this->getServiceManager()->register(QtiResultsService::SERVICE_ID, new QtiResultsService());
            }
            $this->setVersion('3.2.1');
        }

        $this->skip('3.2.1', '3.2.5');
        
        if ($this->isVersion('3.2.5')) {
            // Create a file system for the extension.
            $serviceManager = $this->getServiceManager();
            $service = $serviceManager->get(FileSystemService::SERVICE_ID);
            $service->createFileSystem('taoResultServer');
            $serviceManager->register(FileSystemService::SERVICE_ID, $service);
            
            // Register VariableManager service.
            $variableManager = new DefaultVariableManager();
            $serviceManager->propagate($variableManager);
            $serviceManager->register(DefaultVariableManager::SERVICE_ID, $variableManager);
            
            $this->setVersion('3.3.0');
        }
    }
}

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
 * Copyright (c) 2014-2016 (original work) Open Assessment Technologies SA;
 *
 *
 */

use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\implementation\OntologyService;
use oat\taoResultServer\scripts\install\RegisterResultService;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\user\TaoRoles;
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
            $updater = new RegisterResultService();
            $updater([]);
            $this->setVersion('2.12.0');
        }

        $this->skip('2.12.0', '3.1.0');
    }
}

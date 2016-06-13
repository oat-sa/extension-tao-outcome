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

        $this->skip('2.6', '2.10.1');
        $this->skip('2.10.1', '2.11.0');

    }
}

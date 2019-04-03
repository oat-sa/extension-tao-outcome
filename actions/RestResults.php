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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\actions;

use oat\taoResultServer\models\classes\CrudResultsService;
use oat\taoResultServer\models\classes\CrudScorableResultsService;

class RestResults extends \tao_actions_CommonRestModule
{
    protected function getCrudService()
    {
        if ($this->hasGetParameter('onlyScorable') && $this->getGetParameter('onlyScorable') == 'true') {
            $this->service = CrudScorableResultsService::singleton();
        } else {
            $this->service = CrudResultsService::singleton();
        }
        return parent::getCrudService();
    }


    /**
     * Optionnaly a specific rest controller may declare
     * aliases for parameters used for the rest communication
     */
    protected function getParametersAliases()
    {
        return array_merge(parent::getParametersAliases(), array());
    }

    /**
     * Optional Requirements for parameters to be sent on every service
     *
     * You may use either the alias or the uri, if the parameter identifier
     * is set it will become mandatory for the operation in $key
     * Default Parameters Requirents are applied
     * type by default is not required and the root class type is applied
     *
     * @return array
     *
     */
    protected function getParametersRequirements()
    {
        return [];
    }


}

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
 * Copyright (c) 2013-2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\actions;

use oat\taoResultServer\models\classes\CrudResultsService;
use oat\taoResultServer\models\classes\CrudScorableResultsService;

class RestResults extends \tao_actions_CommonRestModule
{
    const ONLY_SCORABLE = 'onlyScorable';

    /** @var CrudResultsService */
    protected $resultService;

    /**
     * Get the service to manage delivery executions CRUD operations
     * If HTTP parameters "onlyScorable" is specified then use CrudScorableResultsService
     *
     * @return CrudResultsService
     */
    protected function getCrudService()
    {
        if (!$this->resultService) {
            $this->resultService = $this->propagate(
                ($this->hasGetParameter(self::ONLY_SCORABLE) && $this->getGetParameter(self::ONLY_SCORABLE) == 'true')
                    ? new CrudScorableResultsService()
                    : new CrudResultsService()
            );
        }
        return $this->resultService;
    }

    /**
     * Optional Requirements for parameters to be sent on every service
     *
     * You may use either the alias or the uri, if the parameter identifier
     * is set it will become mandatory for the operation in $key
     * Default Parameters Requirements are applied
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
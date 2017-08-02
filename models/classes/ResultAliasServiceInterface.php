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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

/**
 * Service is used to map delivery execution id and result aliases.
 * In tao delivery execution identifier and result identifier are equal
 * but some external services may have their own result identifiers (as LTI result id).
 *
 * Interface ResultIdService
 * @package oat\taoResultServer\models\classes
 */
interface ResultAliasServiceInterface
{
    const SERVICE_ID = 'taoResultServer/ResultAliasService';

    /**
     * Get result alias by delivery execution identifier.
     *
     * @param $deliveryExecutionId
     * @return array
     */
    public function getResultAlias($deliveryExecutionId);

    /**
     * Get delivery execution identifier by result alias. Returns null if not found
     *
     * @param $aliasId
     * @return string|null
     */
    public function getDeliveryExecutionId($aliasId);
}
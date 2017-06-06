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

use oat\oatbox\service\ConfigurableService;

/**
 * Class AbstractResultService
 * @package oat\taoResultServer\models\classes\implementation
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class AbstractResultService extends ConfigurableService
{
    /**
     * @param $class
     * @return \taoResultServer_models_classes_WritableResultStorage
     * @throws \common_exception_Error
     */
    public function instantiateResultStorage($class)
    {
        if (
            !class_exists($class) ||
            !is_subclass_of($class, \taoResultServer_models_classes_WritableResultStorage::class)
        ) {
            throw new \common_exception_Error(__('This delivery has no readable Result Server'));
        }
        if (is_subclass_of($class, ConfigurableService::class) && defined($class.'::SERVICE_ID')) {
            $storage = $this->getServiceManager()->get($class::SERVICE_ID);
        } else {
            $storage = new $class();
        }
        return $storage;
    }
}
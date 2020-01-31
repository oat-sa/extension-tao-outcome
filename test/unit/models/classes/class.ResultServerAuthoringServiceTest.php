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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\test\TestCase;

class ResultServerAuthoringTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testGetDefaultResultServer()
    {
        $extMock = $this->getMockBuilder(common_ext_Extension::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasConfig'])
            ->getMock();
        $extMock->method('hasConfig')->willReturn(false);

        $extManagerMock = $this->getMockBuilder(common_ext_ExtensionsManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionById'])
            ->getMock();
        $extManagerMock->method('getExtensionById')->willReturn($extMock);

        $serviceLocator = $this->getServiceLocatorMock([
            common_ext_ExtensionsManager::SERVICE_ID => $extManagerMock,
        ]);

        $service = new taoResultServer_models_classes_ResultServerAuthoringService();
        $service->setServiceLocator($serviceLocator);

        $this->assertInstanceOf(taoResultServer_models_classes_ResultServerAuthoringService::class, $service);
        
        $defaultResultServer = $service->getDefaultResultServer();

        $this->assertInstanceOf(core_kernel_classes_Resource::class, $defaultResultServer);
    }

    public function testSetDefaultResultServer()
    {
        $extMock = $this->getMockBuilder(common_ext_Extension::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasConfig', 'setConfig'])
            ->getMock();
        $extMock->method('hasConfig')->willReturn(false);
        $extMock->method('setConfig')->willReturn(true);

        $extManager = $this->getMockBuilder(common_ext_ExtensionsManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionById'])
            ->getMock();
        $extManager->method('getExtensionById')->willReturn($extMock);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            common_ext_ExtensionsManager::SERVICE_ID => $extManager,
        ]);

        $service = new taoResultServer_models_classes_ResultServerAuthoringService();
        $service->setServiceLocator($serviceLocatorMock);
        $defaultResultServer = $service->getDefaultResultServer();
        $service->setDefaultResultServer($defaultResultServer);

        $this->assertInstanceOf(taoResultServer_models_classes_ResultServerAuthoringService::class, $service);
        $this->assertInstanceOf(core_kernel_classes_Resource::class, $defaultResultServer);
    }
}

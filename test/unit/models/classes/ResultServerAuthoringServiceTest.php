<?php

use oat\generis\test\TestCase;
use oat\taoResultServer\models\classes\ResultServerService;

class ResultServerAuthoringServiceTest extends TestCase
{
    public function testGetDefaultResultServerVoid()
    {
        // Mock dependencies.
        $extMock = $this->getMockBuilder(common_ext_Extension::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['hasConfig'])
                        ->getMock();
        // hasConfig will return false in this case.
        $extMock->method('hasConfig')
                ->willReturn(false);

        $extManagerMock = $this->getMockBuilder(common_ext_ExtensionsManager::class)
                                ->setMethods(['getExtensionById'])
                                ->getMock();

        $extManagerMock->method('getExtensionById')
                       ->willReturn($extMock);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            common_ext_ExtensionsManager::SERVICE_ID => $extManagerMock
        ]);

        // Instantiate the service with mocked service locator.
        $service = new taoResultServer_models_classes_ResultServerAuthoringService();
        $service->setServiceLocator($serviceLocatorMock);

        $defaultResultServer = $service->getDefaultResultServer();

        // Make sure that when calling ::getDefaultResultServer in a context where there
        // is no specific configuration for the result server, the VOID result server resource
        // is returned.
        $this->assertInstanceOf(core_kernel_classes_Resource::class, $defaultResultServer);
        $this->assertEquals(ResultServerService::INSTANCE_VOID_RESULT_SERVER, $defaultResultServer->getUri());
    }

    public function testGetDefaultResultServerDefault()
    {
        // Mock dependencies.
        $extMock = $this->getMockBuilder(common_ext_Extension::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasConfig', 'getConfig'])
            ->getMock();
        // hasConfig will return true in this case.
        $extMock->method('hasConfig')
            ->willReturn(true);

        // The expected configuration for the default server URI is 'https://someuri.com#resource'.
        $expectedDefaultServerUri = 'https://someuri.com#resource';
        $extMock->method('getConfig')
            ->willReturn($expectedDefaultServerUri);

        $extManagerMock = $this->getMockBuilder(common_ext_ExtensionsManager::class)
            ->setMethods(['getExtensionById'])
            ->getMock();

        $extManagerMock->method('getExtensionById')
            ->willReturn($extMock);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            common_ext_ExtensionsManager::SERVICE_ID => $extManagerMock
        ]);

        // Instantiate the service with mocked service locator.
        $service = new taoResultServer_models_classes_ResultServerAuthoringService();
        $service->setServiceLocator($serviceLocatorMock);

        $defaultResultServer = $service->getDefaultResultServer();

        // Make sure that when calling ::getDefaultResultServer in a context where there
        // is a specific configuration for the result server, an appropriate resource is
        // returned based on the current configuration.
        $this->assertInstanceOf(core_kernel_classes_Resource::class, $defaultResultServer);
        $this->assertEquals($expectedDefaultServerUri, $defaultResultServer->getUri());
    }

    public function testSetDefaultResultServer()
    {
        $extMock = $this->getMockBuilder(common_ext_Extension::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfig'])
            ->getMock();

        $resource = new core_kernel_classes_Resource('myUri');

        $extMock->expects($this->once())
            ->method('setConfig')
            ->with(taoResultServer_models_classes_ResultServerAuthoringService::DEFAULT_RESULTSERVER_KEY, $resource->getUri());

        $extManagerMock = $this->getMockBuilder(common_ext_ExtensionsManager::class)
            ->setMethods(['getExtensionById'])
            ->getMock();

        $extManagerMock->method('getExtensionById')
            ->willReturn($extMock);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            common_ext_ExtensionsManager::SERVICE_ID => $extManagerMock
        ]);

        $service = new taoResultServer_models_classes_ResultServerAuthoringService();
        $service->setServiceLocator($serviceLocatorMock);

        $service->setDefaultResultServer($resource);
    }
}
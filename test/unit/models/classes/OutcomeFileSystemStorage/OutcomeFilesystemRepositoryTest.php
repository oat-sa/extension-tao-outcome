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

namespace oat\taoResultServer\models\classes\OutcomeFileSystemStorage;


use common_exception_Error;
use common_exception_NotFound;
use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoResultServer\models\classes\ResultStorageInterface;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use taoResultServer_models_classes_ResponseVariable;

class OutcomeFilesystemRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var OutcomeFilesystemRepository */
    private $repository;

    protected function setUp()
    {
        $this->repository = $this->getRepository();
    }

    /**
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws InvalidServiceManagerException
     */
    public function testGetVariable()
    {
        $variable = $this->repository->getVariable('callId', 'varId');

        $this->assertNotEmpty($variable);
        $this->assertInternalType('array', $variable);
        $this->assertInstanceOf(taoResultServer_models_classes_ResponseVariable::class, $variable[0]->variable);
        $this->assertEquals('file', $variable[0]->variable->getBaseType());
    }

    /**
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws InvalidServiceManagerException
     */
    public function testGetVariables()
    {
        $variable = $this->repository->getVariables('callId');

        $this->assertNotEmpty($variable);
        $this->assertInternalType('array', $variable);
        $this->assertInstanceOf(taoResultServer_models_classes_ResponseVariable::class, $variable[0][0]->variable);
        $this->assertEquals('file', $variable[0][0]->variable->getBaseType());
    }

    public function testStoreVariables()
    {
        $variable1 = $this->getVariable('file', 'fileContent');
        $variable2 = $this->getVariable('string', 'something');

        $this->repository->storeItemVariables(
            'deliveryId',
            'test',
            'item',
            [$variable1, $variable2],
            'callIdItem'
        );
    }

    public function testStoreVariable()
    {
        $variable = $this->getVariable('file', 'fileContent');

        $this->repository->storeItemVariable('deliveryId', 'test', 'item', $variable, 'callIdItem');
    }

    /**
     * @param string $baseType
     * @param string $value
     *
     * @return stdClass
     */
    private function getRawVariable($baseType, $value = '')
    {
        $rawVariable = new stdClass();
        $rawVariable->uri = 'uri';
        $rawVariable->class = 'class';
        $rawVariable->deliveryResultIdentifier = 'deliveryResultIdentifier';
        $rawVariable->callIdItem = 'callIdItem';
        $rawVariable->variable = $this->getVariable($baseType, $value);

        return $rawVariable;
    }

    private function getVariable($baseType, $value = '')
    {
        $variable = new taoResultServer_models_classes_ResponseVariable;
        $variable->setBaseType($baseType);
        $variable->setCandidateResponse($value);

        return $variable;
    }

    private function getRepository()
    {
        $rawVariable = $this->getRawVariable(OutcomeFilesystemRepository::BASE_TYPE_FILE_REFERENCE);
        $fileSystem = $this->getFileSystem();
        $dbStorage = $this->getDbStorage([$rawVariable]);

        /** @var OutcomeFilesystemRepository|PHPUnit_Framework_MockObject_MockObject $repository */
        $repository = $this->getMockBuilder(OutcomeFilesystemRepository::class)
            ->setMethods(['getDbStorage', 'getFileSystem', 'getFilePathFactory'])
            ->getMock();

        $repository->method('getDbStorage')->willReturn($dbStorage);
        $repository->method('getFileSystem')->willReturn($fileSystem);
        $repository->method('getFilePathFactory')->willReturn($this->getFilePathFactory());

        return $repository;
    }

    private function getFilePathFactory()
    {
        $mock = $this->getMockBuilder(FilePathFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getFilePath')->willReturn('filePath');

        return $mock;
    }

    private function getDbStorage(array $variables)
    {
        $dbStorage = $this->getMockBuilder(ResultStorageInterface::class)->getMock();
        $dbStorage->method('getVariable')->willReturn($variables);
        $dbStorage->method('getVariables')->willReturn([$variables]);

        $dbStorage->method('storeItemVariables')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->callback(
                function ($subjects) {
                    if (!is_array($subjects)) {
                        return false;
                    }

                    foreach ($subjects as $subject) {
                        if ($subject->getBaseType() === 'file') {
                            return false;
                        }
                    }

                    return true;
                }
            )
        );

        $dbStorage->method('storeItemVariable')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->callback(
                function ($subject) {
                    return $subject->getBaseType() !== 'file'
                        && $subject->getCandidateResponse() === 'filePath';
                }
            )
        );

        return $dbStorage;
    }

    private function getFileSystem()
    {
        return $this->getMockBuilder(FileSystem::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}

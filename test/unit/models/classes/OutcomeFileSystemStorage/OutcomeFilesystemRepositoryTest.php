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
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoResultServer\models\classes\ResultStorageInterface;
use PHPUnit\Framework\MockObject\MockObject as GenericMockObject;
use stdClass;
use taoResultServer_models_classes_ResponseVariable;

class OutcomeFilesystemRepositoryTest extends TestCase
{
    /** @var OutcomeFilesystemRepository */
    private $repository;
    /** @var ResultStorageInterface|GenericMockObject */
    private $dbStorage;

    protected function setUp(): void
    {
        $rawVariable = $this->getRawVariable(OutcomeFilesystemRepository::BASE_TYPE_FILE_REFERENCE);
        $this->dbStorage = $this->getDbStorage([$rawVariable]);

        $this->repository = $this->getRepository();
    }

    /**
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws InvalidServiceManagerException
     */
    public function testGetVariable(): void
    {
        $variable = $this->repository->getVariable('callId', 'varId');

        $this->assertNotEmpty($variable);
        $this->assertIsArray($variable);
        $this->assertInstanceOf(taoResultServer_models_classes_ResponseVariable::class, $variable[0]->variable);
        $this->assertEquals('file', $variable[0]->variable->getBaseType());
    }

    /**
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws InvalidServiceManagerException
     */
    public function testGetVariables(): void
    {
        $variable = $this->repository->getVariables('callId');

        $this->assertNotEmpty($variable);
        $this->assertIsArray($variable);
        $this->assertInstanceOf(taoResultServer_models_classes_ResponseVariable::class, $variable[0][0]->variable);
        $this->assertEquals('file', $variable[0][0]->variable->getBaseType());
    }

    /**
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function testStoreVariables(): void
    {
        $this->dbStorage->expects($this->once())->method('storeItemVariables');

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

    /**
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function testStoreVariable(): void
    {
        $this->dbStorage->expects($this->once())->method('storeItemVariable');

        $variable = $this->getVariable('file', 'fileContent');

        $this->repository->storeItemVariable('deliveryId', 'test', 'item', $variable, 'callIdItem');
    }

    /**
     * @param string $baseType
     * @param string $value
     *
     * @return stdClass
     */
    private function getRawVariable($baseType, $value = ''): stdClass
    {
        $rawVariable = new stdClass();
        $rawVariable->uri = 'uri';
        $rawVariable->class = 'class';
        $rawVariable->deliveryResultIdentifier = 'deliveryResultIdentifier';
        $rawVariable->callIdItem = 'callIdItem';
        $rawVariable->variable = $this->getVariable($baseType, $value);

        return $rawVariable;
    }

    private function getVariable($baseType, $value = ''): taoResultServer_models_classes_ResponseVariable
    {
        $variable = new taoResultServer_models_classes_ResponseVariable();
        $variable->setBaseType($baseType);
        $variable->setCandidateResponse($value);

        return $variable;
    }

    private function getRepository()
    {
        $fileSystem = $this->getFileSystem();

        /** @var OutcomeFilesystemRepository|MockObject $repository */
        $repository = $this->createPartialMock(
            OutcomeFilesystemRepository::class,
            ['getDbStorage', 'getFileSystem', 'getFilePathFactory']
        );

        $repository->method('getDbStorage')->willReturn($this->dbStorage);
        $repository->method('getFileSystem')->willReturn($fileSystem);
        $repository->method('getFilePathFactory')->willReturn($this->getFilePathFactory());

        return $repository;
    }

    private function getFilePathFactory()
    {
        $mock = $this->createMock(FilePathFactory::class);

        $mock->method('getFilePath')->willReturn('filePath');

        return $mock;
    }

    /**
     * @param array $variables
     *
     * @return ResultStorageInterface|GenericMockObject
     */
    private function getDbStorage(array $variables)
    {
        $dbStorage = $this->createMock(ResultStorageInterface::class);
        $dbStorage->method('getVariable')->willReturn($variables);
        $dbStorage->method('getVariables')->willReturn([$variables]);

        $dbStorage->method('storeItemVariables')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->callback(
                static function ($subjects) {
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
                static function ($subject) {
                    return $subject->getBaseType() !== 'file'
                        && $subject->getCandidateResponse() === 'filePath';
                }
            )
        );

        return $dbStorage;
    }

    /**
     * @return GenericMockObject|FileSystem
     */
    private function getFileSystem()
    {
        return $this->createMock(FileSystem::class);
    }
}

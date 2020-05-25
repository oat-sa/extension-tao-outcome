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

namespace oat\taoResultServer\test\Unit\models\classes\OutcomeFileSystemStorage;

use oat\generis\test\TestCase;
use oat\taoResultServer\models\classes\OutcomeFileSystemStorage\FilePathFactory;

class FilePathFactoryTest extends TestCase
{
    /** @var  FilePathFactory */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new FilePathFactory();
    }

    public function testGetDirPath()
    {
        $path = $this->factory->getDirPath('someIdentifier');

        // path example: 7/a/4/94d6734e0a9d6753da1ef6cf827f7
        $this->assertStringMatchesFormat('%c%e%c%e%c%e%x', $path);
    }

    public function testGetFilePath()
    {
        $path = $this->factory->getFilePath('someIdentifier');

        // path example: 7/a/4/94d6734e0a9d6753da1ef6cf827f7/68ab75dd015889510a7924acda29e0bc
        $this->assertStringMatchesFormat('%c%e%c%e%c%e%x%e%x', $path);
    }
}

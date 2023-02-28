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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoResultServer\test\Unit\models\Import\Task;

use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\taoResultServer\models\Import\Factory\ImportResultInputFactory;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use oat\taoResultServer\models\Import\Task\ResultImportScheduler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ResultImportSchedulerTest extends TestCase
{
    /** @var QueueDispatcher|MockObject */
    private QueueDispatcher $queueDisaptcher;
    /** @var ImportResultInputFactory|MockObject */
    private ImportResultInputFactory $importResultInputFactory;
    private ResultImportScheduler $sut;

    public function setUp(): void
    {
        $this->queueDisaptcher = $this->createMock(QueueDispatcher::class);
        $this->importResultInputFactory = $this->createMock(ImportResultInputFactory::class);
        $this->sut = new ResultImportScheduler($this->queueDisaptcher, $this->importResultInputFactory);
    }

    public function testScheduleByRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $input = new ImportResultInput('id', true);
        $task = $this->createMock(TaskInterface::class);

        $this->queueDisaptcher
            ->expects($this->once())
            ->method('createTask')
            ->willReturn($task);

        $this->importResultInputFactory
            ->expects($this->once())
            ->method('createFromRequest')
            ->willReturn($input);

        $this->assertSame($task, $this->sut->scheduleByRequest($request));
    }
}

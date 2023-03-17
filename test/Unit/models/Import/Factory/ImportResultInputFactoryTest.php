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

namespace oat\taoResultServer\test\Unit\models\Import\Factory;

use common_exception_MissingParameter;
use common_exception_ResourceNotFound;
use GuzzleHttp\Psr7\ServerRequest;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\Import\Factory\ImportResultInputFactory;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportResultInputFactoryTest extends TestCase
{
    /**
     * @var DeliveryExecutionService|MockObject
     */
    private DeliveryExecutionService $deliveryExecutionService;

    private ImportResultInputFactory $sut;

    public function setUp(): void
    {
        $this->deliveryExecutionService = $this->createMock(DeliveryExecutionService::class);
        $this->sut = new ImportResultInputFactory($this->deliveryExecutionService);
    }

    public function testCreateSuccessfully(): void
    {
        $request = $this->createRequest(
            [
                'itemVariables' => [
                    [
                        'itemId' => 'item-1',
                        'outcomes' => [
                            [
                                'id' => 'SCORE',
                                'value' => 0.8
                            ]
                        ],
                        'responses' => [
                            [
                                'id' => 'RESPONSE',
                                'correctResponse' => true
                            ]
                        ]
                    ]
                ]
            ],
            'deliveryExecutionId',
            'true'
        );

        $deliveryExecution = $this->createMock(DeliveryExecution::class);

        $this->deliveryExecutionService
            ->expects($this->once())
            ->method('getDeliveryExecution')
            ->willReturn($deliveryExecution);

        $deliveryExecution
            ->expects($this->once())
            ->method('getFinishTime')
            ->willReturn('time');

        $expected = new ImportResultInput(
            'deliveryExecutionId',
            true
        );
        $expected->addOutcome('item-1', 'SCORE', 0.8);
        $expected->addResponse('item-1', 'RESPONSE', ['correctResponse' => true]);

        $this->assertSame($expected->jsonSerialize(), $this->sut->createFromRequest($request)->jsonSerialize());
    }

    public function testValidateMissingItemResponse(): void
    {
        $request = $this->createRequest(
            [
                'itemVariables' => [
                    [
                        'itemId' => 'item-1',
                        'outcomes' => [
                            [
                                'id' => 'SCORE',
                                'value' => 0.8
                            ]
                        ],
                        'responses' => [
                            [
                                'id' => 'RESPONSE',
                            ]
                        ]
                    ]
                ]
            ],
            'deliveryExecutionId',
            'true'
        );

        $deliveryExecution = $this->createMock(DeliveryExecution::class);

        $this->deliveryExecutionService
            ->expects($this->once())
            ->method('getDeliveryExecution')
            ->willReturn($deliveryExecution);

        $deliveryExecution
            ->expects($this->once())
            ->method('getFinishTime')
            ->willReturn('time');

        $this->expectException(common_exception_MissingParameter::class);
        $this->expectExceptionMessage('Expected parameter id|correctResponse is missing');

        $this->sut->createFromRequest($request)->jsonSerialize();
    }

    public function testValidateMissingItemVariables(): void
    {
        $request = $this->createRequest(
            [
                'itemVariables' => [
                    [
                        'itemId' => 'item-1',
                        'outcomes' => [
                            [
                            ]
                        ]
                    ]
                ]
            ],
            'deliveryExecutionId',
            'true'
        );

        $deliveryExecution = $this->createMock(DeliveryExecution::class);

        $this->deliveryExecutionService
            ->expects($this->once())
            ->method('getDeliveryExecution')
            ->willReturn($deliveryExecution);

        $deliveryExecution
            ->expects($this->once())
            ->method('getFinishTime')
            ->willReturn('time');

        $this->expectException(common_exception_MissingParameter::class);
        $this->expectExceptionMessage('Expected parameter id|value is missing');

        $this->sut->createFromRequest($request);
    }

    public function testValidateMissingItemVariablesOutcomes(): void
    {
        $request = $this->createRequest(
            [
                'itemVariables' => [
                    []
                ]
            ],
            'deliveryExecutionId',
            'true'
        );

        $deliveryExecution = $this->createMock(DeliveryExecution::class);

        $this->deliveryExecutionService
            ->expects($this->once())
            ->method('getDeliveryExecution')
            ->willReturn($deliveryExecution);

        $deliveryExecution
            ->expects($this->once())
            ->method('getFinishTime')
            ->willReturn('time');

        $this->expectException(common_exception_MissingParameter::class);
        $this->expectExceptionMessage('Expected parameter itemId|outcomes is missing');

        $this->sut->createFromRequest($request);
    }

    public function testValidateMissingExecutionId(): void
    {
        $request = new ServerRequest('patch', 'https://myuri.com');

        $this->expectException(common_exception_MissingParameter::class);
        $this->expectExceptionMessage('Expected parameter execution is missing');

        $this->sut->createFromRequest($request);
    }

    public function testValidateExecutionNotFinished(): void
    {
        $request = new ServerRequest('patch', 'https://myuri.com');
        $request = $request->withQueryParams(['execution' => 'id']);

        $deliveryExecution = $this->createMock(DeliveryExecution::class);

        $this->deliveryExecutionService
            ->expects($this->once())
            ->method('getDeliveryExecution')
            ->willReturn($deliveryExecution);

        $deliveryExecution
            ->expects($this->once())
            ->method('getFinishTime')
            ->willReturn(null);

        $this->expectException(common_exception_ResourceNotFound::class);
        $this->expectExceptionMessage('Finished delivery execution id not found');

        $this->sut->createFromRequest($request);
    }

    private function createRequest(array $content, string $execution, string $sendAgs): ServerRequest
    {
        $request = new ServerRequest(
            'patch',
            'https://myuri.com',
            [
                'Content-type' => 'application/json'
            ],
            json_encode($content)
        );

        return $request->withQueryParams(
            [
                'execution' => $execution,
                'send_ags' => $sendAgs,
            ]
        );
    }
}

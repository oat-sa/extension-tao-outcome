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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoResultServer\test\Unit\models\AssessmentResultResolver;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use oat\dtms\DateTime;
use oat\taoResultServer\models\AssessmentResultResolver\AssessmentResultFileResponseResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\results\AssessmentResult;
use qtism\data\results\CandidateResponse;
use qtism\data\results\Context;
use qtism\data\results\ItemResult;
use qtism\data\results\ItemResultCollection;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\ResultResponseVariable;
use qtism\data\results\SessionStatus;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use Ramsey\Uuid\Uuid;
use tao_helpers_File;

class AssessmentResultFileResponseResolverTest extends TestCase
{
    private const FILE_DOWNLOAD_URL = 'https://localhost/file/download';
    private const FILE_NAME = 'filename';

    /** @var AssessmentResultFileResponseResolver */
    private $subject;
    /** @var ClientInterface */
    private $clientMock;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->subject = new AssessmentResultFileResponseResolver($this->clientMock);
    }

    public function testSuccessResolveFullPossibleFileResponseChangedResponse()
    {
        [$filename, $mimeType, $fileContent] = $this->buildFileData();

        $streamResponseMock = $this->createMock(StreamInterface::class);
        $streamResponseMock->expects(self::once())
            ->method('getContents')
            ->willReturn($fileContent);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamResponseMock);
        $responseMock->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(random_int(200, 399));

        $this->clientMock
            ->expects(self::once())
            ->method('request')->willReturn($responseMock);

        $fileResponseValue = new Value(sprintf(
            '%s,%s,base64,%s,download_url,https://localhost/file/download',
            $filename,
            $mimeType,
            base64_encode($fileContent)
        ));

        $this->subject->resolve($this->buildAssessmentResult($fileResponseValue));

        self::assertEquals(
            $this->encodeFile($filename, $mimeType, $fileContent),
            $fileResponseValue->getValue()
        );
    }

    public function testSuccessResolveMinimalPossibleFileResponseChangedResponse()
    {
        [$filename, $mimeType, $fileContent] = $this->buildFileData();

        $streamResponseMock = $this->createMock(StreamInterface::class);
        $streamResponseMock->expects(self::once())
            ->method('getContents')
            ->willReturn($fileContent);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamResponseMock);
        $responseMock->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(random_int(200, 399));

        $this->clientMock
            ->expects(self::once())
            ->method('request')->willReturn($responseMock);

        $fileResponseValue = new Value(sprintf('%s,download_url,https://localhost/file/download', $filename));

        $this->subject->resolve($this->buildAssessmentResult($fileResponseValue));

        self::assertEquals(
            $this->encodeFile($filename, $mimeType, $fileContent),
            $fileResponseValue->getValue()
        );
    }

    private function testResolveWithoutDownloadUrl()
    {
        $randomValue = Uuid::uuid4()->toString();
        $fileResponseValue = new Value($randomValue);

        $this->subject->resolve($this->buildAssessmentResult($fileResponseValue));

        self::assertEquals(
            $randomValue,
            $fileResponseValue->getValue()
        );
    }

    private function testResolveWithErrorResponseStatusCode()
    {
        [$filename, $mimeType, $fileContent] = $this->buildFileData();
        $responseValueContent = sprintf(
            '%s,%s,base64,%s,download_url,https://localhost/file/download',
            $filename,
            $mimeType,
            base64_encode($fileContent)
        );

        $streamResponseMock = $this->createMock(StreamInterface::class);
        $streamResponseMock->expects(self::once())
            ->method('getContents')
            ->willReturn($fileContent);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamResponseMock);
        $responseMock->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(random_int(400, 599));

        $this->clientMock
            ->expects(self::once())
            ->method('request')->willReturn($responseMock);

        $fileResponseValue = new Value($responseValueContent);

        $this->subject->resolve($this->buildAssessmentResult($fileResponseValue));

        self::assertEquals(
            $responseValueContent,
            $fileResponseValue->getValue()
        );
    }

    private function testResolveWithRequestException()
    {
        [$filename, $mimeType, $fileContent] = $this->buildFileData();
        $responseValueContent = sprintf(
            '%s,%s,base64,%s,download_url,https://localhost/file/download',
            $filename,
            $mimeType,
            base64_encode($fileContent)
        );

        $streamResponseMock = $this->createMock(StreamInterface::class);
        $streamResponseMock->expects(self::never())
            ->method('getContents')
            ->willReturn($fileContent);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::never())
            ->method('getBody')
            ->willReturn($streamResponseMock);
        $responseMock->expects(self::never())
            ->method('getStatusCode');

        $this->clientMock
            ->expects(self::once())
            ->method('request')->willThrowException($this->createMock(GuzzleException::class));

        $fileResponseValue = new Value($responseValueContent);

        $this->subject->resolve($this->buildAssessmentResult($fileResponseValue));

        self::assertEquals(
            $responseValueContent,
            $fileResponseValue->getValue()
        );
    }

    private function buildAssessmentResult(Value $responseValue): AssessmentResult
    {
        return new AssessmentResult(
            new Context(),
            null,
            new ItemResultCollection([
                new ItemResult(
                    new QtiIdentifier('item'),
                    new DateTime(),
                    SessionStatus::STATUS_FINAL,
                    new ItemVariableCollection([
                        new ResultResponseVariable(
                            new QtiIdentifier('RESPONSE'),
                            Cardinality::SINGLE,
                            new CandidateResponse(new ValueCollection([
                                $responseValue
                            ])),
                            BaseType::FILE
                        )
                    ])
                )
            ])
        );
    }

    private function getRandomFileExtension(): string
    {
        $mimeTypes = tao_helpers_File::getMimeTypeList();
        return array_rand($mimeTypes);
    }

    private function encodeFile(string $fileName, string $mimeType, string $binaryContent): string
    {
        $packedUnsignedShortFileNameLen = pack('S', strlen($fileName));
        $packedUnsignedShortMimeTypeLen = pack('S', strlen($mimeType));

        return $packedUnsignedShortFileNameLen . $fileName . $packedUnsignedShortMimeTypeLen . $mimeType . $binaryContent;
    }

    /**
     * @return array [$filename,$mimeType,$fileContent]
     * @throws \Exception
     */
    private function buildFileData(): array
    {
        $fileContent = Uuid::uuid4()->toString();

        $mimeTypes = tao_helpers_File::getMimeTypeList();
        $randomExtension = array_rand($mimeTypes);
        $mimeType = $mimeTypes[$randomExtension];

        $filename = self::FILE_NAME . '.' . $randomExtension;

        return [$filename, $mimeType, $fileContent];
    }
}

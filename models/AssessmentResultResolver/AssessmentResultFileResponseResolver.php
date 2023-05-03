<?php

namespace oat\taoResultServer\models\AssessmentResultResolver;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\data\results\AssessmentResult;
use qtism\data\results\ItemResult;
use qtism\data\results\ResultResponseVariable;
use qtism\data\state\Value;
use qtism\data\storage\xml\XmlResultDocument;
use qtism\data\storage\xml\XmlStorageException;
use tao_helpers_File;

class AssessmentResultFileResponseResolver
{
    private const PATTERN = '/^(?<' . self::FILENAME_KEY . '>[^,]+).*download_url,(?<' . self::DOWNLOAD_URL_KEY . '>[^,]+)/';
    private const FILENAME_KEY = 'fileName';
    private const DOWNLOAD_URL_KEY = 'downloadUrl';

    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function resolve(AssessmentResult $assessmentResult): AssessmentResult
    {
        /** @var ItemResult $itemResult */
        foreach ($assessmentResult->getItemResults() as $itemResult) {
            foreach ($itemResult->getItemVariables() as $itemVariable) {
                if (
                    $itemVariable instanceof ResultResponseVariable &&
                    $itemVariable->getBaseType() === BaseType::FILE
                ) {
                    /** @var Value $value */
                    foreach ($itemVariable->getCandidateResponse()->getValues() as $value) {
                        $value->setValue($this->resolveFileString($value->getValue()));
                    }
                }
            }
        }

        return $assessmentResult;
    }

    /**
     * @throws XmlStorageException
     */
    private function convertXmlTotAssessmentResul(string $xml): AssessmentResult
    {
        $xmlResultDocument = new XmlResultDocument();
        $xmlResultDocument->loadFromString($xml);
        $assessmentResult = $xmlResultDocument->getDocumentComponent();
        if (!$assessmentResult instanceof AssessmentResult) {
            throw new InvalidArgumentException('Unsupported xml provided');
        }
        return $assessmentResult;
    }

    private function resolveFileString($value)
    {
        $matches = [];
        preg_match(self::PATTERN, $value, $matches);
        if (!array_key_exists(self::DOWNLOAD_URL_KEY, $matches)) {
            return $value;
        }
        $downloadUrl = $matches[self::DOWNLOAD_URL_KEY];
        $fileName = $matches[self::FILENAME_KEY];
        $fileMimeType = tao_helpers_File::getMimeType($fileName);

        try {
            $response = $this->client->request('GET', $downloadUrl);
            if ($response->getStatusCode() >= 400) {
                return $fileName;
            }
        } catch (GuzzleException $e) {
            return $fileName;
        }

        return $this->encodeFile($fileName, $fileMimeType, $response->getBody()->getContents());
    }

    private function encodeFile(string $fileName, string $mimeType, string $binaryContent): string
    {
        $packedUnsignedShortFileNameLen = pack('S', strlen($fileName));
        $packedUnsignedShortMimeTypeLen = pack('S', strlen($mimeType));

        return $packedUnsignedShortFileNameLen . $fileName . $packedUnsignedShortMimeTypeLen . $mimeType . $binaryContent;
    }
}

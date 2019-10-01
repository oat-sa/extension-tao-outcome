<?php

namespace oat\taoResultServer\models\Parser;

use oat\oatbox\service\ConfigurableService;
use oat\taoResultServer\models\Mapper\ResultMapper;
use qtism\data\results\AssessmentResult;
use qtism\data\storage\xml\XmlResultDocument;
use qtism\data\storage\xml\XmlStorageException;

class QtiResultParser extends ConfigurableService
{
    /**
     * @param $xml
     * @return mixed
     * @throws XmlStorageException
     */
    public function parse($xml)
    {
        if (!is_string($xml)) {
            throw new \LogicException('Qti Result parser expects a string as data source.');
        }

        $doc = new XmlResultDocument();
        $doc->loadFromString($xml, true);

        /** @var AssessmentResult $assessmentResult */
        return $this->getMapper()->loadSource($doc->getDocumentComponent());
    }

    protected function getMapper()
    {
        return $this->getServiceLocator()->get(ResultMapper::class);
    }

}
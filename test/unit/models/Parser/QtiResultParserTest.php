<?php


namespace oat\taoResultServer\test\unit\models\Parser;


use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\taoResultServer\models\classes\QtiResultsService;
use oat\taoResultServer\models\classes\ResultService;
use oat\taoResultServer\models\Parser\QtiResultParser;
use qtism\data\storage\xml\XmlResultDocument;

class QtiResultParserTest extends TestCase
{
    public function testParse()
    {
        $xml = file_get_contents(__DIR__ . '/../../../resources/result/qti-result-de-1.xml');
        $doc = new XmlResultDocument();
        try {
        $doc->loadFromString($xml, true     );

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        die();

        $parser = new QtiResultParser();
        $parser->parse($doc);
        $parser->getItemVariables($doc);
    }

    public function testInjectVariables()
    {
        $deliveryExecutionId = 'http://www.taotesting.com/ontologies/community.rdf#i5d8df66b9eb6820054edc738f43c48cc95';
        $xml = file_get_contents(__DIR__ . '/../../../resources/result/qti-result-de.xml');

        /** @var QtiResultsService $service */
        $deliveryExecutionId = 'http://www.taotesting.com/ontologies/community.rdf#i5d8df66b9eb6820054edc738f43c48cc95';
        $xml = file_get_contents(__DIR__ . '/../../../resources/result/qti-result-de.xml');

        /** @var QtiResultsService $service */
        $service = ServiceManager::getServiceManager()->get(ResultService::SERVICE_ID);
        $service->injectXmlResultToDewliveryExecution($deliveryExecutionId, $xml);
//        $service = new QtiinjectXmlResultToDeliveryExecution
    }
}
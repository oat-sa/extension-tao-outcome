<?php

namespace oat\taoResultServer\models\Import;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\oatbox\event\EventManager;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Factory\ImportResultInputFactory;
use oat\taoResultServer\models\Import\Factory\QtiResultXmlFactory;
use oat\taoResultServer\models\Import\Service\QtiResultXmlImporter;
use oat\taoResultServer\models\Import\Service\SendCalculatedResultService;
use oat\taoResultServer\models\Import\Task\ResultImportScheduler;
use oat\taoResultServer\models\Parser\QtiResultParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use taoQtiTest_models_classes_QtiTestService;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ImportServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(QtiResultXmlFactory::class, QtiResultXmlFactory::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ResultServerService::SERVICE_ID)
                ]
            );

        $services->set(QtiResultXmlImporter::class, QtiResultXmlImporter::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ResultServerService::SERVICE_ID),
                    service(QtiResultXmlFactory::class),
                    service(QtiResultParser::class),
                    service(taoQtiTest_models_classes_QtiTestService::class),
                    service(DeliveryExecutionService::SERVICE_ID),
                ]
            );

        $services->set(ResultImportScheduler::class, ResultImportScheduler::class)
            ->public()
            ->args(
                [
                    service(QueueDispatcher::SERVICE_ID),
                    service(ImportResultInputFactory::class),
                ]
            );

        $services->set(SendCalculatedResultService::class, SendCalculatedResultService::class)
            ->public()
            ->args(
                [
                    service(ResultServerService::SERVICE_ID),
                    service(EventManager::SERVICE_ID),
                    service(DeliveryExecutionService::SERVICE_ID),
                ]
            );

        $services->set(ImportResultInputFactory::class, ImportResultInputFactory::class)
            ->public()
            ->args(
                [
                    service(DeliveryExecutionService::SERVICE_ID),
                ]
            );
    }
}

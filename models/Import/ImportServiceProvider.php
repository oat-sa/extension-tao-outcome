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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\Import;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\oatbox\event\EventManager;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoDeliveryRdf\model\DeliveryContainerService;
use oat\taoQtiTest\models\runner\QtiRunnerService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Import\Factory\ImportResultInputFactory;
use oat\taoResultServer\models\Import\Factory\QtiResultXmlFactory;
use oat\taoResultServer\models\Import\Service\QtiResultXmlImporter;
use oat\taoResultServer\models\Import\Service\QtiTestItemsService;
use oat\taoResultServer\models\Import\Service\ResultImporter;
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

        $services->set(ResultImporter::class, ResultImporter::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ResultServerService::SERVICE_ID),
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


        $services->set(QtiTestItemsService::class, QtiTestItemsService::class)
            ->public()
            ->args(
                [

                    service(QtiRunnerService::SERVICE_ID),
                    service(DeliveryExecutionService::SERVICE_ID),
                    service(DeliveryContainerService::SERVICE_ID),
                ]
            );

        $services->set(SendCalculatedResultService::class, SendCalculatedResultService::class)
            ->public()
            ->args(
                [
                    service(ResultServerService::SERVICE_ID),
                    service(EventManager::SERVICE_ID),
                    service(DeliveryExecutionService::SERVICE_ID),
                    service(QtiTestItemsService::class),
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

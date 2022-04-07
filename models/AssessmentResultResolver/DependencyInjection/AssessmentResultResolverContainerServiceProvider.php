<?php

namespace oat\taoResultServer\models\AssessmentResultResolver\DependencyInjection;

use GuzzleHttp\Client;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\taoResultServer\models\AssessmentResultResolver\AssessmentResultFileResponseResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;

class AssessmentResultResolverContainerServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(AssessmentResultFileResponseResolver::class, AssessmentResultFileResponseResolver::class)
            ->public()
            ->args([
                inline_service(Client::class)
            ]);
    }
}
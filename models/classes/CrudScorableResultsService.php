<?php

namespace oat\taoResultServer\models\classes;

use oat\taoResultServer\models\classes\scorableResult\DeliveryExecutionFilter;

class CrudScorableResultsService extends CrudResultsService
{
    protected function filter(array $deliveryExecutions)
    {
        return $this->getServiceLocator()
            ->get(DeliveryExecutionFilter::SERVICE_ID)
            ->filter(
                $deliveryExecutions,
                [DeliveryExecutionFilter::ONLY_SCORABLE => true]
            );
    }
}
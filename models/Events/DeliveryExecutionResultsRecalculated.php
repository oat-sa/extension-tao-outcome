<?php

namespace oat\taoResultServer\models\Events;

use oat\oatbox\event\Event;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class DeliveryExecutionResultsRecalculated implements Event
{
    private DeliveryExecutionInterface $deliveryExecution;
    private ?float $totalScore;
    private ?float $totalMaxScore;

    public function __construct(
        DeliveryExecutionInterface $deliveryExecution,
        ?float $totalScore,
        ?float $totalMaxScore
    ) {
        $this->deliveryExecution = $deliveryExecution;
        $this->totalScore = $totalScore;
        $this->totalMaxScore = $totalMaxScore;
    }

    public function getDeliveryExecution(): DeliveryExecutionInterface
    {
        return $this->deliveryExecution;
    }

    public function getName(): string
    {
        return __CLASS__;
    }

    public function getScore(): ?float
    {
        return $this->totalScore;
    }

    public function getMaxScore(): ?float
    {
        return $this->totalMaxScore;
    }
}

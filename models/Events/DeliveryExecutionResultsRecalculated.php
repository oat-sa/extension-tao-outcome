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
 * Copyright (c) 2021-2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\Events;

use oat\oatbox\event\Event;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;

class DeliveryExecutionResultsRecalculated implements Event
{
    private DeliveryExecutionInterface $deliveryExecution;
    private ?float $totalScore;
    private ?float $totalMaxScore;
    private bool $isFullyGraded;
    private ?int $gradingTimestamp;

    public function __construct(
        DeliveryExecutionInterface $deliveryExecution,
        ?float $totalScore,
        ?float $totalMaxScore,
        bool $isFullyGraded,
        ?int $gradingTimestamp
    ) {
        $this->deliveryExecution = $deliveryExecution;
        $this->totalScore = $totalScore;
        $this->totalMaxScore = $totalMaxScore;
        $this->isFullyGraded = $isFullyGraded;
        $this->gradingTimestamp = $gradingTimestamp;
    }

    public function getDeliveryExecution(): DeliveryExecutionInterface
    {
        return $this->deliveryExecution;
    }

    public function getName(): string
    {
        return self::class;
    }

    public function getTotalScore(): ?float
    {
        return $this->totalScore;
    }

    public function getTotalMaxScore(): ?float
    {
        return $this->totalMaxScore;
    }

    public function isFullyGraded(): bool
    {
        return $this->isFullyGraded;
    }

    public function getGradingTimestamp(): ?int
    {
        return $this->gradingTimestamp;
    }
}

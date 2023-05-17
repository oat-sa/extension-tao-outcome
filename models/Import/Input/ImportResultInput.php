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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoResultServer\models\Import\Input;

use JsonSerializable;

class ImportResultInput implements JsonSerializable
{
    private string $deliveryExecutionId;
    private bool $sendAgs;
    private array $outcomes;
    private array $responses;

    public function __construct(string $deliveryExecutionId, bool $sendAgs)
    {
        $this->deliveryExecutionId = $deliveryExecutionId;
        $this->sendAgs = $sendAgs;
        $this->outcomes = [];
        $this->responses = [];
    }

    public function getDeliveryExecutionId(): string
    {
        return $this->deliveryExecutionId;
    }

    public function isSendAgs(): bool
    {
        return $this->sendAgs;
    }

    public function addOutcome(string $itemId, string $outcomeId, float $outcomeValue): void
    {
        $this->outcomes[$itemId][$outcomeId] = $outcomeValue;
    }

    public function addResponse(string $itemId, string $responseId, array $values): void
    {
        $this->responses[$itemId][$responseId] = $values;
    }

    /**
     * [
     *    'itemId' => [
     *        'outcomeId' => 'outcomeValue'
     *    ]
     * ]
     *
     * @return array
     */
    public function getOutcomes(): array
    {
        return $this->outcomes;
    }

    public function hasOutcomes(): bool
    {
        return count($this->outcomes) > 0;
    }

    /**
     * [
     *    'itemId' => [
     *        'responseId' => [
     *            'correctResponse' => true
     *        ]
     *    ]
     * ]
     *
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public static function fromJson(array $json): self
    {
        $new = new self($json['deliveryExecutionId'], $json['sendAgs']);

        foreach ($json['outcomes'] as $itemId => $values) {
            foreach ($values as $outcomeId => $outcomeValue) {
                $new->addOutcome($itemId, $outcomeId, $outcomeValue);
            }
        }

        foreach ($json['responses'] as $itemId => $values) {
            foreach ($values as $responseId => $responseValues) {
                $new->addResponse(
                    $itemId,
                    $responseId,
                    $responseValues
                );
            }
        }

        return $new;
    }

    public function jsonSerialize(): array
    {
        return [
            'deliveryExecutionId' => $this->deliveryExecutionId,
            'sendAgs' => $this->sendAgs,
            'outcomes' => $this->outcomes,
            'responses' => $this->responses,
        ];
    }
}

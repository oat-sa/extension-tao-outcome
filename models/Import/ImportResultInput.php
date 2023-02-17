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

namespace oat\taoResultServer\models\Import;

use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;

class ImportResultInput implements JsonSerializable
{
    private string $deliveryExecutionId;
    private bool $sendAgs;
    private array $outcomes;

    public function __construct(string $deliveryExecutionId, bool $sendAgs)
    {
        $this->deliveryExecutionId = $deliveryExecutionId;
        $this->sendAgs = $sendAgs;
        $this->outcomes = [];
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

    public static function fromRequest(ServerRequestInterface $request): self
    {
        $queryParams = $request->getQueryParams();
        $body = json_decode((string)$request->getBody(), true);
        $new = new self(
            $queryParams['execution'],
            isset($queryParams['send_ags']) && $queryParams['send_ags'] !== false
        );

        foreach ($body as $item) {
            foreach ($item['outcomes'] ?? [] as $outcome) {
                $new->addOutcome($item['itemId'], $outcome['id'], (float)$outcome['value']);
            }
        }

        return $new;
    }

    public static function fromJson(array $json): self
    {
        $new = new self($json['deliveryExecutionId'], $json['sendAgs']);

        foreach ($json['outcomes'] as $itemId => $values) {
            foreach ($values as $outcomeId => $outcomeValue) {
                $new->addOutcome($itemId, $outcomeId, $outcomeValue);
            }
        }

        return $new;
    }

    public function jsonSerialize(): array
    {
        return [
            'deliveryExecutionId' => $this->deliveryExecutionId,
            'outcomes' => $this->outcomes,
            'sendAgs' => $this->sendAgs,
        ];
    }
}

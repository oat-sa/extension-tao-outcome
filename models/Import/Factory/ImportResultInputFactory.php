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

namespace oat\taoResultServer\models\Import\Factory;

use common_exception_MissingParameter;
use common_exception_NotFound;
use common_exception_ResourceNotFound;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoResultServer\models\Import\Exception\ImportResultException;
use oat\taoResultServer\models\Import\Input\ImportResultInput;
use Psr\Http\Message\ServerRequestInterface;

class ImportResultInputFactory
{
    private const Q_PARAM_DELIVERY_EXECUTION_ID = 'execution';
    private const Q_PARAM_TRIGGER_AGS_SEND = 'send_ags';

    private DeliveryExecutionService $deliveryExecutionService;
    private array $allowedOutcomes;

    public function __construct(DeliveryExecutionService $deliveryExecutionService, array $allowedOutcomes = ['SCORE'])
    {
        $this->deliveryExecutionService = $deliveryExecutionService;
        $this->allowedOutcomes = $allowedOutcomes;
    }

    /**
     * @throws common_exception_NotFound
     * @throws common_exception_MissingParameter
     * @throws common_exception_ResourceNotFound
     * @throws ImportResultException
     */
    public function createFromRequest(ServerRequestInterface $request): ImportResultInput
    {
        $params = $request->getQueryParams();
        $body = json_decode((string)$request->getBody(), true);

        if (!isset($params[self::Q_PARAM_DELIVERY_EXECUTION_ID])) {
            throw new common_exception_MissingParameter(self::Q_PARAM_DELIVERY_EXECUTION_ID);
        };

        $deliveryExecution = $this->deliveryExecutionService
            ->getDeliveryExecution($params[self::Q_PARAM_DELIVERY_EXECUTION_ID]);

        if (!$deliveryExecution->getFinishTime()) {
            throw new common_exception_ResourceNotFound(
                sprintf(
                    'Finished delivery execution %s not found',
                    $params[self::Q_PARAM_DELIVERY_EXECUTION_ID]
                )
            );
        }

        $new = new ImportResultInput(
            $params[self::Q_PARAM_DELIVERY_EXECUTION_ID],
            isset($params[self::Q_PARAM_TRIGGER_AGS_SEND]) && $params[self::Q_PARAM_TRIGGER_AGS_SEND] !== 'false'
        );

        foreach ($body['itemVariables'] ?? [] as $item) {
            if (!isset($item['itemId'], $item['outcomes'])) {
                throw new common_exception_MissingParameter('itemId|outcomes');
            }

            foreach ($item['outcomes'] ?? [] as $outcome) {
                if (!isset($outcome['id'], $outcome['value'])) {
                    throw new common_exception_MissingParameter('id|value');
                }

                if (!in_array($outcome['id'], $this->allowedOutcomes, true)) {
                    throw new ImportResultException(
                        sprintf(
                            'Outcome %s not supported',
                            $outcome['id']
                        )
                    );
                }

                $new->addOutcome((string)$item['itemId'], (string)$outcome['id'], (float)$outcome['value']);
            }
        }

        return $new;
    }
}

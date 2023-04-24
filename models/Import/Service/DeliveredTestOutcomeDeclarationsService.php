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

namespace oat\taoResultServer\models\Import\Service;

use oat\taoDeliveryRdf\model\DeliveryContainerService;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoQtiTest\models\runner\QtiRunnerService;
use oat\taoQtiTest\models\runner\QtiRunnerServiceContext;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\TestPart;
use taoQtiTest_helpers_Utils;

class DeliveredTestOutcomeDeclarationsService
{
    private QtiRunnerService $qtiRunnerService;
    private DeliveryExecutionService $deliveryExecutionService;
    private DeliveryContainerService $deliveryContainerService;

    public function __construct(
        QtiRunnerService $qtiRunnerService,
        DeliveryExecutionService $deliveryExecutionService,
        DeliveryContainerService $deliveryContainerService
    ) {

        $this->qtiRunnerService = $qtiRunnerService;
        $this->deliveryExecutionService = $deliveryExecutionService;
        $this->deliveryContainerService = $deliveryContainerService;
    }

    public function getDeliveredTestOutcomeDeclarations(string $deliveryExecutionId): array
    {
        $context = $this->getServiceContext($deliveryExecutionId);
        $testDefinition = $this->getDefinition($context);
        $items = [];
        /** @var TestPart $testPart */
        foreach ($testDefinition->getTestParts() as $testPart) {
            /** @var ExtendedAssessmentSection $section */
            foreach ($testPart->getAssessmentSections() as $section) {
                $items = [];
                /** @var ExtendedAssessmentItemRef $item */
                foreach ($section->getSectionParts() as $item) {
                    $itemData = $this->qtiRunnerService->getItemData($context, $item->getHref());
                    $itemData['data']['isExternallyScored'] = $this->isExternallyScored($itemData['data'] ?? []);

                    $items[$item->getIdentifier()] = $itemData['data'];
                }
            }
        }

        return $items;
    }

    protected function getDefinition(QtiRunnerServiceContext $context): AssessmentTest
    {
        return taoQtiTest_helpers_Utils::getTestDefinition($context->getTestCompilationUri());
    }

    protected function getServiceContext($deliveryExecutionId): QtiRunnerServiceContext
    {
        $deliveryExecution = $this->deliveryExecutionService->getDeliveryExecution($deliveryExecutionId);

        $compilation = $this->deliveryContainerService->getTestCompilation($deliveryExecution);
        $testId = $this->deliveryContainerService->getTestDefinition($deliveryExecution);

        return $this->qtiRunnerService->getServiceContext($testId, $compilation, $deliveryExecutionId);
    }

    private function isExternallyScored(array $data): bool
    {
        foreach ($data['outcomes'] ?? [] as $outcome) {
            if (isset($outcome['attributes']['externalScored'])) {
                return true;
            }
        }

        return false;
    }
}

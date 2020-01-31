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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoResultServer\models\Mapper;

use common_exception_InvalidArgumentType;
use common_exception_NotImplemented;
use LogicException;
use oat\dtms\DateTime;
use oat\oatbox\service\ConfigurableService;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\results\AssessmentResult;
use qtism\data\results\ItemResult;
use qtism\data\results\ItemVariable;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\ResultOutcomeVariable;
use qtism\data\results\ResultResponseVariable;
use qtism\data\results\ResultTemplateVariable;
use qtism\data\results\SessionIdentifier;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use taoResultServer_models_classes_OutcomeVariable;
use taoResultServer_models_classes_ResponseVariable;
use taoResultServer_models_classes_Variable;

class ResultMapper extends ConfigurableService
{
    /** @var AssessmentResult */
    protected $assessmentResult;

    /**
     * Initialize Mapper with qti-sqk AssessmentResult
     *
     * @param AssessmentResult $assessmentResult
     * @return ResultMapper
     */
    public function loadSource(AssessmentResult $assessmentResult)
    {
        $this->assessmentResult = $assessmentResult;
        return $this;
    }

    /**
     * Get a formatted array of AssessmentContext object
     *
     * @return array
     * @throws LogicException If AssessmentResult is not loaded
     */
    public function getContext()
    {
        $this->assertIsLoaded();

        $context = $this->assessmentResult->getContext();

        $sessionIdentifiers = [];
        if ($context->hasSessionIdentifiers()) {
            $contextSessionIdentifiers = iterator_to_array($context->getSessionIdentifiers());
            /** @var SessionIdentifier $sessionIdentifier */
            foreach ($contextSessionIdentifiers as $sessionIdentifier) {
                $sessionIdentifiers[$sessionIdentifier->getIdentifier()->getValue()] = $sessionIdentifier->getSourceID()->getValue();
            }
        }

        $sourcedId = '';
        if ($context->hasSourcedId()) {
            $sourcedId = $context->getSourcedId()->getValue();
        }

        return [
            'sourcedId' => $sourcedId,
            'sessionIdentifiers' => $sessionIdentifiers
        ];
    }

    /**
     * Get test variables of result
     * - Loop on all test itemVariables
     * - Build tao outcome/response variables from qti sk variables
     *
     * @return array
     * @throws common_exception_NotImplemented
     * @throws common_exception_InvalidArgumentType
     * @throws LogicException If AssessmentResult is not loaded
     */
    public function getTestVariables()
    {
        $this->assertIsLoaded();

        if (!$this->assessmentResult->hasTestResult()) {
            return [];
        }

        $testResult = $this->assessmentResult->getTestResult();

        if (!$testResult->hasItemVariables()) {
            return [];
        }

        return [
            $testResult->getIdentifier()->getValue() => $this->createVariables($testResult->getItemVariables(), $testResult->getDatestamp())
        ];
    }

    /**
     * Get item variables of result
     * - Loop on all itemResult itemVariables
     * - Build tao outcome/response variables from qti sk variables
     *
     * @return array
     * @throws common_exception_NotImplemented
     * @throws common_exception_InvalidArgumentType
     * @throws LogicException If AssessmentResult is not loaded
     */
    public function getItemVariables()
    {
        $this->assertIsLoaded();

        if (!$this->assessmentResult->hasItemResults()) {
            return [];
        }

        $itemResults = $this->assessmentResult->getItemResults();
        $itemVariables = [];

        /** @var ItemResult $itemResult */
        foreach ($itemResults as $itemResult) {
            if (!$itemResult->hasItemVariables()) {
                continue;
            }
            $itemVariables[$itemResult->getIdentifier()->getValue()] = $this->createVariables($itemResult->getItemVariables(), $itemResult->getDatestamp());
        }

        return $itemVariables;
    }

    /**
     * Create tao variables from ItemVariableCollection
     * - Based on itemVariable class, create associated tao variable
     * - Set variable epoch with itemResult datetime
     *
     * @param ItemVariableCollection $itemVariables
     * @param DateTime $datetime
     * @return taoResultServer_models_classes_Variable[]
     * @throws common_exception_NotImplemented If itemVariable is not outcome|response (e.g. template)
     * @throws common_exception_InvalidArgumentType
     */
    protected function createVariables(ItemVariableCollection $itemVariables, DateTime $datetime)
    {
        $variables = [];
        $i = 0;
        foreach ($itemVariables as $itemVariable) {
            $i++;
            switch (get_class($itemVariable)) {
                case ResultOutcomeVariable::class:
                    $variable = $this->createOutcomeVariable($itemVariable);
                    break;

                case ResultResponseVariable::class:
                    $variable = $this->createResponseVariable($itemVariable);
                    break;

                case ResultTemplateVariable::class:
                default:
                    throw new common_exception_NotImplemented('Qti Result parser cannot deals with "' . get_class($itemVariable) . '".');
                    break;
            }

            $datetime->modify('+' . $i . ' microsecond');
            $variable->setEpoch(number_format($datetime->getMicroseconds(true), 8) . ' ' . $datetime->format('U'));
            $variables[] = $variable;
        }

        return $variables;
    }

    /**
     * Initialize a taoResultServer_models_classes_Variable based on ItemVariable
     * - including identifier, cardinality, baseType
     *
     * @param ItemVariable $itemVariable
     * @param taoResultServer_models_classes_Variable $variable
     * @return taoResultServer_models_classes_Variable
     * @throws common_exception_InvalidArgumentType
     */
    protected function createVariableFromItemVariable(ItemVariable $itemVariable, taoResultServer_models_classes_Variable $variable)
    {
        $variable->setIdentifier((string) $itemVariable->getIdentifier());
        $variable->setCardinality(Cardinality::getNameByConstant($itemVariable->getCardinality()));

        if (null !== $itemVariable->getBaseType()) {
            $variable->setBaseType(BaseType::getNameByConstant($itemVariable->getBaseType()));
        }

        return $variable;
    }

    /**
     * Transfer all attributes of a ResultOutcomeVariable to taoResultServer_models_classes_OutcomeVariable
     *
     * @param ResultOutcomeVariable $itemVariable
     * @return taoResultServer_models_classes_OutcomeVariable
     * @throws common_exception_InvalidArgumentType
     * @todo Implements Long Interpretation
     * @todo Implements Mastery Value
     * @todo Implements multiple Values
     * @todo Implements View
     * @todo Implements Interpretation
     */
    protected function createOutcomeVariable(ResultOutcomeVariable $itemVariable)
    {
        /** @var taoResultServer_models_classes_OutcomeVariable $variable */
        $variable = $this->createVariableFromItemVariable(
            $itemVariable,
            new taoResultServer_models_classes_OutcomeVariable()
        );

        if ($itemVariable->hasValues()) {
            $variable->setValue($this->serializeValueCollection($itemVariable->getValues()));
        }

        if ($itemVariable->hasNormalMaximum()) {
            $variable->setNormalMaximum((string)  $itemVariable->getNormalMaximum()->getValue());
        }

        if ($itemVariable->hasNormalMinimum()) {
            $variable->setNormalMinimum((string)  $itemVariable->getNormalMinimum()->getValue());
        }

        if ($itemVariable->hasView()) {
            $this->logInfo('Qti Result Parser does not handle Outcome View');
        }

        if ($itemVariable->hasInterpretation()) {
            $this->logInfo('Qti Result Parser does not handle Outcome Interpretation');
        }

        if ($itemVariable->hasLongInterpretation()) {
            $this->logInfo('Qti Result Parser does not handle Outcome Long Interpretation');
        }

        if ($itemVariable->hasMasteryValue()) {
            $this->logInfo('Qti Result Parser does not handle Outcome Mastery Value');
        }

        return $variable;
    }

    /**
     * Transfer all attributes of a ResultResponseVariable to taoResultServer_models_classes_ResponseVariable
     *
     * @param ResultResponseVariable $itemVariable
     * @return taoResultServer_models_classes_ResponseVariable
     * @throws common_exception_InvalidArgumentType
     * @todo Deals with multiple CandidateResponse Values
     * @todo Deals with multiple CorrectResponse Values
     * @todo Implements Choice Sequence
     */
    protected function createResponseVariable(ResultResponseVariable $itemVariable)
    {
        /** @var taoResultServer_models_classes_ResponseVariable $variable */
        $variable = $this->createVariableFromItemVariable(
            $itemVariable,
            new taoResultServer_models_classes_ResponseVariable()
        );

        if ($itemVariable->getCandidateResponse()->hasValues()) {
            $variable->setCandidateResponse($this->serializeValueCollection($itemVariable->getCandidateResponse()->getValues()));
        }

        if ($itemVariable->hasCorrectResponse()) {
            $variable->setCorrectResponse($this->serializeValueCollection($itemVariable->getCorrectResponse()->getValues()));
        }

        if ($itemVariable->hasChoiceSequence()) {
            $this->logInfo('Qti Result Parser does not handle Response ChoiceSequence');
        }

        return $variable;
    }

    /**
     * Helper to serialize qti valueCollection to string
     *
     * @param ValueCollection $valueCollection
     * @return string
     */
    protected function serializeValueCollection(ValueCollection $valueCollection)
    {
        $values = array_map(
            function (Value $value) {
                return $value->getValue();
            },
            iterator_to_array($valueCollection)
        );

        return implode(';', $values);
    }

    /**
     * @throws LogicException If assessmentResult is not loaded
     */
    protected function assertIsLoaded()
    {
        if (!$this->assessmentResult) {
            throw new LogicException('Result parser is not loaded and cannot parse QTI XML result.');
        }
    }
}

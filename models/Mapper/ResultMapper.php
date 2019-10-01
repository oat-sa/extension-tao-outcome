<?php


namespace oat\taoResultServer\models\Mapper;

use oat\oatbox\service\ConfigurableService;
use qtism\common\enums\Cardinality;
use qtism\data\results\AssessmentResult;
use qtism\data\results\ItemResult;
use qtism\data\results\ItemVariable;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\ResultOutcomeVariable;
use qtism\data\results\ResultResponseVariable;
use qtism\data\results\ResultTemplateVariable;

class ResultMapper extends ConfigurableService
{
    /** @var AssessmentResult */
    protected $assessmentResult;

    public function loadSource(AssessmentResult $assessmentResult)
    {
        $this->assessmentResult = $assessmentResult;
        return $this;
    }

    public function getContext()
    {
        $this->assertIsLoaded();
        $context = $this->assessmentResult->getContext();

        return [
            'sourcedId' => $context->getSourcedId(),
            'sessionIdentifiers' => iterator_to_array($context->getSessionIdentifiers()),
        ];
    }

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

        return $this->createVariables($testResult->getItemVariables());
    }

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
            /** @var ItemVariable $itemVariable */
            $itemVariables = array_merge($itemVariables, $this->createVariables($itemResult->getItemVariables()));
        }

        return $itemVariables;
    }

    protected function createVariables(ItemVariableCollection $itemVariables)
    {
        $variables = [];
        foreach ($itemVariables as $itemVariable) {
            switch (get_class($itemVariable)) {

                case ResultOutcomeVariable::class:
                    $variables[] = $this->createOutcomeVariable($itemVariable);
                    break;

                case ResultResponseVariable::class:
                    $variables[] = $this->createResponseVariable($itemVariable);
                    break;

                case ResultTemplateVariable::class:
                default:
                    throw new \common_exception_NotImplemented('Qti Result parser cannot deals with "' . get_class($itemVariable) . '".');
                    break;
            }
        }

        return $variables;
    }

    protected function createVariableFromItemVariable(ItemVariable $itemVariable, \taoResultServer_models_classes_Variable $variable)
    {
        $variable->setIdentifier((string) $itemVariable->getIdentifier());
        $variable->setCardinality(Cardinality::getNameByConstant($itemVariable->getCardinality()));
        $variable->setBaseType($itemVariable->getBaseType());

        return $variable;
    }

    /**
     * @todo Implements multiple Values
     * @todo Implements View
     * @todo Implements Interpretation
     * @todo Implements Long Interpretation
     * @todo Implements Mastery Value
     *
     * @param ResultOutcomeVariable $itemVariable
     * @return \taoResultServer_models_classes_OutcomeVariable
     */
    protected function createOutcomeVariable(ResultOutcomeVariable $itemVariable)
    {
        /** @var \taoResultServer_models_classes_OutcomeVariable $variable */
        $variable = $this->createVariableFromItemVariable(
            $itemVariable,
            new \taoResultServer_models_classes_OutcomeVariable()
        );

        if ($itemVariable->hasValues()) {
            $variable->setValue((string) $itemVariable->getValues()[0]->getValue());
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

//        $variable->setEpoch();

        return $variable;
    }

    /**
     * @todo Deals with multiple CandidateResponse Values
     * @todo Deals with multiple CorrectResponse Values
     * @todo Implements Choice Sequence
     *
     * @param ResultResponseVariable $itemVariable
     * @return \taoResultServer_models_classes_ResponseVariable
     */
    protected function createResponseVariable(ResultResponseVariable $itemVariable)
    {
        /** @var \taoResultServer_models_classes_ResponseVariable $variable */
        $variable = $this->createVariableFromItemVariable(
            $itemVariable,
            new \taoResultServer_models_classes_ResponseVariable()
        );

        $values = iterator_to_array($itemVariable->getCandidateResponse()->getValues());
        foreach ($values as &$value) {
            $value = $value->getValue();
        }
        $variable->setCandidateResponse((string)  implode('|', $values));

        if ($itemVariable->hasCorrectResponse()) {
            $variable->setCorrectResponse((string)  $itemVariable->getCorrectResponse()->getValues()[0]->getValue());
        }

        if ($itemVariable->hasChoiceSequence()) {
            $this->logInfo('Qti Result Parser does not handle Outcome Mastery Value');
        }

        return $variable;
    }

    protected function assertIsLoaded()
    {
        if (!$this->assessmentResult) {
            throw new \LogicException('Result parser is not loaded and cannot parse QTI XML result.');
        }
    }
}
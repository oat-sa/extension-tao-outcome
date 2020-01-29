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
 * Copyright (c) 2013 Open Assessment Technologies
 *
 *
 */

use \oat\tao\model\routing\AnnotationReader\security;

/**
 *
 * A session for a particular delivery execution/session on the corresponding result server
 * Statefull api for results submission from the client
 *
 *
 * @package taoResultServer
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class taoResultServer_actions_ResultServerStateFull extends tao_actions_SaSModule
{

    protected $service;

    /**
     * constructor: initialize the service and the default data
     * @security("hide");
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = $this->getClassService();
    }

    protected function returnFailure(Exception $exception)
    {
        $data = [];
        $data['success'] = false;
        $data['errorCode'] = $exception->getCode();
        $data['errorMsg'] = ($exception instanceof common_exception_UserReadableException) ? $exception->getUserMessage() : $exception->getMessage();
        $data['version'] = TAO_VERSION;
        echo json_encode($data);
        exit(0);
    }

    protected function returnSuccess($rawData = "")
    {
        $data = [];
        $data['success'] = true;
        $data['data'] = $rawData;
        $data['version'] = TAO_VERSION;

        echo json_encode($data);
        exit(0);
    }

    /**
     * @author  "Patrick Plichart, <patrick@taotesting.com>"
     */
    public function storeItemVariableSet()
    {
        $variables = [];
        $item = $this->hasRequestParameter("itemId") ? $this->getRequestParameter("itemId") : "undefined";
        $callIdItem = $this->hasRequestParameter("serviceCallId") ? $this->getRequestParameter("serviceCallId") : "undefined";
        $test = $this->hasRequestParameter("testId") ? $this->getRequestParameter("testId") : "undefined";
        if ($this->hasRequestParameter("outcomeVariables")) {
            $outcomeVariables = $this->getRequestParameter("outcomeVariables");
            foreach ($outcomeVariables as $variableName => $outcomeValue) {
                $outComeVariable = new taoResultServer_models_classes_OutcomeVariable();
                //$outComeVariable->setBaseType("int");
                $outComeVariable->setCardinality("single");
                $outComeVariable->setIdentifier($variableName);
                $outComeVariable->setValue($outcomeValue);
                $variables[] = $outComeVariable;
            }
        }
        if ($this->hasRequestParameter("responseVariables")) {
            $responseVariables = $this->getRequestParameter("responseVariables");
            foreach ($responseVariables as $variableName => $responseValue) {
                $responseVariable = new taoResultServer_models_classes_ResponseVariable();
                //$responseVariable->setBaseType("int");
                //$responseVariable->setCardinality("single");
                $responseVariable->setIdentifier($variableName);
                $responseVariable->setCandidateResponse($responseValue);
                //$responseVariable->setCorrectResponse(true);
                $variables[] = $responseVariable;
            }
        }
        if ($this->hasRequestParameter("traceVariables")) {
            $traceVariables = $this->getRequestParameter("outcomeVariables");
            foreach ($traceVariables as $variableName => $traceValue) {
                $traceVariable = new taoResultServer_models_classes_TraceVariable();
                //$outComeVariable->setBaseType("int");
                $traceVariable->setIdentifier($variableName);
                $traceVariable->setTrace($traceValue);
                $variables[] = $traceVariable;
            }
        }

        try {
            $data = $this->service->storeItemVariableSet($test, $item, $variables, $callIdItem);
        } catch (exception $e) {
            $this->returnFailure($e);
        }
        return $this->returnSuccess($data);
    }
}

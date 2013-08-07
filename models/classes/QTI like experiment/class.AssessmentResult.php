<?php
/*
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
 * Copyright (c) 2013 (original work) Open Assessment Technologies S.A.
 *
 */

/**
 * Description of taoResultServer_models_classes_assessmentResult
 *
 * @author plichart
 */

/**
 * An Assessment Result is used to report the results of a candidate's interaction
 * with a test and/or one or more items attempted. Information about the test is optional,
 *  in some systems it may be possible to interact with items that are not organized into a test at all. For example, items that are organized with learning resources and presented individually in a formative context.
 */
class taoResultServer_models_classes_AssessmentResult {

    /**
     * @var taoResultServer_models_classes_context
     */
    private $context;
    /**
     * When a test result is given the following item results must relate only to items that were selected for presentation as part of the corresponding test session. Furthermore, all items selected for presentation should be reported with a corresponding itemResult.
     * @var taoResultServer_models_classes_testResult
     */
    private $testResult;
    /**
     *
     * @var array taoResultServer_models_classes_itemResult
     */
    private $itemResults;

    public function setContext(taoResultServer_models_classes_Context $context) {
	$this->context = $context;
    }
    public function getContext(){
	return $this->context;
    }
    public function setTestResult(taoResultServer_models_classes_TestResult $testResult){
	$this->testResult = $testResult;
    }
    public function getTestResult(){
	return $this->testResult;
    }

}

?>